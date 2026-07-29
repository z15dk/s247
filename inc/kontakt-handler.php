<?php
/**
 * Kontakt-form — POST-handler via admin-post.php.
 *
 * Nordicway's ModSecurity returnerer 404 på POSTs direkte til
 * /kontakt/ med form-data. admin-post.php er whitelistet.
 *
 * Form-action → /wp-admin/admin-post.php?action=s247_kontakt
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_nopriv_s247_kontakt', 'studie247_handle_kontakt' );
add_action( 'admin_post_s247_kontakt',        'studie247_handle_kontakt' );

function studie247_handle_kontakt() {
	$redirect = home_url( '/kontakt/' );

	if ( empty( $_POST['s247_ko_nonce'] )
		|| ! wp_verify_nonce( $_POST['s247_ko_nonce'], 's247_kontakt_os' ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'nonce', $redirect ) );
		exit;
	}

	$form_topic   = sanitize_text_field(     wp_unslash( $_POST['s247_topic']   ?? '' ) );
	$form_message = sanitize_textarea_field( wp_unslash( $_POST['s247_message'] ?? '' ) );
	$form_name    = sanitize_text_field(     wp_unslash( $_POST['s247_name']    ?? '' ) );
	$form_email   = sanitize_email(          wp_unslash( $_POST['s247_email']   ?? '' ) );
	$form_phone   = sanitize_text_field(     wp_unslash( $_POST['s247_phone']   ?? '' ) );
	$form_newsletter = ! empty( $_POST['s247_newsletter_optin'] ) ? '1' : '0';

	$errors = array();
	if ( ! $form_message || strlen( $form_message ) < 5 ) { $errors[] = 'besked'; }
	if ( ! $form_name ) { $errors[] = 'navn'; }
	if ( ! is_email( $form_email ) ) { $errors[] = 'email'; }
	if ( empty( $_POST['s247_consent'] ) ) { $errors[] = 'consent'; }

	if ( ! empty( $errors ) ) {
		wp_safe_redirect( add_query_arg( 'err', implode( ',', $errors ), $redirect ) );
		exit;
	}

	if ( function_exists( 'studie247_save_kontakt_besked' ) ) {
		$besked_id = studie247_save_kontakt_besked( array(
			'name'    => $form_name,
			'email'   => $form_email,
			'phone'   => $form_phone,
			'topic'   => $form_topic,
			'message' => $form_message,
			'source'  => 'kontakt',
		) );
		if ( $besked_id ) {
			update_post_meta( $besked_id, '_s247_newsletter_optin', $form_newsletter );
			if ( '1' === $form_newsletter ) {
				update_post_meta( $besked_id, '_s247_newsletter_optin_timestamp', current_time( 'mysql' ) );
			}
		}
	}

	$admin_to      = get_theme_mod( 's247_email', 'info@s247.dk' );
	$admin_subject = sprintf( '[Studie 247] Ny henvendelse fra %s', $form_name );
	$admin_body    = "Navn: {$form_name}\nEmail: {$form_email}\nTelefon: {$form_phone}\n";
	if ( $form_topic ) { $admin_body .= "Emne: {$form_topic}\n"; }
	$admin_body   .= "\nBesked:\n{$form_message}\n";
	@wp_mail( $admin_to, $admin_subject, $admin_body, array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $form_name . ' <' . $form_email . '>',
	) );

	if ( function_exists( 'studie247_render_mail_template' ) ) {
		$mail = studie247_render_mail_template( 'contact', array(
			'navn'   => $form_name,
			'email'  => $form_email,
			'side'   => $form_topic,
			'besked' => nl2br( $form_message ),
		) );
		$is_html = function_exists( 'studie247_mail_template_is_html' )
			? studie247_mail_template_is_html( 'contact' ) : true;
		$send = function () use ( $form_email, $mail, $is_html, $admin_to ) {
			@wp_mail( $form_email, $mail['subject'], $mail['body'], array(
				'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8',
				'From: Studie 247 <' . $admin_to . '>',
				'Reply-To: ' . $admin_to,
			) );
		};
		// Kør afsendelsen med tråd-kontekst hvis besked'en blev gemt, så
		// subject får [#S247-ID-token] og Message-ID matcher tråd'en.
		if ( ! empty( $besked_id ) && function_exists( 'studie247_with_thread' ) ) {
			studie247_with_thread( $besked_id, $send );
		} else {
			$send();
		}
	}

	wp_safe_redirect( add_query_arg( 'sendt', '1', $redirect ) );
	exit;
}
