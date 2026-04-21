<?php
/**
 * Template Name: Dashboard
 *
 * Moderne dashboard med egen shell — ingen site-header/footer.
 * Bag login-gate, sektioner styret af per-bruger tilladelser.
 *
 * @package Studie247
 */

// Ingen get_header() — vi bygger egen minimal shell.
nocache_headers();

/**
 * CSV-eksport af alle kunder (admin + edit_posts med CRM-adgang).
 * Streames direkte og exit — kører før enhver anden output.
 */
if ( isset( $_GET['export'] ) && 'customers_csv' === $_GET['export']
	&& is_user_logged_in() && current_user_can( 'edit_posts' )
	&& ( studie247_can_view_dash( 'customers' ) || current_user_can( 'manage_options' ) )
) {
	check_admin_referer( 's247_export_customers' );

	$is_admin_user      = current_user_can( 'manage_options' );
	$cust_can_revenue   = studie247_can_view_dash( 'revenue' ) || $is_admin_user;
	$show_studio_spend  = $is_admin_user || ( $cust_can_revenue && studie247_can_view_dash( 'studio' ) );
	$show_rental_spend  = $is_admin_user || ( $cust_can_revenue && studie247_can_view_dash( 'rental' ) );

	$customers = get_posts( array(
		'post_type'      => 's247_customer',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );

	$filename = 'studie247-kunder-' . date( 'Y-m-d' ) . '.csv';
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" );

	$header = array( 'Navn', 'E-mail', 'Telefon', 'Virksomhed', 'CVR',
		'Nyhedsbrev', 'Nyhedsbrev_tilmeldt', 'Antal_bookinger', 'Antal_beskeder' );
	if ( $show_studio_spend ) { $header[] = 'Studie_forbrug_DKK'; }
	if ( $show_rental_spend ) { $header[] = 'Udlejning_forbrug_DKK'; }
	if ( $show_studio_spend && $show_rental_spend ) { $header[] = 'Samlet_forbrug_DKK'; }
	$header = array_merge( $header, array( 'Første_gang', 'Sidst_set', 'Interne_noter' ) );
	fputcsv( $out, $header, ';' );

	foreach ( $customers as $c ) {
		$spend_studio = 0;
		$spend_rental = 0;
		$bk = $ms = 0;
		if ( function_exists( 'studie247_customer_activity' ) ) {
			$act = studie247_customer_activity( $c->ID );
			foreach ( $act['bookings'] as $b ) {
				if ( 'publish' !== $b->post_status ) { continue; }
				$p   = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
				$pid = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
				if ( $pid ) { $spend_rental += $p; } else { $spend_studio += $p; }
			}
			$bk = count( $act['bookings'] );
			$ms = count( $act['messages'] );
		}

		$row = array(
			get_post_meta( $c->ID, '_s247_cust_name', true ),
			get_post_meta( $c->ID, '_s247_cust_email', true ),
			get_post_meta( $c->ID, '_s247_cust_phone', true ),
			get_post_meta( $c->ID, '_s247_cust_company', true ),
			get_post_meta( $c->ID, '_s247_cust_cvr', true ),
			'1' === get_post_meta( $c->ID, '_s247_cust_newsletter', true ) ? 'Ja' : 'Nej',
			get_post_meta( $c->ID, '_s247_cust_newsletter_ts', true ),
			$bk, $ms,
		);
		if ( $show_studio_spend ) { $row[] = $spend_studio; }
		if ( $show_rental_spend ) { $row[] = $spend_rental; }
		if ( $show_studio_spend && $show_rental_spend ) { $row[] = $spend_studio + $spend_rental; }
		$row[] = get_post_meta( $c->ID, '_s247_cust_first_seen', true );
		$row[] = get_post_meta( $c->ID, '_s247_cust_last_seen', true );
		$row[] = str_replace( array( "\r", "\n" ), ' / ', (string) get_post_meta( $c->ID, '_s247_cust_notes', true ) );
		fputcsv( $out, $row, ';' );
	}
	fclose( $out );

	if ( function_exists( 'studie247_audit_log' ) ) {
		studie247_audit_log( __( 'eksporterede kunde-CSV', 'studie247' ), 0, 's247_customer' );
	}
	exit;
}

/**
 * CSV-eksport af bookinger for en given måned og type (studio/rental/all).
 * Kræver relevant sektion-adgang (studio → studie-data, rental → rental-data).
 */
if ( isset( $_GET['export'] ) && 'monthly_bookings' === $_GET['export']
	&& is_user_logged_in() && current_user_can( 'edit_posts' )
) {
	check_admin_referer( 's247_export_monthly' );

	$type  = sanitize_key( $_GET['type'] ?? 'all' );
	$month = sanitize_text_field( $_GET['month'] ?? '' );
	if ( ! preg_match( '/^\d{4}-\d{2}$/', $month ) ) {
		$month = date( 'Y-m', strtotime( 'first day of last month' ) );
	}

	$is_admin_user = current_user_can( 'manage_options' );
	$has_studio    = $is_admin_user || studie247_can_view_dash( 'studio' );
	$has_rental    = $is_admin_user || studie247_can_view_dash( 'rental' );
	if ( 'studio' === $type && ! $has_studio ) { wp_die( esc_html__( 'Ingen adgang.', 'studie247' ), 403 ); }
	if ( 'rental' === $type && ! $has_rental ) { wp_die( esc_html__( 'Ingen adgang.', 'studie247' ), 403 ); }
	if ( 'all' === $type && ! $has_studio && ! $has_rental ) { wp_die( esc_html__( 'Ingen adgang.', 'studie247' ), 403 ); }

	$can_revenue        = studie247_can_view_dash( 'revenue' ) || $is_admin_user;
	$show_studio_price  = $is_admin_user || ( $can_revenue && $has_studio );
	$show_rental_price  = $is_admin_user || ( $can_revenue && $has_rental );

	$start_ts = strtotime( $month . '-01' );
	$end_ts   = strtotime( '+1 month', $start_ts );
	$date_from = date( 'Y-m-d', $start_ts );
	$date_to   = date( 'Y-m-d', $end_ts - 86400 );

	$bookings = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => array( 'pending', 'publish', 'trash' ),
		'posts_per_page' => -1,
		'meta_query'     => array(
			array( 'key' => '_s247_date', 'value' => array( $date_from, $date_to ), 'compare' => 'BETWEEN', 'type' => 'DATE' ),
		),
		'orderby'        => 'meta_value',
		'meta_key'       => '_s247_date',
		'order'          => 'ASC',
	) );

	$filename = 'studie247-bookinger-' . $type . '-' . $month . '.csv';
	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" );

	fputcsv( $out, array(
		'Type', 'Booking_ID', 'Dato', 'Varighed', 'Produkt',
		'Status', 'Intern',
		'Kunde', 'E-mail', 'Telefon', 'Virksomhed', 'CVR',
		'Pris_DKK',
		'Besked', 'Oprettet',
	), ';' );

	$status_map = array( 'pending' => 'Afventer', 'publish' => 'Godkendt', 'trash' => 'Afvist' );

	foreach ( $bookings as $b ) {
		$pid     = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
		$row_typ = $pid ? 'Udlejning' : 'Studie';
		if ( 'studio' === $type && $pid ) { continue; }
		if ( 'rental' === $type && ! $pid ) { continue; }
		if ( 'all' === $type ) {
			if ( $pid && ! $has_rental ) { continue; }
			if ( ! $pid && ! $has_studio ) { continue; }
		}

		$price     = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
		$internal  = '1' === get_post_meta( $b->ID, '_s247_internal', true );
		$price_out = '';
		if ( $pid ? $show_rental_price : $show_studio_price ) {
			$price_out = $price;
		}

		fputcsv( $out, array(
			$row_typ,
			$b->ID,
			get_post_meta( $b->ID, '_s247_date', true ),
			get_post_meta( $b->ID, '_s247_duration', true ),
			$pid ? get_the_title( $pid ) : get_post_meta( $b->ID, '_s247_produkt', true ),
			$status_map[ $b->post_status ] ?? $b->post_status,
			$internal ? 'Ja' : 'Nej',
			get_post_meta( $b->ID, '_s247_name', true ),
			get_post_meta( $b->ID, '_s247_email', true ),
			get_post_meta( $b->ID, '_s247_phone', true ),
			get_post_meta( $b->ID, '_s247_company', true ),
			get_post_meta( $b->ID, '_s247_cvr', true ),
			$price_out,
			str_replace( array( "\r", "\n" ), ' / ', (string) get_post_meta( $b->ID, '_s247_message', true ) ),
			get_the_date( 'Y-m-d H:i', $b ),
		), ';' );
	}
	fclose( $out );

	if ( function_exists( 'studie247_audit_log' ) ) {
		studie247_audit_log( sprintf( __( 'eksporterede bookinger (%1$s, %2$s)', 'studie247' ), $type, $month ), 0, 'booking' );
	}
	exit;
}

/**
 * POST-handler: opret, opdatér eller slet brugere (admin-only).
 */
if ( isset( $_POST['s247_dash_user_action'] ) && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
	check_admin_referer( 's247_dash_users' );
	$action = sanitize_key( $_POST['s247_dash_user_action'] );
	$sections = array_keys( studie247_dash_sections() );

	if ( 'create' === $action ) {
		$email = sanitize_email( wp_unslash( $_POST['new_email'] ?? '' ) );
		$first = sanitize_text_field( wp_unslash( $_POST['new_first_name'] ?? '' ) );
		$last  = sanitize_text_field( wp_unslash( $_POST['new_last_name'] ?? '' ) );
		$role  = sanitize_key( $_POST['new_role'] ?? 'editor' );
		$send  = ! empty( $_POST['new_send_mail'] );
		$perms = (array) ( $_POST['perms'] ?? array() );

		if ( ! is_email( $email ) || email_exists( $email ) || username_exists( $email ) ) {
			wp_safe_redirect( add_query_arg( array( 'view' => 'users', 'err' => 'exists' ), home_url( '/dashboard/' ) ) );
			exit;
		}
		$pwd    = wp_generate_password( 14, true );
		$new_id = wp_insert_user( array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $pwd,
			'first_name'   => $first,
			'last_name'    => $last,
			'display_name' => trim( $first . ' ' . $last ) ?: $email,
			'role'         => in_array( $role, array( 'administrator', 'editor' ), true ) ? $role : 'editor',
		) );
		if ( is_wp_error( $new_id ) ) {
			wp_safe_redirect( add_query_arg( array( 'view' => 'users', 'err' => 'create' ), home_url( '/dashboard/' ) ) );
			exit;
		}
		// Sæt tilladelser
		foreach ( $sections as $sec ) {
			if ( in_array( $sec, $perms, true ) ) {
				update_user_meta( $new_id, '_s247_dash_view_' . $sec, '1' );
			}
		}
		// Send velkomst-mail
		if ( $send ) {
			$subject = __( 'Velkommen til Studie 247 dashboard', 'studie247' );
			$body    = sprintf(
				__( "Hej %s,\n\nDu har nu adgang til dashboardet på:\n%s\n\nBrugernavn: %s\nAdgangskode: %s\n\nLog ind og skift adgangskoden ved første besøg.\n\n— Studie 247", 'studie247' ),
				$first ?: $email,
				home_url( '/dashboard/' ),
				$email,
				$pwd
			);
			wp_mail( $email, $subject, $body );
		}
		if ( function_exists( 'studie247_audit_log' ) ) {
			studie247_audit_log( sprintf( __( 'oprettede ny bruger %s (%s)', 'studie247' ), trim( $first . ' ' . $last ) ?: $email, $role ), $new_id, 'user' );
		}
		wp_safe_redirect( add_query_arg( array( 'view' => 'users', 'created' => $new_id ), home_url( '/dashboard/' ) ) );
		exit;
	}

	if ( 'update' === $action ) {
		$uid = (int) ( $_POST['user_id'] ?? 0 );
		if ( ! $uid || $uid === get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( add_query_arg( array( 'view' => 'users' ), home_url( '/dashboard/' ) ) );
			exit;
		}
		$perms = (array) ( $_POST['perms'] ?? array() );
		foreach ( $sections as $sec ) {
			if ( in_array( $sec, $perms, true ) ) {
				update_user_meta( $uid, '_s247_dash_view_' . $sec, '1' );
			} else {
				delete_user_meta( $uid, '_s247_dash_view_' . $sec );
			}
		}
		// Opdatér rolle hvis angivet
		if ( isset( $_POST['role'] ) ) {
			$new_role = sanitize_key( $_POST['role'] );
			if ( in_array( $new_role, array( 'administrator', 'editor' ), true ) ) {
				$u = new WP_User( $uid );
				$u->set_role( $new_role );
			}
		}
		if ( function_exists( 'studie247_audit_log' ) ) {
			$u = get_userdata( $uid );
			studie247_audit_log( sprintf( __( 'opdaterede tilladelser for %s', 'studie247' ), $u ? $u->display_name : '#' . $uid ), $uid, 'user' );
		}
		wp_safe_redirect( add_query_arg( array( 'view' => 'users', 'saved' => '1' ), home_url( '/dashboard/' ) ) );
		exit;
	}

	if ( 'delete' === $action ) {
		$uid = (int) ( $_POST['user_id'] ?? 0 );
		if ( $uid && $uid !== get_current_user_id() ) {
			$u = get_userdata( $uid );
			require_once ABSPATH . 'wp-admin/includes/user.php';
			wp_delete_user( $uid );
			if ( function_exists( 'studie247_audit_log' ) ) {
				studie247_audit_log( sprintf( __( 'slettede bruger %s', 'studie247' ), $u ? $u->display_name : '#' . $uid ), $uid, 'user' );
			}
		}
		wp_safe_redirect( add_query_arg( array( 'view' => 'users', 'deleted' => '1' ), home_url( '/dashboard/' ) ) );
		exit;
	}
}

/**
 * POST-handler: gem kunde-noter + kontakt-info i CRM.
 */
if ( isset( $_POST['s247_dash_save_customer'] ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$cid = (int) $_POST['s247_dash_save_customer'];
	if ( $cid && check_admin_referer( 's247_dash_cust_' . $cid ) && 's247_customer' === get_post_type( $cid ) ) {
		$text_fields = array( '_s247_cust_name', '_s247_cust_email', '_s247_cust_phone', '_s247_cust_company', '_s247_cust_cvr' );
		foreach ( $text_fields as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				update_post_meta( $cid, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		if ( isset( $_POST['_s247_cust_notes'] ) ) {
			update_post_meta( $cid, '_s247_cust_notes', sanitize_textarea_field( wp_unslash( $_POST['_s247_cust_notes'] ) ) );
		}
		// Opdater title til nyeste navn
		$new_name = get_post_meta( $cid, '_s247_cust_name', true );
		if ( $new_name ) { wp_update_post( array( 'ID' => $cid, 'post_title' => $new_name ) ); }

		if ( function_exists( 'studie247_audit_log' ) ) {
			studie247_audit_log( sprintf( __( 'opdaterede kunde %s', 'studie247' ), $new_name ?: get_the_title( $cid ) ), $cid, 's247_customer' );
		}

		wp_safe_redirect( add_query_arg(
			array( 'view' => 'crm', 'customer' => $cid, 'saved' => '1' ),
			home_url( '/dashboard/' )
		) );
		exit;
	}
}

/**
 * POST-handler: tilføj en logpost (intern besked) på en kunde.
 */
if ( isset( $_POST['s247_dash_cust_log'] ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$cid = (int) $_POST['s247_dash_cust_log'];
	if ( $cid && check_admin_referer( 's247_dash_cust_log_' . $cid ) && 's247_customer' === get_post_type( $cid ) ) {
		$log_text = sanitize_textarea_field( wp_unslash( $_POST['log_text'] ?? '' ) );
		if ( $log_text ) {
			$entries = get_post_meta( $cid, '_s247_cust_log', true );
			if ( ! is_array( $entries ) ) { $entries = array(); }
			$uid = get_current_user_id();
			$u   = $uid ? get_userdata( $uid ) : null;
			array_unshift( $entries, array(
				'time'      => current_time( 'mysql' ),
				'user_id'   => $uid,
				'user_name' => $u ? $u->display_name : '',
				'text'      => $log_text,
			) );
			// Keep last 200 entries
			$entries = array_slice( $entries, 0, 200 );
			update_post_meta( $cid, '_s247_cust_log', $entries );

			if ( function_exists( 'studie247_audit_log' ) ) {
				studie247_audit_log( sprintf( __( 'tilføjede note på kunde %s', 'studie247' ), get_the_title( $cid ) ), $cid, 's247_customer' );
			}
		}
		wp_safe_redirect( add_query_arg(
			array( 'view' => 'crm', 'customer' => $cid ),
			home_url( '/dashboard/' )
		) );
		exit;
	}
}

/**
 * POST-handler: gem redigerede felter på en udlejnings-vare (udlejning_item).
 * Varer er vores egne data, så alle felter er editerbare.
 */
if ( isset( $_POST['s247_dash_save_item'] ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$iid = (int) $_POST['s247_dash_save_item'];
	if ( $iid && check_admin_referer( 's247_dash_item_' . $iid ) && 'udlejning_item' === get_post_type( $iid ) ) {
		// Title + content
		if ( isset( $_POST['post_title'] ) ) {
			wp_update_post( array(
				'ID'           => $iid,
				'post_title'   => sanitize_text_field( wp_unslash( $_POST['post_title'] ) ),
				'post_excerpt' => isset( $_POST['post_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['post_excerpt'] ) ) : '',
				'post_content' => isset( $_POST['post_content'] ) ? wp_kses_post( wp_unslash( $_POST['post_content'] ) ) : '',
			) );
		}
		$text_fields = array(
			'_s247_pris_dag', '_s247_pris_uge', '_s247_deposit',
			'_s247_sku', '_s247_ejer', '_s247_serienummer',
		);
		foreach ( $text_fields as $k ) {
			if ( isset( $_POST[ $k ] ) ) {
				update_post_meta( $iid, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
			}
		}
		if ( isset( $_POST['_s247_antal'] ) ) {
			update_post_meta( $iid, '_s247_antal', max( 0, (int) $_POST['_s247_antal'] ) );
		}
		// In-stock styres af antal > 0
		$antal = (int) get_post_meta( $iid, '_s247_antal', true );
		update_post_meta( $iid, '_s247_in_stock', $antal > 0 ? '1' : '0' );

		// Kategori
		if ( isset( $_POST['s247_category'] ) ) {
			$cat_ids = array_map( 'intval', (array) $_POST['s247_category'] );
			wp_set_object_terms( $iid, $cat_ids, 'udlejning_kategori', false );
		}

		if ( function_exists( 'studie247_audit_log' ) ) {
			studie247_audit_log( sprintf( __( 'opdaterede varen "%s"', 'studie247' ), get_the_title( $iid ) ), $iid, 'udlejning_item' );
		}

		wp_safe_redirect( add_query_arg(
			array( 'view' => 'rental', 'tab' => 'items', 'item' => $iid, 'saved' => '1' ),
			home_url( '/dashboard/' )
		) );
		exit;
	}
}

/**
 * POST-handler: gem en udlejnings-forespørgsel (booking med produkt).
 * Samme interne felter som studie-booking: pris, intern, status.
 */
if ( isset( $_POST['s247_dash_save_rental_req'] ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$rid = (int) $_POST['s247_dash_save_rental_req'];
	if ( $rid && check_admin_referer( 's247_dash_rental_' . $rid ) && 'booking' === get_post_type( $rid ) ) {
		if ( isset( $_POST['_s247_estimated_price'] ) && ( studie247_can_view_dash( 'revenue' ) || current_user_can( 'manage_options' ) ) ) {
			$new_price = max( 0, (int) $_POST['_s247_estimated_price'] );
			$old_price = (int) get_post_meta( $rid, '_s247_estimated_price', true );
			update_post_meta( $rid, '_s247_estimated_price', $new_price );
			if ( $new_price !== $old_price && function_exists( 'studie247_audit_log' ) ) {
				$label = studie247_audit_label_for( get_post( $rid ) );
				studie247_audit_log(
					sprintf( __( 'ændrede pris på udlejning %1$s: %2$s kr → %3$s kr', 'studie247' ),
						$label,
						number_format( $old_price, 0, ',', '.' ),
						number_format( $new_price, 0, ',', '.' )
					),
					$rid, 'booking'
				);
			}
		}
		if ( function_exists( 'studie247_apply_internal_state' ) ) {
			$want = ! empty( $_POST['_s247_internal'] );
			$is   = '1' === get_post_meta( $rid, '_s247_internal', true );
			if ( $want !== $is ) { studie247_apply_internal_state( $rid, $want ); }
		}
		if ( isset( $_POST['_s247_post_status'] ) ) {
			$ns = sanitize_key( $_POST['_s247_post_status'] );
			if ( in_array( $ns, array( 'pending', 'publish', 'trash' ), true ) && $ns !== get_post_status( $rid ) ) {
				wp_update_post( array( 'ID' => $rid, 'post_status' => $ns ) );
			}
		}
		wp_safe_redirect( add_query_arg(
			array( 'view' => 'rental', 'booking' => $rid, 'saved' => '1' ),
			home_url( '/dashboard/' )
		) );
		exit;
	}
}

/**
 * POST-handler: gem redigerede felter i kontakt-beskeder.
 */
if ( isset( $_POST['s247_dash_save_message'] ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$mid = (int) $_POST['s247_dash_save_message'];
	if ( $mid && check_admin_referer( 's247_dash_msg_' . $mid ) && 'kontakt_besked' === get_post_type( $mid ) ) {
		// Kunde-felter (navn, email, tlf, emne, besked) er read-only i
		// dashboardet — vi rører ikke ved hvad kunden har sendt.
		if ( isset( $_POST['_s247_internal_notes'] ) ) {
			update_post_meta( $mid, '_s247_internal_notes', sanitize_textarea_field( wp_unslash( $_POST['_s247_internal_notes'] ) ) );
		}
		// Håndteret-toggle
		if ( isset( $_POST['_s247_msg_handled'] ) ) {
			update_post_meta( $mid, '_s247_msg_handled', '1' );
			if ( ! get_post_meta( $mid, '_s247_msg_handled_at', true ) ) {
				update_post_meta( $mid, '_s247_msg_handled_at', current_time( 'mysql' ) );
				update_post_meta( $mid, '_s247_msg_handled_by', get_current_user_id() );
			}
		} else {
			delete_post_meta( $mid, '_s247_msg_handled' );
			delete_post_meta( $mid, '_s247_msg_handled_at' );
			delete_post_meta( $mid, '_s247_msg_handled_by' );
		}
		// Status (publish/trash)
		if ( isset( $_POST['_s247_post_status'] ) ) {
			$new_status = sanitize_key( $_POST['_s247_post_status'] );
			if ( in_array( $new_status, array( 'publish', 'trash' ), true ) && $new_status !== get_post_status( $mid ) ) {
				wp_update_post( array( 'ID' => $mid, 'post_status' => $new_status ) );
			}
		}
		// (Ingen title-opdatering — kunde-felter ændres ikke længere.)

		wp_safe_redirect( add_query_arg(
			array( 'view' => 'messages', 'message' => $mid, 'saved' => '1' ),
			home_url( '/dashboard/' )
		) );
		exit;
	}
}

/**
 * POST-handler: gem redigerede booking-felter fra dashboard.
 * Kører før output så vi kan redirecte rent bagefter.
 */
if ( isset( $_POST['s247_dash_save_booking'] ) && is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
	$save_id = (int) $_POST['s247_dash_save_booking'];
	if ( $save_id && check_admin_referer( 's247_dash_edit_' . $save_id ) && 'booking' === get_post_type( $save_id ) ) {
		// Kunde-felter (navn/email/tlf/virksomhed/cvr, dato/tid/varighed,
		// formål, noter) er read-only i dashboardet — vi rører ikke ved
		// hvad kunden har indsendt. Kun interne felter opdateres:
		// pris, intern-toggle, post-status.

		if ( isset( $_POST['_s247_estimated_price'] ) && ( studie247_can_view_dash( 'revenue' ) || current_user_can( 'manage_options' ) ) ) {
			$new_price = max( 0, (int) $_POST['_s247_estimated_price'] );
			$old_price = (int) get_post_meta( $save_id, '_s247_estimated_price', true );
			update_post_meta( $save_id, '_s247_estimated_price', $new_price );
			if ( $new_price !== $old_price && function_exists( 'studie247_audit_log' ) ) {
				$label = function_exists( 'studie247_audit_label_for' ) ? studie247_audit_label_for( get_post( $save_id ) ) : '';
				studie247_audit_log(
					sprintf( __( 'ændrede pris på %1$s: %2$s kr → %3$s kr', 'studie247' ),
						$label,
						number_format( $old_price, 0, ',', '.' ),
						number_format( $new_price, 0, ',', '.' )
					),
					$save_id,
					'booking'
				);
			}
		}
		if ( function_exists( 'studie247_apply_internal_state' ) ) {
			$want_internal = ! empty( $_POST['_s247_internal'] );
			$is_internal   = '1' === get_post_meta( $save_id, '_s247_internal', true );
			if ( $want_internal !== $is_internal ) {
				studie247_apply_internal_state( $save_id, $want_internal );
			}
		}
		if ( isset( $_POST['_s247_post_status'] ) ) {
			$new_status = sanitize_key( $_POST['_s247_post_status'] );
			if ( in_array( $new_status, array( 'pending', 'publish', 'trash' ), true ) ) {
				$current = get_post_status( $save_id );
				if ( $current !== $new_status ) {
					wp_update_post( array( 'ID' => $save_id, 'post_status' => $new_status ) );
				}
			}
		}

		wp_safe_redirect( add_query_arg(
			array( 'view' => 'bookings', 'booking' => $save_id, 'saved' => '1' ),
			home_url( '/dashboard/' )
		) );
		exit;
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'Dashboard — Studie 247', 'studie247' ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 's247-dash-app' ); ?>>

<?php if ( ! is_user_logged_in() ) : ?>

<div class="sd-login">
	<div class="sd-login__card">
		<div class="sd-brand"><span>STUDIE</span><span class="sd-brand__num">247</span></div>
		<h1 class="sd-login__title"><?php esc_html_e( 'Log ind', 'studie247' ); ?></h1>
		<p class="sd-login__lead"><?php esc_html_e( 'Dashboard-adgang er forbeholdt holdet.', 'studie247' ); ?></p>
		<?php
		wp_login_form( array(
			'redirect'       => esc_url( home_url( '/dashboard/' ) ),
			'label_username' => __( 'Brugernavn eller e-mail', 'studie247' ),
			'label_password' => __( 'Adgangskode', 'studie247' ),
			'label_remember' => __( 'Husk mig', 'studie247' ),
			'label_log_in'   => __( 'Log ind', 'studie247' ),
		) );
		?>
	</div>
</div>

<?php elseif ( ! current_user_can( 'edit_posts' ) ) : ?>

<div class="sd-login">
	<div class="sd-login__card">
		<h1 class="sd-login__title"><?php esc_html_e( 'Ingen adgang', 'studie247' ); ?></h1>
		<p class="sd-login__lead"><?php esc_html_e( 'Din bruger har ikke rettigheder til dashboardet.', 'studie247' ); ?></p>
		<p><a class="sd-btn" href="<?php echo esc_url( wp_logout_url( home_url( '/dashboard/' ) ) ); ?>"><?php esc_html_e( 'Log ud', 'studie247' ); ?></a></p>
	</div>
</div>

<?php else :
	$current_view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'overview';
	$user            = wp_get_current_user();
	$pending_count   = (int) wp_count_posts( 'booking' )->pending;
	$publish_count   = (int) wp_count_posts( 'booking' )->publish;
	$trash_count     = (int) wp_count_posts( 'booking' )->trash;
	$total_bookings  = $pending_count + $publish_count + $trash_count;
	$msg_count       = (int) wp_count_posts( 'kontakt_besked' )->publish;
	$item_count      = (int) wp_count_posts( 'udlejning_item' )->publish;

	// Split pending i studie vs. udlejning (baseret på _s247_produkt_id).
	$rental_pending_count = count( get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'pending',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_s247_produkt_id', 'compare' => 'EXISTS' ) ),
	) ) );
	$studio_pending_count = max( 0, $pending_count - $rental_pending_count );

	$month_start = date( 'Y-m-01' );
	$year_start  = date( 'Y-01-01' );
	$approved = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	) );
	$rev_month_studio = 0;
	$rev_year_studio  = 0;
	$rev_month_rental = 0;
	$rev_year_rental  = 0;
	$prod_counts = array();
	foreach ( $approved as $b ) {
		$d     = get_post_meta( $b->ID, '_s247_date', true );
		$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
		$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
		if ( $pid ) {
			if ( $d && $d >= $year_start )  { $rev_year_rental  += $price; }
			if ( $d && $d >= $month_start ) { $rev_month_rental += $price; }
			if ( ! isset( $prod_counts[ $pid ] ) ) { $prod_counts[ $pid ] = 0; }
			$prod_counts[ $pid ]++;
		} else {
			if ( $d && $d >= $year_start )  { $rev_year_studio  += $price; }
			if ( $d && $d >= $month_start ) { $rev_month_studio += $price; }
		}
	}
	arsort( $prod_counts );
	$top_products = array_slice( $prod_counts, 0, 5, true );

	$recent_pending = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'pending',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$recent_msgs = get_posts( array(
		'post_type'      => 'kontakt_besked',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$fmt_dkk = function ( $n ) { return number_format( (int) $n, 0, ',', '.' ) . ' kr'; };
	$can_rental   = studie247_can_view_dash( 'rental' );
	$can_studio   = studie247_can_view_dash( 'studio' );
	$can_messages = studie247_can_view_dash( 'messages' );
	$can_revenue  = studie247_can_view_dash( 'revenue' );
	$can_customers = studie247_can_view_dash( 'customers' );
	$is_admin_user = current_user_can( 'manage_options' );
	// Økonomi følger sektion: kun se udlejnings-omsætning hvis bruger har
	// udlejning + økonomi (eller er admin). Samme for studie.
	$can_studio_revenue = $is_admin_user || ( $can_revenue && $can_studio );
	$can_rental_revenue = $is_admin_user || ( $can_revenue && $can_rental );
	$has_any      = $can_rental || $can_studio || $can_messages || $can_revenue || $can_customers;
	$first_name   = trim( explode( ' ', trim( $user->display_name ) )[0] ) ?: $user->display_name;
	$initials     = strtoupper( substr( $first_name, 0, 1 ) );
?>

<div class="sd-shell">

	<aside class="sd-side">
		<div class="sd-brand"><span>STUDIE</span><span class="sd-brand__num">247</span></div>

		<nav class="sd-nav">
			<a class="sd-nav__item <?php echo 'overview' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
				<span class="sd-nav__dot"></span> <?php esc_html_e( 'Oversigt', 'studie247' ); ?>
			</a>
			<?php if ( $can_studio ) : ?>
				<a class="sd-nav__item <?php echo 'bookings' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Studie-bookinger', 'studie247' ); ?>
					<?php if ( $studio_pending_count ) : ?><span class="sd-nav__badge"><?php echo (int) $studio_pending_count; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ( $can_rental ) : ?>
				<a class="sd-nav__item <?php echo 'rental' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=rental' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Udlejning', 'studie247' ); ?>
					<?php if ( $rental_pending_count ) : ?><span class="sd-nav__badge"><?php echo (int) $rental_pending_count; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ( $can_messages ) :
				// Antal ubehandlede til badge
				$unhandled = (int) count( get_posts( array(
					'post_type'      => 'kontakt_besked',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						'relation' => 'OR',
						array( 'key' => '_s247_msg_handled', 'compare' => 'NOT EXISTS' ),
						array( 'key' => '_s247_msg_handled', 'value' => '', 'compare' => '=' ),
					),
				) ) );
			?>
				<a class="sd-nav__item <?php echo 'messages' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=messages' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Beskeder', 'studie247' ); ?>
					<?php if ( $unhandled ) : ?><span class="sd-nav__badge"><?php echo (int) $unhandled; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ( studie247_can_view_dash( 'customers' ) || current_user_can( 'manage_options' ) ) :
				$cust_count = (int) wp_count_posts( 's247_customer' )->publish;
			?>
				<a class="sd-nav__item <?php echo 'crm' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=crm' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Kunder (CRM)', 'studie247' ); ?>
					<?php if ( $cust_count ) : ?><span class="sd-nav__badge sd-nav__badge--muted"><?php echo (int) $cust_count; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_options' ) ) :
				$user_total = count_users();
			?>
				<a class="sd-nav__item <?php echo 'users' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=users' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Brugere', 'studie247' ); ?>
					<?php if ( ! empty( $user_total['total_users'] ) ) : ?><span class="sd-nav__badge sd-nav__badge--muted"><?php echo (int) $user_total['total_users']; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<a class="sd-nav__item" href="<?php echo esc_url( admin_url() ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'WP-admin', 'studie247' ); ?>
				</a>
			<?php endif; ?>
		</nav>

		<div class="sd-side__footer">
			<div class="sd-user">
				<span class="sd-user__avatar"><?php echo esc_html( $initials ); ?></span>
				<div class="sd-user__meta">
					<span class="sd-user__name"><?php echo esc_html( $user->display_name ); ?></span>
					<span class="sd-user__role"><?php echo esc_html( current_user_can( 'manage_options' ) ? __( 'Administrator', 'studie247' ) : __( 'Redaktør', 'studie247' ) ); ?></span>
				</div>
				<a class="sd-user__logout" href="<?php echo esc_url( wp_logout_url( home_url( '/dashboard/' ) ) ); ?>" title="<?php esc_attr_e( 'Log ud', 'studie247' ); ?>">↪</a>
			</div>
		</div>
	</aside>

	<main class="sd-main">

		<header class="sd-topbar">
			<div>
				<?php
				$crumb_map = array(
					'overview' => __( 'Dashboard / Oversigt', 'studie247' ),
					'bookings' => __( 'Dashboard / Studie-bookinger', 'studie247' ),
					'messages' => __( 'Dashboard / Beskeder', 'studie247' ),
					'rental'   => __( 'Dashboard / Udlejning', 'studie247' ),
					'crm'      => __( 'Dashboard / Kunder', 'studie247' ),
					'users'    => __( 'Dashboard / Brugere', 'studie247' ),
				);
				$title_map = array(
					'overview' => sprintf( __( 'Hej %s', 'studie247' ), $first_name ),
					'bookings' => __( 'Studie-bookinger', 'studie247' ),
					'messages' => __( 'Beskeder', 'studie247' ),
					'rental'   => __( 'Udlejning', 'studie247' ),
					'crm'      => __( 'Kunder / CRM', 'studie247' ),
					'users'    => __( 'Brugere', 'studie247' ),
				);
				?>
				<span class="sd-breadcrumb"><?php echo esc_html( $crumb_map[ $current_view ] ?? $crumb_map['overview'] ); ?></span>
				<h1 class="sd-topbar__title">
					<?php echo esc_html( $title_map[ $current_view ] ?? $title_map['overview'] ); ?>
					<span class="sd-topbar__title-sub"><?php echo esc_html( date_i18n( 'l j. F', current_time( 'timestamp' ) ) ); ?></span>
				</h1>
			</div>
			<div class="sd-topbar__actions">
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Se sitet', 'studie247' ); ?></a>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'wp-admin', 'studie247' ); ?></a>
				<?php endif; ?>
			</div>
		</header>

		<?php
		if ( 'bookings' === $current_view ) {
			include STUDIE247_DIR . '/template-parts/dashboard-bookings.php';
		} elseif ( 'messages' === $current_view ) {
			include STUDIE247_DIR . '/template-parts/dashboard-messages.php';
		} elseif ( 'rental' === $current_view ) {
			include STUDIE247_DIR . '/template-parts/dashboard-rental.php';
		} elseif ( 'crm' === $current_view ) {
			include STUDIE247_DIR . '/template-parts/dashboard-crm.php';
		} elseif ( 'users' === $current_view && current_user_can( 'manage_options' ) ) {
			include STUDIE247_DIR . '/template-parts/dashboard-users.php';
		} else {
			include STUDIE247_DIR . '/template-parts/dashboard-overview.php';
		}
		?>

		<?php
		$audit_entries = function_exists( 'studie247_audit_log_get' ) ? studie247_audit_log_get( 20 ) : array();
		if ( ! empty( $audit_entries ) ) : ?>
			<section class="sd-audit">
				<header class="sd-audit__head">
					<h2><?php esc_html_e( 'Seneste aktivitet', 'studie247' ); ?></h2>
					<span class="sd-audit__hint"><?php esc_html_e( 'Alt der ændres logges her.', 'studie247' ); ?></span>
				</header>
				<ul class="sd-audit__list">
					<?php foreach ( $audit_entries as $e ) :
						$initial = strtoupper( substr( (string) $e['user_name'], 0, 1 ) );
						$ago     = studie247_audit_time_ago( $e['ts'] );
						$link    = '';
						if ( ! empty( $e['target_id'] ) && ! empty( $e['target_type'] ) ) {
							if ( 'booking' === $e['target_type'] ) {
								$link = home_url( '/dashboard/?view=bookings&booking=' . (int) $e['target_id'] );
							} elseif ( 'kontakt_besked' === $e['target_type'] ) {
								$link = home_url( '/dashboard/?view=messages&message=' . (int) $e['target_id'] );
							}
						}
					?>
						<li class="sd-audit__item">
							<span class="sd-audit__avatar"><?php echo esc_html( $initial ); ?></span>
							<span class="sd-audit__body">
								<span class="sd-audit__text">
									<strong><?php echo esc_html( $e['user_name'] ); ?></strong>
									<?php echo esc_html( $e['message'] ); ?>
								</span>
								<span class="sd-audit__meta">
									<?php echo esc_html( $ago ); ?>
									<?php if ( $link ) : ?>
										· <a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Se', 'studie247' ); ?></a>
									<?php endif; ?>
								</span>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

	</main>

</div>

<?php endif; // logged-in + edit_posts ?>

<?php wp_footer(); ?>
</body>
</html>
