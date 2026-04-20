<?php
/**
 * Studie 247 — audit log.
 *
 * Registrerer alle ændringer (status-skift, intern-toggle, håndteret-
 * toggle, pris-ændring, sletning osv.) med bruger + tidsstempel.
 * Gemmes i option s247_audit_log (sidste 100 poster).
 *
 * Vises i bunden af dashboardet.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skriv en log-post.
 *
 * @param string $message  Fx "godkendte Lars' booking"
 * @param int    $target_id Post-ID (valgfri).
 * @param string $target_type CPT-slug (valgfri).
 */
function studie247_audit_log( $message, $target_id = 0, $target_type = '' ) {
	$uid  = get_current_user_id();
	$user = $uid ? get_userdata( $uid ) : null;
	$entry = array(
		'time'        => current_time( 'mysql' ),
		'ts'          => current_time( 'timestamp' ),
		'user_id'     => $uid,
		'user_name'   => $user ? $user->display_name : ( $uid ? '#' . $uid : __( 'System', 'studie247' ) ),
		'message'     => (string) $message,
		'target_id'   => (int) $target_id,
		'target_type' => (string) $target_type,
	);
	$log = get_option( 's247_audit_log', array() );
	if ( ! is_array( $log ) ) { $log = array(); }
	array_unshift( $log, $entry );
	$log = array_slice( $log, 0, 100 );
	update_option( 's247_audit_log', $log, false );
}

/**
 * Hent de sidste N poster.
 */
function studie247_audit_log_get( $limit = 20 ) {
	$log = get_option( 's247_audit_log', array() );
	if ( ! is_array( $log ) ) { return array(); }
	return array_slice( $log, 0, max( 1, (int) $limit ) );
}

/* ───────── Automatiske loggere ───────── */

/**
 * Log status-skift på booking og kontakt_besked.
 * transition_post_status har signaturen ($new_status, $old_status, $post).
 */
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	if ( $old_status === $new_status ) { return; }
	if ( 'new' === $old_status && 'pending' === $new_status ) { return; } // ignore creation
	if ( ! in_array( $post->post_type, array( 'booking', 'kontakt_besked' ), true ) ) { return; }

	$target_label = studie247_audit_label_for( $post );
	$type_label   = 'booking' === $post->post_type ? 'bookingen' : 'beskeden';

	// Kortlæg status til menneske-tekst.
	$s_map = array(
		'pending' => __( 'afventer godkendelse', 'studie247' ),
		'publish' => __( 'godkendt', 'studie247' ),
		'trash'   => __( 'afvist/aflyst', 'studie247' ),
		'draft'   => __( 'kladde', 'studie247' ),
	);
	$from = $s_map[ $old_status ] ?? $old_status;
	$to   = $s_map[ $new_status ] ?? $new_status;

	studie247_audit_log(
		sprintf(
			/* translators: 1: type-label (bookingen/beskeden) 2: gammel status 3: ny status 4: target-label (navn + dato) */
			__( 'ændrede %1$s fra %2$s → %3$s (%4$s)', 'studie247' ),
			$type_label,
			$from,
			$to,
			$target_label
		),
		$post->ID,
		$post->post_type
	);
}, 10, 3 );

/**
 * Log _s247_internal-toggle på booking.
 */
add_action( 'updated_post_meta', function ( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_s247_internal' !== $meta_key ) { return; }
	if ( 'booking' !== get_post_type( $post_id ) ) { return; }
	static $guard = false;
	if ( $guard ) { return; }
	$guard = true;

	$on    = ( '1' === (string) $meta_value );
	$label = studie247_audit_label_for( get_post( $post_id ) );
	studie247_audit_log(
		sprintf(
			$on ? __( 'markerede %s som intern brug', 'studie247' ) : __( 'fjernede intern-markering fra %s', 'studie247' ),
			$label
		),
		$post_id,
		'booking'
	);
	$guard = false;
}, 20, 4 );

/**
 * Log _s247_msg_handled-toggle på kontakt_besked.
 */
add_action( 'added_post_meta', 'studie247_log_handled_change', 10, 4 );
add_action( 'updated_post_meta', 'studie247_log_handled_change', 10, 4 );
add_action( 'deleted_post_meta', function ( $meta_ids, $post_id, $meta_key ) {
	if ( '_s247_msg_handled' !== $meta_key ) { return; }
	if ( 'kontakt_besked' !== get_post_type( $post_id ) ) { return; }
	$label = studie247_audit_label_for( get_post( $post_id ) );
	studie247_audit_log( sprintf( __( 'fjernede håndteret-flag på beskeden fra %s', 'studie247' ), $label ), $post_id, 'kontakt_besked' );
}, 20, 3 );

function studie247_log_handled_change( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_s247_msg_handled' !== $meta_key ) { return; }
	if ( 'kontakt_besked' !== get_post_type( $post_id ) ) { return; }
	if ( '1' !== (string) $meta_value ) { return; }
	$label = studie247_audit_label_for( get_post( $post_id ) );
	studie247_audit_log( sprintf( __( 'markerede beskeden fra %s som håndteret', 'studie247' ), $label ), $post_id, 'kontakt_besked' );
}

/**
 * Log sletning (before trash/delete).
 */
add_action( 'before_delete_post', function ( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || ! in_array( $post->post_type, array( 'booking', 'kontakt_besked', 'udlejning_item' ), true ) ) { return; }
	$label     = studie247_audit_label_for( $post );
	$type_name = array( 'booking' => 'bookingen', 'kontakt_besked' => 'beskeden', 'udlejning_item' => 'varen' )[ $post->post_type ] ?? 'posten';
	studie247_audit_log( sprintf( __( 'slettede %1$s %2$s permanent', 'studie247' ), $type_name, $label ), $post_id, $post->post_type );
} );

/**
 * Bygger et menneske-læseligt label for en post (bruges i log-beskeder).
 */
function studie247_audit_label_for( $post ) {
	if ( ! $post ) { return ''; }
	if ( 'booking' === $post->post_type ) {
		$name = get_post_meta( $post->ID, '_s247_name', true ) ?: '(uden navn)';
		$date = get_post_meta( $post->ID, '_s247_date', true );
		return $name . ( $date ? ' (' . date_i18n( 'j. M', strtotime( $date ) ) . ')' : '' );
	}
	if ( 'kontakt_besked' === $post->post_type ) {
		$name = get_post_meta( $post->ID, '_s247_name', true ) ?: '(uden navn)';
		return $name;
	}
	return $post->post_title;
}

/**
 * Relativ dansk tid: "for 3 min siden" osv.
 */
function studie247_audit_time_ago( $ts ) {
	$diff = max( 0, current_time( 'timestamp' ) - (int) $ts );
	if ( $diff < 60 )       { return __( 'lige nu', 'studie247' ); }
	if ( $diff < 3600 )     { return sprintf( __( 'for %d min siden', 'studie247' ), floor( $diff / 60 ) ); }
	if ( $diff < 86400 )    { return sprintf( __( 'for %d timer siden', 'studie247' ), floor( $diff / 3600 ) ); }
	if ( $diff < 172800 )   { return __( 'i går', 'studie247' ); }
	if ( $diff < 604800 )   { return sprintf( __( 'for %d dage siden', 'studie247' ), floor( $diff / 86400 ) ); }
	return date_i18n( 'j. M Y', $ts );
}
