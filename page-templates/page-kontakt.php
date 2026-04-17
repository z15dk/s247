<?php
/**
 * Template Name: Kontakt
 *
 * @package Studie247
 */

get_header();

$submitted = false;
$errors    = array();

if ( ! empty( $_POST['s247_contact_nonce'] ) && wp_verify_nonce( $_POST['s247_contact_nonce'], 's247_contact' ) ) {
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( ! $name )                { $errors[] = __( 'Udfyld dit navn.', 'studie247' ); }
	if ( ! is_email( $email ) )   { $errors[] = __( 'Indtast en gyldig email.', 'studie247' ); }
	if ( strlen( $message ) < 5 ) { $errors[] = __( 'Skriv en besked.', 'studie247' ); }

	if ( empty( $errors ) ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf( '[Studie 247] Ny kontaktbesked fra %s', $name );
		$body    = sprintf( "Navn: %s\nEmail: %s\n\n%s", $name, $email, $message );
		$headers = array( 'Reply-To: ' . $email );
		wp_mail( $to, $subject, $body, $headers );
		$submitted = true;
	}
}
?>

<section class="section">
	<div class="wrap" style="display:grid;gap:var(--sp-12);grid-template-columns:1fr;">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Skriv til os', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Lad os', 'studie247' ); ?> <em><?php esc_html_e( 'tage en snak', 'studie247' ); ?></em>
			</h1>
			<p class="section-head__lead"><?php esc_html_e( 'Send en besked, så vender vi tilbage inden for 24 timer på hverdage.', 'studie247' ); ?></p>
		</header>

		<div style="display:grid;gap:var(--sp-12);grid-template-columns:1fr;" class="kontakt-grid">
			<div>
				<?php if ( $submitted ) : ?>
					<div class="card" style="padding: var(--sp-8);">
						<h2 class="card__title"><?php esc_html_e( 'Tak for din besked', 'studie247' ); ?></h2>
						<p><?php esc_html_e( 'Vi vender tilbage hurtigst muligt.', 'studie247' ); ?></p>
					</div>
				<?php else : ?>
					<?php if ( $errors ) : ?>
						<div class="card" style="padding:var(--sp-5); border-color: var(--color-accent);">
							<ul>
								<?php foreach ( $errors as $error ) : ?>
									<li><?php echo esc_html( $error ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<form method="post" class="contact-form" novalidate>
						<?php wp_nonce_field( 's247_contact', 's247_contact_nonce' ); ?>
						<div class="field">
							<label class="field__label" for="f-name"><?php esc_html_e( 'Dit navn', 'studie247' ); ?></label>
							<input class="field__input" id="f-name" type="text" name="name" required>
						</div>
						<div class="field">
							<label class="field__label" for="f-email"><?php esc_html_e( 'Email', 'studie247' ); ?></label>
							<input class="field__input" id="f-email" type="email" name="email" required>
						</div>
						<div class="field">
							<label class="field__label" for="f-msg"><?php esc_html_e( 'Besked', 'studie247' ); ?></label>
							<textarea class="field__textarea" id="f-msg" name="message" required></textarea>
						</div>
						<button type="submit" class="btn btn--primary btn--lg"><?php esc_html_e( 'Send', 'studie247' ); ?></button>
					</form>
				<?php endif; ?>
			</div>

			<aside style="display:grid;gap:var(--sp-5);">
				<div class="card" style="padding:var(--sp-6);">
					<h3 class="card__title" style="font-size:var(--fs-md);"><?php esc_html_e( 'Kontakt', 'studie247' ); ?></h3>
					<p style="display:flex;align-items:center;gap:var(--sp-2);"><?php echo studie247_icon( 'mail', 18 ); ?> <a href="mailto:hej@studie247.dk">hej@studie247.dk</a></p>
					<p style="display:flex;align-items:center;gap:var(--sp-2);"><?php echo studie247_icon( 'phone', 18 ); ?> <a href="tel:+4500000000">+45 00 00 00 00</a></p>
					<p style="display:flex;align-items:center;gap:var(--sp-2);"><?php echo studie247_icon( 'pin', 18 ); ?> <?php esc_html_e( 'Adresse kommer', 'studie247' ); ?></p>
				</div>
				<div class="card" style="padding:var(--sp-6);">
					<h3 class="card__title" style="font-size:var(--fs-md);"><?php esc_html_e( 'Åbningstider', 'studie247' ); ?></h3>
					<p><?php esc_html_e( 'Man-fre: 08-18', 'studie247' ); ?></p>
					<p><?php esc_html_e( 'Lør-søn: efter aftale', 'studie247' ); ?></p>
					<p style="color:var(--color-accent);font-family:var(--font-serif);font-style:italic;"><?php esc_html_e( 'Bookinger 24/7 online', 'studie247' ); ?></p>
				</div>
			</aside>
		</div>

		<?php while ( have_posts() ) : the_post(); if ( get_the_content() ) : ?>
			<div class="page-content"><?php the_content(); ?></div>
		<?php endif; endwhile; ?>
	</div>
</section>

<style>
@media (min-width: 900px) {
	.kontakt-grid { grid-template-columns: 1.3fr 1fr !important; }
}
</style>

<?php get_footer();
