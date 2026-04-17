<?php
/**
 * CPT: Case (Referencer / showreel-indhold)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'case', array(
		'labels' => array(
			'name'          => __( 'Cases', 'studie247' ),
			'singular_name' => __( 'Case', 'studie247' ),
			'add_new_item'  => __( 'Tilføj ny case', 'studie247' ),
		),
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => 'cases',
		'menu_icon'    => 'dashicons-format-gallery',
		'menu_position' => 6,
		'rewrite'      => array( 'slug' => 'cases', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
	) );
} );

function studie247_get_cases( $limit = 6 ) {
	return get_posts( array(
		'post_type'      => 'case',
		'posts_per_page' => $limit,
	) );
}
