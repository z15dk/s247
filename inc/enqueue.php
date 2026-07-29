<?php
/**
 * Enqueue styles and scripts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	// Brug filens ændringstid som cache-buster så browseren altid henter
	// nyeste udgave efter et git pull (STUDIE247_VERSION bumpes sjældent).
	$ver = function ( $rel_path ) {
		$abs = STUDIE247_DIR . '/' . ltrim( $rel_path, '/' );
		return file_exists( $abs ) ? (string) filemtime( $abs ) : STUDIE247_VERSION;
	};
	$version = STUDIE247_VERSION;

	// Google Fonts — Inter (sans) + Fraunces (dramatic serif italics).
	wp_enqueue_style(
		'studie247-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,200;1,9..144,400;1,9..144,700;1,9..144,900&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'studie247-variables',   STUDIE247_URI . '/assets/css/variables.css',   array(),                              $ver( 'assets/css/variables.css' ) );
	wp_enqueue_style( 'studie247-fonts-local', STUDIE247_URI . '/assets/css/fonts.css',       array( 'studie247-variables' ),       $ver( 'assets/css/fonts.css' ) );
	wp_enqueue_style( 'studie247-base',        STUDIE247_URI . '/assets/css/base.css',        array( 'studie247-variables' ),       $ver( 'assets/css/base.css' ) );
	wp_enqueue_style( 'studie247-components',  STUDIE247_URI . '/assets/css/components.css',  array( 'studie247-base' ),            $ver( 'assets/css/components.css' ) );
	wp_enqueue_style( 'studie247-sections',    STUDIE247_URI . '/assets/css/sections.css',    array( 'studie247-components' ),      $ver( 'assets/css/sections.css' ) );
	wp_enqueue_style( 'studie247-main',        STUDIE247_URI . '/style.css',                  array(),                              $ver( 'style.css' ) );

	wp_enqueue_script( 'studie247-main', STUDIE247_URI . '/assets/js/main.js', array(), $ver( 'assets/js/main.js' ), true );
} );

/**
 * Dashboard-siden bruger egen skin — fjern tema-CSS og enqueue kun
 * dashboard.css + skrifttype-link (Inter).
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page_template( 'page-dashboard.php' ) ) { return; }

	// Dequeue tema-stylesheets der ellers ville farve login-formen etc.
	foreach ( array( 'studie247-variables', 'studie247-fonts-local', 'studie247-base', 'studie247-components', 'studie247-sections', 'studie247-main' ) as $h ) {
		wp_dequeue_style( $h );
		wp_deregister_style( $h );
	}

	// Inter font
	wp_enqueue_style(
		's247-dash-font',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	$dash_path = STUDIE247_DIR . '/assets/css/dashboard.css';
	$dash_ver  = file_exists( $dash_path ) ? (string) filemtime( $dash_path ) : STUDIE247_VERSION;
	wp_enqueue_style( 's247-dashboard', STUDIE247_URI . '/assets/css/dashboard.css', array( 's247-dash-font' ), $dash_ver );
}, 20 );

// Preconnect <link>s for perf.
add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );
