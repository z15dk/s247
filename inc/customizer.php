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
