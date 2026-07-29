<?php
/**
 * Studio section — editorial, overlap, asymmetric.
 */
$studio_img    = get_theme_mod( 's247_studio_image' );
$badge_top     = get_theme_mod( 's247_studio_badge_top', 'Aarhus' );
$badge_bottom  = get_theme_mod( 's247_studio_badge_bottom', 'Studie 247' );
$title_a       = get_theme_mod( 's247_studio_title_a', 'Ét rum —' );
$title_b       = get_theme_mod( 's247_studio_title_b', 'uendelige muligheder' );
$lead          = get_theme_mod( 's247_studio_lead', 'Fuldt udstyret produktionsrum med cyklorama, professionelt lys og lyd, green screen og plads til store opsætninger. <em>Alt du skal have med er idéen.</em>' );
$specs = array(
	array( get_theme_mod( 's247_studio_spec1_num', '200 m²' ), get_theme_mod( 's247_studio_spec1_label', 'Studio space' ) ),
	array( get_theme_mod( 's247_studio_spec2_num', '24/7' ),   get_theme_mod( 's247_studio_spec2_label', 'Adgang' ) ),
	array( get_theme_mod( 's247_studio_spec3_num', '4K' ),     get_theme_mod( 's247_studio_spec3_label', 'Fast kamera-rig' ) ),
	array( get_theme_mod( 's247_studio_spec4_num', '∞' ),      get_theme_mod( 's247_studio_spec4_label', 'Kreative idéer' ) ),
);
$cta1_text = get_theme_mod( 's247_studio_cta1_text', 'Se studiet' );
$cta1_url  = get_theme_mod( 's247_studio_cta1_url', home_url( '/studiet/' ) );
$cta2_text = get_theme_mod( 's247_studio_cta2_text', 'Lej studiet råt' );
$cta2_url  = get_theme_mod( 's247_studio_cta2_url', home_url( '/studiet/#raalej' ) );
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
				<?php if ( $badge_top || $badge_bottom ) : ?>
				<div class="studio__badge">
					<?php echo studie247_icon( 'sparkle', 18 ); ?>
					<div style="display:flex;flex-direction:column;line-height:1;gap:var(--sp-1);">
						<?php if ( $badge_top ) : ?>
							<span style="font-family:var(--font-serif);font-style:italic;color:var(--color-accent);font-size:var(--fs-md);"><?php echo esc_html( $badge_top ); ?></span>
						<?php endif; ?>
						<?php if ( $badge_bottom ) : ?>
							<span style="font-size:var(--fs-micro);text-transform:uppercase;letter-spacing:var(--tracking-caps);color:var(--color-ink-mute);"><?php echo esc_html( $badge_bottom ); ?></span>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>

			<div class="studio__content" data-reveal style="--reveal-delay: 120ms;">
				<div class="section-head__meta">
					<span class="section-num">01</span>
					<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Studiet', 'studie247' ); ?></span>
				</div>
				<h2 id="studio-title" class="section-head__title">
					<?php echo esc_html( $title_a ); ?> <em><?php echo esc_html( $title_b ); ?></em>
				</h2>
				<p class="lead">
					<?php echo wp_kses_post( $lead ); ?>
				</p>

				<div class="studio__specs">
					<?php foreach ( $specs as $spec ) : if ( empty( $spec[0] ) && empty( $spec[1] ) ) continue; ?>
						<div>
							<span class="studio__spec-num"><?php echo esc_html( $spec[0] ); ?></span>
							<span class="studio__spec-label"><?php echo esc_html( $spec[1] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<div style="display:flex; gap: var(--sp-4); flex-wrap: wrap;">
					<?php if ( $cta1_text && $cta1_url ) studie247_button( $cta1_text, $cta1_url, 'primary' ); ?>
					<?php if ( $cta2_text && $cta2_url ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $cta2_url ); ?>">
							<?php echo esc_html( $cta2_text ); ?>
							<?php echo studie247_icon( 'arrow-right', 16 ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
