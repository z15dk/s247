<?php
/**
 * Template tags and small view helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Split a heading into "sans first word" + "italic rest" per brand guide.
 *
 * Eg. "Vores services" → <span class="h-sans">Vores</span> <em class="h-italic">services</em>
 */
function studie247_dual_heading( $first, $rest, $tag = 'h2', $class = '' ) {
	$class = trim( 'heading-dual ' . $class );
	printf(
		'<%1$s class="%2$s"><span class="heading-dual__sans">%3$s</span> <em class="heading-dual__italic">%4$s</em></%1$s>',
		tag_escape( $tag ),
		esc_attr( $class ),
		esc_html( $first ),
		esc_html( $rest )
	);
}

/**
 * Find logo-fil i temaets assets/images/logo/ hvis den eksisterer.
 *
 * Forventede filnavne (enhver af dem virker):
 *  - studie247-main.svg      — STUDIE 247 horizontal, mørk
 *  - studie247-main-red.svg  — STUDIE 247 horizontal, rød
 *  - studie247-main-bone.svg — STUDIE 247 horizontal, lys (til mørk baggrund)
 *  - studie247-stacked.svg   — STUDIE / 247 stacked, rød
 *  - studie247-symbol.svg    — kun 247-mærket, mørk
 *  - studie247-symbol-red.svg
 *  - studie247-symbol-bone.svg
 *
 * PNG virker også (samme navn, .png-endelse).
 */
function studie247_logo_url( $variant = 'main' ) {
	$candidates = array(
		$variant . '.svg',
		$variant . '.png',
		'studie247-' . $variant . '.svg',
		'studie247-' . $variant . '.png',
	);
	foreach ( $candidates as $file ) {
		$path = STUDIE247_DIR . '/assets/images/logo/' . $file;
		if ( file_exists( $path ) ) {
			return STUDIE247_URI . '/assets/images/logo/' . $file;
		}
	}
	return '';
}

/**
 * Render the logo — prioriterer (1) tema-fil, (2) WP custom_logo, (3) typografisk fallback.
 *
 * @param string $variant  main | main-red | main-bone | stacked | symbol | symbol-red | symbol-bone
 * @param array  $args     Optional: height, class, alt
 */
function studie247_logo( $variant = 'main', $args = array() ) {
	$args = wp_parse_args( $args, array(
		'height' => 0,
		'class'  => '',
		'alt'    => __( 'Studie 247', 'studie247' ),
	) );

	$home = esc_url( home_url( '/' ) );

	// 1) Tema-fil?
	$url = studie247_logo_url( $variant );
	if ( $url ) {
		$class = trim( 'logo logo--file logo--' . $variant . ' ' . $args['class'] );
		$style = $args['height'] ? sprintf( 'height:%spx;width:auto;', absint( $args['height'] ) ) : '';
		printf(
			'<a class="%s" href="%s" aria-label="%s"><img src="%s" alt="%s" style="%s" loading="eager" decoding="async"></a>',
			esc_attr( $class ),
			$home,
			esc_attr( $args['alt'] ),
			esc_url( $url ),
			esc_attr( $args['alt'] ),
			esc_attr( $style )
		);
		return;
	}

	// 2) WordPress custom_logo?
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	// 3) Typografisk fallback.
	if ( strpos( $variant, 'symbol' ) !== false ) {
		printf( '<a class="logo logo--symbol" href="%s" aria-label="%s"><span>247</span></a>', $home, esc_attr( $args['alt'] ) );
	} else {
		printf(
			'<a class="logo logo--main" href="%s" aria-label="%s"><span class="logo__word">STUDIE</span><span class="logo__num">247</span></a>',
			$home,
			esc_attr( $args['alt'] )
		);
	}
}

/**
 * Button helper.
 */
function studie247_button( $text, $url, $variant = 'primary', $extra_class = '' ) {
	$class = sprintf( 'btn btn--%s %s', esc_attr( $variant ), esc_attr( $extra_class ) );
	printf( '<a class="%s" href="%s">%s</a>', $class, esc_url( $url ), esc_html( $text ) );
}

/**
 * Split title from a post into 2 words so we can style with dual heading.
 * If the title has 1 word it returns array('', $title) — caller decides.
 */
function studie247_split_title( $title ) {
	$parts = preg_split( '/\s+/', trim( $title ), 2 );
	if ( count( $parts ) < 2 ) {
		return array( '', $parts[0] );
	}
	return array( $parts[0], $parts[1] );
}
