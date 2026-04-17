<?php
/**
 * Structured data (JSON-LD) — Google SEO goldmine.
 *
 * Outputs:
 *  - LocalBusiness / Organization on every page
 *  - Service on single-service
 *  - FAQPage when the page has FAQ blocks
 *  - BreadcrumbList on non-front pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'studie247_output_schema', 50 );
function studie247_output_schema() {
	$graph = array();

	// Organization / LocalBusiness.
	$graph[] = array(
		'@type'    => 'LocalBusiness',
		'@id'      => home_url( '/#organization' ),
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'slogan'   => __( 'Optag. Skab. Udgiv 247.', 'studie247' ),
		'description' => get_bloginfo( 'description' ),
	);

	// Service schema.
	if ( is_singular( 'service' ) ) {
		$graph[] = array(
			'@type'       => 'Service',
			'name'        => get_the_title(),
			'description' => wp_strip_all_tags( get_the_excerpt() ),
			'provider'    => array( '@id' => home_url( '/#organization' ) ),
			'url'         => get_permalink(),
		);
	}

	// Breadcrumbs on non-front pages.
	if ( ! is_front_page() ) {
		$breadcrumbs = studie247_build_breadcrumb_schema();
		if ( ! empty( $breadcrumbs['itemListElement'] ) ) {
			$graph[] = $breadcrumbs;
		}
	}

	if ( empty( $graph ) ) {
		return;
	}

	$json = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

function studie247_build_breadcrumb_schema() {
	$items = array();
	$pos   = 1;

	$items[] = array(
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => __( 'Forside', 'studie247' ),
		'item'     => home_url( '/' ),
	);

	if ( is_singular() ) {
		global $post;
		if ( $post->post_type === 'service' ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => __( 'Services', 'studie247' ),
				'item'     => home_url( '/services/' ),
			);
		}
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}
