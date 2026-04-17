<?php
/**
 * CTA banner — huge editorial statement with 247 backdrop.
 */
?>
<section class="section section--tight" aria-labelledby="cta-title">
	<div class="wrap wrap--wide">
		<div class="cta-banner" data-reveal>
			<div class="cta-banner__content">
				<span class="eyebrow cta-banner__eyebrow"><?php esc_html_e( 'Kom i gang', 'studie247' ); ?></span>
				<h2 id="cta-title" class="cta-banner__title">
					<?php esc_html_e( 'Klar til at', 'studie247' ); ?>
					<em><?php esc_html_e( 'skabe?', 'studie247' ); ?></em>
				</h2>
			</div>
			<div class="cta-banner__actions">
				<?php studie247_button( __( 'Book studie', 'studie247' ), home_url( '/book/' ), 'primary', 'btn--xl' ); ?>
				<a class="btn btn--ghost btn--on-dark" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">
					<?php esc_html_e( 'Tag en snak først', 'studie247' ); ?>
					<?php echo studie247_icon( 'arrow-right', 16 ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
