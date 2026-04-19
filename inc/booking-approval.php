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
			<a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
		<?php endif; ?>
	</p>
	<?php
}

/* ───────── admin-post handlers ───────── */
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

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: Studie 247 <info@s247.dk>',
		'Reply-To: info@s247.dk',
	);

	$is_rental = (bool) $pid;

	if ( 'approved' === $action ) {
		if ( $is_rental ) {
			$subject = 'Lejeforespørgsel bekræftet — Studie 247';
			$body    = "Hej {$name},\n\nDin lejeforespørgsel er godkendt. Vi glæder os til at udlåne udstyret til dig.\n\n";
			if ( $prod ) { $body .= "Udstyr: {$prod}\n"; }
			$body   .= "Start-dato: {$date_dk}\nVarighed: {$dur}\n";
			$body   .= "\nVi kontakter dig for at aftale afhentning og eventuel depositums-betaling.\n\n— Studie 247\ninfo@s247.dk";
		} else {
			$subject = 'Booking bekræftet — Studie 247';
			$body    = "Hej {$name},\n\nDin booking er bekræftet! Vi glæder os til at se dig.\n\n";
			if ( $prod ) { $body .= "Produkt: {$prod}\n"; }
			$body   .= "Dato: {$date_dk}\nStart: {$start}\nVarighed: {$dur}\n";
			$body   .= "\nHar du spørgsmål inden da, så ring eller skriv.\n\n— Studie 247\ninfo@s247.dk";
		}
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
} );

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
