<?php
/**
 * Studie 247 — functions.php
 *
 * Bootstraps the custom theme. Keep this file thin: each concern
 * lives in /inc/ and is loaded below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STUDIE247_VERSION', '0.4.1' );
define( 'STUDIE247_DIR', get_template_directory() );
define( 'STUDIE247_URI', get_template_directory_uri() );

require_once STUDIE247_DIR . '/inc/theme-setup.php';
require_once STUDIE247_DIR . '/inc/enqueue.php';
require_once STUDIE247_DIR . '/inc/menus.php';
require_once STUDIE247_DIR . '/inc/cpt-service.php';
require_once STUDIE247_DIR . '/inc/cpt-case.php';
require_once STUDIE247_DIR . '/inc/cpt-testimonial.php';
require_once STUDIE247_DIR . '/inc/cpt-booking.php';
require_once STUDIE247_DIR . '/inc/cpt-udlejning.php';
require_once STUDIE247_DIR . '/inc/meta-boxes.php';
require_once STUDIE247_DIR . '/inc/seo.php';
require_once STUDIE247_DIR . '/inc/schema.php';
require_once STUDIE247_DIR . '/inc/template-tags.php';
require_once STUDIE247_DIR . '/inc/customizer.php';
require_once STUDIE247_DIR . '/inc/svg-icons.php';
require_once STUDIE247_DIR . '/inc/svg-upload.php';
require_once STUDIE247_DIR . '/inc/admin-style.php';
require_once STUDIE247_DIR . '/inc/udlejning-import.php';
require_once STUDIE247_DIR . '/inc/booking-inspection.php';
require_once STUDIE247_DIR . '/inc/booking-approval.php';
require_once STUDIE247_DIR . '/inc/product-uid.php';

/**
 * Auto-render team sektion efter indhold på Om-siden,
 * uanset hvilken page-template der bruges.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$title = strtolower( trim( get_the_title() ) );
	$slug  = get_post_field( 'post_name', get_the_ID() );
	if ( $slug === 'om' || $slug === 'om-os' || $title === 'om' || $title === 'om os' ) {
		ob_start();
		get_template_part( 'template-parts/section', 'team' );
		$content .= ob_get_clean();
	}
	return $content;
}, 20 );
