<?php
/**
 * CPT: Testimonial
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'testimonial', array(
		'labels' => array(
			'name'          => __( 'Testimonials', 'studie247' ),
			'singular_name' => __( 'Testimonial', 'studie247' ),
			'add_new_item'  => __( 'Tilføj ny testimonial', 'studie247' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-format-quote',
		'menu_position' => 7,
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
	) );
} );

function studie247_get_testimonials( $limit = 6 ) {
	return get_posts( array(
		'post_type'      => 'testimonial',
		'posts_per_page' => $limit,
	) );
}
