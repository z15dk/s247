<?php
/**
 * CPT: Udlejning (udstyrs-udlejning — kamera, lys, lyd, grip, osv.)
 *
 * Stubbet men INAKTIV — vi bygger selve udlejningssiden til sidst.
 * CPT'en registreres allerede så data/struktur er på plads.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'udlejning_item', array(
		'labels' => array(
			'name'          => __( 'Udlejning', 'studie247' ),
			'singular_name' => __( 'Udlejningsgenstand', 'studie247' ),
			'add_new_item'  => __( 'Tilføj udstyr', 'studie247' ),
			'menu_name'     => __( 'Udlejning', 'studie247' ),
		),
		// Offentligt fra dag ét så vi kan redigere data; templates laves senere.
		'public'       => true,
		'show_in_rest' => true,
		'has_archive'  => 'udlejning',
		'menu_icon'    => 'dashicons-camera',
		'menu_position' => 8,
		'rewrite'      => array( 'slug' => 'udlejning', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
	) );

	// Kategorier til udstyr (kamera, lys, lyd, grip, osv.)
	register_taxonomy( 'udlejning_kategori', 'udlejning_item', array(
		'labels' => array(
			'name'          => __( 'Kategorier', 'studie247' ),
			'singular_name' => __( 'Kategori', 'studie247' ),
		),
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'udlejning-kategori' ),
	) );
} );
