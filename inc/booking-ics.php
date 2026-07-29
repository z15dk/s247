<?php
/**
 * Booking → kalender: .ics-fil, Google Calendar + Outlook Web-links.
 *
 * Linkene indsættes i godkendelses-mailen så kunden kan tilføje
 * bookingen til deres kalender med ét klik.
 *
 * Sikkerhed: .ics-endpointet er offentligt (linket ligger i en mail),
 * men URL'en indeholder en HMAC-token udledt af booking-ID + wp_salt,
 * så kun den rette modtager kan gætte linket.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Udled en HMAC-token til en booking-ID. Bruger 'auth' salt så den er
 * stabil på tværs af requests men ikke gættes udefra.
 */
function studie247_booking_ics_token( $booking_id ) {
	return substr( hash_hmac( 'sha256', 'ics|' . (int) $booking_id, wp_salt( 'auth' ) ), 0, 24 );
}

/**
 * Beregn start/slut-tidsstempler (UTC) for en booking.
 * Returnerer null hvis der ikke er nok data til et kalender-event.
 */
function studie247_booking_ics_range( $booking_id ) {
	$date  = get_post_meta( $booking_id, '_s247_date', true );
	$start = get_post_meta( $booking_id, '_s247_start', true );
	$dur   = get_post_meta( $booking_id, '_s247_duration', true );
	$pid   = (int) get_post_meta( $booking_id, '_s247_produkt_id', true );
	if ( ! $date ) { return null; }

	$tz = new DateTimeZone( 'Europe/Copenhagen' );

	if ( $pid ) {
		// Udlejning: heldagsevent fra dato til dato + N dage.
		$days = 1;
		$map  = array(
			'1 dag' => 1, '2 dage' => 2, '3 dage' => 3, '4 dage' => 4,
			'1 uge' => 7, '2 uger' => 14,
		);
		if ( isset( $map[ $dur ] ) ) { $days = (int) $map[ $dur ]; }
		$start_dt = DateTime::createFromFormat( 'Y-m-d|', $date, $tz );
		if ( ! $start_dt ) { return null; }
		$end_dt = clone $start_dt;
		$end_dt->modify( "+{$days} days" );
		return array( 'start' => $start_dt, 'end' => $end_dt, 'allday' => true );
	}

	// Studie: start-klokkeslæt + varighed i timer.
	if ( ! $start ) { return null; }
	$hours_map = array( '6 timer' => 6, '12 timer' => 12 );
	$hours     = isset( $hours_map[ $dur ] ) ? $hours_map[ $dur ] : 3;
	$start_dt  = DateTime::createFromFormat( 'Y-m-d H:i', $date . ' ' . $start, $tz );
	if ( ! $start_dt ) { return null; }
	$end_dt = clone $start_dt;
	$end_dt->modify( "+{$hours} hours" );
	return array( 'start' => $start_dt, 'end' => $end_dt, 'allday' => false );
}

/**
 * Returnér links til ICS-download, Google Calendar og Outlook Web.
 * Tom array hvis bookingen ikke har dato.
 */
function studie247_booking_calendar_urls( $booking_id ) {
	$range = studie247_booking_ics_range( $booking_id );
	if ( ! $range ) { return array(); }

	$token = studie247_booking_ics_token( $booking_id );
	$ics   = add_query_arg(
		array(
			's247_ics' => 1,
			'b'        => (int) $booking_id,
			't'        => $token,
		),
		home_url( '/' )
	);

	$title = studie247_booking_event_title( $booking_id );
	$loc   = 'Studie 247';
	$desc  = sprintf( "Booking hos Studie 247\nSe detaljer: %s\n", home_url( '/' ) );

	$start = $range['start'];
	$end   = $range['end'];

	// Google + Outlook forventer UTC-tider i Zulu-format.
	$start_utc = clone $start; $start_utc->setTimezone( new DateTimeZone( 'UTC' ) );
	$end_utc   = clone $end;   $end_utc->setTimezone( new DateTimeZone( 'UTC' ) );

	if ( $range['allday'] ) {
		$google_dates  = $start->format( 'Ymd' ) . '/' . $end->format( 'Ymd' );
		$outlook_start = $start->format( 'Y-m-d' );
		$outlook_end   = $end->format( 'Y-m-d' );
	} else {
		$google_dates  = $start_utc->format( 'Ymd\THis\Z' ) . '/' . $end_utc->format( 'Ymd\THis\Z' );
		$outlook_start = $start_utc->format( 'Y-m-d\TH:i:s' ) . 'Z';
		$outlook_end   = $end_utc->format( 'Y-m-d\TH:i:s' ) . 'Z';
	}

	$google = add_query_arg( array(
		'action'   => 'TEMPLATE',
		'text'     => rawurlencode( $title ),
		'dates'    => $google_dates,
		'details'  => rawurlencode( $desc ),
		'location' => rawurlencode( $loc ),
	), 'https://calendar.google.com/calendar/render' );

	$outlook = add_query_arg( array(
		'path'       => '/calendar/action/compose',
		'rru'        => 'addevent',
		'subject'    => rawurlencode( $title ),
		'body'       => rawurlencode( $desc ),
		'location'   => rawurlencode( $loc ),
		'startdt'    => $outlook_start,
		'enddt'      => $outlook_end,
		'allday'     => $range['allday'] ? 'true' : 'false',
	), 'https://outlook.live.com/calendar/0/deeplink/compose' );

	return array(
		'ics'     => esc_url_raw( $ics ),
		'google'  => esc_url_raw( $google ),
		'outlook' => esc_url_raw( $outlook ),
	);
}

/**
 * Titel til kalender-eventet.
 */
function studie247_booking_event_title( $booking_id ) {
	$pid  = (int) get_post_meta( $booking_id, '_s247_produkt_id', true );
	if ( $pid ) {
		return sprintf( 'Udlejning: %s', get_the_title( $pid ) );
	}
	return 'Studie 247 — booking';
}

/**
 * Byg en gyldig ICS-body for en booking.
 */
function studie247_booking_ics_body( $booking_id ) {
	$range = studie247_booking_ics_range( $booking_id );
	if ( ! $range ) { return ''; }

	$title = studie247_booking_event_title( $booking_id );
	$loc   = 'Studie 247';
	$desc  = 'Booking hos Studie 247';

	$esc = function ( $s ) {
		return preg_replace( array( '/\\\\/', '/\r?\n/', '/,/', '/;/' ),
			array( '\\\\\\\\', '\\n', '\\,', '\\;' ), (string) $s );
	};

	$now = gmdate( 'Ymd\THis\Z' );
	$uid = sprintf( 'booking-%d@%s', (int) $booking_id, wp_parse_url( home_url(), PHP_URL_HOST ) );

	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//Studie 247//Booking//DA',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VEVENT',
		'UID:' . $uid,
		'DTSTAMP:' . $now,
	);

	if ( $range['allday'] ) {
		$lines[] = 'DTSTART;VALUE=DATE:' . $range['start']->format( 'Ymd' );
		$lines[] = 'DTEND;VALUE=DATE:'   . $range['end']->format( 'Ymd' );
	} else {
		$s_utc = clone $range['start']; $s_utc->setTimezone( new DateTimeZone( 'UTC' ) );
		$e_utc = clone $range['end'];   $e_utc->setTimezone( new DateTimeZone( 'UTC' ) );
		$lines[] = 'DTSTART:' . $s_utc->format( 'Ymd\THis\Z' );
		$lines[] = 'DTEND:'   . $e_utc->format( 'Ymd\THis\Z' );
	}

	$lines[] = 'SUMMARY:' . $esc( $title );
	$lines[] = 'LOCATION:' . $esc( $loc );
	$lines[] = 'DESCRIPTION:' . $esc( $desc );
	$lines[] = 'STATUS:CONFIRMED';
	$lines[] = 'END:VEVENT';
	$lines[] = 'END:VCALENDAR';

	return implode( "\r\n", $lines ) . "\r\n";
}

/* ───────── Public endpoint: /?s247_ics=1&b=ID&t=TOKEN ───────── */
add_action( 'init', function () {
	if ( empty( $_GET['s247_ics'] ) || empty( $_GET['b'] ) || empty( $_GET['t'] ) ) { return; }

	$id    = (int) $_GET['b'];
	$token = sanitize_text_field( wp_unslash( $_GET['t'] ) );
	if ( 'booking' !== get_post_type( $id ) ) { wp_die( __( 'Ukendt booking.', 'studie247' ), 404 ); }
	if ( ! hash_equals( studie247_booking_ics_token( $id ), $token ) ) {
		wp_die( __( 'Ugyldigt link.', 'studie247' ), 403 );
	}

	$body = studie247_booking_ics_body( $id );
	if ( ! $body ) { wp_die( __( 'Bookingen har ikke nok data til et kalender-event.', 'studie247' ), 400 ); }

	nocache_headers();
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( sprintf( 'Content-Disposition: attachment; filename="studie247-booking-%d.ics"', $id ) );
	echo $body;
	exit;
} );
