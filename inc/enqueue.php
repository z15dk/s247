<?php
/**
 * Enqueue styles and scripts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	$version = STUDIE247_VERSION;

	// Preconnect to Google Fonts (fallback/temporary Migra-alternative).
	wp_enqueue_style(
		'studie247-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,400;1,700;1,900&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'studie247-variables', STUDIE247_URI . '/assets/css/variables.css', array(), $version );
	wp_enqueue_style( 'studie247-fonts-local', STUDIE247_URI . '/assets/css/fonts.css', array( 'studie247-variables' ), $version );
	wp_enqueue_style( 'studie247-base',       STUDIE247_URI . '/assets/css/base.css', array( 'studie247-variables' ), $version );
	wp_enqueue_style( 'studie247-components', STUDIE247_URI . '/assets/css/components.css', array( 'studie247-base' ), $version );
	wp_enqueue_style( 'studie247-sections',   STUDIE247_URI . '/assets/css/sections.css', array( 'studie247-components' ), $version );
	wp_enqueue_style( 'studie247-main',       STUDIE247_URI . '/style.css', array(), $version );

	wp_enqueue_script( 'studie247-main', STUDIE247_URI . '/assets/js/main.js', array(), $version, true );
} );

// Preconnect <link>s for perf.
add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );
