<?php
/**
 * Template Name: Udlejning (placeholder)
 *
 * Selve udstyrs-udlejningen bygges til sidst. Denne side
 * holder pladsen, så menu/routing/SEO er på plads fra dag ét.
 *
 * @package Studie247
 */

get_header();
?>

<section class="section placeholder">
	<div class="wrap placeholder__inner">
		<span class="eyebrow"><?php esc_html_e( 'Udstyrs-udlejning', 'studie247' ); ?></span>
		<h1 class="section-head__title" style="margin-block: var(--sp-4);">
			<?php esc_html_e( 'Udlejning —', 'studie247' ); ?> <em><?php esc_html_e( 'på vej', 'studie247' ); ?></em>
		</h1>
		<p style="color: var(--color-ink-soft); margin-bottom: var(--sp-6);">
			<?php esc_html_e( 'Snart kan du leje vores kameraer, lys, mikrofoner og grip direkte herfra. I mellemtiden — skriv til os, så finder vi en løsning.', 'studie247' ); ?>
		</p>
		<div style="display:flex;gap:var(--sp-3);flex-wrap:wrap;justify-content:center;">
			<?php
			studie247_button( __( 'Kontakt os', 'studie247' ), home_url( '/kontakt/' ), 'primary' );
			studie247_button( __( 'Tilbage til forsiden', 'studie247' ), home_url( '/' ), 'ghost' );
			?>
		</div>
	</div>
</section>

<?php get_footer();
