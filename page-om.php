<?php
/**
 * Om-side — bruges automatisk når side-slug er "om".
 *
 * @package Studie247
 */

get_header();

$intro = get_theme_mod( 's247_om_intro' );
?>

<section class="section">
	<div class="wrap wrap--tight">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Om Studie 247', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Simpelt.', 'studie247' ); ?> <em><?php esc_html_e( 'Professionelt.', 'studie247' ); ?></em> <?php esc_html_e( 'Menneskeligt.', 'studie247' ); ?>
			</h1>
			<?php if ( $intro ) : ?>
				<p class="section-head__lead"><?php echo wp_kses_post( $intro ); ?></p>
			<?php else : ?>
				<p class="section-head__lead">
					<?php esc_html_e( 'Vi holder det enkelt — fordi klarhed skaber tillid. Vores udtryk er skarpt, roligt og varmt på samme tid. Målet er, at man med det samme føler sig i trygge hænder.', 'studie247' ); ?>
				</p>
			<?php endif; ?>
		</header>

		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="page-content"><?php the_content(); ?></div>
			<?php endif; ?>
		<?php endwhile; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'team' ); ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
