<?php
/**
 * Studio section — editorial, overlap, asymmetric.
 */
$studio_img = get_theme_mod( 's247_studio_image' );
?>
<section class="section" aria-labelledby="studio-title" style="background: var(--color-surface-deep);">
	<div class="wrap wrap--wide">
		<div class="studio">
			<div class="studio__media" data-reveal>
				<?php if ( $studio_img ) : ?>
					<img src="<?php echo esc_url( $studio_img ); ?>" alt="<?php esc_attr_e( 'Studie 247 — studierum', 'studie247' ); ?>" loading="lazy">
				<?php else : ?>
					<div style="width:100%;height:100%;background:linear-gradient(135deg,#282828 0%,#3a2a26 60%, #9E2B25 140%);display:grid;place-items:center;color:var(--s247-bone);font-family:var(--font-serif);font-style:italic;font-size:8rem;letter-spacing:-0.05em;">247</div>
				<?php endif; ?>
				<div class="studio__badge">
					<?php echo studie247_icon( 'sparkle', 18 ); ?>
					<div style="display:flex;flex-direction:column;line-height:1;gap:var(--sp-1);">
						<span style="font-family:var(--font-serif);font-style:italic;color:var(--color-accent);font-size:var(--fs-md);"><?php esc_html_e( 'Aarhus', 'studie247' ); ?></span>
						<span style="font-size:var(--fs-micro);text-transform:uppercase;letter-spacing:var(--tracking-caps);color:var(--color-ink-mute);"><?php esc_html_e( 'Studie 247', 'studie247' ); ?></span>
					</div>
				</div>
			</div>

			<div class="studio__content" data-reveal style="--reveal-delay: 120ms;">
				<div class="section-head__meta">
					<span class="section-num">02</span>
					<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Studiet', 'studie247' ); ?></span>
				</div>
				<h2 id="studio-title" class="section-head__title">
					<?php esc_html_e( 'Ét rum —', 'studie247' ); ?> <em><?php esc_html_e( 'uendelige muligheder', 'studie247' ); ?></em>
				</h2>
				<p class="lead">
					<?php echo wp_kses_post( __( 'Fuldt udstyret produktionsrum med cyklorama, professionelt lys og lyd, green screen og plads til store opsætninger. <em>Alt du skal have med er idéen.</em>', 'studie247' ) ); ?>
				</p>

				<div class="studio__specs">
					<div>
						<span class="studio__spec-num">200<span style="font-size:0.5em;vertical-align:0.5em;">m²</span></span>
						<span class="studio__spec-label"><?php esc_html_e( 'Studio space', 'studie247' ); ?></span>
					</div>
					<div>
						<span class="studio__spec-num">24/7</span>
						<span class="studio__spec-label"><?php esc_html_e( 'Adgang', 'studie247' ); ?></span>
					</div>
					<div>
						<span class="studio__spec-num">4K</span>
						<span class="studio__spec-label"><?php esc_html_e( 'Fast kamera-rig', 'studie247' ); ?></span>
					</div>
					<div>
						<span class="studio__spec-num">∞</span>
						<span class="studio__spec-label"><?php esc_html_e( 'Kreative idéer', 'studie247' ); ?></span>
					</div>
				</div>

				<div style="display:flex; gap: var(--sp-4); flex-wrap: wrap;">
					<?php studie247_button( __( 'Se studiet', 'studie247' ), home_url( '/studiet/' ), 'primary' ); ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/studiet/#raalej' ) ); ?>">
						<?php esc_html_e( 'Lej studiet råt', 'studie247' ); ?>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
