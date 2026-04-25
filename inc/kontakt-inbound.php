<?php
/**
 * Kontakt-inbound: IMAP-poller der henter kundesvar og tilknytter dem
 * til den rette kontakt_besked via tråd-ID.
 *
 * Kører hver 5. minut via wp-cron. Kan også triggers manuelt via admin:
 *   Værktøjer → Kontakt-inbound → "Hent svar nu".
 *
 * IMAP-credentials hentes fra wp-config.php:
 *   define( 'S247_IMAP_HOST', 'mail.s247.dk' );   // default: S247_SMTP_HOST
 *   define( 'S247_IMAP_PORT', 993 );
 *   define( 'S247_IMAP_USER', 'info@s247.dk' );   // default: S247_SMTP_USER
 *   define( 'S247_IMAP_PASS', '...' );            // default: S247_SMTP_PASS
 *   define( 'S247_IMAP_MAILBOX', 'INBOX' );
 *
 * Hvis IMAP_HOST/USER/PASS ikke er defineret, genbruger vi SMTP-værdierne.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saml imap-credentials fra wp-config.php, med SMTP som fallback.
 */
function studie247_imap_config() {
	$host = defined( 'S247_IMAP_HOST' ) ? S247_IMAP_HOST : ( defined( 'S247_SMTP_HOST' ) ? S247_SMTP_HOST : '' );
	$port = defined( 'S247_IMAP_PORT' ) ? (int) S247_IMAP_PORT : 993;
	$user = defined( 'S247_IMAP_USER' ) ? S247_IMAP_USER : ( defined( 'S247_SMTP_USER' ) ? S247_SMTP_USER : '' );
	$pass = defined( 'S247_IMAP_PASS' ) ? S247_IMAP_PASS : ( defined( 'S247_SMTP_PASS' ) ? S247_SMTP_PASS : '' );
	$mbx  = defined( 'S247_IMAP_MAILBOX' ) ? S247_IMAP_MAILBOX : 'INBOX';
	return compact( 'host', 'port', 'user', 'pass', 'mbx' );
}

/**
 * Åbn en IMAP-forbindelse. Returnerer resource eller WP_Error.
 */
function studie247_imap_open() {
	if ( ! function_exists( 'imap_open' ) ) {
		return new WP_Error( 's247_no_imap', 'PHP imap-extension er ikke installeret.' );
	}
	$cfg = studie247_imap_config();
	if ( ! $cfg['host'] || ! $cfg['user'] || ! $cfg['pass'] ) {
		return new WP_Error( 's247_imap_config', 'IMAP-credentials mangler i wp-config.php.' );
	}
	$ref = sprintf( '{%s:%d/imap/ssl}%s', $cfg['host'], $cfg['port'], $cfg['mbx'] );
	$mbx = @imap_open( $ref, $cfg['user'], $cfg['pass'], 0, 1 );
	if ( ! $mbx ) {
		return new WP_Error( 's247_imap_connect', 'IMAP-login fejlede: ' . imap_last_error() );
	}
	return $mbx;
}

/**
 * Find plain- eller HTML-body i en IMAP-struktur. Returnerer array med 'text' og 'html'.
 */
function studie247_imap_extract_body( $mbx, $uid, $structure, $prefix = '' ) {
	$out = array( 'text' => '', 'html' => '' );
	if ( empty( $structure->parts ) ) {
		$data = imap_fetchbody( $mbx, $uid, $prefix ?: '1', FT_UID );
		$data = studie247_imap_decode_body( $data, $structure );
		$subtype = strtolower( $structure->subtype ?? '' );
		if ( 'html' === $subtype )       { $out['html'] = $data; }
		elseif ( 'plain' === $subtype )  { $out['text'] = $data; }
		else                             { $out['text'] = $data; }
		return $out;
	}
	foreach ( $structure->parts as $i => $part ) {
		$part_no = $prefix ? $prefix . '.' . ( $i + 1 ) : (string) ( $i + 1 );
		$sub = strtolower( $part->subtype ?? '' );
		if ( ! empty( $part->parts ) ) {
			$nested = studie247_imap_extract_body( $mbx, $uid, $part, $part_no );
			if ( ! $out['text'] && $nested['text'] ) { $out['text'] = $nested['text']; }
			if ( ! $out['html'] && $nested['html'] ) { $out['html'] = $nested['html']; }
			continue;
		}
		$data = imap_fetchbody( $mbx, $uid, $part_no, FT_UID );
		$data = studie247_imap_decode_body( $data, $part );
		if ( 'html' === $sub && ! $out['html'] )     { $out['html'] = $data; }
		elseif ( 'plain' === $sub && ! $out['text'] ){ $out['text'] = $data; }
	}
	return $out;
}

/**
 * Dekod body baseret på encoding + charset.
 */
function studie247_imap_decode_body( $data, $part ) {
	$enc = (int) ( $part->encoding ?? 0 );
	switch ( $enc ) {
		case 3: $data = base64_decode( $data ); break;       // ENCBASE64
		case 4: $data = quoted_printable_decode( $data ); break; // ENCQUOTEDPRINTABLE
	}
	// Konvertér charset til UTF-8 hvis muligt.
	if ( ! empty( $part->parameters ) ) {
		foreach ( $part->parameters as $p ) {
			if ( strtolower( $p->attribute ?? '' ) === 'charset' ) {
				$cs = strtoupper( $p->value );
				if ( 'UTF-8' !== $cs && function_exists( 'mb_convert_encoding' ) ) {
					$data = @mb_convert_encoding( $data, 'UTF-8', $cs );
				}
				break;
			}
		}
	}
	return (string) $data;
}

/**
 * Fjern citeret tekst fra et kundesvar ("On Wed, ... wrote:" osv.) så
 * kun den nye besked gemmes. Best-effort heuristik.
 */
function studie247_imap_strip_quoted( $text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	$out   = array();
	$stop_patterns = array(
		'/^On .+ wrote:$/i',
		'/^Den .+ skrev .+:$/i',
		'/^Fra: .+/i',
		'/^From: .+/i',
		'/^-{2,} Original Message -{2,}/i',
		'/^_{5,}$/',
	);
	foreach ( $lines as $line ) {
		$t = trim( $line );
		foreach ( $stop_patterns as $pat ) {
			if ( preg_match( $pat, $t ) ) { break 2; }
		}
		// Linjer der starter med ">" er citeret — men lad dem stå hvis de står alene.
		$out[] = $line;
	}
	// Fjern trailing blanklinjer.
	while ( $out && '' === trim( end( $out ) ) ) { array_pop( $out ); }
	return implode( "\n", $out );
}

/**
 * Find tråd-ID i en indkommende mail (subject + references + in-reply-to).
 */
function studie247_imap_resolve_thread_id( $subject, $headers ) {
	// 1. Subject indeholder [#S247-...]
	$id = studie247_thread_parse( (string) $subject );
	if ( $id ) { return $id; }

	// 2. References / In-Reply-To header matcher en gemt Message-ID.
	$refs = trim( ( $headers['references'] ?? '' ) . ' ' . ( $headers['in_reply_to'] ?? '' ) );
	if ( $refs ) {
		preg_match_all( '/<([^>]+)>/', $refs, $m );
		$ids = $m[1] ?? array();
		foreach ( $ids as $mid ) {
			// Vores Message-IDs har format s247-thread-<id>-<token>-...
			if ( preg_match( '/^s247-thread-(\d+)-([a-f0-9]{6})-/i', $mid, $mm ) ) {
				$pid   = (int) $mm[1];
				$token = strtolower( $mm[2] );
				if ( function_exists( 'studie247_thread_token' ) &&
					studie247_thread_token( $pid ) === $token &&
					'kontakt_besked' === get_post_type( $pid ) ) {
					return $pid;
				}
			}
			// Alternativt: scan gemte Message-IDs på alle nylige besked'er.
			$found = get_posts( array(
				'post_type'      => 'kontakt_besked',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array( 'key' => '_s247_thread_messageids', 'value' => $mid, 'compare' => 'LIKE' ),
				),
			) );
			if ( ! empty( $found[0] ) ) { return (int) $found[0]; }
		}
	}

	return 0;
}

/**
 * Hoved-polleren: hent nye (UNSEEN) mails og knyt dem til tråde.
 */
function studie247_imap_poll() {
	$mbx = studie247_imap_open();
	if ( is_wp_error( $mbx ) ) {
		if ( function_exists( 'studie247_audit_log' ) ) {
			studie247_audit_log( 'IMAP poll fejl: ' . $mbx->get_error_message(), 0, 'inbound' );
		}
		return $mbx;
	}

	$seen    = array();
	$uids    = imap_search( $mbx, 'UNSEEN', SE_UID );
	if ( ! $uids ) {
		imap_close( $mbx );
		update_option( 's247_imap_last_poll', time() );
		return array( 'matched' => 0, 'unmatched' => 0, 'scanned' => 0 );
	}

	$matched = 0;
	$unmatched = 0;

	foreach ( $uids as $uid ) {
		$overview_arr = imap_fetch_overview( $mbx, $uid, FT_UID );
		$overview = $overview_arr[0] ?? null;
		if ( ! $overview ) { continue; }

		$subject    = isset( $overview->subject ) ? imap_utf8( $overview->subject ) : '';
		$from       = $overview->from ?? '';
		$from_email = '';
		$from_name  = $from;
		if ( preg_match( '/<([^>]+)>/', $from, $m ) ) {
			$from_email = strtolower( trim( $m[1] ) );
			$from_name  = trim( str_replace( $m[0], '', $from ), ' "' );
		} elseif ( is_email( $from ) ) {
			$from_email = strtolower( $from );
		}

		$raw_headers = imap_fetchheader( $mbx, $uid, FT_UID );
		$parsed      = imap_rfc822_parse_headers( $raw_headers );
		$message_id  = isset( $parsed->message_id ) ? trim( $parsed->message_id, '<>' ) : '';
		$in_reply_to = '';
		$references  = '';
		if ( preg_match( '/^In-Reply-To:\s*(.+)$/mi', $raw_headers, $m ) ) { $in_reply_to = trim( $m[1] ); }
		if ( preg_match( '/^References:\s*(.+(?:\n\s+.+)*)$/mi', $raw_headers, $m ) ) { $references = trim( $m[1] ); }

		$besked_id = studie247_imap_resolve_thread_id( $subject, array(
			'in_reply_to' => $in_reply_to,
			'references'  => $references,
		) );

		if ( ! $besked_id ) {
			// Ingen match — ikke vores. Lad mailen være UNSEEN så admin selv kan håndtere.
			$unmatched++;
			continue;
		}

		$structure = imap_fetchstructure( $mbx, $uid, FT_UID );
		$body = studie247_imap_extract_body( $mbx, $uid, $structure );
		$body['text'] = studie247_imap_strip_quoted( $body['text'] );

		studie247_thread_save_reply( $besked_id, 'inbound', array(
			'name'        => $from_name,
			'email'       => $from_email,
			'subject'     => $subject,
			'body_text'   => $body['text'],
			'body_html'   => $body['html'],
			'message_id'  => $message_id,
			'in_reply_to' => $in_reply_to,
		) );
		$matched++;

		// Markér mailen som læst (Seen), så vi ikke genprocesserer.
		imap_setflag_full( $mbx, (string) $uid, '\\Seen', ST_UID );
	}

	imap_expunge( $mbx );
	imap_close( $mbx );

	update_option( 's247_imap_last_poll', time() );
	update_option( 's247_imap_last_poll_result', array(
		'matched'   => $matched,
		'unmatched' => $unmatched,
		'scanned'   => count( $uids ),
		'time'      => time(),
	) );

	if ( function_exists( 'studie247_audit_log' ) ) {
		studie247_audit_log( sprintf( 'IMAP poll: %d matchede, %d ignoreret', $matched, $unmatched ), 0, 'inbound' );
	}

	return array( 'matched' => $matched, 'unmatched' => $unmatched, 'scanned' => count( $uids ) );
}

/* ───────── Cron: hver 5. minut ───────── */
add_filter( 'cron_schedules', function ( $s ) {
	if ( ! isset( $s['s247_five_min'] ) ) {
		$s['s247_five_min'] = array( 'interval' => 300, 'display' => __( 'Hvert 5. minut', 'studie247' ) );
	}
	return $s;
} );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 's247_imap_poll_event' ) ) {
		wp_schedule_event( time() + 60, 's247_five_min', 's247_imap_poll_event' );
	}
} );
add_action( 's247_imap_poll_event', 'studie247_imap_poll' );

/* ───────── Admin-side: Værktøjer → Kontakt-inbound ───────── */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'tools.php',
		__( 'Studie 247 — Kontakt-inbound', 'studie247' ),
		__( 'Kontakt-inbound', 'studie247' ),
		'manage_options',
		's247-inbound',
		'studie247_inbound_admin_page'
	);
} );

function studie247_inbound_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$msg = '';
	if ( isset( $_POST['s247_inbound_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['s247_inbound_nonce'] ), 's247_inbound' ) ) {
		$r = studie247_imap_poll();
		if ( is_wp_error( $r ) ) {
			$msg = '<div class="notice notice-error"><p>' . esc_html( $r->get_error_message() ) . '</p></div>';
		} else {
			$msg = sprintf( '<div class="notice notice-success"><p>%d matchede, %d ignoreret, %d scannet.</p></div>',
				$r['matched'], $r['unmatched'], $r['scanned'] );
		}
	}
	$last    = (int) get_option( 's247_imap_last_poll', 0 );
	$last_r  = get_option( 's247_imap_last_poll_result', array() );
	$cfg     = studie247_imap_config();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Kontakt-inbound', 'studie247' ); ?></h1>
		<?php echo $msg; ?>
		<p><?php esc_html_e( 'Henter kundesvar fra IMAP-indbakken og tilknytter dem til den rigtige besked via tråd-ID.', 'studie247' ); ?></p>
		<table class="form-table" role="presentation">
			<tr><th>Host</th><td><code><?php echo esc_html( $cfg['host'] . ':' . $cfg['port'] ); ?></code></td></tr>
			<tr><th>User</th><td><code><?php echo esc_html( $cfg['user'] ); ?></code></td></tr>
			<tr><th>Mailbox</th><td><code><?php echo esc_html( $cfg['mbx'] ); ?></code></td></tr>
			<tr><th>Sidste poll</th><td><?php echo $last ? esc_html( date_i18n( 'j. M Y H:i:s', $last ) ) : '—'; ?></td></tr>
			<?php if ( $last_r ) : ?>
				<tr><th>Sidste resultat</th><td><?php printf( '%d matchede · %d ignoreret · %d scannet',
					(int) ( $last_r['matched'] ?? 0 ), (int) ( $last_r['unmatched'] ?? 0 ), (int) ( $last_r['scanned'] ?? 0 ) ); ?></td></tr>
			<?php endif; ?>
		</table>
		<form method="post" style="margin-top:12px;">
			<?php wp_nonce_field( 's247_inbound', 's247_inbound_nonce' ); ?>
			<button type="submit" class="button button-primary">↻ <?php esc_html_e( 'Hent svar nu', 'studie247' ); ?></button>
		</form>
	</div>
	<?php
}
