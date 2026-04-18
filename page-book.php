<?php
/**
 * Book-side — booking-kalender.
 *
 * Understøtter valgfrit produkt-forvalg via ?produkt=<slug>.
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

// Behandl POST før get_header().
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

	if ( ! $form_name )                { $errors[] = __( 'Udfyld dit navn.', 'studie247' ); }
	if ( ! is_email( $form_email ) )   { $errors[] = __( 'Indtast en gyldig email.', 'studie247' ); }
	if ( ! $form_date )                { $errors[] = __( 'Vælg en dato.', 'studie247' ); }
	if ( ! $form_start )               { $errors[] = __( 'Vælg et start-tidspunkt.', 'studie247' ); }
	if ( ! $form_dur )                 { $errors[] = __( 'Vælg varighed.', 'studie247' ); }

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
		$admin_headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $form_name . ' <' . $form_email . '>',
		);
		@wp_mail( $admin_to, $admin_subject, $admin_body, $admin_headers );

		$user_subject  = 'Tak for din booking — Studie 247';
		$user_body     = "Hej {$form_name},\n\nTak for din booking-anmodning. Vi har modtaget følgende:\n\n";
		if ( $prod_label ) { $user_body .= "Produkt: {$prod_label}\n"; }
		$user_body    .= "Dato: {$date_dk}\nStart: {$form_start}\nVarighed: {$form_dur}\n";
		if ( $form_notes ) { $user_body .= "\nDine noter:\n{$form_notes}\n"; }
		$user_body    .= "\nVi bekræfter tilgængelighed og vender tilbage inden for 24 timer på hverdage.\n\n— Studie 247\ninfo@s247.dk";
		$user_headers  = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Studie 247 <info@s247.dk>',
			'Reply-To: info@s247.dk',
		);
		@wp_mail( $form_email, $user_subject, $user_body, $user_headers );

		$redirect = add_query_arg( 'sendt', '1', wp_get_referer() ?: home_url( '/book/' ) );
		if ( $form_prod ) { $redirect = add_query_arg( 'produkt', $form_prod, $redirect ); }
		wp_safe_redirect( $redirect );
		exit;
	}
}

get_header();
?>

<section class="book-section">
	<div class="wrap wrap--wide">
		<header class="book-head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Book', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Reservér', 'studie247' ); ?>
				<em><?php echo $product ? esc_html( $product->post_title ) : esc_html__( 'din tid', 'studie247' ); ?></em>
			</h1>
			<p class="book-head__lead">
				<?php esc_html_e( 'Vælg dato og tidsrum — vi bekræfter inden for 24 timer på hverdage.', 'studie247' ); ?>
			</p>
		</header>

		<?php if ( $submitted ) : ?>
			<div class="book-success">
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Modtaget', 'studie247' ); ?></span>
				<h2 class="book-success__title"><?php esc_html_e( 'Tak — vi har din booking', 'studie247' ); ?></h2>
				<p><?php esc_html_e( 'Vi har sendt en bekræftelse til din mail og kigger kalenderen igennem med det samme. Du hører fra os hurtigt.', 'studie247' ); ?></p>
				<div style="display:flex; gap: var(--sp-3); flex-wrap: wrap; margin-top: var(--sp-5);">
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/udlejning/' ) ); ?>">
						<?php esc_html_e( 'Se mere udstyr', 'studie247' ); ?>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</a>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Tilbage til forsiden', 'studie247' ); ?>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</a>
				</div>
			</div>
		<?php else : ?>
			<div class="book-layout">
				<?php if ( $product ) :
					$pris_dag = get_post_meta( $product->ID, '_s247_pris_dag', true );
					$pris_uge = get_post_meta( $product->ID, '_s247_pris_uge', true );
					$deposit  = get_post_meta( $product->ID, '_s247_deposit', true );
					$cats     = get_the_terms( $product->ID, 'udlejning_kategori' );
				?>
					<aside class="book-product">
						<div class="book-product__media">
							<?php if ( has_post_thumbnail( $product->ID ) ) : ?>
								<?php echo get_the_post_thumbnail( $product->ID, 's247-square', array( 'loading' => 'lazy' ) ); ?>
							<?php else : ?>
								<div class="product-card__placeholder" style="position:relative;height:100%;"><?php echo studie247_icon( 'camera', 48 ); ?></div>
							<?php endif; ?>
						</div>
						<div class="book-product__body">
							<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
								<span class="product-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
							<?php endif; ?>
							<h2 class="book-product__title"><?php echo esc_html( $product->post_title ); ?></h2>
							<?php if ( $pris_dag ) : ?>
								<div class="book-product__price">
									<span class="product-card__price-num"><?php echo esc_html( $pris_dag ); ?></span>
									<span class="product-card__price-unit"><?php esc_html_e( '/ dag', 'studie247' ); ?></span>
								</div>
							<?php endif; ?>
							<?php if ( $pris_uge || $deposit ) : ?>
								<ul class="book-product__meta">
									<?php if ( $pris_uge ) : ?>
										<li><span><?php esc_html_e( 'Uge', 'studie247' ); ?></span><strong><?php echo esc_html( $pris_uge ); ?></strong></li>
									<?php endif; ?>
									<?php if ( $deposit ) : ?>
										<li><span><?php esc_html_e( 'Depositum', 'studie247' ); ?></span><strong><?php echo esc_html( $deposit ); ?></strong></li>
									<?php endif; ?>
								</ul>
							<?php endif; ?>
						</div>
					</aside>
				<?php endif; ?>

				<div class="book-form-wrap">
					<?php if ( $errors ) : ?>
						<div class="kontakt-errors" role="alert" style="margin-bottom: var(--sp-5);">
							<span class="kontakt-errors__label"><?php esc_html_e( 'Lige en ting—', 'studie247' ); ?></span>
							<ul>
								<?php foreach ( $errors as $e ) : ?>
									<li><?php echo esc_html( $e ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<form method="post" action="" class="book-form" novalidate>
						<?php wp_nonce_field( 's247_book', 's247_book_nonce' ); ?>
						<input type="hidden" name="produkt" value="<?php echo esc_attr( $form_prod ); ?>">
						<input type="hidden" name="type"    value="<?php echo esc_attr( $form_type ); ?>">

						<fieldset class="book-form__section">
							<legend><?php esc_html_e( 'Hvornår?', 'studie247' ); ?></legend>
							<div class="book-form__row">
								<label class="book-field">
									<span class="book-field__label"><?php esc_html_e( 'Dato', 'studie247' ); ?></span>
									<input type="date" name="date" min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $form_date ); ?>" required>
								</label>
								<label class="book-field">
									<span class="book-field__label"><?php esc_html_e( 'Start-tidspunkt', 'studie247' ); ?></span>
									<select name="start" required>
										<option value=""><?php esc_html_e( '— Vælg —', 'studie247' ); ?></option>
										<?php for ( $h = 8; $h <= 20; $h++ ) :
											$val = sprintf( '%02d:00', $h ); ?>
											<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $form_start, $val ); ?>><?php echo esc_html( $val ); ?></option>
										<?php endfor; ?>
									</select>
								</label>
								<label class="book-field">
									<span class="book-field__label"><?php esc_html_e( 'Varighed', 'studie247' ); ?></span>
									<select name="duration" required>
										<option value=""><?php esc_html_e( '— Vælg —', 'studie247' ); ?></option>
										<?php
										$options = array( '2 timer', '4 timer', '8 timer (fuld dag)', '1 dag', '2 dage', '1 uge' );
										foreach ( $options as $opt ) : ?>
											<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $form_dur, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
							</div>
						</fieldset>

						<fieldset class="book-form__section">
							<legend><?php esc_html_e( 'Dine oplysninger', 'studie247' ); ?></legend>
							<div class="book-form__row">
								<label class="book-field">
									<span class="book-field__label"><?php esc_html_e( 'Navn', 'studie247' ); ?></span>
									<input type="text" name="name" value="<?php echo esc_attr( $form_name ); ?>" required>
								</label>
								<label class="book-field">
									<span class="book-field__label"><?php esc_html_e( 'E-mail', 'studie247' ); ?></span>
									<input type="email" name="email" value="<?php echo esc_attr( $form_email ); ?>" required>
								</label>
								<label class="book-field">
									<span class="book-field__label"><?php esc_html_e( 'Telefon', 'studie247' ); ?></span>
									<input type="tel" name="phone" value="<?php echo esc_attr( $form_phone ); ?>">
								</label>
							</div>
							<label class="book-field">
								<span class="book-field__label"><?php esc_html_e( 'Noter (valgfrit)', 'studie247' ); ?></span>
								<textarea name="notes" rows="3" placeholder="<?php esc_attr_e( 'Ekstra ønsker, spørgsmål eller leverings-info?', 'studie247' ); ?>"><?php echo esc_textarea( $form_notes ); ?></textarea>
							</label>
						</fieldset>

						<button type="submit" class="btn btn--primary btn--lg">
							<?php esc_html_e( 'Send booking-anmodning', 'studie247' ); ?>
							<?php echo studie247_icon( 'arrow-right', 16 ); ?>
						</button>
						<p class="book-form__note"><?php esc_html_e( 'Vi bekræfter tilgængelighed og sender faktura eller betalingslink inden for 24 timer på hverdage.', 'studie247' ); ?></p>
					</form>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer();
