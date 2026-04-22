<?php
/**
 * Forespørgsel (tilbud-quiz) — POST-handler via admin-post.php.
 *
 * Nordicway's ModSecurity blokerer POSTs direkte til page-URL'er
 * med visse felt-kombinationer (name + email + project) ved at
 * returnere 404. WordPress' admin-post.php-endpoint er whitelistet.
 *
 * Form-action → /wp-admin/admin-post.php?action=s247_forespoergsel
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_post_nopriv_s247_forespoergsel', 'studie247_handle_forespoergsel' );
add_action( 'admin_post_s247_forespoergsel',        'studie247_handle_forespoergsel' );

function studie247_handle_forespoergsel() {
	$redirect = home_url( '/forespoergsel/' );

	if ( empty( $_POST['s247_tilbud_nonce'] )
		|| ! wp_verify_nonce( $_POST['s247_tilbud_nonce'], 's247_tilbud' ) ) {
		wp_safe_redirect( add_query_arg( 'err', 'nonce', $redirect ) );
		exit;
	}

	$form_project = sanitize_text_field( wp_unslash( $_POST['project']  ?? '' ) );
	$form_mood    = sanitize_text_field( wp_unslash( $_POST['mood']     ?? '' ) );
	$form_budget  = sanitize_text_field( wp_unslash( $_POST['budget']   ?? '' ) );
	$form_when    = sanitize_text_field( wp_unslash( $_POST['when']     ?? '' ) );
	$form_name    = sanitize_text_field( wp_unslash( $_POST['name']     ?? '' ) );
	$form_email   = sanitize_email(      wp_unslash( $_POST['email']    ?? '' ) );
	$form_phone   = sanitize_text_field( wp_unslash( $_POST['phone']    ?? '' ) );
	$form_details = sanitize_textarea_field( wp_unslash( $_POST['details'] ?? '' ) );

	$errors = array();
	if ( ! $form_project ) { $errors[] = 'projekt'; }
	if ( ! $form_name )    { $errors[] = 'navn'; }
	if ( ! is_email( $form_email ) ) { $errors[] = 'email'; }
	if ( empty( $_POST['s247_consent'] ) ) { $errors[] = 'consent'; }

	if ( ! empty( $errors ) ) {
		wp_safe_redirect( add_query_arg( 'err', implode( ',', $errors ), $redirect ) );
		exit;
	}

	$topic = sprintf( '%s · %s · %s', $form_project, $form_budget ?: 'uoplyst', $form_when ?: 'når som helst' );
	$message = sprintf(
		"Projekt: %s\nStemning: %s\nBudget: %s\nTidshorisont: %s\n\n%s",
		$form_project,
		$form_mood    ?: '—',
		$form_budget  ?: '—',
		$form_when    ?: '—',
		$form_details ?: ''
	);

	if ( function_exists( 'studie247_save_kontakt_besked' ) ) {
		studie247_save_kontakt_besked( array(
			'name'    => $form_name,
			'email'   => $form_email,
			'phone'   => $form_phone,
			'topic'   => 'Tilbud: ' . $topic,
			'message' => $message,
			'source'  => 'tilbud',
		) );
	}

	$admin_to      = get_theme_mod( 's247_email', 'info@s247.dk' );
	$admin_subject = sprintf( '[Studie 247] Tilbud ønsket: %s', $form_project );
	$admin_body    = "TILBUDSFORESPØRGSEL\n\nNavn: {$form_name}\nEmail: {$form_email}\nTelefon: {$form_phone}\n\n{$message}";
	@wp_mail( $admin_to, $admin_subject, $admin_body, array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $form_name . ' <' . $form_email . '>',
	) );

	if ( function_exists( 'studie247_render_mail_template' ) ) {
		$mail = studie247_render_mail_template( 'contact', array(
			'navn'   => $form_name,
			'email'  => $form_email,
			'side'   => 'Tilbud',
			'besked' => nl2br( $message ),
		) );
		$is_html = function_exists( 'studie247_mail_template_is_html' )
			? studie247_mail_template_is_html( 'contact' ) : true;
		@wp_mail( $form_email, $mail['subject'], $mail['body'], array(
			'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8',
			'From: Studie 247 <' . $admin_to . '>',
			'Reply-To: ' . $admin_to,
		) );
	}

	wp_safe_redirect( add_query_arg( 'sendt', '1', $redirect ) );
	exit;
}
