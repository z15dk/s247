<?php
/**
 * Customizer settings — hero media.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 's247_hero', array(
		'title'    => __( 'Hero', 'studie247' ),
		'priority' => 30,
	) );

	// Baggrundsvideo (mp4-URL).
	$wp_customize->add_setting( 's247_hero_video', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 's247_hero_video', array(
		'label'       => __( 'Hero baggrundsvideo (MP4 URL)', 'studie247' ),
		'description' => __( 'Indsæt URL til en .mp4 fil. Upload evt. først via Medier.', 'studie247' ),
		'section'     => 's247_hero',
		'type'        => 'url',
	) );

	// Fallback-billede / video poster.
	$wp_customize->add_setting( 's247_hero_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 's247_hero_image', array(
		'label'       => __( 'Hero fallback-billede', 'studie247' ),
		'description' => __( 'Vises hvis ingen video er sat. Bruges også som video-poster.', 'studie247' ),
		'section'     => 's247_hero',
	) ) );

	// Display-billede (erstatter det store STUDIE 247 tekst).
	$wp_customize->add_setting( 's247_hero_display_image', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 's247_hero_display_image', array(
		'label'       => __( 'Hero display-billede', 'studie247' ),
		'description' => __( 'Erstatter det store "STUDIE 247" display. Lad stå tomt for typografisk version.', 'studie247' ),
		'section'     => 's247_hero',
	) ) );

	// Marquee items (text eller billede-URL, én pr. linje).
	$wp_customize->add_setting( 's247_marquee_items', array(
		'default'           => "Optag\nSkab\nUdgiv\nPodcast\nVideo\nFoto\nKursus",
		'sanitize_callback' => 'sanitize_textarea_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 's247_marquee_items', array(
		'label'       => __( 'Marquee (rulle-bjælke)', 'studie247' ),
		'description' => __( 'Én pr. linje. Skriv et ord (fx "Podcast") eller en billede-URL (http/https, fx .svg eller .png). Billeder skalerer automatisk til bjælkens højde.', 'studie247' ),
		'section'     => 's247_hero',
		'type'        => 'textarea',
	) );
} );
