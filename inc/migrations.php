<?php
/**
 * Engangs-migrationer (defaults der skal sættes én gang efter deploy).
 *
 * Bruges fx til at indsætte standard team-bios uden at overskrive
 * eksisterende værdier. Hver migration har en option-flag så den kun
 * kører én gang.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'studie247_seed_team_bios', 20 );
add_action( 'after_setup_theme', 'studie247_seed_demo_service', 25 );
add_action( 'after_setup_theme', 'studie247_seed_dashboard_page', 30 );
add_action( 'after_setup_theme', 'studie247_backfill_customers', 35 );

function studie247_seed_team_bios() {
	$flag = 's247_team_bios_seeded_v1';
	if ( get_option( $flag ) ) {
		return;
	}

	// Map navn → bio. Matches case-insensitivt mod existing s247_team{i}_name.
	$bios = array(
		'rasmus h'   => 'Holder Studie 247 i live på alt det, der ikke handler om at trykke optag. Skriver mails, jagter ordentlige headers, og forhandler den næste reklame-pakke færdig før morgenkaffen. Hvis du har talt med os før, er det 90% sikkert Rasmus\' stemme du kender.',
		'emilie g'   => 'Hende der laver de klip du scrollede forbi tre gange og endte med at se. Tænker i hooks, transitions og lyrics-cues — og himler med øjnene hver gang nogen siger <em>"kan vi ikke bare lave en TikTok"</em>. Reels-nørder er hendes folk.',
		'frederik p' => 'Tager billeder før du har nået at sætte dig ordentligt. Hvis du tror du skal stå stille og smile, så har han sandsynligvis allerede fanget dig grine af en af Mads\' jokes. 12 års erfaring fra erhvervsportrætter til nat-events — og en stædig overbevisning om at det bedste lys altid er det naturlige.',
		'mads l'     => 'Ham der bygger din film færdig længe efter alle andre er gået hjem. Trimmer hvert klip ned til det sekund det skal være, og finder fejl i lyden ingen andre hørte. Lever på timeline-snacks og har glemt at tælle hvor mange "uhmm" han har klippet ud i karrieren.',
		'rune s'     => 'Hvis kameraet bibber, lyset blinker eller et kabel ikke vil virke — er det Rune der finder fejlen. Studie 247\'s stille superkraft, og den eneste der ved hvor de ekstra XLR\'er ligger. Drikker kaffen sort og tror grundlæggende ikke på sætningen <em>"det burde virke"</em>.',
	);

	for ( $i = 1; $i <= 8; $i++ ) {
		$name = strtolower( trim( (string) get_theme_mod( "s247_team{$i}_name", '' ) ) );
		if ( ! $name || ! isset( $bios[ $name ] ) ) {
			continue;
		}
		$existing_text = get_theme_mod( "s247_team{$i}_modal_text", '' );
		if ( $existing_text ) {
			continue; // Respektér eksisterende bio.
		}
		set_theme_mod( "s247_team{$i}_modal_text", $bios[ $name ] );
	}

	update_option( $flag, time() );
}

/**
 * Engangs-backfill: kør alle eksisterende bookinger + beskeder
 * gennem customer_upsert() så CRM-arkivet starter med fuld historik.
 */
function studie247_backfill_customers() {
	$flag = 's247_customers_backfilled_v1';
	if ( get_option( $flag ) ) { return; }
	if ( ! function_exists( 'studie247_customer_upsert' ) ) { return; }

	$bookings = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => array( 'pending', 'publish', 'trash' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	foreach ( $bookings as $bid ) {
		if ( get_post_meta( $bid, '_s247_cust_id', true ) ) { continue; }
		$cid = studie247_customer_upsert( array(
			'name'    => get_post_meta( $bid, '_s247_name', true ),
			'email'   => get_post_meta( $bid, '_s247_email', true ),
			'phone'   => get_post_meta( $bid, '_s247_phone', true ),
			'company' => get_post_meta( $bid, '_s247_company', true ),
			'cvr'     => get_post_meta( $bid, '_s247_cvr', true ),
		) );
		if ( $cid ) { update_post_meta( $bid, '_s247_cust_id', $cid ); }
	}

	$msgs = get_posts( array(
		'post_type'      => 'kontakt_besked',
		'post_status'    => array( 'publish', 'trash' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	foreach ( $msgs as $mid ) {
		if ( get_post_meta( $mid, '_s247_cust_id', true ) ) { continue; }
		$cid = studie247_customer_upsert( array(
			'name'  => get_post_meta( $mid, '_s247_name', true ),
			'email' => get_post_meta( $mid, '_s247_email', true ),
			'phone' => get_post_meta( $mid, '_s247_phone', true ),
		) );
		if ( $cid ) { update_post_meta( $mid, '_s247_cust_id', $cid ); }
	}

	update_option( $flag, time() );
}

/**
 * Opret en 'Dashboard'-side med slug 'dashboard' og Dashboard-template
 * hvis den ikke allerede findes.
 */
function studie247_seed_dashboard_page() {
	$flag = 's247_dashboard_page_seeded_v1';
	if ( get_option( $flag ) ) { return; }

	$existing = get_page_by_path( 'dashboard' );
	if ( ! $existing ) {
		$page_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Dashboard',
			'post_name'    => 'dashboard',
			'post_content' => '',
		) );
		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-dashboard.php' );
		}
	} else {
		// Sikr at template er sat hvis siden blev oprettet manuelt.
		$current_template = get_post_meta( $existing->ID, '_wp_page_template', true );
		if ( 'page-dashboard.php' !== $current_template ) {
			update_post_meta( $existing->ID, '_wp_page_template', 'page-dashboard.php' );
		}
	}
	update_option( $flag, time() );
}

/**
 * Seed en demo-service ("Podcast-produktion") så det dynamiske single-
 * service-layout kan ses uden at brugeren først skal udfylde alt selv.
 * Kører kun én gang; springer over hvis demo-posten allerede findes.
 */
function studie247_seed_demo_service() {
	$flag = 's247_demo_service_seeded_v1';
	if ( get_option( $flag ) ) { return; }

	$existing = get_page_by_path( 'podcast-produktion', OBJECT, 'service' );
	if ( $existing ) {
		update_option( $flag, time() );
		return;
	}

	$content  = "<h2>Fra idé til færdig episode</h2>\n\n";
	$content .= "<p>Vi bygger din podcast op omkring dit brand og dine lyttere. Fra den første sparring over optagelse i studiet til redigering, mastering og publicering på alle større platforme — du får en færdig pakke uden bekymringer undervejs.</p>\n\n";
	$content .= "<h3>Sådan forløber en produktion</h3>\n\n";
	$content .= "<p>Vi starter med et kort sparringsmøde hvor vi aftaler tone, længde, målgruppe og interviewstil. Dagen før optagelse får du et manuskript-skelet du kan læse igennem. I studiet tager vi os af alt det tekniske — du skal bare tale.</p>\n\n";
	$content .= "<p>Efter optagelse redigerer vi klippet til en strammere version uden fyldord, og leverer den som færdig MP3 samt en video-version til sociale medier. Du får 2 revisioner inkluderet.</p>\n\n";
	$content .= "<h3>Hvad kunder typisk spørger om</h3>\n\n";
	$content .= "<p><strong>Kan jeg bruge vores egen vært?</strong> Ja, vi leverer gerne bare teknik og redigering hvis I selv har en intern vært. Vi kan også stille en erfaren vært til rådighed mod tillæg.</p>\n\n";
	$content .= "<p><strong>Hvor lang tid tager en typisk episode?</strong> Fra optagelse til færdig episode regner vi med 5-7 hverdage. Har du deadline? Sig til — vi kan ofte forcere hvis du ringer i god tid.</p>";

	$post_id = wp_insert_post( array(
		'post_type'    => 'service',
		'post_status'  => 'publish',
		'post_title'   => 'Podcast-produktion',
		'post_name'    => 'podcast-produktion',
		'post_content' => $content,
	) );

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_s247_tagline',   'Lyd der fanger — og holder ører åbne helt til sidst.' );
		update_post_meta( $post_id, '_s247_icon',      'mic' );
		update_post_meta( $post_id, '_s247_startpris', 'fra 4.995 kr' );
		update_post_meta( $post_id, '_s247_cta_text',  'Book podcast' );
		update_post_meta( $post_id, '_s247_included', implode( "\n", array(
			'Sparringsmøde (30 min)',
			'Studie-tid op til 3 timer',
			'Op til 4 mikrofoner + kamera-rig',
			'Fuld redigering + mastering',
			'Upload til Spotify, Apple Podcasts m.fl.',
			'2 revisionsrunder',
			'Video-version til SoMe',
		) ) );
		update_post_meta( $post_id, '_s247_statement', "Vi optager ikke bare din podcast.\n*Vi gør den værd at lytte til.*" );
	}

	update_option( $flag, time() );
}
