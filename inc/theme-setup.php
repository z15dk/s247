<?php
/**
 * Theme setup: supports, image sizes, language.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'studie247', STUDIE247_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// Brand image sizes.
	add_image_size( 's247-card',    800,  600, true );
	add_image_size( 's247-hero',   1920, 1080, true );
	add_image_size( 's247-square',  800,  800, true );
	add_image_size( 's247-wide',   1600,  900, true );
} );

// Remove emoji bloat for performance + SEO.
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
} );

// Remove WP version from head (small SEO/sec win).
remove_action( 'wp_head', 'wp_generator' );
