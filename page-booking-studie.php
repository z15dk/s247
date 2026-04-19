<?php
/**
 * Booking-studie — kalender-grid for studie-bookinger (max 1 dag).
 *
 * @package Studie247
 */

$errors    = array();
$submitted = isset( $_GET['sendt'] ) && '1' === $_GET['sendt'];

$form_name  = '';
$form_email = '';
$form_phone = '';
$form_date  = '';
$form_start = '';
$form_dur   = '';
$form_notes = '';
$form_prod  = isset( $_GET['produkt'] ) ? sanitize_title( wp_unslash( $_GET['produkt'] ) ) : '';
$form_type  = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

// Find produkt hvis slug er sat.
$product = null;
if ( $form_prod ) {
	$product = get_page_by_path( $form_prod, OBJECT, 'udlejning_item' );
}

// Produkt-mode: varighed angives i dage/uger, ikke timer, og produkt-
// forespørgsler deler ikke samme kalender-kapacitet som studie-booking.
$is_product = (bool) $product;
$duration_options = $is_product
	? array( '1 dag', '2 dage', '3 dage', '4 dage', '1 uge', '2 uger' )
	: array( '2 timer', '4 timer', '6 timer', '8 timer', '1 dag' );

// Pris-multiplikatorer for udlejning.
$rental_price_table = array(
	'1 dag'  => array( 'base' => 'dag', 'mult' => 1.0 ),
	'2 dage' => array( 'base' => 'dag', 'mult' => 2.0 ),
	'3 dage' => array( 'base' => 'dag', 'mult' => 3.0 ),
	'4 dage' => array( 'base' => 'dag', 'mult' => 3.5 ),
	'1 uge'  => array( 'base' => 'uge', 'mult' => 1.0 ),
	'2 uger' => array( 'base' => 'uge', 'mult' => 1.5 ),
);

// Parser fx "1.499 kr" → 1499 (ignorerer alle ikke-tal-tegn).
function studie247_price_to_int( $price_str ) {
	return (int) preg_replace( '/[^\d]/', '', (string) $price_str );
}

function studie247_format_dkk( $amount ) {
	return number_format( (float) $amount, 0, ',', '.' ) . ' kr';
}

$product_price_day  = 0;
$product_price_week = 0;
if ( $is_product ) {
	$product_price_day  = studie247_price_to_int( get_post_meta( $product->ID, '_s247_pris_dag', true ) );
	$product_price_week = studie247_price_to_int( get_post_meta( $product->ID, '_s247_pris_uge', true ) );
	// Hvis der ikke er sat en uge-pris, brug 7× dag-prisen som fallback.
	if ( ! $product_price_week && $product_price_day ) {
		$product_price_week = $product_price_day * 7;
	}
}

/**
 * Bereg lejepris ud fra produkt + varighed.
 * Returnerer heltal i DKK eller 0 hvis ikke beregnelig.
 */
function studie247_calc_rental_price( $price_day, $price_week, $duration ) {
	$table = array(
		'1 dag'  => array( 'base' => 'dag', 'mult' => 1.0 ),
		'2 dage' => array( 'base' => 'dag', 'mult' => 2.0 ),
		'3 dage' => array( 'base' => 'dag', 'mult' => 3.0 ),
		'4 dage' => array( 'base' => 'dag', 'mult' => 3.5 ),
		'1 uge'  => array( 'base' => 'uge', 'mult' => 1.0 ),
		'2 uger' => array( 'base' => 'uge', 'mult' => 1.5 ),
	);
	if ( ! isset( $table[ $duration ] ) ) { return 0; }
	$base_price = 'uge' === $table[ $duration ]['base'] ? (int) $price_week : (int) $price_day;
	if ( ! $base_price ) { return 0; }
	return (int) round( $base_price * $table[ $duration ]['mult'] );
}

// POST handler.
if ( ! empty( $_POST['s247_book_nonce'] ) && wp_verify_nonce( $_POST['s247_book_nonce'], 's247_book' ) ) {
	$form_name  = sanitize_text_field( wp_unslash( $_POST['s247_name']  ?? '' ) );
	$form_email = sanitize_email(      wp_unslash( $_POST['s247_email'] ?? '' ) );
	$form_phone = sanitize_text_field( wp_unslash( $_POST['s247_phone'] ?? '' ) );
	$form_date  = sanitize_text_field( wp_unslash( $_POST['s247_date']  ?? '' ) );
	$form_start = sanitize_text_field( wp_unslash( $_POST['s247_start'] ?? '' ) );
	$form_dur   = sanitize_text_field( wp_unslash( $_POST['s247_duration'] ?? '' ) );
	$form_notes = sanitize_textarea_field( wp_unslash( $_POST['s247_notes'] ?? '' ) );
	$form_prod  = sanitize_title(      wp_unslash( $_POST['s247_produkt'] ?? '' ) );
	$form_type  = sanitize_text_field( wp_unslash( $_POST['s247_type']  ?? '' ) );

	if ( ! $form_name )              { $errors[] = __( 'Udfyld dit navn.', 'studie247' ); }
	if ( ! is_email( $form_email ) ) { $errors[] = __( 'Indtast en gyldig email.', 'studie247' ); }
	if ( ! $form_date )              { $errors[] = __( 'Vælg en dato.', 'studie247' ); }
	if ( ! $form_start )             { $errors[] = __( 'Vælg et start-tidspunkt.', 'studie247' ); }
	if ( ! $form_dur )               { $errors[] = __( 'Vælg varighed.', 'studie247' ); }

	if ( empty( $errors ) ) {
		$prod_label = '';
		$prod_id    = 0;
		if ( $form_prod ) {
			$p = get_page_by_path( $form_prod, OBJECT, 'udlejning_item' );
			if ( $p ) { $prod_label = $p->post_title; $prod_id = $p->ID; }
			else      { $prod_label = $form_prod; }
		}
		$date_dk = $form_date ? date_i18n( 'l j. F Y', strtotime( $form_date ) ) : $form_date;

		// Gem som booking-post (afventer godkendelse).
		$booking_id = wp_insert_post( array(
			'post_type'   => 'booking',
			'post_status' => 'pending',
			'post_title'  => sprintf( '%s — %s %s', $form_name, $form_date, $form_start ),
		) );
		// Beregn estimeret lejepris hvis det er et udstyrs-produkt.
		$estimated_price     = 0;
		$estimated_price_fmt = '';
		if ( $prod_id ) {
			$pd = studie247_price_to_int( get_post_meta( $prod_id, '_s247_pris_dag', true ) );
			$pu = studie247_price_to_int( get_post_meta( $prod_id, '_s247_pris_uge', true ) );
			if ( ! $pu && $pd ) { $pu = $pd * 7; }
			$estimated_price = studie247_calc_rental_price( $pd, $pu, $form_dur );
			if ( $estimated_price ) { $estimated_price_fmt = studie247_format_dkk( $estimated_price ); }
		}

		if ( $booking_id && ! is_wp_error( $booking_id ) ) {
			update_post_meta( $booking_id, '_s247_date',     $form_date );
			update_post_meta( $booking_id, '_s247_start',    $form_start );
			update_post_meta( $booking_id, '_s247_duration', $form_dur );
			update_post_meta( $booking_id, '_s247_name',     $form_name );
			update_post_meta( $booking_id, '_s247_email',    $form_email );
			update_post_meta( $booking_id, '_s247_phone',    $form_phone );
			update_post_meta( $booking_id, '_s247_notes',    $form_notes );
			update_post_meta( $booking_id, '_s247_produkt',  $form_prod );
			if ( $prod_id )  { update_post_meta( $booking_id, '_s247_produkt_id', $prod_id ); }
			if ( $form_type ){ update_post_meta( $booking_id, '_s247_type',       $form_type ); }
			if ( $estimated_price ) { update_post_meta( $booking_id, '_s247_estimated_price', $estimated_price ); }
		}

		$admin_to      = 'info@s247.dk';
		$admin_subject = sprintf( '[Studie 247] Ny booking fra %s — %s', $form_name, $date_dk );
		$admin_body    = "Navn: {$form_name}\nEmail: {$form_email}\nTelefon: {$form_phone}\n";
		if ( $form_type )   { $admin_body .= "Type: {$form_type}\n"; }
		if ( $prod_label )  { $admin_body .= "Produkt: {$prod_label}\n"; }
		$admin_body   .= "Dato: {$date_dk}\nStart: {$form_start}\nVarighed: {$form_dur}\n";
		if ( $estimated_price_fmt ) { $admin_body .= "Estimeret pris: {$estimated_price_fmt}\n"; }
		if ( $form_notes ) { $admin_body .= "\nNoter:\n{$form_notes}\n"; }
		@wp_mail( $admin_to, $admin_subject, $admin_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $form_name . ' <' . $form_email . '>',
		) );

		$user_subject  = 'Booking modtaget — afventer godkendelse — Studie 247';
		$user_body     = "Hej {$form_name},\n\nVi har modtaget din booking-anmodning og reserveret tiden foreløbigt.\n\n";
		if ( $prod_label ) { $user_body .= "Produkt: {$prod_label}\n"; }
		$user_body    .= "Dato: {$date_dk}\nStart: {$form_start}\nVarighed: {$form_dur}\n";
		if ( $estimated_price_fmt ) { $user_body .= "Estimeret pris: {$estimated_price_fmt}\n"; }
		if ( $form_notes ) { $user_body .= "\nDine noter:\n{$form_notes}\n"; }
		$user_body    .= "\nDin booking er markeret som 'afventer godkendelse'. Du hører fra os inden for 24 timer på hverdage med endelig bekræftelse.\n\n— Studie 247\ninfo@s247.dk";
		@wp_mail( $form_email, $user_subject, $user_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Studie 247 <info@s247.dk>',
			'Reply-To: info@s247.dk',
		) );

		$redirect = add_query_arg( 'sendt', '1', wp_get_referer() ?: home_url( '/booking-studie/' ) );
		if ( $form_prod ) { $redirect = add_query_arg( 'produkt', $form_prod, $redirect ); }
		wp_safe_redirect( $redirect );
		exit;
	}
}

// Hent alle fremtidige bookinger og byg blokerings-map.
$today_key = date( 'Y-m-d' );
$booked_map = array(); // 'YYYY-MM-DD' => array( 8, 9, 10 ... ) = blokerede timer (int)
$fully_booked = array(); // dage hvor alle slots er taget

$booking_posts = get_posts( array(
	'post_type'      => 'booking',
	'post_status'    => array( 'publish', 'pending' ),
	'posts_per_page' => -1,
	'meta_query'     => array(
		array(
			'key'     => '_s247_date',
			'value'   => $today_key,
			'compare' => '>=',
			'type'    => 'DATE',
		),
	),
) );

foreach ( $booking_posts as $b ) {
	// Produkt-bookinger blokerer ikke studie-timer (og omvendt) — de
	// bruger samme booking-CPT men forskellig kapacitet.
	if ( get_post_meta( $b->ID, '_s247_produkt', true ) ) { continue; }

	$d   = get_post_meta( $b->ID, '_s247_date', true );
	$s   = get_post_meta( $b->ID, '_s247_start', true );
	$dur = get_post_meta( $b->ID, '_s247_duration', true );
	if ( ! $d || ! $s ) { continue; }
	$start_h = (int) substr( $s, 0, 2 );

	// Bestem hvor mange timer der er blokeret (max 1 dag).
	$hours = 2;
	switch ( $dur ) {
		case '2 timer': $hours = 2;  break;
		case '4 timer': $hours = 4;  break;
		case '6 timer': $hours = 6;  break;
		case '8 timer':
		case '1 dag':   $hours = 13; break; // hele dagen (08-20)
	}

	if ( ! isset( $booked_map[ $d ] ) ) {
		$booked_map[ $d ] = array();
	}
	for ( $h = $start_h; $h < $start_h + $hours && $h <= 20; $h++ ) {
		if ( ! in_array( $h, $booked_map[ $d ], true ) ) {
			$booked_map[ $d ][] = $h;
		}
	}
}

// For produkt-mode: find dage hvor alle lager-enheder af produktet er
// udlejet via GODKENDTE (publish) lejeforespørgsler. Afventende (pending)
// forespørgsler blokerer IKKE — admin skal først godkende.
if ( $is_product ) {
	$antal = (int) get_post_meta( $product->ID, '_s247_antal', true );
	if ( $antal < 1 ) { $antal = 1; }

	$duration_days = array(
		'1 dag'  => 1, '2 dage' => 2, '3 dage' => 3, '4 dage' => 4,
		'1 uge'  => 7, '2 uger' => 14,
	);

	$rental_bookings = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array( 'key' => '_s247_produkt_id', 'value' => $product->ID, 'compare' => '=' ),
			array( 'key' => '_s247_date',       'value' => $today_key,   'compare' => '>=', 'type' => 'DATE' ),
		),
	) );

	$rental_day_count = array(); // 'YYYY-MM-DD' => antal reservationer
	foreach ( $rental_bookings as $rb ) {
		$start_date = get_post_meta( $rb->ID, '_s247_date', true );
		$rb_dur     = get_post_meta( $rb->ID, '_s247_duration', true );
		$days       = isset( $duration_days[ $rb_dur ] ) ? $duration_days[ $rb_dur ] : 1;
		if ( ! $start_date ) { continue; }
		$cursor = strtotime( $start_date );
		for ( $i = 0; $i < $days; $i++ ) {
			$k = date( 'Y-m-d', $cursor );
			$rental_day_count[ $k ] = ( $rental_day_count[ $k ] ?? 0 ) + 1;
			$cursor = strtotime( '+1 day', $cursor );
		}
	}
	// Fyldt op = alle lager-enheder taget den dag.
	$fully_booked = array();
	foreach ( $rental_day_count as $day => $count ) {
		if ( $count >= $antal ) { $fully_booked[] = $day; }
	}
	// Produkt-mode bruger ikke per-time blokering.
	$booked_map = array();
}

// Beregn fuldt bookede dage (alle 13 slots 08-20 er taget).
$all_slots = range( 8, 20 );
foreach ( $booked_map as $day => $hours_arr ) {
	sort( $hours_arr );
	if ( count( array_intersect( $all_slots, $hours_arr ) ) >= count( $all_slots ) ) {
		$fully_booked[] = $day;
	}
}

// Kalender — udregn måned.
$ym = isset( $_GET['ym'] ) ? sanitize_text_field( wp_unslash( $_GET['ym'] ) ) : date( 'Y-m' );
if ( ! preg_match( '/^\d{4}-\d{2}$/', $ym ) ) { $ym = date( 'Y-m' ); }
$first_ts    = strtotime( $ym . '-01' );
$month_label = date_i18n( 'F Y', $first_ts );
$days_in_mo  = (int) date( 't', $first_ts );
$first_wday  = (int) date( 'N', $first_ts ); // 1 (man) .. 7 (søn)
$prev_ym     = date( 'Y-m', strtotime( '-1 month', $first_ts ) );
$next_ym     = date( 'Y-m', strtotime( '+1 month', $first_ts ) );
$today       = date( 'Y-m-d' );
$today_ts    = strtotime( $today );

get_header();
?>

<section class="book2">
	<div class="wrap wrap--wide">
		<header class="book2__head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Book', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Reservér', 'studie247' ); ?>
				<em><?php echo $product ? esc_html( $product->post_title ) : esc_html__( 'studiet', 'studie247' ); ?></em>
			</h1>
			<p class="book2__lead">
				<?php esc_html_e( 'Vælg dag i kalenderen, tidspunkt og varighed. Vi bekræfter inden for 24 timer.', 'studie247' ); ?>
			</p>
		</header>

		<?php if ( $submitted ) : ?>
			<div class="book-success">
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Modtaget', 'studie247' ); ?></span>
				<h2 class="book-success__title"><?php esc_html_e( 'Tak — vi har din booking', 'studie247' ); ?></h2>
				<p><?php esc_html_e( 'Bekræftelse er sendt til din mail. Vi vender tilbage hurtigst muligt.', 'studie247' ); ?></p>
				<div style="display:flex;gap:var(--sp-3);flex-wrap:wrap;margin-top:var(--sp-5);">
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Til forsiden', 'studie247' ); ?> <?php echo studie247_icon( 'arrow-right', 16 ); ?></a>
				</div>
			</div>
		<?php else : ?>
			<div class="book2__layout">
				<!-- Venstre: kalender + valg -->
				<div class="book2__picker" data-book-picker data-book-mode="<?php echo $is_product ? 'product' : 'studio'; ?>" data-booked="<?php echo esc_attr( wp_json_encode( $booked_map ) ); ?>" data-price-day="<?php echo esc_attr( $product_price_day ); ?>" data-price-week="<?php echo esc_attr( $product_price_week ); ?>">
					<div class="book2__calendar">
						<div class="book2__cal-head">
							<a class="book2__cal-nav" href="<?php echo esc_url( add_query_arg( 'ym', $prev_ym ) ); ?>" aria-label="<?php esc_attr_e( 'Forrige måned', 'studie247' ); ?>">‹</a>
							<span class="book2__cal-title"><?php echo esc_html( ucfirst( $month_label ) ); ?></span>
							<a class="book2__cal-nav" href="<?php echo esc_url( add_query_arg( 'ym', $next_ym ) ); ?>" aria-label="<?php esc_attr_e( 'Næste måned', 'studie247' ); ?>">›</a>
						</div>
						<div class="book2__cal-weeknames">
							<span>Man</span><span>Tir</span><span>Ons</span><span>Tor</span><span>Fre</span><span>Lør</span><span>Søn</span>
						</div>
						<div class="book2__cal-days">
							<?php
							// Tomme celler før månedens start.
							for ( $e = 1; $e < $first_wday; $e++ ) {
								echo '<span class="book2__day book2__day--empty"></span>';
							}
							for ( $d = 1; $d <= $days_in_mo; $d++ ) {
								$date = sprintf( '%s-%02d', $ym, $d );
								$ts   = strtotime( $date );
								$wday = (int) date( 'N', $ts );
								$cls  = 'book2__day';
								if ( $ts < $today_ts ) {
									$cls .= ' book2__day--past';
								}
								if ( 7 === $wday || 6 === $wday ) {
									$cls .= ' book2__day--weekend';
								}
								if ( $date === $today ) {
									$cls .= ' book2__day--today';
								}
								$is_full = in_array( $date, $fully_booked, true );
								if ( $is_full ) {
									$cls .= ' book2__day--booked';
								}
								$disabled = ( $ts < $today_ts || $is_full );
								$attrs    = $disabled ? 'aria-disabled="true"' : 'data-date="' . esc_attr( $date ) . '"';
								printf(
									'<button type="button" class="%s" %s>%d</button>',
									esc_attr( $cls ),
									$attrs,
									$d
								);
							}
							?>
						</div>
						<p class="book2__cal-legend">
							<span><span class="book2__legend-dot book2__legend-dot--today"></span> I dag</span>
							<span><span class="book2__legend-dot book2__legend-dot--on"></span> Valgt</span>
							<span><span class="book2__legend-dot book2__legend-dot--booked"></span> Optaget</span>
							<span><span class="book2__legend-dot book2__legend-dot--past"></span> Lukket</span>
						</p>
					</div>

					<div class="book2__slots" data-book-slots hidden>
						<h3 class="book2__section-title">
							<span class="book2__step-num">02</span>
							<?php echo $is_product ? esc_html__( 'Vælg lejeperiode', 'studie247' ) : esc_html__( 'Vælg tidspunkt', 'studie247' ); ?>
						</h3>
						<p class="book2__picked" data-book-picked></p>
						<?php if ( ! $is_product ) : ?>
							<div class="book2__time-grid">
								<?php for ( $h = 8; $h <= 20; $h++ ) :
									$val = sprintf( '%02d:00', $h ); ?>
									<button type="button" class="book2__time" data-time="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $val ); ?></button>
								<?php endfor; ?>
							</div>
						<?php endif; ?>
						<div class="book2__duration">
							<span class="book2__dur-label"><?php echo $is_product ? esc_html__( 'Varighed', 'studie247' ) : esc_html__( 'Varighed', 'studie247' ); ?></span>
							<div class="book2__dur-grid">
								<?php foreach ( $duration_options as $opt ) : ?>
									<button type="button" class="book2__dur" data-duration="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></button>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Højre: opsummering + oplysninger -->
				<aside class="book2__side" data-book-side>
					<?php if ( $product ) :
						$pris_dag = get_post_meta( $product->ID, '_s247_pris_dag', true );
						$cats     = get_the_terms( $product->ID, 'udlejning_kategori' );
					?>
						<div class="book2__product">
							<?php if ( has_post_thumbnail( $product->ID ) ) : ?>
								<div class="book2__product-media"><?php echo get_the_post_thumbnail( $product->ID, 's247-square', array( 'loading' => 'lazy' ) ); ?></div>
							<?php endif; ?>
							<div class="book2__product-body">
								<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
									<span class="product-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
								<?php endif; ?>
								<h2 class="book2__product-title"><?php echo esc_html( $product->post_title ); ?></h2>
								<?php if ( $pris_dag ) : ?>
									<span class="product-card__price-num"><?php echo esc_html( $pris_dag ); ?><span class="product-card__price-unit"> / dag</span></span>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>

					<div class="book2__summary">
						<h3 class="book2__section-title"><span class="book2__step-num">01</span> <?php echo $is_product ? esc_html__( 'Din forespørgsel', 'studie247' ) : esc_html__( 'Din booking', 'studie247' ); ?></h3>
						<dl class="book2__sum-list">
							<div><dt><?php echo $is_product ? esc_html__( 'Start-dato', 'studie247' ) : esc_html__( 'Dato', 'studie247' ); ?></dt><dd data-sum-date>—</dd></div>
							<?php if ( ! $is_product ) : ?>
								<div><dt><?php esc_html_e( 'Tid', 'studie247' ); ?></dt><dd data-sum-time>—</dd></div>
							<?php endif; ?>
							<div><dt><?php esc_html_e( 'Varighed', 'studie247' ); ?></dt><dd data-sum-duration>—</dd></div>
							<?php if ( $is_product ) : ?>
								<div class="book2__sum-total"><dt><?php esc_html_e( 'Estimeret pris', 'studie247' ); ?></dt><dd data-sum-price>—</dd></div>
							<?php endif; ?>
						</dl>
					</div>

					<form method="post" action="" class="book2__form" data-book-form novalidate>
						<?php wp_nonce_field( 's247_book', 's247_book_nonce' ); ?>
						<input type="hidden" name="s247_produkt"  value="<?php echo esc_attr( $form_prod ); ?>">
						<input type="hidden" name="s247_type"     value="<?php echo esc_attr( $form_type ); ?>">
						<input type="hidden" name="s247_date"     value="" data-field-date>
						<input type="hidden" name="s247_start"    value="<?php echo $is_product ? '10:00' : ''; ?>" data-field-time>
						<input type="hidden" name="s247_duration" value="" data-field-duration>

						<h3 class="book2__section-title"><span class="book2__step-num">03</span> <?php esc_html_e( 'Dine oplysninger', 'studie247' ); ?></h3>

						<?php if ( $errors ) : ?>
							<div class="kontakt-errors" role="alert">
								<span class="kontakt-errors__label"><?php esc_html_e( 'Lige en ting—', 'studie247' ); ?></span>
								<ul>
									<?php foreach ( $errors as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'Navn', 'studie247' ); ?></span>
							<input type="text"  name="s247_name"  value="<?php echo esc_attr( $form_name ); ?>" required>
						</label>
						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'E-mail', 'studie247' ); ?></span>
							<input type="email" name="s247_email" value="<?php echo esc_attr( $form_email ); ?>" required>
						</label>
						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'Telefon', 'studie247' ); ?></span>
							<input type="tel"   name="s247_phone" value="<?php echo esc_attr( $form_phone ); ?>">
						</label>
						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'Noter', 'studie247' ); ?></span>
							<textarea name="s247_notes" rows="3" placeholder="<?php esc_attr_e( 'Ekstra ønsker eller spørgsmål?', 'studie247' ); ?>"><?php echo esc_textarea( $form_notes ); ?></textarea>
						</label>

						<button type="submit" class="btn btn--primary btn--lg" data-book-submit disabled>
							<?php echo $is_product ? esc_html__( 'Send forespørgsel', 'studie247' ) : esc_html__( 'Send booking-anmodning', 'studie247' ); ?>
							<?php echo studie247_icon( 'arrow-right', 16 ); ?>
						</button>
						<p class="book-form__note" data-book-hint><?php echo $is_product ? esc_html__( 'Vælg start-dato og varighed for at fortsætte.', 'studie247' ) : esc_html__( 'Vælg dato, tid og varighed for at fortsætte.', 'studie247' ); ?></p>
						<?php if ( $is_product ) : ?>
							<p class="book-form__bulk-hint">
								<?php esc_html_e( 'Skal du bruge flere produkter?', 'studie247' ); ?>
								<a href="mailto:udlejning@s247.dk?subject=Udlejning%20%E2%80%94%20flere%20produkter">
									<?php esc_html_e( 'Send en mail til udlejning@s247.dk', 'studie247' ); ?>
								</a>
							</p>
						<?php endif; ?>
					</form>
				</aside>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
