<?php
/**
 * Aktiverer SVG-uploads i WordPress.
 *
 * WP blokerer SVG som default af sikkerhedsårsager. Vi tillader dem,
 * men sanitizer dem strengt så ingen <script>/<iframe>/on-handlers
 * kommer igennem.
 *
 * Kun admin-roller kan uploade SVG.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Tilføj SVG til tilladte MIME-typer. */
add_filter( 'upload_mimes', function ( $mimes ) {
	// Kun brugere der kan uploade filer — primært admins.
	if ( ! current_user_can( 'upload_files' ) ) {
		return $mimes;
	}
	$mimes['svg']  = 'image/svg+xml';
	$mimes['svgz'] = 'image/svg+xml';
	return $mimes;
} );

/** Fix WP's MIME-check som ellers fejler for SVG. */
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes ) {
	if ( ! empty( $data['type'] ) ) {
		return $data;
	}

	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( $ext === 'svg' ) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svg';
	} elseif ( $ext === 'svgz' ) {
		$data['type'] = 'image/svg+xml';
		$data['ext']  = 'svgz';
	}
	return $data;
}, 10, 4 );

/** Basal SVG-sanitization: afvis farlige tags/attributter. */
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
	if ( empty( $file['type'] ) || $file['type'] !== 'image/svg+xml' ) {
		return $file;
	}
	if ( ! current_user_can( 'upload_files' ) ) {
		$file['error'] = __( 'Du har ikke rettigheder til at uploade SVG-filer.', 'studie247' );
		return $file;
	}

	$content = @file_get_contents( $file['tmp_name'] );
	if ( $content === false ) {
		$file['error'] = __( 'Kunne ikke læse SVG-filen.', 'studie247' );
		return $file;
	}

	$blocked_patterns = array(
		'/<script\b/i',
		'/<iframe\b/i',
		'/<object\b/i',
		'/<embed\b/i',
		'/<foreignObject\b/i',
		'/javascript:/i',
		'/\bon\w+\s*=/i',   // onclick=, onload=, osv.
		'/<!DOCTYPE[^>]*ENTITY/i',
	);
	foreach ( $blocked_patterns as $pattern ) {
		if ( preg_match( $pattern, $content ) ) {
			$file['error'] = __( 'SVG indeholder kode der ikke er tilladt (fx <script> eller event-handlers).', 'studie247' );
			return $file;
		}
	}

	return $file;
} );

/** Sørg for at SVGs vises som thumbnails i Media Library. */
add_filter( 'wp_prepare_attachment_for_js', function ( $response, $attachment ) {
	if ( $response['type'] === 'image' && $response['subtype'] === 'svg+xml' ) {
		$response['image'] = array( 'src' => $response['url'] );
		$response['thumb'] = array( 'src' => $response['url'] );
	}
	return $response;
}, 10, 2 );
