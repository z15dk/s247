<?php
/**
 * CPT: s247_customer — kunde-profil der samler alle bookinger og beskeder
 * pr. person på tværs af formularer. Matches på email (primær) med phone
 * som fallback.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 's247_customer', array(
		'labels' => array(
			'name'          => __( 'Kunder', 'studie247' ),
			'singular_name' => __( 'Kunde', 'studie247' ),
			'menu_name'     => __( 'Kunder', 'studie247' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => true,
		'rest_base'       => 's247_customer',
		'menu_icon'       => 'dashicons-businessperson',
		'menu_position'   => 22,
		'capability_type' => 'post',
		'supports'        => array( 'title', 'custom-fields' ),
	) );
} );

/**
 * Find eller opret en kunde-post baseret på email (primær) eller phone.
 * Opdaterer navn/telefon/virksomhed/cvr hvis nye felter er udfyldt.
 *
 * @return int kunde-post-ID eller 0 hvis intet kunne identificere personen.
 */
function studie247_customer_upsert( $data ) {
	$name       = sanitize_text_field( $data['name']    ?? '' );
	$email      = sanitize_email(      $data['email']   ?? '' );
	$phone      = sanitize_text_field( $data['phone']   ?? '' );
	$company    = sanitize_text_field( $data['company'] ?? '' );
	$cvr        = preg_replace( '/\D/', '', (string) ( $data['cvr'] ?? '' ) );
	$newsletter = '1' === (string) ( $data['newsletter'] ?? '' );
	$news_ts    = sanitize_text_field( $data['newsletter_ts'] ?? '' );

	// Email er primær nøgle. Uden email forsøger vi phone.
	if ( ! $email && ! $phone ) { return 0; }

	$existing = 0;
	if ( $email ) {
		$q = get_posts( array(
			'post_type'      => 's247_customer',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_s247_cust_email',
			'meta_value'     => $email,
		) );
		if ( ! empty( $q ) ) { $existing = (int) $q[0]; }
	}
	if ( ! $existing && $phone ) {
		$q = get_posts( array(
			'post_type'      => 's247_customer',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_s247_cust_phone',
			'meta_value'     => $phone,
		) );
		if ( ! empty( $q ) ) { $existing = (int) $q[0]; }
	}

	if ( ! $existing ) {
		$existing = wp_insert_post( array(
			'post_type'   => 's247_customer',
			'post_status' => 'publish',
			'post_title'  => $name ?: ( $email ?: $phone ),
		) );
		if ( is_wp_error( $existing ) ) { return 0; }
		update_post_meta( $existing, '_s247_cust_first_seen', current_time( 'mysql' ) );
	}

	// Opdater data (kun hvis der er ny info — ikke overskriv med tom).
	if ( $name    && $name    !== get_post_meta( $existing, '_s247_cust_name',    true ) ) { update_post_meta( $existing, '_s247_cust_name',    $name ); }
	if ( $email   && $email   !== get_post_meta( $existing, '_s247_cust_email',   true ) ) { update_post_meta( $existing, '_s247_cust_email',   $email ); }
	if ( $phone   && $phone   !== get_post_meta( $existing, '_s247_cust_phone',   true ) ) { update_post_meta( $existing, '_s247_cust_phone',   $phone ); }
	if ( $company && $company !== get_post_meta( $existing, '_s247_cust_company', true ) ) { update_post_meta( $existing, '_s247_cust_company', $company ); }
	if ( $cvr     && $cvr     !== get_post_meta( $existing, '_s247_cust_cvr',     true ) ) { update_post_meta( $existing, '_s247_cust_cvr',     $cvr ); }

	// Nyhedsbrev: opgradér kun — aldrig overskriv '1' tilbage til '0' automatisk.
	// Kunden afmelder manuelt via admin eller via dedikeret flow.
	if ( $newsletter ) {
		if ( '1' !== get_post_meta( $existing, '_s247_cust_newsletter', true ) ) {
			update_post_meta( $existing, '_s247_cust_newsletter', '1' );
			update_post_meta( $existing, '_s247_cust_newsletter_ts', $news_ts ?: current_time( 'mysql' ) );
		}
	}

	// Hvis kundens titel stadig er email/phone, og vi nu har et navn, opgrader title.
	if ( $name ) {
		$title = get_the_title( $existing );
		if ( $title !== $name && ( $title === $email || $title === $phone || '' === $title ) ) {
			wp_update_post( array( 'ID' => $existing, 'post_title' => $name ) );
		}
	}

	update_post_meta( $existing, '_s247_cust_last_seen', current_time( 'mysql' ) );

	return (int) $existing;
}

/**
 * Auto-hook: når en booking eller kontakt-besked gemmes, find/opret kunde
 * og link via _s247_cust_id.
 */
add_action( 'save_post_booking', function ( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) { return; }
	$cid = studie247_customer_upsert( array(
		'name'          => get_post_meta( $post_id, '_s247_name', true ),
		'email'         => get_post_meta( $post_id, '_s247_email', true ),
		'phone'         => get_post_meta( $post_id, '_s247_phone', true ),
		'company'       => get_post_meta( $post_id, '_s247_company', true ),
		'cvr'           => get_post_meta( $post_id, '_s247_cvr', true ),
		'newsletter'    => get_post_meta( $post_id, '_s247_newsletter_optin', true ),
		'newsletter_ts' => get_post_meta( $post_id, '_s247_newsletter_optin_timestamp', true ),
	) );
	if ( $cid ) {
		update_post_meta( $post_id, '_s247_cust_id', $cid );
	}
}, 20, 3 );

add_action( 'save_post_kontakt_besked', function ( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) { return; }
	$cid = studie247_customer_upsert( array(
		'name'          => get_post_meta( $post_id, '_s247_name', true ),
		'email'         => get_post_meta( $post_id, '_s247_email', true ),
		'phone'         => get_post_meta( $post_id, '_s247_phone', true ),
		'newsletter'    => get_post_meta( $post_id, '_s247_newsletter_optin', true ),
		'newsletter_ts' => get_post_meta( $post_id, '_s247_newsletter_optin_timestamp', true ),
	) );
	if ( $cid ) {
		update_post_meta( $post_id, '_s247_cust_id', $cid );
	}
}, 20, 3 );

/**
 * Hent alle bookinger + beskeder der hører til en kunde (via cust_id
 * eller fallback på email-match).
 */
function studie247_customer_activity( $cust_id ) {
	$email = get_post_meta( $cust_id, '_s247_cust_email', true );

	$bookings = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => array( 'pending', 'publish', 'trash' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_s247_cust_id', 'value' => $cust_id, 'compare' => '=' ),
			$email ? array( 'key' => '_s247_email', 'value' => $email, 'compare' => '=' ) : array(),
		),
	) );

	$messages = get_posts( array(
		'post_type'      => 'kontakt_besked',
		'post_status'    => array( 'publish', 'trash' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_s247_cust_id', 'value' => $cust_id, 'compare' => '=' ),
			$email ? array( 'key' => '_s247_email', 'value' => $email, 'compare' => '=' ) : array(),
		),
	) );

	return array( 'bookings' => $bookings, 'messages' => $messages );
}

/**
 * Admin-liste: ekstra kolonner med email/telefon/antal-aktiviteter.
 */
add_filter( 'manage_s247_customer_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['s247_cust_email'] = __( 'E-mail', 'studie247' );
			$new['s247_cust_phone'] = __( 'Telefon', 'studie247' );
			$new['s247_cust_count'] = __( 'Aktivitet', 'studie247' );
		}
	}
	return $new;
} );
add_action( 'manage_s247_customer_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 's247_cust_email':
			$v = get_post_meta( $post_id, '_s247_cust_email', true );
			echo $v ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '—';
			break;
		case 's247_cust_phone':
			$v = get_post_meta( $post_id, '_s247_cust_phone', true );
			echo $v ? '<a href="tel:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '—';
			break;
		case 's247_cust_count':
			$a = studie247_customer_activity( $post_id );
			printf( esc_html__( '%1$d bookinger · %2$d beskeder', 'studie247' ), count( $a['bookings'] ), count( $a['messages'] ) );
			break;
	}
}, 10, 2 );
