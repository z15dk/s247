<?php
/**
 * Kontakt — spørgsmåls-flow.
 *
 * @package Studie247
 */

$submitted = isset( $_GET['sendt'] ) && '1' === $_GET['sendt'];
$errors    = array();
if ( ! empty( $_GET['err'] ) ) {
	$err_map = array(
		'besked'  => __( 'Skriv en besked.', 'studie247' ),
		'navn'    => __( 'Udfyld dit navn.', 'studie247' ),
		'email'   => __( 'Indtast en gyldig email.', 'studie247' ),
		'consent' => __( 'Du skal acceptere privatlivspolitikken for at kunne sende beskeden.', 'studie247' ),
		'nonce'   => __( 'Sikkerhedstoken udløb — prøv venligst igen.', 'studie247' ),
	);
	foreach ( explode( ',', sanitize_text_field( $_GET['err'] ) ) as $code ) {
		if ( isset( $err_map[ $code ] ) ) { $errors[] = $err_map[ $code ]; }
	}
}

$form_name    = '';
$form_email   = '';
$form_phone   = '';
$form_topic   = '';
$form_message = '';

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
				<span class="tb-eyebrow">
					<span class="tb-eyebrow__icon" aria-hidden="true"><?php echo studie247_icon( 'mail', 14 ); ?></span>
					<span class="tb-eyebrow__text"><?php esc_html_e( 'Kontakt os', 'studie247' ); ?></span>
				</span>
				<h1 class="ko-head">Én ting <em>ad gangen</em></h1>
				<p class="ko-lead">Besvar tre korte spørgsmål. Vi læser hver eneste besked selv — og vender tilbage inden for 24 timer.</p>
			</header>

			<?php
			$k_hero_video = get_theme_mod( 's247_kontakt_hero_video' );
			$k_hero_image = get_theme_mod( 's247_kontakt_hero_image' );
			if ( $k_hero_video || $k_hero_image ) : ?>
				<div class="ko-hero" aria-hidden="true">
					<?php if ( $k_hero_video ) : ?>
						<video autoplay muted loop playsinline <?php echo $k_hero_image ? 'poster="' . esc_url( $k_hero_image ) . '"' : ''; ?>>
							<source src="<?php echo esc_url( $k_hero_video ); ?>" type="video/mp4">
						</video>
					<?php elseif ( $k_hero_image ) : ?>
						<img src="<?php echo esc_url( $k_hero_image ); ?>" alt="" loading="eager">
					<?php endif; ?>
					<span class="ko-hero__grain"></span>
					<span class="ko-hero__fade"></span>
					<div class="ko-hero__caption">
						<span class="ko-hero__dot"></span>
						<span><?php esc_html_e( 'Vi er i studiet lige nu', 'studie247' ); ?></span>
					</div>
				</div>
			<?php endif; ?>

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
						<?php
						$ko_email = get_theme_mod( 's247_email', 'hej@s247.dk' );
						$ko_phone = get_theme_mod( 's247_phone', '+45 00 00 00 00' );
						$ko_phone_link = '+' . preg_replace( '/\D/', '', $ko_phone );
						?>
						<a class="ko-direct__link" href="mailto:<?php echo esc_attr( $ko_email ); ?>">
							<?php echo studie247_icon( 'mail', 16 ); ?> <?php echo esc_html( $ko_email ); ?>
						</a>
						<a class="ko-direct__link" href="tel:<?php echo esc_attr( $ko_phone_link ); ?>">
							<?php echo studie247_icon( 'phone', 16 ); ?> <?php echo esc_html( $ko_phone ); ?>
						</a>
					</div>
				</aside>

				<div class="ko-main">

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ko-flow" data-ko-flow novalidate>
				<input type="hidden" name="action" value="s247_kontakt">
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
							<?php
							$ko_topics = array(
								array( 'icon' => 'calendar',     'label' => 'Booking' ),
								array( 'icon' => 'circle-dot',   'label' => 'Priser' ),
								array( 'icon' => 'camera',       'label' => 'Udstyrs-leje' ),
								array( 'icon' => 'handshake',    'label' => 'Samarbejde' ),
								array( 'icon' => 'help',         'label' => 'Andet' ),
							);
							foreach ( $ko_topics as $t ) : ?>
								<button
									type="button"
									class="ko-topic<?php echo $form_topic === $t['label'] ? ' is-selected' : ''; ?>"
									data-ko-topic="<?php echo esc_attr( $t['label'] ); ?>"
									role="radio"
									aria-checked="<?php echo $form_topic === $t['label'] ? 'true' : 'false'; ?>"
								>
									<span class="ko-topic__icon" aria-hidden="true"><?php echo studie247_icon( $t['icon'], 18 ); ?></span>
									<span class="ko-topic__label"><?php echo esc_html( $t['label'] ); ?></span>
								</button>
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
