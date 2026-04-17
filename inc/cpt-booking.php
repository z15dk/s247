<?php
/**
 * CPT: Booking (stub — the full custom booking system builds on this).
 *
 * Kept admin-only for now; booking flow is wired up in a later phase.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'booking', array(
		'labels' => array(
			'name'          => __( 'Bookinger', 'studie247' ),
			'singular_name' => __( 'Booking', 'studie247' ),
			'add_new_item'  => __( 'Opret booking manuelt', 'studie247' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_rest' => false,
		'menu_icon'    => 'dashicons-calendar-alt',
		'menu_position' => 20,
		'capability_type' => 'post',
		'supports'     => array( 'title', 'custom-fields' ),
	) );
} );
