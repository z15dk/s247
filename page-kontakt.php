<?php
/**
 * Kontakt — spørgsmåls-flow.
 *
 * @package Studie247
 */

$errors    = array();
$submitted = isset( $_GET['sendt'] ) && '1' === $_GET['sendt'];

$form_name    = '';
$form_email   = '';
$form_phone   = '';
$form_topic   = '';
$form_message = '';

if ( ! empty( $_POST['s247_ko_nonce'] ) && wp_verify_nonce( $_POST['s247_ko_nonce'], 's247_kontakt_os' ) ) {
	$form_topic   = sanitize_text_field( wp_unslash( $_POST['s247_topic']   ?? '' ) );
	$form_message = sanitize_textarea_field( wp_unslash( $_POST['s247_message'] ?? '' ) );
	$form_name    = sanitize_text_field( wp_unslash( $_POST['s247_name']    ?? '' ) );
	$form_email   = sanitize_email(      wp_unslash( $_POST['s247_email']   ?? '' ) );
	$form_phone   = sanitize_text_field( wp_unslash( $_POST['s247_phone']   ?? '' ) );

	if ( ! $form_message || strlen( $form_message ) < 5 ) {
		$errors[] = __( 'Skriv en besked.', 'studie247' );
	}
	if ( ! $form_name ) {
		$errors[] = __( 'Udfyld dit navn.', 'studie247' );
	}
	if ( ! is_email( $form_email ) ) {
		$errors[] = __( 'Indtast en gyldig email.', 'studie247' );
	}
	if ( empty( $_POST['s247_consent'] ) ) {
		$errors[] = __( 'Du skal acceptere privatlivspolitikken for at kunne sende beskeden.', 'studie247' );
	}

	if ( empty( $errors ) ) {
		// Persister besked som CPT så dashboardet/CRM kan hente den.
		studie247_save_kontakt_besked( array(
			'name'    => $form_name,
			'email'   => $form_email,
			'phone'   => $form_phone,
			'topic'   => $form_topic,
			'message' => $form_message,
			'source'  => 'kontakt-os',
		) );

		$admin_to      = 'info@s247.dk';
		$admin_subject = sprintf( '[Studie 247] Ny henvendelse fra %s', $form_name );
		$admin_body    = "Navn: {$form_name}\nEmail: {$form_email}\nTelefon: {$form_phone}\n";
		if ( $form_topic )   { $admin_body .= "Emne: {$form_topic}\n"; }
		$admin_body   .= "\nBesked:\n{$form_message}\n";
		@wp_mail( $admin_to, $admin_subject, $admin_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $form_name . ' <' . $form_email . '>',
		) );

		$user_subject = 'Tak for din besked — Studie 247';
		$user_body    = "Hej {$form_name},\n\nTak for at du skrev. Vi vender tilbage inden for 24 timer på hverdage.\n\nDin besked:\n{$form_message}\n\n— Studie 247\ninfo@s247.dk";
		@wp_mail( $form_email, $user_subject, $user_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Studie 247 <info@s247.dk>',
			'Reply-To: info@s247.dk',
		) );

		wp_safe_redirect( add_query_arg( 'sendt', '1', wp_get_referer() ?: home_url( '/kontakt-os/' ) ) );
		exit;
	}
}

get_header();
?>

<section class="ko-section">
	<div class="wrap wrap--tight">

		<?php if ( $submitted ) : ?>
			<div class="ko-success">
				<span class="eyebrow eyebrow--accent eyebrow--no-line">Modtaget</span>
				<h1 class="ko-head"><em>Tak</em> — vi læser den nu.</h1>
				<p>Vi vender tilbage inden for 24 timer på hverdage. Bekræftelse er sendt til din mail.</p>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">Tilbage til forsiden <?php echo studie247_icon( 'arrow-right', 16 ); ?></a>
			</div>
		<?php else : ?>
			<header class="ko-headwrap">
				<span class="eyebrow eyebrow--accent eyebrow--no-line">Kontakt os</span>
				<h1 class="ko-head">Én ting <em>ad gangen</em></h1>
				<p class="ko-lead">Besvar tre korte spørgsmål. Vi læser hver eneste besked selv.</p>
			</header>

			<div class="ko-layout">
				<aside class="ko-side">
					<?php
					// Hent op til 3 team-medlemmer med billede til "vi svarer"-strip.
					$ko_team = array();
					for ( $i = 1; $i <= 8 && count( $ko_team ) < 3; $i++ ) {
						$nm = get_theme_mod( "s247_team{$i}_name" );
						$im = get_theme_mod( "s247_team{$i}_image" );
						if ( $nm && $im ) { $ko_team[] = array( 'name' => $nm, 'image' => $im ); }
					}
					?>

					<div class="ko-promise">
						<span class="ko-promise__pulse" aria-hidden="true"></span>
						<span class="ko-promise__label"><?php esc_html_e( 'Vi svarer hurtigt', 'studie247' ); ?></span>
					</div>

					<dl class="ko-stats">
						<div>
							<dt><?php esc_html_e( 'Svartid', 'studie247' ); ?></dt>
							<dd><span class="ko-stats__big">&lt; 24t</span> <span class="ko-stats__hint"><?php esc_html_e( 'på hverdage', 'studie247' ); ?></span></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Læst af', 'studie247' ); ?></dt>
							<dd><span class="ko-stats__big">5</span> <span class="ko-stats__hint"><?php esc_html_e( 'mennesker (ikke en bot)', 'studie247' ); ?></span></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Bedste tid', 'studie247' ); ?></dt>
							<dd><span class="ko-stats__big">9–17</span> <span class="ko-stats__hint"><?php esc_html_e( 'man–fre', 'studie247' ); ?></span></dd>
						</div>
					</dl>

					<?php if ( ! empty( $ko_team ) ) : ?>
						<div class="ko-team">
							<div class="ko-team__avatars" aria-hidden="true">
								<?php foreach ( $ko_team as $m ) : ?>
									<span class="ko-team__avatar"><img src="<?php echo esc_url( $m['image'] ); ?>" alt=""></span>
								<?php endforeach; ?>
							</div>
							<p class="ko-team__caption">
								<?php
								$names = array_map( function ( $m ) { return explode( ' ', trim( $m['name'] ) )[0]; }, $ko_team );
								printf(
									esc_html__( '%s og resten af holdet svarer dig.', 'studie247' ),
									esc_html( implode( ', ', $names ) )
								);
								?>
							</p>
						</div>
					<?php endif; ?>

					<div class="ko-direct">
						<p class="ko-direct__label"><?php esc_html_e( 'Eller direkte:', 'studie247' ); ?></p>
						<a class="ko-direct__link" href="mailto:hej@studie247.dk">
							<?php echo studie247_icon( 'mail', 16 ); ?> hej@studie247.dk
						</a>
						<a class="ko-direct__link" href="tel:+4500000000">
							<?php echo studie247_icon( 'phone', 16 ); ?> +45 00 00 00 00
						</a>
					</div>
				</aside>

				<div class="ko-main">

			<form method="post" action="" class="ko-flow" data-ko-flow novalidate>
				<?php wp_nonce_field( 's247_kontakt_os', 's247_ko_nonce' ); ?>

				<?php if ( $errors ) : ?>
					<div class="kontakt-errors" role="alert">
						<span class="kontakt-errors__label">Lige en ting—</span>
						<ul><?php foreach ( $errors as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?></ul>
					</div>
				<?php endif; ?>

				<div class="ko-steps">
					<!-- Step 1 -->
					<section class="ko-step is-active" data-ko-step="1">
						<span class="ko-counter">Spørgsmål 01 / 03</span>
						<h2 class="ko-q"><em>Hvad</em> kan vi hjælpe med?</h2>
						<div class="ko-topics" role="radiogroup" aria-label="Emne">
							<?php foreach ( array( 'Booking', 'Priser', 'Udstyrs-leje', 'Samarbejde', 'Andet' ) as $topic ) : ?>
								<button
									type="button"
									class="ko-topic<?php echo $form_topic === $topic ? ' is-selected' : ''; ?>"
									data-ko-topic="<?php echo esc_attr( $topic ); ?>"
									role="radio"
									aria-checked="<?php echo $form_topic === $topic ? 'true' : 'false'; ?>"
								><?php echo esc_html( $topic ); ?></button>
							<?php endforeach; ?>
						</div>
						<input type="hidden" name="s247_topic" value="<?php echo esc_attr( $form_topic ); ?>" data-ko-topic-input>
						<label class="ko-field">
							<span class="ko-field-label">Fortæl gerne lidt mere</span>
							<textarea name="s247_message" rows="4" required placeholder="Skriv frit her …"><?php echo esc_textarea( $form_message ); ?></textarea>
						</label>
						<div class="ko-actions">
							<span></span>
							<button type="button" class="btn btn--primary" data-ko-next="2">Næste <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>

					<!-- Step 2 -->
					<section class="ko-step" data-ko-step="2" hidden>
						<span class="ko-counter">Spørgsmål 02 / 03</span>
						<h2 class="ko-q"><em>Hvem</em> skal vi skrive til?</h2>
						<label class="ko-field">
							<span class="ko-field-label">Dit navn</span>
							<input type="text" name="s247_name" value="<?php echo esc_attr( $form_name ); ?>" required>
						</label>
						<label class="ko-field">
							<span class="ko-field-label">E-mail</span>
							<input type="email" name="s247_email" value="<?php echo esc_attr( $form_email ); ?>" required>
						</label>
						<div class="ko-actions">
							<button type="button" class="btn btn--ghost" data-ko-prev="1">← Tilbage</button>
							<button type="button" class="btn btn--primary" data-ko-next="3">Næste <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>

					<!-- Step 3 -->
					<section class="ko-step" data-ko-step="3" hidden>
						<span class="ko-counter">Spørgsmål 03 / 03</span>
						<h2 class="ko-q">Hvor kan vi <em>ringe</em>?</h2>
						<label class="ko-field">
							<span class="ko-field-label">Telefon (valgfrit)</span>
							<input type="tel" name="s247_phone" value="<?php echo esc_attr( $form_phone ); ?>" placeholder="+45 …">
						</label>
						<p class="ko-note">Vi foretrækker mail, men ringer gerne hvis det er nemmere.</p>
						<?php studie247_consent_field(); ?>
						<div class="ko-actions">
							<button type="button" class="btn btn--ghost" data-ko-prev="2">← Tilbage</button>
							<button type="submit" class="btn btn--primary btn--lg">Send besked <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>
				</div>

				<div class="ko-progress" aria-hidden="true">
					<span class="ko-dot is-active" data-ko-dot="1"></span>
					<span class="ko-dot" data-ko-dot="2"></span>
					<span class="ko-dot" data-ko-dot="3"></span>
				</div>
			</form>
				</div><!-- .ko-main -->
			</div><!-- .ko-layout -->
		<?php endif; ?>

	</div>
</section>

<?php get_footer(); ?>
