<?php
/**
 * Kontakt-besked threading.
 *
 * Hver kontakt_besked får et tråd-ID (S247-<ID>-<token>). ID'et indsættes
 * i subject på alle udgående mails relateret til besked'en. Når kunden
 * svarer, kan kontakt-inbound.php finde tråd-ID'et og tilknytte svaret
 * til den rigtige besked som en kontakt_reply-post.
 *
 * Token er en HMAC af post-ID + wp_salt('auth'), så admin kan validere
 * at en indkommende mail's thread-ID ikke er opfundet.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── Thread ID ───────── */

/**
 * Udled tråd-token (6 hex-tegn) for en kontakt_besked.
 */
function studie247_thread_token( $besked_id ) {
	return substr( hash_hmac( 'sha256', 'thread|' . (int) $besked_id, wp_salt( 'auth' ) ), 0, 6 );
}

/**
 * Byg det fulde tråd-ID, fx "S247-42-a8c1d2".
 */
function studie247_thread_id( $besked_id ) {
	return sprintf( 'S247-%d-%s', (int) $besked_id, studie247_thread_token( $besked_id ) );
}

/**
 * Parse et tråd-ID fra en string (subject, header, osv.).
 * Returnerer post-ID eller 0 hvis ikke valid.
 */
function studie247_thread_parse( $text ) {
	if ( ! preg_match( '/S247-(\d+)-([a-f0-9]{6})/i', (string) $text, $m ) ) { return 0; }
	$id    = (int) $m[1];
	$token = strtolower( $m[2] );
	if ( studie247_thread_token( $id ) !== $token ) { return 0; }
	if ( 'kontakt_besked' !== get_post_type( $id ) ) { return 0; }
	return $id;
}

/**
 * Prepend tråd-ID til en subject, hvis det ikke allerede er der.
 */
function studie247_thread_subject( $subject, $besked_id ) {
	$tid = studie247_thread_id( $besked_id );
	if ( false !== strpos( $subject, $tid ) ) { return $subject; }
	return sprintf( '[#%s] %s', $tid, $subject );
}

/* ───────── Outbound: tilføj tråd-ID til subject for contact-mails ───── */

/**
 * Filter `wp_mail` på subject for at indsætte tråd-ID når det er muligt.
 * Dette kræver at afsender sætter filter `s247_active_thread` (se
 * studie247_with_thread()) før wp_mail kaldes.
 */
function studie247_active_thread_id() {
	return (int) apply_filters( 's247_active_thread', 0 );
}

/**
 * Hjælper: kør en callback med en aktiv tråd-kontekst, så mails sendt
 * indeni får subject-prefix og Message-ID tilknyttet tråd'en.
 */
function studie247_with_thread( $besked_id, $callback ) {
	$besked_id = (int) $besked_id;
	$add = function () use ( $besked_id ) { return $besked_id; };
	add_filter( 's247_active_thread', $add );
	add_filter( 'wp_mail', 'studie247_thread_inject_subject' );
	add_action( 'phpmailer_init', 'studie247_thread_inject_message_id', 20 );
	try {
		$callback();
	} finally {
		remove_filter( 's247_active_thread', $add );
		remove_filter( 'wp_mail', 'studie247_thread_inject_subject' );
		remove_action( 'phpmailer_init', 'studie247_thread_inject_message_id', 20 );
	}
}

/**
 * `wp_mail`-filter: prepend tråd-ID til subject.
 */
function studie247_thread_inject_subject( $args ) {
	$tid = studie247_active_thread_id();
	if ( $tid && isset( $args['subject'] ) ) {
		$args['subject'] = studie247_thread_subject( $args['subject'], $tid );
	}
	return $args;
}

/**
 * `phpmailer_init`: sæt en stabil Message-ID så vi kan matche kundens
 * In-Reply-To/References header tilbage til tråd'en hvis mail-klienten
 * fjerner subject-prefix.
 */
function studie247_thread_inject_message_id( $phpmailer ) {
	$tid = studie247_active_thread_id();
	if ( ! $tid ) { return; }
	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 's247.dk';
	$mid  = sprintf( 's247-thread-%d-%s-%s@%s',
		$tid, studie247_thread_token( $tid ), wp_generate_password( 8, false, false ), $host );
	$phpmailer->MessageID = '<' . $mid . '>';
	// Gem Message-ID som meta så inbound-parseren kan matche
	// In-Reply-To → thread.
	$seen = get_post_meta( $tid, '_s247_thread_messageids', true );
	if ( ! is_array( $seen ) ) { $seen = array(); }
	$seen[] = $mid;
	// Hold listen rimeligt kort.
	if ( count( $seen ) > 50 ) { $seen = array_slice( $seen, -50 ); }
	update_post_meta( $tid, '_s247_thread_messageids', $seen );
}

/* ───────── CPT: kontakt_reply ───────── */
add_action( 'init', function () {
	register_post_type( 'kontakt_reply', array(
		'labels'       => array(
			'name'          => __( 'Besked-svar', 'studie247' ),
			'singular_name' => __( 'Besked-svar', 'studie247' ),
		),
		'public'       => false,
		'show_ui'      => false, // vises kun i dashboardets tråd
		'show_in_rest' => false,
		'supports'     => array( 'title', 'editor', 'custom-fields', 'page-attributes' ),
		'hierarchical' => true, // tillader post_parent
	) );
} );

/**
 * Gem et tråd-svar som kontakt_reply tilknyttet en besked.
 *
 * @param int    $besked_id  Parent kontakt_besked ID.
 * @param string $direction  'inbound' (fra kunde) eller 'outbound' (fra os).
 * @param array  $data       name, email, subject, body_text, body_html, message_id, in_reply_to, raw_headers.
 * @return int Reply post-ID (0 ved fejl).
 */
function studie247_thread_save_reply( $besked_id, $direction, $data ) {
	$besked_id = (int) $besked_id;
	if ( 'kontakt_besked' !== get_post_type( $besked_id ) ) { return 0; }
	$direction = in_array( $direction, array( 'inbound', 'outbound' ), true ) ? $direction : 'inbound';

	$title = sprintf( '%s — %s', ucfirst( $direction ), gmdate( 'Y-m-d H:i' ) );
	if ( ! empty( $data['subject'] ) ) { $title .= ' — ' . wp_trim_words( $data['subject'], 10 ); }

	$content = (string) ( $data['body_text'] ?? '' );
	if ( ! $content && ! empty( $data['body_html'] ) ) {
		$content = wp_strip_all_tags( $data['body_html'] );
	}

	$id = wp_insert_post( array(
		'post_type'    => 'kontakt_reply',
		'post_status'  => 'publish',
		'post_parent'  => $besked_id,
		'post_title'   => $title,
		'post_content' => $content,
	), true );
	if ( ! $id || is_wp_error( $id ) ) { return 0; }

	update_post_meta( $id, '_s247_direction',   $direction );
	if ( ! empty( $data['name'] ) )        { update_post_meta( $id, '_s247_name',        sanitize_text_field( $data['name'] ) ); }
	if ( ! empty( $data['email'] ) )       { update_post_meta( $id, '_s247_email',       sanitize_email( $data['email'] ) ); }
	if ( ! empty( $data['subject'] ) )     { update_post_meta( $id, '_s247_subject',     sanitize_text_field( $data['subject'] ) ); }
	if ( ! empty( $data['body_html'] ) )   { update_post_meta( $id, '_s247_body_html',   wp_kses_post( $data['body_html'] ) ); }
	if ( ! empty( $data['message_id'] ) )  { update_post_meta( $id, '_s247_message_id',  sanitize_text_field( $data['message_id'] ) ); }
	if ( ! empty( $data['in_reply_to'] ) ) { update_post_meta( $id, '_s247_in_reply_to', sanitize_text_field( $data['in_reply_to'] ) ); }

	// Marker tråd'en som "uafsluttet" når der kommer et indgående svar.
	if ( 'inbound' === $direction ) {
		update_post_meta( $besked_id, '_s247_msg_handled', '' );
		update_post_meta( $besked_id, '_s247_has_unread_reply', '1' );
	}
	update_post_meta( $besked_id, '_s247_last_activity', current_time( 'mysql' ) );

	if ( function_exists( 'studie247_audit_log' ) ) {
		studie247_audit_log(
			sprintf( __( 'Tråd-svar (%s) tilføjet til besked #%d', 'studie247' ), $direction, $besked_id ),
			$id,
			'kontakt_reply'
		);
	}

	return (int) $id;
}

/**
 * Send et admin-svar til kunden og gem det som outbound reply.
 *
 * @param int    $besked_id         Parent besked-ID.
 * @param string $body_raw          Den rå tekst/HTML som admin skrev (gemmes i tråd).
 * @param string $subject_override  Override subject (default: 'Re: <post-titel>').
 */
function studie247_thread_send_outbound( $besked_id, $body_raw, $subject_override = '' ) {
	$besked_id = (int) $besked_id;
	$email     = get_post_meta( $besked_id, '_s247_email', true );
	if ( ! $email || ! is_email( $email ) ) { return new WP_Error( 's247_no_email', 'Ingen gyldig modtager-mail på besked.' ); }

	$base_subject = $subject_override ?: ( 'Re: ' . get_the_title( $besked_id ) );
	$full_subject = studie247_thread_subject( $base_subject, $besked_id );

	// Pak admin'ens rå svar ind i brand-HTML — bruges KUN til afsendelse.
	$mail_html = studie247_thread_render_outbound_html( $besked_id, $body_raw );

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: Studie 247 <info@s247.dk>',
		'Reply-To: info@s247.dk',
	);

	$sent = false;
	studie247_with_thread( $besked_id, function () use ( $email, $full_subject, $mail_html, $headers, &$sent ) {
		$sent = wp_mail( $email, $full_subject, $mail_html, $headers );
	} );

	// Gem kun admin'ens oprindelige svar (ikke wrapperen) i tråd'en,
	// så dashboardet viser "fra os"-bobler på samme måde som kundens.
	$reply_id = studie247_thread_save_reply( $besked_id, 'outbound', array(
		'name'      => 'Studie 247',
		'email'     => 'info@s247.dk',
		'subject'   => $full_subject,
		'body_html' => $body_raw,
		'body_text' => wp_strip_all_tags( $body_raw ),
	) );

	if ( ! $sent ) {
		return new WP_Error( 's247_send_failed', 'wp_mail returnerede false.' );
	}
	return $reply_id;
}

/* ───────── POST-handler: admin sender svar fra dashboardet ───────── */
add_action( 'init', function () {
	if ( empty( $_POST['s247_dash_reply'] ) ) { return; }
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) { return; }
	$mid = (int) $_POST['s247_dash_reply'];
	if ( ! $mid || ! check_admin_referer( 's247_dash_reply_' . $mid ) ) { return; }
	if ( 'kontakt_besked' !== get_post_type( $mid ) ) { return; }

	$body = isset( $_POST['s247_reply_body'] ) ? wp_kses_post( wp_unslash( $_POST['s247_reply_body'] ) ) : '';
	if ( '' === trim( wp_strip_all_tags( $body ) ) ) {
		wp_safe_redirect( add_query_arg(
			array( 'view' => 'messages', 'message' => $mid, 'reply_err' => 'empty' ),
			home_url( '/dashboard/' )
		) );
		exit;
	}

	// Wrap svar i en simpel HTML-skabelon så mailen ligner brandet.
	$wrapped = studie247_thread_render_outbound_html( $mid, $body );

	$r = studie247_thread_send_outbound( $mid, $wrapped );
	$q = is_wp_error( $r )
		? array( 'reply_err' => 'send', 'reply_msg' => rawurlencode( $r->get_error_message() ) )
		: array( 'reply_ok'  => '1' );

	// Ved afsendelse markeres tråd'en som håndteret og "seneste svar læst".
	if ( ! is_wp_error( $r ) ) {
		delete_post_meta( $mid, '_s247_has_unread_reply' );
	}

	wp_safe_redirect( add_query_arg( array_merge(
		array( 'view' => 'messages', 'message' => $mid ),
		$q
	), home_url( '/dashboard/' ) ) );
	exit;
} );

/**
 * Pak et admin-svar ind i fuld brand-HTML — samme layout som
 * kontakt-kvitteringen, så kunden genkender afsenderen.
 */
function studie247_thread_render_outbound_html( $besked_id, $body_html ) {
	$name       = get_post_meta( $besked_id, '_s247_name', true );
	$greet      = $name ? sprintf( 'Hej <strong>%s</strong>,', esc_html( $name ) ) : 'Hej,';
	$body_safe  = wp_kses_post( $body_html );
	$orig_topic = esc_html( get_post_meta( $besked_id, '_s247_side', true ) ?: get_post_meta( $besked_id, '_s247_topic', true ) ?: '' );

	$context_row = '';
	if ( $orig_topic ) {
		$context_row = '<tr><td style="padding:0 32px 20px;">'
			. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4E9DD;border-radius:10px;">'
			. '<tr><td style="padding:14px 22px;">'
			. '<div style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:#9E2B25;font-weight:700;margin-bottom:6px;">Din henvendelse</div>'
			. '<div style="font-size:14px;color:#282828;font-weight:600;">' . $orig_topic . '</div>'
			. '</td></tr></table></td></tr>';
	}

	return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4E9DD;padding:32px 16px;font-family:'Helvetica Neue',Arial,sans-serif;color:#282828;">
<tr><td align="center">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#FBF5EC;border:1px solid rgba(40,40,40,0.1);border-radius:14px;overflow:hidden;">
    <tr>
      <td style="background:#282828;padding:28px 32px;text-align:left;">
        <span style="color:#F4E9DD;font-size:13px;letter-spacing:0.18em;text-transform:uppercase;font-weight:600;">Studie 247</span>
        <h1 style="margin:6px 0 0;color:#F4E9DD;font-size:28px;line-height:1.15;font-family:Georgia,serif;font-weight:400;">
          Et <em style="color:#E89B6B;">svar</em> til dig
        </h1>
      </td>
    </tr>
    <tr>
      <td style="padding:32px 32px 8px;">
        <p style="margin:0 0 16px;font-size:16px;line-height:1.55;">{$greet}</p>
        <div style="font-size:15px;line-height:1.7;color:#282828;">{$body_safe}</div>
      </td>
    </tr>
    {$context_row}
    <tr>
      <td style="padding:0 32px 24px;">
        <p style="margin:0;font-size:13px;line-height:1.55;color:#666;font-style:italic;border-left:3px solid #E89B6B;padding:4px 0 4px 14px;">
          Har du flere spørgsmål, så svar bare på denne mail — vi samler tråden hos os.
        </p>
      </td>
    </tr>
    <tr>
      <td style="background:#282828;padding:22px 32px;text-align:center;">
        <p style="margin:0;color:#F4E9DD;font-size:13px;line-height:1.6;">
          Studie 247 · <a href="mailto:info@s247.dk" style="color:#E89B6B;text-decoration:none;">info@s247.dk</a><br>
          <a href="https://s247.dk/" style="color:#F4E9DD;text-decoration:underline;opacity:0.75;">s247.dk</a>
        </p>
      </td>
    </tr>
  </table>
</td></tr>
</table>
HTML;
}

/**
 * Hent alle svar i en tråd (nyeste øverst).
 */
function studie247_thread_replies( $besked_id ) {
	return get_posts( array(
		'post_type'      => 'kontakt_reply',
		'post_parent'    => (int) $besked_id,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
}
