<?php
/**
 * Booking-godkendelse: admin approve/reject flow.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── Meta-box øverst på booking med approve/reject knapper ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_booking_approval',
		__( 'Status & godkendelse', 'studie247' ),
		'studie247_render_booking_approval',
		'booking',
		'side',
		'high'
	);
} );

function studie247_render_booking_approval( $post ) {
	$approve_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=s247_approve_booking&booking=' . $post->ID ),
		's247_approve_' . $post->ID
	);
	$reject_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=s247_reject_booking&booking=' . $post->ID ),
		's247_reject_' . $post->ID
	);

	$status      = $post->post_status;
	$status_map  = array(
		'pending' => array( '#7d6500', 'Afventer godkendelse' ),
		'publish' => array( '#0a7c2f', 'Godkendt' ),
		'draft'   => array( '#666',    'Kladde' ),
		'trash'   => array( '#9E2B25', 'Afvist' ),
	);
	$label = isset( $status_map[ $status ] ) ? $status_map[ $status ] : array( '#666', $status );
	?>
	<p style="font-size:13px;margin:0 0 12px;">
		<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr( $label[0] ); ?>;vertical-align:middle;margin-right:6px;"></span>
		<strong><?php echo esc_html( $label[1] ); ?></strong>
	</p>

	<?php if ( 'pending' === $status ) : ?>
		<p style="margin:0 0 8px;color:#666;font-size:12px;"><?php esc_html_e( 'Godkend for at sende endelig bekræftelse til kunden. Afvis for at frigive tiden.', 'studie247' ); ?></p>
		<p style="display:flex;gap:6px;flex-wrap:wrap;">
			<a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary" style="background:#0a7c2f;border-color:#0a7c2f;">✓ <?php esc_html_e( 'Godkend', 'studie247' ); ?></a>
			<a href="<?php echo esc_url( $reject_url ); ?>" class="button" style="color:#9E2B25;border-color:#9E2B25;">✕ <?php esc_html_e( 'Afvis', 'studie247' ); ?></a>
		</p>
	<?php elseif ( 'publish' === $status ) : ?>
		<p style="margin:0 0 8px;color:#666;font-size:12px;"><?php esc_html_e( 'Bookingen er godkendt, og kunden har modtaget bekræftelse.', 'studie247' ); ?></p>
		<p><a href="<?php echo esc_url( $reject_url ); ?>" class="button" style="color:#9E2B25;border-color:#9E2B25;">✕ <?php esc_html_e( 'Aflys booking', 'studie247' ); ?></a></p>
	<?php elseif ( 'trash' === $status ) : ?>
		<p style="margin:0 0 8px;color:#666;font-size:12px;"><?php esc_html_e( 'Bookingen er afvist/aflyst.', 'studie247' ); ?></p>
		<p><a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary" style="background:#0a7c2f;border-color:#0a7c2f;">✓ <?php esc_html_e( 'Gendan og godkend', 'studie247' ); ?></a></p>
	<?php endif; ?>

	<hr style="margin:14px 0;">
	<?php
	$is_internal_now = '1' === get_post_meta( $post->ID, '_s247_internal', true );
	$internal_url    = wp_nonce_url(
		admin_url( 'admin-post.php?action=s247_toggle_internal&booking=' . $post->ID ),
		's247_internal_' . $post->ID
	);
	?>
	<p style="margin:0 0 8px;font-size:12px;">
		<strong><?php esc_html_e( 'Intern brug:', 'studie247' ); ?></strong>
		<?php if ( $is_internal_now ) : ?>
			<span style="color:#0a7c2f;font-weight:700;">✓ <?php esc_html_e( 'Ja — prisen er fjernet', 'studie247' ); ?></span>
		<?php else : ?>
			<span style="color:#666;"><?php esc_html_e( 'Nej', 'studie247' ); ?></span>
		<?php endif; ?>
	</p>
	<p style="margin:0 0 12px;">
		<a href="<?php echo esc_url( $internal_url ); ?>" class="button">
			<?php echo $is_internal_now
				? '↩ ' . esc_html__( 'Fjern intern-markering', 'studie247' )
				: '⚙ ' . esc_html__( 'Markér som intern brug', 'studie247' ); ?>
		</a>
	</p>

	<hr style="margin:14px 0;">
	<?php
	$produkt_id = (int) get_post_meta( $post->ID, '_s247_produkt_id', true );
	$is_rental  = $produkt_id > 0;
	$date       = get_post_meta( $post->ID, '_s247_date', true );
	$dur        = get_post_meta( $post->ID, '_s247_duration', true );
	?>
	<p style="margin:0 0 10px;font-size:12px;">
		<strong><?php esc_html_e( 'Type:', 'studie247' ); ?></strong>
		<?php if ( $is_rental ) : ?>
			<span style="color:#9E2B25;font-weight:700;"><?php esc_html_e( 'Udstyrs-udlejning', 'studie247' ); ?></span><br>
			<strong><?php esc_html_e( 'Udstyr:', 'studie247' ); ?></strong>
			<a href="<?php echo esc_url( get_edit_post_link( $produkt_id ) ); ?>"><?php echo esc_html( get_the_title( $produkt_id ) ); ?></a><br>
			<?php if ( $date ) : ?>
				<strong><?php esc_html_e( 'Periode:', 'studie247' ); ?></strong>
				<?php echo esc_html( date_i18n( 'j. M Y', strtotime( $date ) ) ); ?>
				<?php if ( $dur ) : ?> — <?php echo esc_html( $dur ); ?><?php endif; ?>
			<?php endif; ?>
		<?php else : ?>
			<?php esc_html_e( 'Studie-booking', 'studie247' ); ?>
			<?php
			$use_type_map = array(
				'podcast' => 'Podcast', 'kursusvideo' => 'Kursusvideo',
				'undervisningsvideo' => 'Undervisningsvideo',
				'some-content' => 'SoMe Content', 'annonce-video' => 'Annonce-video',
			);
			$edit_map = array( 'redigering' => 'Redigering', 'kun-filer' => 'Kun filerne' );
			$podcast_map = array( 'lyd' => 'Lyd-podcast', 'video' => 'Video-podcast' );
			$tilkoeb_map = array(
				'jingle-standard'      => 'Jingle — standard',
				'jingle-skraeddersyet' => 'Jingle — skræddersyet',
			);
			$ut = get_post_meta( $post->ID, '_s247_use_type', true );
			$et = get_post_meta( $post->ID, '_s247_edit_type', true );
			$pt = get_post_meta( $post->ID, '_s247_podcast_type', true );
			$tk = get_post_meta( $post->ID, '_s247_tilkoeb', true );
			$vc = (int) get_post_meta( $post->ID, '_s247_video_count', true );
			$vd = (int) get_post_meta( $post->ID, '_s247_video_duration', true );
			$fm = get_post_meta( $post->ID, '_s247_format', true );
			?>
			<?php if ( $ut && isset( $use_type_map[ $ut ] ) ) : ?><br><strong>Formål:</strong> <?php echo esc_html( $use_type_map[ $ut ] ); ?><?php endif; ?>
			<?php if ( $et && isset( $edit_map[ $et ] ) )    : ?><br><strong>Ønsker:</strong> <?php echo esc_html( $edit_map[ $et ] ); ?><?php endif; ?>
			<?php if ( $pt && isset( $podcast_map[ $pt ] ) ) : ?><br><strong>Podcast-type:</strong> <?php echo esc_html( $podcast_map[ $pt ] ); ?><?php endif; ?>
			<?php if ( $tk && isset( $tilkoeb_map[ $tk ] ) ) : ?><br><strong>Tilkøb:</strong> <?php echo esc_html( $tilkoeb_map[ $tk ] ); ?><?php endif; ?>
			<?php if ( $vc ) : ?><br><strong>Antal videoer:</strong> <?php echo (int) $vc; ?><?php endif; ?>
			<?php if ( $vd ) : ?><br><strong>Varighed pr. video:</strong> <?php echo (int) $vd; ?> min<?php endif; ?>
			<?php if ( $fm ) : ?><br><strong>Format:</strong> <?php echo esc_html( $fm ); ?><?php endif; ?>
		<?php endif; ?>
	</p>
	<hr style="margin:10px 0;">
	<p style="margin:0;font-size:12px;color:#666;">
		<strong><?php esc_html_e( 'Kunde:', 'studie247' ); ?></strong>
		<?php echo esc_html( get_post_meta( $post->ID, '_s247_name', true ) ); ?><br>
		<?php $email = get_post_meta( $post->ID, '_s247_email', true ); ?>
		<?php if ( $email ) : ?>
			<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a><br>
		<?php endif; ?>
		<?php $phone = get_post_meta( $post->ID, '_s247_phone', true ); ?>
		<?php if ( $phone ) : ?>
			<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a><br>
		<?php endif; ?>
		<?php $company = get_post_meta( $post->ID, '_s247_company', true ); ?>
		<?php $cvr     = get_post_meta( $post->ID, '_s247_cvr', true ); ?>
		<?php if ( $company ) : ?>
			<strong><?php esc_html_e( 'Virksomhed:', 'studie247' ); ?></strong> <?php echo esc_html( $company ); ?><br>
		<?php endif; ?>
		<?php if ( $cvr ) : ?>
			<strong>CVR:</strong> <?php echo esc_html( $cvr ); ?>
		<?php endif; ?>
	</p>
	<?php
}

/* ───────── admin-post handlers ───────── */

/**
 * Toggle intern-brug. Sættes til true → gemmer nuværende pris i backup
 * og nulstiller den aktive pris. Sættes til false → gendanner backup.
 * Bruges af både wp-admin-knappen og REST-endpointet, så begge sider
 * altid ender med samme state.
 */
function studie247_apply_internal_state( $booking_id, $is_internal ) {
	if ( 'booking' !== get_post_type( $booking_id ) ) { return false; }

	if ( $is_internal ) {
		// Gem oprindelig pris (kun hvis vi ikke allerede har en backup).
		$existing_backup = get_post_meta( $booking_id, '_s247_estimated_price_original', true );
		if ( '' === $existing_backup ) {
			$current = (int) get_post_meta( $booking_id, '_s247_estimated_price', true );
			if ( $current > 0 ) {
				update_post_meta( $booking_id, '_s247_estimated_price_original', $current );
			}
		}
		update_post_meta( $booking_id, '_s247_estimated_price', 0 );
		update_post_meta( $booking_id, '_s247_internal', '1' );
	} else {
		delete_post_meta( $booking_id, '_s247_internal' );
		$original = get_post_meta( $booking_id, '_s247_estimated_price_original', true );
		if ( '' !== $original ) {
			update_post_meta( $booking_id, '_s247_estimated_price', (int) $original );
			delete_post_meta( $booking_id, '_s247_estimated_price_original' );
		}
	}
	return true;
}

/**
 * Toggle intern-brug på en booking.
 * Intern = ingen pris (skjul/nulstil _s247_estimated_price), men alt
 * andet (kunde-info, kalender-blokering, godkendelse) virker som normalt.
 * Den oprindelige pris gemmes i _s247_estimated_price_original så den
 * kan gendannes hvis man fjerner intern-markeringen.
 */
add_action( 'admin_post_s247_toggle_internal', function () {
	$id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) { wp_die( 'Nope.' ); }
	check_admin_referer( 's247_internal_' . $id );

	$is_internal = '1' === get_post_meta( $id, '_s247_internal', true );
	studie247_apply_internal_state( $id, ! $is_internal );
	$msg = $is_internal ? 'internal_off' : 'internal_on';

	wp_safe_redirect( add_query_arg( 's247_msg', $msg, get_edit_post_link( $id, 'raw' ) ) );
	exit;
} );

/**
 * Hvis nogen skriver direkte til _s247_internal (fx via REST PATCH fra
 * dashboardet uden at bruge /internal-endpointet), spejl logikken
 * automatisk så pris-state holder i sync.
 */
function studie247_on_internal_meta_change( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( '_s247_internal' !== $meta_key ) { return; }
	if ( 'booking' !== get_post_type( $post_id ) ) { return; }
	// Guard: undgå rekursion når studie247_apply_internal_state selv
	// opdaterer _s247_internal.
	static $guard = false;
	if ( $guard ) { return; }
	$guard = true;

	$target = ( '1' === (string) $meta_value );
	$backup = get_post_meta( $post_id, '_s247_estimated_price_original', true );
	$price  = (int) get_post_meta( $post_id, '_s247_estimated_price', true );

	// Kun kør logikken hvis state ikke allerede er i sync
	// (undgår ekstra arbejde når vi selv sætter den).
	$already_nulled   = $target  && 0 === $price;
	$already_restored = ! $target && '' === $backup;
	if ( $already_nulled || $already_restored ) { $guard = false; return; }

	studie247_apply_internal_state( $post_id, $target );
	$guard = false;
}
add_action( 'updated_post_meta', 'studie247_on_internal_meta_change', 10, 4 );
add_action( 'added_post_meta',   'studie247_on_internal_meta_change', 10, 4 );
add_action( 'deleted_post_meta', function ( $meta_ids, $post_id, $meta_key ) {
	if ( '_s247_internal' !== $meta_key ) { return; }
	if ( 'booking' !== get_post_type( $post_id ) ) { return; }
	// Slettet meta = intern slået fra → gendan evt. backup-pris.
	studie247_apply_internal_state( $post_id, false );
}, 10, 3 );

add_action( 'admin_post_s247_approve_booking', function () {
	$id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) { wp_die( 'Nope.' ); }
	check_admin_referer( 's247_approve_' . $id );
	wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
	studie247_send_booking_confirmation( $id, 'approved' );
	wp_safe_redirect( add_query_arg( 's247_msg', 'approved', get_edit_post_link( $id, 'raw' ) ) );
	exit;
} );

add_action( 'admin_post_s247_reject_booking', function () {
	$id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) { wp_die( 'Nope.' ); }
	check_admin_referer( 's247_reject_' . $id );
	wp_update_post( array( 'ID' => $id, 'post_status' => 'trash' ) );
	studie247_send_booking_confirmation( $id, 'rejected' );
	wp_safe_redirect( add_query_arg( 's247_msg', 'rejected', admin_url( 'edit.php?post_type=booking' ) ) );
	exit;
} );

/**
 * Send bekræftelses-/afvisnings-mail til kunden.
 */
function studie247_send_booking_confirmation( $booking_id, $action ) {
	$name  = get_post_meta( $booking_id, '_s247_name', true );
	$email = get_post_meta( $booking_id, '_s247_email', true );
	$date  = get_post_meta( $booking_id, '_s247_date', true );
	$start = get_post_meta( $booking_id, '_s247_start', true );
	$dur   = get_post_meta( $booking_id, '_s247_duration', true );
	$pid   = (int) get_post_meta( $booking_id, '_s247_produkt_id', true );
	$prod  = $pid ? get_the_title( $pid ) : '';

	if ( ! $email || ! is_email( $email ) ) { return; }

	$date_dk = $date ? date_i18n( 'l j. F Y', strtotime( $date ) ) : $date;

	$is_rental = (bool) $pid;
	$is_html   = false;

	if ( 'approved' === $action ) {
		$cal = function_exists( 'studie247_booking_calendar_urls' )
			? studie247_booking_calendar_urls( $booking_id )
			: array();
		$after_hours  = (int) get_post_meta( $booking_id, '_s247_after_hours', true );
		$after_hours_fee = (int) get_post_meta( $booking_id, '_s247_after_hours_fee', true );
		$aftenpris_rate  = defined( 'STUDIE247_AFTER_HOURS_RATE' ) ? (int) STUDIE247_AFTER_HOURS_RATE : 200;
		$mail    = studie247_render_mail_template( 'booking_approved', array(
			'navn'            => $name,
			'email'           => $email,
			'produkt'         => $prod,
			'dato'            => $date_dk,
			'start'           => $start,
			'varighed'        => $dur,
			'virksomhed'      => get_post_meta( $booking_id, '_s247_company', true ),
			'cvr'             => get_post_meta( $booking_id, '_s247_cvr', true ),
			'kalender_ics'    => $cal['ics']     ?? '',
			'kalender_google' => $cal['google']  ?? '',
			'kalender_outlook'=> $cal['outlook'] ?? '',
			'aftenpris_timer'   => $after_hours > 0 ? (string) $after_hours : '',
			'aftenpris_tillaeg' => $after_hours_fee > 0 && function_exists( 'studie247_format_dkk' )
				? studie247_format_dkk( $after_hours_fee ) : '',
			'aftenpris_sats'    => (string) $aftenpris_rate,
		) );
		$subject = $mail['subject'];
		$body    = $mail['body'];
		$is_html = studie247_mail_template_is_html( 'booking_approved' );
	} else {
		if ( $is_rental ) {
			$subject = 'Lejeforespørgsel afvist — Studie 247';
			$body    = "Hej {$name},\n\nVi kan desværre ikke imødekomme følgende lejeforespørgsel:\n\n";
			if ( $prod ) { $body .= "Udstyr: {$prod}\n"; }
			$body   .= "Start-dato: {$date_dk}\nVarighed: {$dur}\n";
			$body   .= "\nSkriv endelig til os hvis du vil prøve et andet tidspunkt eller et andet stykke udstyr.\n\n— Studie 247\ninfo@s247.dk";
		} else {
			$subject = 'Booking aflyst — Studie 247';
			$body    = "Hej {$name},\n\nVi er nødt til at aflyse følgende booking:\n\n";
			if ( $prod ) { $body .= "Produkt: {$prod}\n"; }
			$body   .= "Dato: {$date_dk}\nStart: {$start}\nVarighed: {$dur}\n";
			$body   .= "\nSkriv til os hvis du vil booke en anden tid — vi hjælper dig gerne med at finde en løsning.\n\n— Studie 247\ninfo@s247.dk";
		}
	}

	$headers = array(
		'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8',
		'From: Studie 247 <info@s247.dk>',
		'Reply-To: info@s247.dk',
	);

	@wp_mail( $email, $subject, $body, $headers );
}

/* ───────── Admin-notice efter handling ───────── */
add_action( 'admin_notices', function () {
	if ( empty( $_GET['s247_msg'] ) ) { return; }
	$msg = sanitize_text_field( $_GET['s247_msg'] );
	if ( 'approved' === $msg ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Booking godkendt og bekræftelses-mail sendt til kunden.', 'studie247' ) . '</p></div>';
	}
	if ( 'rejected' === $msg ) {
		echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Booking afvist. Tiden er frigivet og kunden er informeret.', 'studie247' ) . '</p></div>';
	}
	if ( 'internal_on' === $msg ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Markeret som intern brug. Prisen er nulstillet.', 'studie247' ) . '</p></div>';
	}
	if ( 'internal_off' === $msg ) {
		echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Intern-markering fjernet. Oprindelig pris gendannet.', 'studie247' ) . '</p></div>';
	}
} );

/* ───────── Admin-liste: type-kolonne + intern-badge ───────── */
add_filter( 'manage_booking_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['s247_type'] = __( 'Type', 'studie247' );
			$new['s247_when'] = __( 'Hvornår', 'studie247' );
			$new['s247_price'] = __( 'Pris', 'studie247' );
		}
	}
	return $new;
} );

add_action( 'manage_booking_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 's247_type':
			$pid      = (int) get_post_meta( $post_id, '_s247_produkt_id', true );
			$internal = '1' === get_post_meta( $post_id, '_s247_internal', true );
			if ( $pid ) {
				printf(
					'<span style="color:#9E2B25;font-weight:700;">⎁ %s</span><br><span style="color:#666;font-size:11px;">%s</span>',
					esc_html__( 'Udstyrs-udlejning', 'studie247' ),
					esc_html( get_the_title( $pid ) )
				);
			} else {
				echo '<span style="color:#0a7c2f;font-weight:700;">▣ ' . esc_html__( 'Studie-booking', 'studie247' ) . '</span>';
			}
			if ( $internal ) {
				echo '<br><span style="display:inline-block;margin-top:4px;padding:2px 8px;background:#2b5e2b;color:#fff;border-radius:3px;font-size:10px;font-weight:700;letter-spacing:0.05em;">INTERN</span>';
			}
			break;
		case 's247_when':
			$date  = get_post_meta( $post_id, '_s247_date', true );
			$start = get_post_meta( $post_id, '_s247_start', true );
			$dur   = get_post_meta( $post_id, '_s247_duration', true );
			if ( $date ) {
				echo esc_html( date_i18n( 'j. M Y', strtotime( $date ) ) );
				if ( $start ) { echo ' · ' . esc_html( $start ); }
				if ( $dur )   { echo '<br><span style="color:#666;font-size:11px;">' . esc_html( $dur ) . '</span>'; }
			} else {
				echo '—';
			}
			break;
		case 's247_price':
			$p = (int) get_post_meta( $post_id, '_s247_estimated_price', true );
			if ( '1' === get_post_meta( $post_id, '_s247_internal', true ) ) {
				echo '<span style="color:#2b5e2b;">0 kr</span><br><span style="color:#666;font-size:10px;">(intern)</span>';
			} elseif ( $p > 0 ) {
				echo esc_html( number_format( $p, 0, ',', '.' ) ) . ' kr';
			} else {
				echo '—';
			}
			break;
	}
}, 10, 2 );

/* ───────── Dashboard-widget: afventende bookinger ───────── */
add_action( 'wp_dashboard_setup', function () {
	$pending = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'pending',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( empty( $pending ) ) { return; }

	wp_add_dashboard_widget(
		's247_pending_bookings',
		__( '⏳ Bookinger der venter på godkendelse', 'studie247' ),
		'studie247_pending_bookings_widget'
	);
} );

function studie247_pending_bookings_widget() {
	$bookings = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'pending',
		'posts_per_page' => 10,
		'orderby'        => 'meta_value',
		'meta_key'       => '_s247_date',
		'order'          => 'ASC',
	) );
	if ( empty( $bookings ) ) {
		echo '<p>' . esc_html__( 'Ingen afventende bookinger.', 'studie247' ) . '</p>';
		return;
	}
	echo '<ul style="margin:0;">';
	foreach ( $bookings as $b ) {
		$date = get_post_meta( $b->ID, '_s247_date', true );
		$time = get_post_meta( $b->ID, '_s247_start', true );
		$name = get_post_meta( $b->ID, '_s247_name', true );
		printf(
			'<li style="padding:6px 0;border-bottom:1px solid #eee;"><strong>%s</strong> — %s kl. %s <a href="%s" style="float:right;">Åbn →</a></li>',
			esc_html( $name ),
			esc_html( $date ),
			esc_html( $time ),
			esc_url( get_edit_post_link( $b->ID ) )
		);
	}
	echo '</ul>';
	echo '<p style="margin-top:10px;"><a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=booking&post_status=pending' ) ) . '">' . esc_html__( 'Se alle afventende', 'studie247' ) . '</a></p>';
}

/* ───────── Liste-visning: vis pending-bookinger som standard ───────── */
add_filter( 'views_edit-booking', function ( $views ) {
	// WP viser normalt "Udgivet" som default. Sæt "Afventende" øverst.
	return $views;
} );
