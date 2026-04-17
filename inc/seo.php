<?php
/**
 * SEO foundation (no plugin dependency).
 *
 * Adds: meta description, canonical, Open Graph, Twitter Card,
 * robots directives and language hints.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', 'studie247_seo_head', 2 );
function studie247_seo_head() {
	$description = studie247_get_meta_description();
	$title       = wp_get_document_title();
	$canonical   = studie247_get_canonical_url();
	$image       = studie247_get_og_image();
	$site_name   = get_bloginfo( 'name' );
	$locale      = get_locale();

	echo "\n<!-- Studie 247 SEO -->\n";
	if ( $description ) {
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $description ) );
	}
	if ( $canonical ) {
		printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( $canonical ) );
	}
	// Open Graph
	printf( "<meta property=\"og:type\" content=\"%s\">\n", is_singular() ? 'article' : 'website' );
	printf( "<meta property=\"og:title\" content=\"%s\">\n", esc_attr( $title ) );
	if ( $description ) {
		printf( "<meta property=\"og:description\" content=\"%s\">\n", esc_attr( $description ) );
	}
	printf( "<meta property=\"og:url\" content=\"%s\">\n", esc_url( $canonical ) );
	printf( "<meta property=\"og:site_name\" content=\"%s\">\n", esc_attr( $site_name ) );
	printf( "<meta property=\"og:locale\" content=\"%s\">\n", esc_attr( $locale ) );
	if ( $image ) {
		printf( "<meta property=\"og:image\" content=\"%s\">\n", esc_url( $image ) );
		echo "<meta property=\"og:image:width\" content=\"1200\">\n";
		echo "<meta property=\"og:image:height\" content=\"630\">\n";
	}
	// Twitter
	echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
	printf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $title ) );
	if ( $description ) {
		printf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $description ) );
	}
	if ( $image ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $image ) );
	}
	echo "<!-- / Studie 247 SEO -->\n\n";
}

function studie247_get_meta_description() {
	if ( is_singular() ) {
		global $post;
		$excerpt = get_the_excerpt( $post );
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}
		$content = wp_strip_all_tags( $post->post_content );
		return wp_trim_words( $content, 30, '…' );
	}
	if ( is_home() || is_front_page() ) {
		return get_bloginfo( 'description' ) ?: __( 'Optag. Skab. Udgiv 247. Dit studie — døgnet rundt.', 'studie247' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		return term_description( $term );
	}
	return get_bloginfo( 'description' );
}

function studie247_get_canonical_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_home() || is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return get_term_link( get_queried_object() );
	}
	return home_url( add_query_arg( null, null ) );
}

function studie247_get_og_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$img = get_the_post_thumbnail_url( null, 'full' );
		if ( $img ) {
			return $img;
		}
	}
	// Fallback to logo/hero once uploaded:
	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$img = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $img ) {
			return $img;
		}
	}
	return '';
}
