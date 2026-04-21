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

	// Hero tagline (stor sætning) — HTML tilladt.
	$wp_customize->add_setting( 's247_hero_tagline', array(
		'default'           => 'Optag. Skab. <em>Udgiv</em> — døgnet rundt.',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 's247_hero_tagline', array(
		'label'       => __( 'Hero — tagline', 'studie247' ),
		'description' => __( 'Den store sætning lige under hero-billedet. HTML tilladt (fx <em>kursiv</em>).', 'studie247' ),
		'section'     => 's247_hero',
		'type'        => 'textarea',
	) );

	// Hero underteksten.
	$wp_customize->add_setting( 's247_hero_sub', array(
		'default'           => 'Vi producerer podcasts, SoMe-videoer, online kurser og fotoshoots — fra idé til færdigt resultat.',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 's247_hero_sub', array(
		'label'       => __( 'Hero — undertekst', 'studie247' ),
		'description' => __( 'Den mindre tekst under taglinen.', 'studie247' ),
		'section'     => 's247_hero',
		'type'        => 'textarea',
	) );

	/* ───────── Services-sektion (forside "02 Services") ───────── */
	$wp_customize->add_section( 's247_services_section', array(
		'title'    => __( 'Services-sektion (forside)', 'studie247' ),
		'priority' => 33,
	) );

	$services_fields = array(
		's247_services_eyebrow' => array(
			'label'   => __( 'Eyebrow (over titlen)', 'studie247' ),
			'type'    => 'text',
			'default' => 'Services',
		),
		's247_services_title_a' => array(
			'label'   => __( 'Overskrift — del 1 (sans)', 'studie247' ),
			'type'    => 'text',
			'default' => 'Hvad vi',
		),
		's247_services_title_b' => array(
			'label'   => __( 'Overskrift — del 2 (kursiv)', 'studie247' ),
			'type'    => 'text',
			'default' => 'laver for dig',
		),
		's247_services_lead'    => array(
			'label'   => __( 'Intro-tekst (HTML tilladt)', 'studie247' ),
			'type'    => 'textarea',
			'default' => 'Vi skræddersyer hver produktion — men alting starter samme sted: <em>vores studie og vores team.</em>',
		),
	);

	foreach ( $services_fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => ( 'textarea' === $cfg['type'] ) ? 'wp_kses_post' : 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $cfg['label'],
			'section' => 's247_services_section',
			'type'    => $cfg['type'],
		) );
	}

	/* ───────── Proces-sektion (forside "03 Proces") ───────── */
	$wp_customize->add_section( 's247_process_section', array(
		'title'    => __( 'Proces-sektion (forside)', 'studie247' ),
		'priority' => 34,
	) );

	$process_fields = array(
		's247_process_eyebrow' => array( 'label' => __( 'Eyebrow', 'studie247' ), 'type' => 'text', 'default' => 'Proces' ),
		's247_process_title_a' => array( 'label' => __( 'Titel — del 1 (sans)', 'studie247' ),    'type' => 'text', 'default' => 'Fra' ),
		's247_process_title_b' => array( 'label' => __( 'Titel — del 2 (kursiv)', 'studie247' ),  'type' => 'text', 'default' => 'idé' ),
		's247_process_title_c' => array( 'label' => __( 'Titel — del 3 (sans)', 'studie247' ),    'type' => 'text', 'default' => 'til' ),
		's247_process_title_d' => array( 'label' => __( 'Titel — del 4 (kursiv)', 'studie247' ),  'type' => 'text', 'default' => 'udgivelse' ),
		's247_process_lead'    => array( 'label' => __( 'Intro-tekst (HTML tilladt)', 'studie247' ), 'type' => 'textarea', 'default' => 'En rolig og rutineret proces, hvor vi tager hånd om detaljerne — <em>så du kan fokusere på budskabet.</em>' ),
		's247_process_s1_title' => array( 'label' => __( 'Trin 1 — titel', 'studie247' ),       'type' => 'text', 'default' => 'Vi tager en snak' ),
		's247_process_s1_desc'  => array( 'label' => __( 'Trin 1 — beskrivelse', 'studie247' ), 'type' => 'textarea', 'default' => 'Du fortæller om projektet — vi hjælper med at ramme det rette format, længde og look.' ),
		's247_process_s2_title' => array( 'label' => __( 'Trin 2 — titel', 'studie247' ),       'type' => 'text', 'default' => 'Vi producerer sammen' ),
		's247_process_s2_desc'  => array( 'label' => __( 'Trin 2 — beskrivelse', 'studie247' ), 'type' => 'textarea', 'default' => 'Du møder op, og vi sørger for udstyr, opsætning og instruktion. Fokus på dit budskab.' ),
		's247_process_s3_title' => array( 'label' => __( 'Trin 3 — titel', 'studie247' ),       'type' => 'text', 'default' => 'Vi klipper og finisher' ),
		's247_process_s3_desc'  => array( 'label' => __( 'Trin 3 — beskrivelse', 'studie247' ), 'type' => 'textarea', 'default' => 'Klip, lyd, farveretouche og grafik — leveret fleksibelt og til tiden.' ),
		's247_process_s4_title' => array( 'label' => __( 'Trin 4 — titel', 'studie247' ),       'type' => 'text', 'default' => 'Klar til udgivelse' ),
		's247_process_s4_desc'  => array( 'label' => __( 'Trin 4 — beskrivelse', 'studie247' ), 'type' => 'textarea', 'default' => 'Du får filer i alle relevante formater og er klar til at udgive — 247.' ),
	);

	foreach ( $process_fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => ( 'textarea' === $cfg['type'] ) ? 'wp_kses_post' : 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $cfg['label'],
			'section' => 's247_process_section',
			'type'    => $cfg['type'],
		) );
	}

	/* ───────── FAQ-sektion (forside "06 FAQ") ───────── */
	$wp_customize->add_section( 's247_faq_section', array(
		'title'    => __( 'FAQ-sektion (forside)', 'studie247' ),
		'priority' => 36,
	) );

	$faq_defaults = array(
		1 => array( 'Hvad er inkluderet i en booking?', 'Adgang til studiet med alt fast udstyr, rigelig opsætningstid og en producer der hjælper dig i gang. Specifikt udstyr kan tillægges.' ),
		2 => array( 'Hvor hurtigt får jeg det færdige materiale?', 'Typisk inden for 5-10 arbejdsdage, afhængigt af omfang. Hastelevering er muligt.' ),
		3 => array( 'Kan jeg leje studiet uden produktion?', 'Ja. Vi udlejer også studiet råt til erfarne produktionsteams. Se siden Studiet for detaljer.' ),
		4 => array( 'Kan I hjælpe med manuskript og idéudvikling?', 'Ja. Vi har producere og tekstforfattere, som kan hjælpe fra idé til færdigt script.' ),
		5 => array( 'Hvor ligger studiet?', 'Aarhus — præcis adresse får du med booking-bekræftelsen. Der er parkering og god offentlig transport.' ),
		6 => array( '', '' ),
		7 => array( '', '' ),
		8 => array( '', '' ),
	);

	$faq_fields = array(
		's247_faq_eyebrow' => array( 'label' => __( 'Eyebrow', 'studie247' ), 'type' => 'text', 'default' => 'FAQ' ),
		's247_faq_title_a' => array( 'label' => __( 'Titel — del 1 (sans)', 'studie247' ),    'type' => 'text', 'default' => 'Ofte stillede' ),
		's247_faq_title_b' => array( 'label' => __( 'Titel — del 2 (kursiv)', 'studie247' ),  'type' => 'text', 'default' => 'spørgsmål' ),
		's247_faq_lead'    => array( 'label' => __( 'Intro-tekst (HTML tilladt)', 'studie247' ), 'type' => 'textarea', 'default' => 'Stilles ofte nok til at vi samlede dem her. Mangler du svar? <em>Skriv til os.</em>' ),
	);
	for ( $n = 1; $n <= 8; $n++ ) {
		$faq_fields[ "s247_faq_q{$n}" ] = array( 'label' => sprintf( __( 'Spørgsmål %d', 'studie247' ), $n ), 'type' => 'text',     'default' => $faq_defaults[ $n ][0] );
		$faq_fields[ "s247_faq_a{$n}" ] = array( 'label' => sprintf( __( 'Svar %d', 'studie247' ), $n ),     'type' => 'textarea', 'default' => $faq_defaults[ $n ][1] );
	}

	foreach ( $faq_fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => ( 'textarea' === $cfg['type'] ) ? 'wp_kses_post' : 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $cfg['label'],
			'section' => 's247_faq_section',
			'type'    => $cfg['type'],
		) );
	}

	/* ───────── Kontakt-info (menu + footer + mails) ───────── */
	$wp_customize->add_section( 's247_contact_info', array(
		'title'    => __( 'Kontakt-info (telefon / email)', 'studie247' ),
		'priority' => 28,
	) );

	$contact_fields = array(
		's247_phone' => array( 'label' => __( 'Telefonnummer (vises i menu/footer)', 'studie247' ), 'default' => '+45 00 00 00 00', 'description' => __( 'Skriv som du vil have det vist, fx "+45 12 34 56 78". Tel-linket genereres automatisk ved at fjerne mellemrum.', 'studie247' ) ),
		's247_email' => array( 'label' => __( 'Kontakt-email (vises i footer)', 'studie247' ),     'default' => 'hej@s247.dk', 'description' => __( 'Bruges også som From/Reply-To på auto-mails hvis intet andet er sat.', 'studie247' ) ),
	);

	foreach ( $contact_fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'       => $cfg['label'],
			'description' => $cfg['description'] ?? '',
			'section'     => 's247_contact_info',
			'type'        => 'text',
		) );
	}

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
	$mood_defaults = array(
		1 => array( 'Lyst & let', 'sun' ),
		2 => array( 'Afdæmpet & cinematisk', 'moon' ),
		3 => array( 'Varmt & hyggeligt', 'home' ),
		4 => array( '', 'sparkle' ),
		5 => array( '', 'video' ),
		6 => array( '', 'mic' ),
		7 => array( '', 'camera' ),
		8 => array( '', 'play' ),
		9 => array( '', 'sparkle' ),
	);
	for ( $i = 1; $i <= 9; $i++ ) {
		$mood_fields[ "s247_mood{$i}_label" ] = array( 'label' => sprintf( __( 'Stemning %d — label', 'studie247' ), $i ), 'type' => 'text', 'default' => $mood_defaults[ $i ][0] );
		$mood_fields[ "s247_mood{$i}_icon" ]  = array( 'label' => sprintf( __( 'Stemning %d — ikon', 'studie247' ), $i ),  'type' => 'select', 'default' => $mood_defaults[ $i ][1], 'choices' => $icon_choices );
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

	/* ───────── Om-side (team) ───────── */
	$wp_customize->add_section( 's247_om', array(
		'title'    => __( 'Om-side (team)', 'studie247' ),
		'priority' => 34,
	) );

	$wp_customize->add_setting( 's247_om_intro', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 's247_om_intro', array(
		'label'       => __( 'Kort tekst om os', 'studie247' ),
		'description' => __( 'Vises øverst på Om-siden. HTML tilladt.', 'studie247' ),
		'section'     => 's247_om',
		'type'        => 'textarea',
	) );

	/* ───────── Studiet-side (dedikeret) ───────── */
	$wp_customize->add_section( 's247_studiet_page', array(
		'title'    => __( 'Studiet-side', 'studie247' ),
		'priority' => 35,
	) );

	$studiet_fields = array(
		's247_studiet_video'       => array( 'label' => 'Hero-video (MP4 URL)', 'type' => 'url',      'default' => '' ),
		's247_studiet_poster'      => array( 'label' => 'Hero-poster/fallback-billede', 'type' => 'image', 'default' => '' ),
		's247_studiet_eyebrow'     => array( 'label' => 'Eyebrow over overskrift',       'type' => 'text', 'default' => 'Studiet' ),
		's247_studiet_title_a'     => array( 'label' => 'Overskrift — del 1 (sans)',     'type' => 'text', 'default' => 'Hvor dit' ),
		's247_studiet_title_b'     => array( 'label' => 'Overskrift — del 2 (kursiv)',   'type' => 'text', 'default' => 'indhold skabes' ),
		's247_studiet_lead'        => array( 'label' => 'Under-tekst',                   'type' => 'textarea', 'default' => '200 m² produktionsrum. Kamera-rig, lys, lyd, cyklorama — alt klart fra dag 1.' ),
		's247_studiet_cta_text'    => array( 'label' => 'Primær knap — tekst',           'type' => 'text', 'default' => 'Book studiet' ),
		's247_studiet_cta_url'     => array( 'label' => 'Primær knap — URL',             'type' => 'url',  'default' => '/booking-studie/' ),

		/* Book-sektion */
		's247_studiet_book_title_a' => array( 'label' => 'Book-sektion overskrift del 1 (sans)', 'type' => 'text', 'default' => 'Book vores studie' ),
		's247_studiet_book_title_b' => array( 'label' => 'Book-sektion overskrift del 2 (kursiv)', 'type' => 'text', 'default' => 'online' ),
		's247_studiet_book_lead'    => array( 'label' => 'Book-sektion tekst',          'type' => 'textarea', 'default' => 'Vælg tid, vælg setup, betal — vi gør resten klar inden du kommer.' ),
		's247_studiet_book_cta'     => array( 'label' => 'Book-knap tekst',             'type' => 'text', 'default' => 'Book nu' ),
		's247_studiet_book_url'     => array( 'label' => 'Book-knap URL',               'type' => 'url',  'default' => '/booking-studie/' ),
		's247_studiet_book_psst'    => array( 'label' => 'Psst-linje (ekstra tip)',     'type' => 'textarea', 'default' => '<em>psst</em> — du kan også leje vores udstyr herfra.' ),
		's247_studiet_book_psst_cta'=> array( 'label' => 'Psst — knap tekst',           'type' => 'text', 'default' => 'Se udlejning' ),
		's247_studiet_book_psst_url'=> array( 'label' => 'Psst — knap URL',             'type' => 'url',  'default' => '/udlejning/' ),

		/* Guide-sektion */
		's247_studiet_guide_eyebrow' => array( 'label' => 'Guide — eyebrow',            'type' => 'text', 'default' => 'Guide' ),
		's247_studiet_guide_title_a' => array( 'label' => 'Guide — overskrift del 1 (sans)', 'type' => 'text', 'default' => 'Sådan booker du' ),
		's247_studiet_guide_title_b' => array( 'label' => 'Guide — overskrift del 2 (kursiv)', 'type' => 'text', 'default' => 'uden at græde' ),

		// Guide-CTA (mørkt card efter guide-stepsene).
		's247_studiet_guide_cta_badge'     => array( 'label' => 'Guide-CTA — badge-tal',            'type' => 'text', 'default' => '60' ),
		's247_studiet_guide_cta_badge_unit'=> array( 'label' => 'Guide-CTA — badge-enhed',          'type' => 'text', 'default' => 'sek' ),
		's247_studiet_guide_cta_title_a'   => array( 'label' => 'Guide-CTA — overskrift del 1',     'type' => 'text', 'default' => 'Så kort tager det.' ),
		's247_studiet_guide_cta_title_b'   => array( 'label' => 'Guide-CTA — overskrift del 2 (kursiv)', 'type' => 'text', 'default' => 'Gå i gang.' ),
		's247_studiet_guide_cta_lead'      => array( 'label' => 'Guide-CTA — under-tekst',          'type' => 'textarea', 'default' => 'Vil du se rummet først? Kig forbi til en gratis rundvisning — 15 min, ingen forpligtelser.' ),
		's247_studiet_guide_cta_button'    => array( 'label' => 'Guide-CTA — primær knap tekst',    'type' => 'text', 'default' => 'Book studiet' ),
		's247_studiet_guide_cta_button_url'=> array( 'label' => 'Guide-CTA — primær knap URL',      'type' => 'url',  'default' => '/booking-studie/' ),
		's247_studiet_guide_cta_link'      => array( 'label' => 'Guide-CTA — sekundær link tekst',  'type' => 'text', 'default' => 'eller tag en rundvisning først' ),
		's247_studiet_guide_cta_link_url'  => array( 'label' => 'Guide-CTA — sekundær link URL',    'type' => 'url',  'default' => '/kontakt-os/?emne=rundvisning' ),
		's247_studiet_guide_cta_video'     => array( 'label' => 'Guide-CTA — video (MP4 URL)',      'type' => 'url',   'default' => '' ),
		's247_studiet_guide_cta_poster'    => array( 'label' => 'Guide-CTA — video-poster/billede', 'type' => 'image', 'default' => '' ),

		// Stats-strip under hero (4 kolonner).
		's247_studiet_stat1_num'   => array( 'label' => 'Stat 1 — tal',   'type' => 'text', 'default' => '200' ),
		's247_studiet_stat1_unit'  => array( 'label' => 'Stat 1 — enhed', 'type' => 'text', 'default' => 'm²' ),
		's247_studiet_stat1_label' => array( 'label' => 'Stat 1 — label', 'type' => 'text', 'default' => 'Produktionsrum' ),

		's247_studiet_stat2_num'   => array( 'label' => 'Stat 2 — tal',   'type' => 'text', 'default' => '4K' ),
		's247_studiet_stat2_unit'  => array( 'label' => 'Stat 2 — enhed', 'type' => 'text', 'default' => '' ),
		's247_studiet_stat2_label' => array( 'label' => 'Stat 2 — label', 'type' => 'text', 'default' => 'Kameraer klar' ),

		's247_studiet_stat3_num'   => array( 'label' => 'Stat 3 — tal',   'type' => 'text', 'default' => '48' ),
		's247_studiet_stat3_unit'  => array( 'label' => 'Stat 3 — enhed', 'type' => 'text', 'default' => 'kanaler' ),
		's247_studiet_stat3_label' => array( 'label' => 'Stat 3 — label', 'type' => 'text', 'default' => 'Lyd-mixer' ),

		's247_studiet_stat4_num'   => array( 'label' => 'Stat 4 — tal',   'type' => 'text', 'default' => '24/7' ),
		's247_studiet_stat4_unit'  => array( 'label' => 'Stat 4 — enhed', 'type' => 'text', 'default' => '' ),
		's247_studiet_stat4_label' => array( 'label' => 'Stat 4 — label', 'type' => 'text', 'default' => 'Book online' ),
	);

	/* 6 setup-valg */
	for ( $i = 1; $i <= 6; $i++ ) {
		$studiet_fields[ "s247_studiet_setup{$i}_label" ] = array( 'label' => sprintf( 'Setup %d — label', $i ), 'type' => 'text', 'default' => '' );
		$studiet_fields[ "s247_studiet_setup{$i}_desc" ]  = array( 'label' => sprintf( 'Setup %d — beskrivelse', $i ), 'type' => 'text', 'default' => '' );
		$studiet_fields[ "s247_studiet_setup{$i}_image" ] = array( 'label' => sprintf( 'Setup %d — billede', $i ), 'type' => 'image', 'default' => '' );
	}
	// Standard setups
	$studiet_fields['s247_studiet_setup1_label']['default'] = 'Podcast';
	$studiet_fields['s247_studiet_setup1_desc']['default']  = '2-4 personer, 3 kameravinkler, rig til lyd.';
	$studiet_fields['s247_studiet_setup2_label']['default'] = 'Video-interview';
	$studiet_fields['s247_studiet_setup2_desc']['default']  = 'Cinematisk setup med prompter og dedikeret lys.';
	$studiet_fields['s247_studiet_setup3_label']['default'] = 'Talking-head';
	$studiet_fields['s247_studiet_setup3_desc']['default']  = 'Ren simpel baggrund, én person, hurtigt i gang.';
	$studiet_fields['s247_studiet_setup4_label']['default'] = 'Produkt / foto';
	$studiet_fields['s247_studiet_setup4_desc']['default']  = 'Cyklorama, softboxe, klar til still og bevægelse.';

	/* 3 portrait-videoer */
	for ( $i = 1; $i <= 3; $i++ ) {
		$studiet_fields[ "s247_studiet_reel{$i}_video" ] = array( 'label' => sprintf( 'Reel %d — video (MP4 URL)', $i ), 'type' => 'url',   'default' => '' );
		$studiet_fields[ "s247_studiet_reel{$i}_poster"] = array( 'label' => sprintf( 'Reel %d — poster',           $i ), 'type' => 'image', 'default' => '' );
		$studiet_fields[ "s247_studiet_reel{$i}_title" ] = array( 'label' => sprintf( 'Reel %d — titel',            $i ), 'type' => 'text',  'default' => '' );
		$studiet_fields[ "s247_studiet_reel{$i}_label" ] = array( 'label' => sprintf( 'Reel %d — label (fx kunde)', $i ), 'type' => 'text',  'default' => '' );
	}

	/* 5 guide-trin */
	for ( $i = 1; $i <= 5; $i++ ) {
		$studiet_fields[ "s247_studiet_step{$i}_title" ] = array( 'label' => sprintf( 'Guide-trin %d — titel',      $i ), 'type' => 'text', 'default' => '' );
		$studiet_fields[ "s247_studiet_step{$i}_text" ]  = array( 'label' => sprintf( 'Guide-trin %d — tekst',      $i ), 'type' => 'textarea', 'default' => '' );
		$studiet_fields[ "s247_studiet_step{$i}_joke" ]  = array( 'label' => sprintf( 'Guide-trin %d — sjov ekstra linje', $i ), 'type' => 'text', 'default' => '' );
	}
	$studiet_fields['s247_studiet_step1_title']['default'] = 'Find en ledig tid';
	$studiet_fields['s247_studiet_step1_text']['default']  = 'Åbn kalenderen og vælg dag + start/slut-tidspunkt. Ingen kode ord, ingen formular der varer 40 minutter.';
	$studiet_fields['s247_studiet_step1_joke']['default']  = 'Pro-tip: undgå fredag kl. 14 — det er når alle andre også vil.';
	$studiet_fields['s247_studiet_step2_title']['default'] = 'Vælg dit setup';
	$studiet_fields['s247_studiet_step2_text']['default']  = 'Podcast, interview, talking-head, foto? Klik det du skal have. Vi rigger klar.';
	$studiet_fields['s247_studiet_step2_joke']['default']  = 'Ja, du må godt skifte mening 3 gange. Vi sletter ikke din booking.';
	$studiet_fields['s247_studiet_step3_title']['default'] = 'Betal online';
	$studiet_fields['s247_studiet_step3_text']['default']  = 'Kort eller faktura. Du får en kvittering på mail plus en ICS-fil til din kalender.';
	$studiet_fields['s247_studiet_step3_joke']['default']  = 'Ingen skjulte gebyrer. Vi lover.';
	$studiet_fields['s247_studiet_step4_title']['default'] = 'Mød op — vi er klar';
	$studiet_fields['s247_studiet_step4_text']['default']  = 'Cyklorama hvidt, lys tændt, kaffe brygget. Du ringer på, vi åbner.';
	$studiet_fields['s247_studiet_step4_joke']['default']  = 'Medbring snacks. Vi har ingen følelser om din valg af slikketype.';
	$studiet_fields['s247_studiet_step5_title']['default'] = 'Optag, slap af, ud';
	$studiet_fields['s247_studiet_step5_text']['default']  = 'Når du er færdig, smider du bare døren til. Vi rydder op.';
	$studiet_fields['s247_studiet_step5_joke']['default']  = 'Du efterlader jer med godt indhold, vi efterlader os med ren gulvvask.';

	foreach ( $studiet_fields as $key => $cfg ) {
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
				'section' => 's247_studiet_page',
			) ) );
		} else {
			$wp_customize->add_control( $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_studiet_page',
				'type'    => 'textarea' === $cfg['type'] ? 'textarea' : ( 'url' === $cfg['type'] ? 'url' : 'text' ),
			) );
		}
	}

	/* ───────── Om-side (team) ───────── */
	$wp_customize->add_section( 's247_om', array(
		'title'    => __( 'Om-side (team)', 'studie247' ),
		'priority' => 34,
	) );

	$wp_customize->add_setting( 's247_om_intro', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 's247_om_intro', array(
		'label'       => __( 'Kort tekst om os', 'studie247' ),
		'description' => __( 'Vises øverst på Om-siden. HTML tilladt.', 'studie247' ),
		'section'     => 's247_om',
		'type'        => 'textarea',
	) );

	/* Hero-video + tekst øverst på Om-siden */
	$om_hero_fields = array(
		's247_om_hero_video'     => array( 'label' => 'Hero — video (MP4 URL)',        'type' => 'url',      'default' => '' ),
		's247_om_hero_poster'    => array( 'label' => 'Hero — poster/fallback-billede', 'type' => 'image',    'default' => '' ),
		's247_om_hero_eyebrow'   => array( 'label' => 'Hero — eyebrow',                 'type' => 'text',     'default' => 'Om Studie 247' ),
		's247_om_hero_title_a'   => array( 'label' => 'Hero — overskrift del 1 (sans)', 'type' => 'text',     'default' => 'Bag kameraet er' ),
		's247_om_hero_title_b'   => array( 'label' => 'Hero — overskrift del 2 (kursiv)','type' => 'text',     'default' => 'rigtige mennesker' ),
		's247_om_hero_lead'      => array( 'label' => 'Hero — under-tekst',             'type' => 'textarea', 'default' => 'Vi er et lille hold med store ambitioner — og en fælles drøm om at gøre dit indhold bedre.' ),
	);
	foreach ( $om_hero_fields as $key => $cfg ) {
		$sanitize = 'sanitize_text_field';
		if ( 'textarea' === $cfg['type'] ) { $sanitize = 'wp_kses_post'; }
		elseif ( 'url' === $cfg['type'] )  { $sanitize = 'esc_url_raw'; }
		elseif ( 'image' === $cfg['type'] ){ $sanitize = 'esc_url_raw'; }
		$wp_customize->add_setting( $key, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );
		if ( 'image' === $cfg['type'] ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_om',
			) ) );
		} else {
			$wp_customize->add_control( $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_om',
				'type'    => 'textarea' === $cfg['type'] ? 'textarea' : ( 'url' === $cfg['type'] ? 'url' : 'text' ),
			) );
		}
	}

	/* Manifest-block (3 principper) + stats-strip */
	$om_extra_fields = array(
		// Stor editorial-statement (mørkt fuld-breddebanner)
		's247_om_statement_eyebrow'    => array( 'label' => 'Statement — eyebrow',       'type' => 'text',     'default' => 'Det vi tror på' ),
		's247_om_statement_text_a'     => array( 'label' => 'Statement — tekst del 1',   'type' => 'textarea', 'default' => 'Vi bliver der lidt længere.' ),
		's247_om_statement_text_em'    => array( 'label' => 'Statement — tekst del 2 (kursiv accent)', 'type' => 'textarea', 'default' => 'Vi tager en ekstra take.' ),
		's247_om_statement_text_b'     => array( 'label' => 'Statement — tekst del 3',   'type' => 'textarea', 'default' => 'Det er ikke en service — det er en arbejdsmoral.' ),

		// Foto-collage (5 billeder i asymmetrisk grid)
		's247_om_collage_eyebrow'      => array( 'label' => 'Collage — eyebrow',         'type' => 'text',     'default' => 'Fra studiet' ),
		's247_om_collage_1'            => array( 'label' => 'Collage — billede 1 (stort, venstre)',  'type' => 'image', 'default' => '' ),
		's247_om_collage_2'            => array( 'label' => 'Collage — billede 2',       'type' => 'image',    'default' => '' ),
		's247_om_collage_3'            => array( 'label' => 'Collage — billede 3',       'type' => 'image',    'default' => '' ),
		's247_om_collage_4'            => array( 'label' => 'Collage — billede 4',       'type' => 'image',    'default' => '' ),
		's247_om_collage_5'            => array( 'label' => 'Collage — billede 5',       'type' => 'image',    'default' => '' ),

		's247_om_manifest_eyebrow'  => array( 'label' => 'Manifest — eyebrow',     'type' => 'text',     'default' => 'Vores principper' ),
		's247_om_manifest_title'    => array( 'label' => 'Manifest — overskrift',  'type' => 'text',     'default' => 'Det vi holder fast i' ),
		's247_om_manifest_1'        => array( 'label' => 'Princip 1',              'type' => 'textarea', 'default' => 'Vi siger nej, når vi mener nej.' ),
		's247_om_manifest_2'        => array( 'label' => 'Princip 2',              'type' => 'textarea', 'default' => 'Et minut for meget er et minut for dårligt.' ),
		's247_om_manifest_3'        => array( 'label' => 'Princip 3',              'type' => 'textarea', 'default' => 'Hvis det ikke er sjovt, så er det forkert.' ),

		's247_om_stat1_num'   => array( 'label' => 'Stat 1 — tal',   'type' => 'text', 'default' => '6' ),
		's247_om_stat1_unit'  => array( 'label' => 'Stat 1 — enhed', 'type' => 'text', 'default' => 'år' ),
		's247_om_stat1_label' => array( 'label' => 'Stat 1 — label', 'type' => 'text', 'default' => 'i branchen' ),
		's247_om_stat2_num'   => array( 'label' => 'Stat 2 — tal',   'type' => 'text', 'default' => '340+' ),
		's247_om_stat2_unit'  => array( 'label' => 'Stat 2 — enhed', 'type' => 'text', 'default' => '' ),
		's247_om_stat2_label' => array( 'label' => 'Stat 2 — label', 'type' => 'text', 'default' => 'Projekter leveret' ),
		's247_om_stat3_num'   => array( 'label' => 'Stat 3 — tal',   'type' => 'text', 'default' => '4.500+' ),
		's247_om_stat3_unit'  => array( 'label' => 'Stat 3 — enhed', 'type' => 'text', 'default' => 'timer' ),
		's247_om_stat3_label' => array( 'label' => 'Stat 3 — label', 'type' => 'text', 'default' => 'Optage-tid i studiet' ),
		's247_om_stat4_num'   => array( 'label' => 'Stat 4 — tal',   'type' => 'text', 'default' => '96%' ),
		's247_om_stat4_unit'  => array( 'label' => 'Stat 4 — enhed', 'type' => 'text', 'default' => '' ),
		's247_om_stat4_label' => array( 'label' => 'Stat 4 — label', 'type' => 'text', 'default' => 'Vender tilbage' ),
	);
	foreach ( $om_extra_fields as $key => $cfg ) {
		$sanitize = 'sanitize_text_field';
		if ( 'textarea' === $cfg['type'] )     { $sanitize = 'wp_kses_post'; }
		elseif ( 'image' === $cfg['type'] )    { $sanitize = 'esc_url_raw'; }
		$wp_customize->add_setting( $key, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => $sanitize,
			'transport'         => 'refresh',
		) );
		if ( 'image' === $cfg['type'] ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_om',
			) ) );
		} else {
			$wp_customize->add_control( $key, array(
				'label'   => $cfg['label'],
				'section' => 's247_om',
				'type'    => 'textarea' === $cfg['type'] ? 'textarea' : 'text',
			) );
		}
	}

	for ( $i = 1; $i <= 8; $i++ ) {
		$fields = array(
			"s247_team{$i}_name"        => array( 'label' => sprintf( __( 'Person %d — navn', 'studie247' ), $i ), 'type' => 'text' ),
			"s247_team{$i}_role"        => array( 'label' => sprintf( __( 'Person %d — rolle', 'studie247' ), $i ), 'type' => 'text' ),
			"s247_team{$i}_image"       => array( 'label' => sprintf( __( 'Person %d — kort-billede', 'studie247' ), $i ), 'type' => 'image' ),
			"s247_team{$i}_modal_image" => array( 'label' => sprintf( __( 'Person %d — popup-billede', 'studie247' ), $i ), 'type' => 'image' ),
			"s247_team{$i}_modal_text"  => array( 'label' => sprintf( __( 'Person %d — popup-tekst (HTML)', 'studie247' ), $i ), 'type' => 'textarea' ),
		);
		foreach ( $fields as $key => $cfg ) {
			$sanitize = 'sanitize_text_field';
			if ( 'image' === $cfg['type'] ) {
				$sanitize = 'esc_url_raw';
			} elseif ( 'textarea' === $cfg['type'] ) {
				$sanitize = 'wp_kses_post';
			}
			$wp_customize->add_setting( $key, array(
				'default'           => '',
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			) );
			if ( 'image' === $cfg['type'] ) {
				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, array(
					'label'   => $cfg['label'],
					'section' => 's247_om',
				) ) );
			} else {
				$wp_customize->add_control( $key, array(
					'label'   => $cfg['label'],
					'section' => 's247_om',
					'type'    => 'textarea' === $cfg['type'] ? 'textarea' : 'text',
				) );
			}
		}
	}
} );
