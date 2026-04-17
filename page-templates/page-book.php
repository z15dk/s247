<?php
/**
 * Template Name: Book (placeholder — custom booking-system bygges som næste fase)
 *
 * @package Studie247
 */

get_header();
?>

<section class="section">
	<div class="wrap wrap--tight">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Book', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Book', 'studie247' ); ?> <em><?php esc_html_e( 'studiet', 'studie247' ); ?></em>
			</h1>
			<p class="section-head__lead">
				<?php esc_html_e( 'Vores booking-system kommer snart. Ring, skriv — eller udfyld formularen, så vender vi tilbage samme dag.', 'studie247' ); ?>
			</p>
		</header>

		<div class="card" style="padding: var(--sp-8);">
			<h2 class="card__title"><?php esc_html_e( 'I mellemtiden', 'studie247' ); ?></h2>
			<p><?php esc_html_e( 'Fortæl os hvad du vil lave, og hvornår — så finder vi den bedste løsning.', 'studie247' ); ?></p>
			<div style="display:flex;gap:var(--sp-3);flex-wrap:wrap;margin-top:var(--sp-4);">
				<?php
				studie247_button( __( 'Kontakt', 'studie247' ), home_url( '/kontakt/' ), 'primary' );
				studie247_button( __( 'Ring til os', 'studie247' ), 'tel:+4500000000', 'ghost' );
				?>
			</div>
		</div>
	</div>
</section>

<?php get_footer();
