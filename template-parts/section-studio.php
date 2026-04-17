<?php
/**
 * Studio section — "ét studie, uendelige muligheder" + lej råt.
 */
?>
<section class="section" aria-labelledby="studio-title" style="background: var(--color-surface);">
	<div class="wrap">
		<div class="studio">
			<div class="studio__media">
				<?php
				$studio_img = get_theme_mod( 's247_studio_image' );
				if ( $studio_img ) {
					printf(
						'<img src="%s" alt="%s" loading="lazy">',
						esc_url( $studio_img ),
						esc_attr__( 'Studie 247 — studierum', 'studie247' )
					);
				} else {
					echo '<div style="width:100%;height:100%;background:linear-gradient(135deg,#282828,#3a2a26);display:grid;place-items:center;color:var(--s247-bone);font-family:var(--font-serif);font-style:italic;font-size:3rem;">247</div>';
				}
				?>
			</div>
			<div class="studio__content">
				<span class="eyebrow"><?php esc_html_e( 'Vores studie', 'studie247' ); ?></span>
				<h2 id="studio-title" class="section-head__title">
					<?php esc_html_e( 'Ét studie —', 'studie247' ); ?> <em><?php esc_html_e( 'uendelige muligheder', 'studie247' ); ?></em>
				</h2>
				<p>
					<?php esc_html_e( 'Fuldt udstyret produktionsrum med cyklorama, professionelt lys og lyd, green screen og plads til store opsætninger. Alt du skal have med er idéen.', 'studie247' ); ?>
				</p>
				<div class="studio__specs">
					<span class="pill"><?php echo studie247_icon( 'check', 14 ); ?> 200 m²</span>
					<span class="pill"><?php echo studie247_icon( 'check', 14 ); ?> Cyklorama</span>
					<span class="pill"><?php echo studie247_icon( 'check', 14 ); ?> Lydisoleret</span>
					<span class="pill"><?php echo studie247_icon( 'check', 14 ); ?> Plug &amp; play</span>
				</div>
				<div style="display:flex; gap: var(--sp-3); flex-wrap: wrap; margin-top: var(--sp-4);">
					<?php
					studie247_button( __( 'Se studiet', 'studie247' ), home_url( '/studiet/' ), 'primary' );
					studie247_button( __( 'Lej studiet råt', 'studie247' ), home_url( '/studiet/#raalej' ), 'ghost' );
					?>
				</div>
			</div>
		</div>
	</div>
</section>
