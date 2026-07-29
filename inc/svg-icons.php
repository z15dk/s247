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
		'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
		'moon'     => '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>',
		'home'     => '<path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-7h-6v7H4a1 1 0 01-1-1v-9.5z"/>',
		'square-video' => '<rect x="6" y="3" width="12" height="18" rx="2"/><path d="M10 9l5 3-5 3V9z"/>',
		'film'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 8h18M3 16h18M8 4v16M16 4v16"/>',
		'zap'      => '<path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/>',
		'feather'  => '<path d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5l6.74-6.76z"/><path d="M16 8L2 22"/><path d="M17.5 15H9"/>',
		'wind'     => '<path d="M9.59 4.59A2 2 0 1111 8H2M12.59 19.41A2 2 0 1014 16H2M17.73 7.73A2.5 2.5 0 1119.5 12H2"/>',
		'circle-dot' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="currentColor"/>',
		'mountain' => '<path d="M8 3l4 8 5-5 5 13H2L8 3z"/>',
		'palette'  => '<circle cx="12" cy="12" r="10"/><circle cx="7.5" cy="10.5" r="1" fill="currentColor"/><circle cx="12" cy="7.5" r="1" fill="currentColor"/><circle cx="16.5" cy="10.5" r="1" fill="currentColor"/><circle cx="15" cy="15.5" r="1" fill="currentColor"/>',
		'book'     => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20V3H6.5A2.5 2.5 0 004 5.5v14z"/><path d="M4 19.5A2.5 2.5 0 016.5 22H20"/>',
		'flame'    => '<path d="M8.5 14.5A2.5 2.5 0 0011 17c1.5 0 3-1 3-3 0-1.5-1-3-3-3s-3 1.5-3 3.5zM14 12c0-3 2-5 2-7 1 1 5 3 5 8a7 7 0 11-14 0c0-2 1-4 2-5 0 2 2 4 5 4z"/>',
		'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'calendar-clock' => '<path d="M21 10V6a2 2 0 00-2-2h-2M3 10V6a2 2 0 012-2h2"/><path d="M16 2v4M8 2v4M3 10h5"/><circle cx="17" cy="17" r="5"/><path d="M17 15v2l1 1"/>',
		'sunrise'  => '<path d="M17 18a5 5 0 10-10 0M12 2v7M4.22 10.22l1.42 1.42M1 18h2M21 18h2M18.36 11.64l1.42-1.42M23 22H1M8 6l4-4 4 4"/>',
		'infinity' => '<path d="M18 8a4 4 0 10-6 4 4 4 0 11-6 4"/>',
		'help'     => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>',
		'handshake'=> '<path d="M11 17l2 2 4-4M13 7l2-2 4 4-2 2M5 13l-2-2 4-4 2 2M17 13l-4-4-4 4-2-2 6-6 6 6-2 2z"/>',
		'rocket'   => '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91-.78-.79-2.07-.8-2.91-.09zM12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2zM9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
		'quote'    => '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.76-2.017-2-2H4c-1.25 0-2 .75-2 2v4c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1v2c0 1.25.75 2 2 2zM15 21c3 0 7-1 7-8V5c0-1.25-.76-2.017-2-2h-4c-1.25 0-2 .75-2 2v4c0 1.25.75 2 2 2h.008c1 0 1.003 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1v2c0 1.25.75 2 2 2z"/>',
		'lightbulb'=> '<path d="M9 18h6M10 22h4M12 2a7 7 0 017 7c0 2.5-1 4-2 5.5-.5.75-1 1.5-1 2.5H8c0-1-.5-1.75-1-2.5C6 13 5 11.5 5 9a7 7 0 017-7z"/>',
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
