<?php
/**
 * Book-side — kalender-grid booking (koncept 05).
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

// POST handler.
if ( ! empty( $_POST['s247_book_nonce'] ) && wp_verify_nonce( $_POST['s247_book_nonce'], 's247_book' ) ) {
	$form_name  = sanitize_text_field( wp_unslash( $_POST['name']  ?? '' ) );
	$form_email = sanitize_email(      wp_unslash( $_POST['email'] ?? '' ) );
	$form_phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$form_date  = sanitize_text_field( wp_unslash( $_POST['date']  ?? '' ) );
	$form_start = sanitize_text_field( wp_unslash( $_POST['start'] ?? '' ) );
	$form_dur   = sanitize_text_field( wp_unslash( $_POST['duration'] ?? '' ) );
	$form_notes = sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) );
	$form_prod  = sanitize_title(      wp_unslash( $_POST['produkt'] ?? '' ) );
	$form_type  = sanitize_text_field( wp_unslash( $_POST['type']  ?? '' ) );

	if ( ! $form_name )              { $errors[] = __( 'Udfyld dit navn.', 'studie247' ); }
	if ( ! is_email( $form_email ) ) { $errors[] = __( 'Indtast en gyldig email.', 'studie247' ); }
	if ( ! $form_date )              { $errors[] = __( 'Vælg en dato.', 'studie247' ); }
	if ( ! $form_start )             { $errors[] = __( 'Vælg et start-tidspunkt.', 'studie247' ); }
	if ( ! $form_dur )               { $errors[] = __( 'Vælg varighed.', 'studie247' ); }

	if ( empty( $errors ) ) {
		$prod_label = '';
		if ( $form_prod ) {
			$p = get_page_by_path( $form_prod, OBJECT, 'udlejning_item' );
			$prod_label = $p ? $p->post_title : $form_prod;
		}
		$date_dk = $form_date ? date_i18n( 'l j. F Y', strtotime( $form_date ) ) : $form_date;

		$admin_to      = 'info@s247.dk';
		$admin_subject = sprintf( '[Studie 247] Ny booking fra %s — %s', $form_name, $date_dk );
		$admin_body    = "Navn: {$form_name}\nEmail: {$form_email}\nTelefon: {$form_phone}\n";
		if ( $form_type )   { $admin_body .= "Type: {$form_type}\n"; }
		if ( $prod_label )  { $admin_body .= "Produkt: {$prod_label}\n"; }
		$admin_body   .= "Dato: {$date_dk}\nStart: {$form_start}\nVarighed: {$form_dur}\n";
		if ( $form_notes ) { $admin_body .= "\nNoter:\n{$form_notes}\n"; }
		@wp_mail( $admin_to, $admin_subject, $admin_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $form_name . ' <' . $form_email . '>',
		) );

		$user_subject  = 'Tak for din booking — Studie 247';
		$user_body     = "Hej {$form_name},\n\nTak for din booking-anmodning.\n\n";
		if ( $prod_label ) { $user_body .= "Produkt: {$prod_label}\n"; }
		$user_body    .= "Dato: {$date_dk}\nStart: {$form_start}\nVarighed: {$form_dur}\n";
		if ( $form_notes ) { $user_body .= "\nDine noter:\n{$form_notes}\n"; }
		$user_body    .= "\nVi bekræfter tilgængelighed inden for 24 timer.\n\n— Studie 247\ninfo@s247.dk";
		@wp_mail( $form_email, $user_subject, $user_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Studie 247 <info@s247.dk>',
			'Reply-To: info@s247.dk',
		) );

		$redirect = add_query_arg( 'sendt', '1', wp_get_referer() ?: home_url( '/book/' ) );
		if ( $form_prod ) { $redirect = add_query_arg( 'produkt', $form_prod, $redirect ); }
		wp_safe_redirect( $redirect );
		exit;
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
				<div class="book2__picker" data-book-picker>
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
								$attrs = $ts >= $today_ts ? 'data-date="' . esc_attr( $date ) . '"' : 'aria-disabled="true"';
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
							<span><span class="book2__legend-dot book2__legend-dot--past"></span> Lukket</span>
						</p>
					</div>

					<div class="book2__slots" data-book-slots hidden>
						<h3 class="book2__section-title"><span class="book2__step-num">02</span> <?php esc_html_e( 'Vælg tidspunkt', 'studie247' ); ?></h3>
						<p class="book2__picked" data-book-picked></p>
						<div class="book2__time-grid">
							<?php for ( $h = 8; $h <= 20; $h++ ) :
								$val = sprintf( '%02d:00', $h ); ?>
								<button type="button" class="book2__time" data-time="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $val ); ?></button>
							<?php endfor; ?>
						</div>
						<div class="book2__duration">
							<span class="book2__dur-label"><?php esc_html_e( 'Varighed', 'studie247' ); ?></span>
							<div class="book2__dur-grid">
								<?php foreach ( array( '2 timer', '4 timer', '8 timer', '1 dag', '2 dage', '1 uge' ) as $opt ) : ?>
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
						<h3 class="book2__section-title"><span class="book2__step-num">01</span> <?php esc_html_e( 'Din booking', 'studie247' ); ?></h3>
						<dl class="book2__sum-list">
							<div><dt><?php esc_html_e( 'Dato', 'studie247' ); ?></dt><dd data-sum-date>—</dd></div>
							<div><dt><?php esc_html_e( 'Tid', 'studie247' ); ?></dt><dd data-sum-time>—</dd></div>
							<div><dt><?php esc_html_e( 'Varighed', 'studie247' ); ?></dt><dd data-sum-duration>—</dd></div>
						</dl>
					</div>

					<form method="post" action="" class="book2__form" data-book-form novalidate>
						<?php wp_nonce_field( 's247_book', 's247_book_nonce' ); ?>
						<input type="hidden" name="produkt"  value="<?php echo esc_attr( $form_prod ); ?>">
						<input type="hidden" name="type"     value="<?php echo esc_attr( $form_type ); ?>">
						<input type="hidden" name="date"     value="" data-field-date>
						<input type="hidden" name="start"    value="" data-field-time>
						<input type="hidden" name="duration" value="" data-field-duration>

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
							<input type="text"  name="name"  value="<?php echo esc_attr( $form_name ); ?>" required>
						</label>
						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'E-mail', 'studie247' ); ?></span>
							<input type="email" name="email" value="<?php echo esc_attr( $form_email ); ?>" required>
						</label>
						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'Telefon', 'studie247' ); ?></span>
							<input type="tel"   name="phone" value="<?php echo esc_attr( $form_phone ); ?>">
						</label>
						<label class="book-field">
							<span class="book-field__label"><?php esc_html_e( 'Noter', 'studie247' ); ?></span>
							<textarea name="notes" rows="3" placeholder="<?php esc_attr_e( 'Ekstra ønsker eller spørgsmål?', 'studie247' ); ?>"><?php echo esc_textarea( $form_notes ); ?></textarea>
						</label>

						<button type="submit" class="btn btn--primary btn--lg" data-book-submit disabled>
							<?php esc_html_e( 'Send booking-anmodning', 'studie247' ); ?>
							<?php echo studie247_icon( 'arrow-right', 16 ); ?>
						</button>
						<p class="book-form__note" data-book-hint><?php esc_html_e( 'Vælg dato, tid og varighed for at fortsætte.', 'studie247' ); ?></p>
					</form>
				</aside>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
