<?php
/**
 * Standard side (statiske WP-sider: Om, Privatliv, etc.).
 *
 * @package Studie247
 */

get_header();
?>

<section class="section">
	<div class="wrap wrap--tight">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<header class="section-head">
					<?php
					$parts = studie247_split_title( get_the_title() );
					if ( $parts[0] ) : ?>
						<h1 class="section-head__title">
							<?php echo esc_html( $parts[0] ); ?> <em><?php echo esc_html( $parts[1] ); ?></em>
						</h1>
					<?php else : ?>
						<h1 class="section-head__title"><em><?php echo esc_html( $parts[1] ); ?></em></h1>
					<?php endif; ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div style="aspect-ratio:16/9;border-radius:var(--radius-lg);overflow:hidden;margin-bottom:var(--sp-8);">
						<?php the_post_thumbnail( 's247-hero' ); ?>
					</div>
				<?php endif; ?>

				<div class="page-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</section>

<?php
// Hvis dette er Om-siden (slug eller titel), vis team-sektionen automatisk.
$is_om_page = is_page( 'om' ) || is_page( 'om-os' ) || ( is_page() && 0 === strcasecmp( trim( get_the_title() ), 'om' ) );
if ( $is_om_page ) {
	get_template_part( 'template-parts/section', 'team' );
}
?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php
get_footer();
