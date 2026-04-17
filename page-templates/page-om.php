<?php
/**
 * Template Name: Om
 *
 * @package Studie247
 */

get_header();
?>

<section class="section">
	<div class="wrap wrap--tight">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Om Studie 247', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Simpelt.', 'studie247' ); ?> <em><?php esc_html_e( 'Professionelt.', 'studie247' ); ?></em> <?php esc_html_e( 'Menneskeligt.', 'studie247' ); ?>
			</h1>
			<p class="section-head__lead">
				<?php esc_html_e( 'Vi holder det enkelt — fordi klarhed skaber tillid. Vores udtryk er skarpt, roligt og varmt på samme tid. Målet er, at man med det samme føler sig i trygge hænder.', 'studie247' ); ?>
			</p>
		</header>

		<?php while ( have_posts() ) : the_post(); ?>
			<div class="page-content"><?php the_content(); ?></div>
		<?php endwhile; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
