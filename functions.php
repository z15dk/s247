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

define( 'STUDIE247_VERSION', '0.3.1' );
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
require_once STUDIE247_DIR . '/inc/svg-icons.php';
require_once STUDIE247_DIR . '/inc/svg-upload.php';
