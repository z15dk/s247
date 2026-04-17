<?php
/**
 * CPT: Service (SoMe-videoer, Podcast, Online kursus, Fotoshoot, +++)
 *
 * Extensible from WP admin: add new services without touching code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'service', array(
		'labels' => array(
			'name'               => __( 'Services', 'studie247' ),
			'singular_name'      => __( 'Service', 'studie247' ),
			'menu_name'          => __( 'Services', 'studie247' ),
			'add_new'            => __( 'Tilføj ny service', 'studie247' ),
			'add_new_item'       => __( 'Tilføj ny service', 'studie247' ),
			'edit_item'          => __( 'Rediger service', 'studie247' ),
			'view_item'          => __( 'Se service', 'studie247' ),
			'search_items'       => __( 'Søg services', 'studie247' ),
			'all_items'          => __( 'Alle services', 'studie247' ),
		),
		'public'              => true,
		'show_in_rest'         => true,
		'has_archive'          => 'services',
		'menu_icon'            => 'dashicons-video-alt3',
		'menu_position'        => 5,
		'rewrite'              => array( 'slug' => 'services', 'with_front' => false ),
		'supports'             => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		'taxonomies'           => array(),
	) );
} );

/**
 * Helper: get all services ordered by menu_order.
 */
function studie247_get_services( $limit = -1 ) {
	return get_posts( array(
		'post_type'      => 'service',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) );
}
