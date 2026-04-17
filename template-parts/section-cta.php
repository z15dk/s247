<?php
/**
 * CTA banner.
 */
?>
<section class="section section--tight" aria-labelledby="cta-title">
	<div class="wrap">
		<div class="cta-banner">
			<h2 id="cta-title" class="cta-banner__title">
				<?php esc_html_e( 'Klar til at', 'studie247' ); ?> <em><?php esc_html_e( 'skabe?', 'studie247' ); ?></em>
			</h2>
			<div style="display:flex;gap:var(--sp-3);flex-wrap:wrap;">
				<?php
				studie247_button( __( 'Book nu', 'studie247' ), home_url( '/book/' ), 'primary', 'btn--lg' );
				studie247_button( __( 'Kontakt os', 'studie247' ), home_url( '/kontakt/' ), 'secondary', 'btn--lg btn--on-dark' );
				?>
			</div>
		</div>
	</div>
</section>
