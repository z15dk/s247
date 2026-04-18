<?php
/**
 * Enqueue styles and scripts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	$version = STUDIE247_VERSION;

	// Google Fonts — Inter (sans) + Fraunces (dramatic serif italics).
	wp_enqueue_style(
		'studie247-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,200;1,9..144,400;1,9..144,700;1,9..144,900&display=swap',
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
