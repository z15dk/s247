<?php
/**
 * Inline SVG icons. Single source of truth so we can style them with
 * currentColor and keep bundle tiny.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function studie247_icon( $name, $size = 32, $classes = '' ) {
	$icons = array(
		'video'    => '<path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
		'mic'      => '<path d="M12 2a3 3 0 00-3 3v7a3 3 0 006 0V5a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 11-14 0v-2M12 19v4M8 23h8"/>',
		'academic' => '<path d="M12 3L2 8l10 5 10-5-10-5z"/><path d="M6 10.5V16c0 1.5 3 3 6 3s6-1.5 6-3v-5.5"/>',
		'camera'   => '<path d="M3 9a2 2 0 012-2h2.5l1.5-2h6l1.5 2H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="4"/>',
		'sparkle'  => '<path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4L12 2z"/>',
		'play'     => '<polygon points="6 4 20 12 6 20 6 4"/>',
		'arrow-right' => '<path d="M5 12h14M13 5l7 7-7 7"/>',
		'check'    => '<path d="M20 6L9 17l-5-5"/>',
		'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
		'close'    => '<path d="M18 6L6 18M6 6l12 12"/>',
		'phone'    => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.9.34 1.78.66 2.61a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.47-1.23a2 2 0 012.11-.45c.83.32 1.71.54 2.61.66A2 2 0 0122 16.92z"/>',
		'mail'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
		'pin'      => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$size    = absint( $size );
	$classes = esc_attr( 'icon icon--' . $name . ( $classes ? ' ' . $classes : '' ) );

	return sprintf(
		'<svg class="%s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		$classes,
		$size,
		$size,
		$icons[ $name ]
	);
}
