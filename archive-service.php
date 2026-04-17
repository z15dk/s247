<?php
/**
 * Service archive — /services/.
 *
 * @package Studie247
 */

get_header();
?>

<section class="section">
	<div class="wrap">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Services', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Alt hvad vi', 'studie247' ); ?> <em><?php esc_html_e( 'tilbyder', 'studie247' ); ?></em>
			</h1>
			<p class="section-head__lead">
				<?php esc_html_e( 'Vælg den service der passer dit projekt — eller kontakt os, så sammensætter vi det rigtige for dig.', 'studie247' ); ?>
			</p>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="services-grid">
				<?php while ( have_posts() ) : the_post();
					$icon      = get_post_meta( get_the_ID(), '_s247_icon', true );
					$tagline   = get_post_meta( get_the_ID(), '_s247_tagline', true );
					$startpris = get_post_meta( get_the_ID(), '_s247_startpris', true );
					$cta_text  = get_post_meta( get_the_ID(), '_s247_cta_text', true ) ?: __( 'Læs mere', 'studie247' );
					?>
					<article <?php post_class( 'card service-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="card__media"><?php the_post_thumbnail( 's247-card', array( 'loading' => 'lazy' ) ); ?></div>
						<?php elseif ( $icon ) : ?>
							<div class="card__icon"><?php echo studie247_icon( $icon, 28 ); ?></div>
						<?php endif; ?>

						<h2 class="card__title">
							<a href="<?php the_permalink(); ?>" style="color:inherit;"><?php the_title(); ?></a>
						</h2>
						<?php if ( $tagline ) : ?>
							<p class="card__body"><?php echo esc_html( $tagline ); ?></p>
						<?php endif; ?>
						<?php if ( $startpris ) : ?>
							<span class="card__price"><?php echo esc_html( $startpris ); ?></span>
						<?php endif; ?>
						<a class="card__cta" href="<?php the_permalink(); ?>">
							<?php echo esc_html( $cta_text ); ?>
							<?php echo studie247_icon( 'arrow-right', 18 ); ?>
						</a>
					</article>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'Ingen services endnu — tilføj dine første i WP admin.', 'studie247' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
