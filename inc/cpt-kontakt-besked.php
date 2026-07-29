<?php
/**
 * CPT: kontakt_besked — persisterer alle kontakt-form-beskeder så de kan
 * hentes via REST af dashboardet/CRM, og ses i wp-admin.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'kontakt_besked', array(
		'labels' => array(
			'name'          => __( 'Kontakt-beskeder', 'studie247' ),
			'singular_name' => __( 'Kontakt-besked', 'studie247' ),
			'menu_name'     => __( 'Beskeder', 'studie247' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => true, // auth krævet — se inc/rest-api.php
		'rest_base'       => 'kontakt_besked',
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 21,
		'capability_type' => 'post',
		'supports'        => array( 'title', 'custom-fields' ),
	) );
} );

/**
 * Gem en kontakt-besked som CPT-post og returnér post-ID (eller 0).
 *
 * @param array $data {
 *     @type string $name
 *     @type string $email
 *     @type string $phone
 *     @type string $topic     — fx "Booking", "Priser" (kan være tom)
 *     @type string $side      — "A"/"B" fra /kontakt/ (kan være tom)
 *     @type string $message
 *     @type string $source    — "kontakt" eller "kontakt-os"
 * }
 */
function studie247_save_kontakt_besked( $data ) {
	$name    = sanitize_text_field( $data['name']    ?? '' );
	$email   = sanitize_email(      $data['email']   ?? '' );
	$phone   = sanitize_text_field( $data['phone']   ?? '' );
	$topic   = sanitize_text_field( $data['topic']   ?? '' );
	$side    = sanitize_text_field( $data['side']    ?? '' );
	$message = sanitize_textarea_field( $data['message'] ?? '' );
	$source  = sanitize_key(        $data['source']  ?? 'kontakt' );

	$title = $name ?: __( '(uden navn)', 'studie247' );
	if ( $topic ) { $title .= ' — ' . $topic; }

	$post_id = wp_insert_post( array(
		'post_type'    => 'kontakt_besked',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_content' => $message,
	), true );

	if ( ! $post_id || is_wp_error( $post_id ) ) { return 0; }

	if ( $name )    { update_post_meta( $post_id, '_s247_name',    $name ); }
	if ( $email )   { update_post_meta( $post_id, '_s247_email',   $email ); }
	if ( $phone )   { update_post_meta( $post_id, '_s247_phone',   $phone ); }
	if ( $topic )   { update_post_meta( $post_id, '_s247_topic',   $topic ); }
	if ( $side )    { update_post_meta( $post_id, '_s247_side',    $side ); }
	if ( $message ) { update_post_meta( $post_id, '_s247_message', $message ); }
	update_post_meta( $post_id, '_s247_source', $source );

	// GDPR-consent bevis.
	update_post_meta( $post_id, '_s247_consent_timestamp', current_time( 'mysql' ) );
	update_post_meta( $post_id, '_s247_consent_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	update_post_meta( $post_id, '_s247_consent_ua', isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '' );

	return (int) $post_id;
}

/**
 * Tilføj handy kolonner i admin-listen så man kan scanne beskeder hurtigt.
 */
add_filter( 'manage_kontakt_besked_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['s247_email'] = __( 'E-mail', 'studie247' );
			$new['s247_phone'] = __( 'Telefon', 'studie247' );
			$new['s247_source'] = __( 'Kilde', 'studie247' );
		}
	}
	return $new;
} );

add_action( 'manage_kontakt_besked_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 's247_email':
			$v = get_post_meta( $post_id, '_s247_email', true );
			echo $v ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '—';
			break;
		case 's247_phone':
			$v = get_post_meta( $post_id, '_s247_phone', true );
			echo $v ? '<a href="tel:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '—';
			break;
		case 's247_source':
			echo esc_html( get_post_meta( $post_id, '_s247_source', true ) ?: '—' );
			break;
	}
}, 10, 2 );
