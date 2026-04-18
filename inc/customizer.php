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

	/* ───────── Studiet ───────── */
	$wp_customize->add_section( 's247_studio', array(
		'title'    => __( 'Studiet (forside)', 'studie247' ),
		'priority' => 32,
	) );

	$studio_fields = array(
		's247_studio_image'        => array( 'label' => __( 'Studie-billede', 'studie247' ), 'type' => 'image', 'default' => '' ),
		's247_studio_badge_top'    => array( 'label' => __( 'Badge — linje 1 (fx by)', 'studie247' ), 'type' => 'text', 'default' => 'Aarhus' ),
		's247_studio_badge_bottom' => array( 'label' => __( 'Badge — linje 2 (fx brand)', 'studie247' ), 'type' => 'text', 'default' => 'Studie 247' ),
		's247_studio_title_a'      => array( 'label' => __( 'Overskrift — del 1 (sans)', 'studie247' ), 'type' => 'text', 'default' => 'Ét rum —' ),
		's247_studio_title_b'      => array( 'label' => __( 'Overskrift — del 2 (kursiv)', 'studie247' ), 'type' => 'text', 'default' => 'uendelige muligheder' ),
		's247_studio_lead'         => array( 'label' => __( 'Beskrivelse (HTML tilladt, <em> osv.)', 'studie247' ), 'type' => 'textarea', 'default' => 'Fuldt udstyret produktionsrum med cyklorama, professionelt lys og lyd, green screen og plads til store opsætninger. <em>Alt du skal have med er idéen.</em>' ),
		's247_studio_spec1_num'    => array( 'label' => __( 'Spec 1 — tal', 'studie247' ), 'type' => 'text', 'default' => '200 m²' ),
		's247_studio_spec1_label'  => array( 'label' => __( 'Spec 1 — label', 'studie247' ), 'type' => 'text', 'default' => 'Studio space' ),
		's247_studio_spec2_num'    => array( 'label' => __( 'Spec 2 — tal', 'studie247' ), 'type' => 'text', 'default' => '24/7' ),
		's247_studio_spec2_label'  => array( 'label' => __( 'Spec 2 — label', 'studie247' ), 'type' => 'text', 'default' => 'Adgang' ),
		's247_studio_spec3_num'    => array( 'label' => __( 'Spec 3 — tal', 'studie247' ), 'type' => 'text', 'default' => '4K' ),
		's247_studio_spec3_label'  => array( 'label' => __( 'Spec 3 — label', 'studie247' ), 'type' => 'text', 'default' => 'Fast kamera-rig' ),
		's247_studio_spec4_num'    => array( 'label' => __( 'Spec 4 — tal', 'studie247' ), 'type' => 'text', 'default' => '∞' ),
		's247_studio_spec4_label'  => array( 'label' => __( 'Spec 4 — label', 'studie247' ), 'type' => 'text', 'default' => 'Kreative idéer' ),
		's247_studio_cta1_text'    => array( 'label' => __( 'Primær knap — tekst', 'studie247' ), 'type' => 'text', 'default' => 'Se studiet' ),
		's247_studio_cta1_url'     => array( 'label' => __( 'Primær knap — URL', 'studie247' ), 'type' => 'url', 'default' => '/studiet/' ),
		's247_studio_cta2_text'    => array( 'label' => __( 'Sekundær knap — tekst', 'studie247' ), 'type' => 'text', 'default' => 'Lej studiet råt' ),
		's247_studio_cta2_url'     => array( 'label' => __( 'Sekundær knap — URL', 'studie247' ), 'type' => 'url', 'default' => '/studiet/#raalej' ),
	);

	foreach ( $studio_fields as $key => $cfg ) {
		$sanitize = 'sanitize_text_field';
		if ( 'textarea' === $cfg['type'] ) {
			$sanitize = 'wp_kses_post';
		} elseif ( 'url' === $cfg['type'] || 'image' === $cfg['type'] ) {
			$sanitize = 'esc_url_raw';
		}
		$wp_customize->add_setting( $key, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );
		if ( 'image' === $cfg['type'] ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_studio',
			) ) );
		} else {
			$wp_customize->add_control( $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_studio',
				'type'    => 'textarea' === $cfg['type'] ? 'textarea' : ( 'url' === $cfg['type'] ? 'url' : 'text' ),
			) );
		}
	}

	/* ───────── Studio moods (fane-sektion) ───────── */
	$wp_customize->add_section( 's247_moods', array(
		'title'    => __( 'Studie-stemninger (fane)', 'studie247' ),
		'priority' => 33,
	) );

	$icon_choices = array(
		'sun'      => 'Sol',
		'moon'     => 'Måne',
		'home'     => 'Hus',
		'sparkle'  => 'Sparkle',
		'video'    => 'Video',
		'mic'      => 'Mikrofon',
		'camera'   => 'Kamera',
		'play'     => 'Play',
	);

	$mood_fields = array(
		's247_moods_title_a' => array( 'label' => __( 'Overskrift — del 1 (sans)', 'studie247' ), 'type' => 'text',     'default' => 'Tilpas studiet til dit' ),
		's247_moods_title_b' => array( 'label' => __( 'Overskrift — del 2 (accent)', 'studie247' ), 'type' => 'text',   'default' => 'Brand' ),
	);
	for ( $i = 1; $i <= 3; $i++ ) {
		$defaults = array(
			1 => array( 'Lyst & let', 'sun' ),
			2 => array( 'Afdæmpet & cinematisk', 'moon' ),
			3 => array( 'Varmt & hyggeligt', 'home' ),
		);
		$mood_fields[ "s247_mood{$i}_label" ] = array( 'label' => sprintf( __( 'Stemning %d — label', 'studie247' ), $i ), 'type' => 'text', 'default' => $defaults[ $i ][0] );
		$mood_fields[ "s247_mood{$i}_icon" ]  = array( 'label' => sprintf( __( 'Stemning %d — ikon', 'studie247' ), $i ),  'type' => 'select', 'default' => $defaults[ $i ][1], 'choices' => $icon_choices );
		$mood_fields[ "s247_mood{$i}_image" ] = array( 'label' => sprintf( __( 'Stemning %d — billede', 'studie247' ), $i ), 'type' => 'image', 'default' => '' );
	}

	foreach ( $mood_fields as $key => $cfg ) {
		$sanitize = 'sanitize_text_field';
		if ( 'image' === $cfg['type'] || 'url' === $cfg['type'] ) {
			$sanitize = 'esc_url_raw';
		}
		$wp_customize->add_setting( $key, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );
		if ( 'image' === $cfg['type'] ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_moods',
			) ) );
		} elseif ( 'select' === $cfg['type'] ) {
			$wp_customize->add_control( $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_moods',
				'type'    => 'select',
				'choices' => $cfg['choices'],
			) );
		} else {
			$wp_customize->add_control( $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_moods',
				'type'    => 'text',
			) );
		}
	}
} );
