<?php
/**
 * Kontakt-side — "Vælg din side" layout.
 *
 * @package Studie247
 */

$errors     = array();
$form_name  = '';
$form_email = '';
$form_msg   = '';
$form_side  = '';
$submitted  = isset( $_GET['sendt'] ) && '1' === $_GET['sendt'];

// Behandl POST før get_header() så vi kan redirecte uden "headers already sent".
if ( ! empty( $_POST['s247_contact_nonce'] ) && wp_verify_nonce( $_POST['s247_contact_nonce'], 's247_contact' ) ) {
	$form_name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$form_email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$form_msg   = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	$form_side  = sanitize_text_field( wp_unslash( $_POST['side'] ?? '' ) );

	if ( ! $form_name ) {
		$errors[] = __( 'Udfyld dit navn.', 'studie247' );
	}
	if ( ! is_email( $form_email ) ) {
		$errors[] = __( 'Indtast en gyldig email.', 'studie247' );
	}
	if ( strlen( $form_msg ) < 5 ) {
		$errors[] = __( 'Skriv en kort besked.', 'studie247' );
	}

	if ( empty( $errors ) ) {
		$side_label = '';
		if ( 'A' === $form_side ) {
			$side_label = 'Side A — Rolig og grundig';
		} elseif ( 'B' === $form_side ) {
			$side_label = 'Side B — Hurtig og beskidt';
		}

		$admin_to      = 'info@s247.dk';
		$admin_subject = sprintf( '[Studie 247] Nyt valg fra %s', $form_name );
		$admin_body    = "Navn: {$form_name}\nEmail: {$form_email}\n";
		if ( $side_label ) {
			$admin_body .= "Valgt side: {$side_label}\n";
		}
		$admin_body .= "\nBesked:\n{$form_msg}\n";
		$admin_headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $form_name . ' <' . $form_email . '>',
		);
		@wp_mail( $admin_to, $admin_subject, $admin_body, $admin_headers );

		$user_subject  = 'Tak for din henvendelse — Studie 247';
		$user_body     = "Hej {$form_name},\n\nTak for at du skrev til os. Vi vender tilbage så hurtigt vi kan — typisk inden for 24 timer på hverdage.\n";
		if ( $side_label ) {
			$user_body .= "\nDu valgte: {$side_label}\n";
		}
		$user_body    .= "\nDin besked:\n{$form_msg}\n\n— Studie 247\ninfo@s247.dk";
		$user_headers  = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: Studie 247 <info@s247.dk>',
			'Reply-To: info@s247.dk',
		);
		@wp_mail( $form_email, $user_subject, $user_body, $user_headers );

		wp_safe_redirect( add_query_arg( 'sendt', '1', wp_get_referer() ?: home_url( '/kontakt/' ) ) );
		exit;
	}
}

get_header();
?>

<section class="kontakt-section">
	<div class="kontakt-card">
		<header class="kontakt-head">
			<span class="kontakt-eyebrow">07 &mdash; <?php esc_html_e( 'Kontakt', 'studie247' ); ?></span>
			<h1 class="kontakt-title">
				<?php esc_html_e( 'Vælg din', 'studie247' ); ?> <em><?php esc_html_e( 'side', 'studie247' ); ?></em>
			</h1>
		</header>

		<div class="sides" role="radiogroup" aria-label="<?php esc_attr_e( 'Vælg side', 'studie247' ); ?>">
			<button
				type="button"
				class="side side--a<?php echo 'A' === $form_side ? ' is-selected' : ''; ?>"
				data-side="A"
				role="radio"
				aria-checked="<?php echo 'A' === $form_side ? 'true' : 'false'; ?>"
			>
				<span class="side__label">Side A</span>
				<span class="side__title"><em><?php esc_html_e( 'Rolig og grundig', 'studie247' ); ?></em></span>
				<ul class="side__list">
					<li><?php esc_html_e( 'Sparringsmøde først', 'studie247' ); ?></li>
					<li><?php esc_html_e( 'Tid til at finde formatet', 'studie247' ); ?></li>
					<li><?php esc_html_e( '2–4 uger fra start til levering', 'studie247' ); ?></li>
				</ul>
			</button>

			<button
				type="button"
				class="side side--b<?php echo 'B' === $form_side ? ' is-selected' : ''; ?>"
				data-side="B"
				role="radio"
				aria-checked="<?php echo 'B' === $form_side ? 'true' : 'false'; ?>"
			>
				<span class="side__label">Side B</span>
				<span class="side__title"><em><?php esc_html_e( 'Hurtig og beskidt', 'studie247' ); ?></em></span>
				<ul class="side__list">
					<li><?php esc_html_e( 'Ind, optag, ud — samme dag', 'studie247' ); ?></li>
					<li><?php esc_html_e( 'Rå redigering, ingen dikkedarer', 'studie247' ); ?></li>
					<li><?php esc_html_e( 'Levering inden for 72 timer', 'studie247' ); ?></li>
				</ul>
			</button>
		</div>

		<?php if ( $submitted ) : ?>
			<div class="kontakt-success">
				<h2><?php esc_html_e( 'Tak for din besked', 'studie247' ); ?></h2>
				<p><?php esc_html_e( 'Vi har sendt en bekræftelse til din mail og vender tilbage hurtigst muligt.', 'studie247' ); ?></p>
			</div>
		<?php else : ?>
			<?php if ( $errors ) : ?>
				<div class="kontakt-errors" role="alert">
					<span class="kontakt-errors__label"><?php esc_html_e( 'Lige en ting—', 'studie247' ); ?></span>
					<ul>
						<?php foreach ( $errors as $err ) : ?>
							<li><?php echo esc_html( $err ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<form method="post" action="" class="kontakt-form" novalidate>
				<?php wp_nonce_field( 's247_contact', 's247_contact_nonce' ); ?>
				<input type="hidden" name="side" value="<?php echo esc_attr( $form_side ); ?>" data-side-input>
				<div class="kontakt-form__row">
					<label class="kontakt-field">
						<span class="screen-reader-text"><?php esc_html_e( 'Navn', 'studie247' ); ?></span>
						<input type="text" name="name" placeholder="<?php esc_attr_e( 'Navn', 'studie247' ); ?>" value="<?php echo esc_attr( $form_name ); ?>" required>
					</label>
					<label class="kontakt-field">
						<span class="screen-reader-text"><?php esc_html_e( 'E-mail', 'studie247' ); ?></span>
						<input type="email" name="email" placeholder="<?php esc_attr_e( 'E-mail', 'studie247' ); ?>" value="<?php echo esc_attr( $form_email ); ?>" required>
					</label>
				</div>
				<label class="kontakt-field">
					<span class="screen-reader-text"><?php esc_html_e( 'Besked', 'studie247' ); ?></span>
					<textarea name="message" rows="3" placeholder="<?php esc_attr_e( 'Kort om projektet — og hvilken side?', 'studie247' ); ?>" required><?php echo esc_textarea( $form_msg ); ?></textarea>
				</label>
				<button type="submit" class="kontakt-submit">
					<?php esc_html_e( 'Send valg', 'studie247' ); ?> <span aria-hidden="true">→</span>
				</button>
			</form>
		<?php endif; ?>
	</div>
</section>

<?php get_footer();
