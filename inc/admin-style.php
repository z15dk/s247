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

/* ───────── Admin footer — byline ───────── */
add_filter( 'admin_footer_text', function () {
	return 'Bygget med <span style="color:#9E2B25;">♥</span> af <a href="https://webza.dk" target="_blank" rel="noopener">webza.dk</a>';
} );

add_filter( 'update_footer', '__return_empty_string', 11 );
