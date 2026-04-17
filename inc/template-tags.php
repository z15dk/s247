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
 * Render the logo (custom_logo or fallback SVG mark).
 */
function studie247_logo( $variant = 'main' ) {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	// Fallback: simple typographic mark.
	$url = esc_url( home_url( '/' ) );
	if ( $variant === 'symbol' ) {
		printf( '<a class="logo logo--symbol" href="%s" aria-label="Studie 247 forside"><span>247</span></a>', $url );
	} else {
		printf(
			'<a class="logo logo--main" href="%s" aria-label="Studie 247 forside"><span class="logo__word">STUDIE</span><span class="logo__num">247</span></a>',
			$url
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
