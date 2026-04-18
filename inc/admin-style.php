<?php
/**
 * Admin brand — kun login-siden.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── Login-siden ───────── */
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style(
		'studie247-login',
		STUDIE247_URI . '/assets/css/login.css',
		array(),
		STUDIE247_VERSION
	);
	wp_enqueue_style(
		'studie247-login-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:ital,wght@1,400;1,700&display=swap',
		array(),
		null
	);
} );

add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );

add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );
