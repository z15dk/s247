<?php
/**
 * 404.
 *
 * @package Studie247
 */

get_header();
?>

<section class="section placeholder">
	<div class="wrap placeholder__inner">
		<span class="eyebrow">404</span>
		<h1 class="section-head__title" style="margin-block: var(--sp-4);">
			<?php esc_html_e( 'Siden er', 'studie247' ); ?> <em><?php esc_html_e( 'forsvundet', 'studie247' ); ?></em>
		</h1>
		<p style="color: var(--color-ink-soft); margin-bottom: var(--sp-6);">
			<?php esc_html_e( 'Linket findes ikke, eller siden er fjernet. Lad os få dig tilbage på sporet.', 'studie247' ); ?>
		</p>
		<?php studie247_button( __( 'Gå til forsiden', 'studie247' ), home_url( '/' ), 'primary', 'btn--lg' ); ?>
	</div>
</section>

<?php get_footer();
