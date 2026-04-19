<?php
/**
 * REST API til eksternt dashboard/CRM.
 *
 * Eksponerer 3 CPT'er med alle meta-felter så et eksternt system kan
 * hente komplet data via /wp-json/wp/v2/…:
 *
 *   - booking         — alle studie- og udlejnings-forespørgsler
 *   - udlejning_item  — alle varer/udstyr (inkl. pris, lager, SKU, UID)
 *   - kontakt_besked  — alle indkomne kontakt-form-beskeder
 *
 * Endpoints for booking + kontakt_besked er låst bag auth (edit_posts-
 * capability). udlejning_item er offentligt synlig på sitet og er derfor
 * også offentligt via REST (det påvirker ikke privat meta).
 *
 * Autentificering på dashboardets side: HTTP Basic Auth med brugernavn +
 * WordPress Application Password (Brugere → din bruger → Adgangskoder
 * til applikationer).
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ─────────────────────────────────────────────────────────────
 * Registrér meta-felter for REST-udlæsning
 * ───────────────────────────────────────────────────────────── */

/**
 * Felter der udstilles på `udlejning_item` (offentligt læsbare).
 */
function studie247_rest_udlejning_fields() {
	return array(
		'_s247_pris_dag'      => 'string',
		'_s247_pris_uge'      => 'string',
		'_s247_deposit'       => 'string',
		'_s247_sku'           => 'string',
		'_s247_in_stock'      => 'string',
		'_s247_antal'         => 'integer',
		'_s247_ejer'          => 'string',
		'_s247_serienummer'   => 'string',
		'_s247_product_uid'   => 'string',
		'_s247_state_images'  => 'string',
	);
}

/**
 * Felter der udstilles på `booking` (kun for auth'ede brugere).
 */
function studie247_rest_booking_fields() {
	return array(
		'_s247_date'             => 'string',
		'_s247_start'            => 'string',
		'_s247_duration'         => 'string',
		'_s247_name'             => 'string',
		'_s247_email'            => 'string',
		'_s247_phone'            => 'string',
		'_s247_company'          => 'string',
		'_s247_cvr'              => 'string',
		'_s247_notes'            => 'string',
		'_s247_produkt'          => 'string',
		'_s247_produkt_id'       => 'integer',
		'_s247_type'             => 'string',
		'_s247_estimated_price'  => 'integer',
		'_s247_use_type'         => 'string',
		'_s247_edit_type'        => 'string',
		'_s247_podcast_type'     => 'string',
		'_s247_tilkoeb'          => 'string',
		'_s247_video_count'      => 'integer',
		'_s247_video_duration'   => 'integer',
		'_s247_format'           => 'string',
		'_s247_consent_timestamp' => 'string',
		'_s247_consent_ip'       => 'string',
		'_s247_consent_ua'       => 'string',
	);
}

/**
 * Felter der udstilles på `kontakt_besked` (kun for auth'ede brugere).
 */
function studie247_rest_kontakt_besked_fields() {
	return array(
		'_s247_name'             => 'string',
		'_s247_email'            => 'string',
		'_s247_phone'            => 'string',
		'_s247_topic'            => 'string',
		'_s247_side'             => 'string',
		'_s247_message'          => 'string',
		'_s247_source'           => 'string',
		'_s247_consent_timestamp' => 'string',
		'_s247_consent_ip'       => 'string',
		'_s247_consent_ua'       => 'string',
	);
}

add_action( 'init', function () {
	$all = array(
		'udlejning_item' => studie247_rest_udlejning_fields(),
		'booking'        => studie247_rest_booking_fields(),
		'kontakt_besked' => studie247_rest_kontakt_besked_fields(),
	);
	foreach ( $all as $post_type => $fields ) {
		foreach ( $fields as $key => $type ) {
			register_post_meta( $post_type, $key, array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => $type,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			) );
		}
	}
}, 20 );

/* ─────────────────────────────────────────────────────────────
 * Auth-gate: booking + kontakt_besked kræver edit_posts
 * ───────────────────────────────────────────────────────────── */

add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( null !== $result ) { return $result; }

	$route = $request->get_route();
	// Match både /wp/v2/booking og /wp/v2/booking/{id}.
	$restricted = array( '/wp/v2/booking', '/wp/v2/kontakt_besked' );
	$hit = false;
	foreach ( $restricted as $prefix ) {
		if ( strpos( $route, $prefix ) === 0 ) { $hit = true; break; }
	}
	if ( ! $hit ) { return $result; }

	if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
		return new WP_Error(
			'studie247_rest_forbidden',
			__( 'Adgang nægtet. Brug Application Password til en bruger med redigerings-rettigheder.', 'studie247' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return $result;
}, 10, 3 );

/* ─────────────────────────────────────────────────────────────
 * Udlejnings-statistik pr. produkt — kræver auth fordi stats afslører
 * kunde-navne/datoer. Eksponerer:
 *   total_inquiries, pending_inquiries, approved_rentals,
 *   active_rentals, upcoming_rentals, total_earnings, rental_history
 * ───────────────────────────────────────────────────────────── */

/**
 * Map varigheds-tekst til antal dage (udlejning).
 */
function studie247_duration_to_days( $dur ) {
	$map = array(
		'1 dag'  => 1, '2 dage' => 2, '3 dage' => 3, '4 dage' => 4,
		'1 uge'  => 7, '2 uger' => 14,
	);
	return isset( $map[ $dur ] ) ? (int) $map[ $dur ] : 1;
}

/**
 * Byg en komplet analyse af udlejning for ét produkt.
 */
function studie247_product_rental_stats( $product_id ) {
	$today_ts = strtotime( gmdate( 'Y-m-d' ) );
	$all = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => array( 'publish', 'pending', 'trash', 'draft' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array( 'key' => '_s247_produkt_id', 'value' => (int) $product_id, 'compare' => '=' ),
		),
	) );

	$stats = array(
		'total_inquiries'    => count( $all ),
		'pending_inquiries'  => 0,
		'approved_rentals'   => 0,
		'rejected_inquiries' => 0,
		'active_rentals'     => 0,  // lige nu igang (start ≤ today ≤ end)
		'upcoming_rentals'   => 0,  // start > today
		'completed_rentals'  => 0,  // end < today
		'total_earnings'     => 0,  // sum af estimated_price for godkendte
		'rental_history'     => array(),
	);

	foreach ( $all as $b ) {
		$status = $b->post_status;
		if ( 'pending' === $status ) { $stats['pending_inquiries']++; }
		if ( 'trash'   === $status ) { $stats['rejected_inquiries']++; }
		if ( 'publish' !== $status ) { continue; }

		$stats['approved_rentals']++;
		$start_date = get_post_meta( $b->ID, '_s247_date',     true );
		$duration   = get_post_meta( $b->ID, '_s247_duration', true );
		$price      = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
		$days       = studie247_duration_to_days( $duration );
		$start_ts   = $start_date ? strtotime( $start_date ) : 0;
		$end_ts     = $start_ts ? strtotime( '+' . ( $days - 1 ) . ' days', $start_ts ) : 0;

		$state = 'upcoming';
		if ( $start_ts && $end_ts ) {
			if ( $end_ts < $today_ts )      { $state = 'completed'; $stats['completed_rentals']++; }
			elseif ( $start_ts <= $today_ts && $today_ts <= $end_ts ) { $state = 'active'; $stats['active_rentals']++; }
			else                            { $stats['upcoming_rentals']++; }
		}

		$stats['total_earnings'] += $price;
		$stats['rental_history'][] = array(
			'id'         => $b->ID,
			'state'      => $state,
			'start_date' => $start_date,
			'end_date'   => $end_ts ? gmdate( 'Y-m-d', $end_ts ) : '',
			'duration'   => $duration,
			'price'      => $price,
			'customer'   => get_post_meta( $b->ID, '_s247_name',    true ),
			'email'      => get_post_meta( $b->ID, '_s247_email',   true ),
			'company'    => get_post_meta( $b->ID, '_s247_company', true ),
			'cvr'        => get_post_meta( $b->ID, '_s247_cvr',     true ),
		);
	}

	return $stats;
}

add_action( 'rest_api_init', function () {
	register_rest_field( 'udlejning_item', 'rental_stats', array(
		'get_callback' => function ( $object ) {
			// Kun auth'ede kan se kunde-navne / omsætning.
			if ( ! current_user_can( 'edit_posts' ) ) { return null; }
			return studie247_product_rental_stats( (int) ( $object['id'] ?? 0 ) );
		},
		'schema' => array(
			'type'        => 'object',
			'description' => 'Komplet udlejnings-statistik for dette produkt (kræver auth).',
		),
	) );
} );
