<?php
/**
 * Default template / fallback + blog index.
 *
 * @package Studie247
 */

get_header();
?>

<section class="section">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<header class="section-head">
				<h1 class="section-head__title">
					<?php esc_html_e( 'Fra', 'studie247' ); ?> <em><?php esc_html_e( 'studiet', 'studie247' ); ?></em>
				</h1>
			</header>

			<div class="grid grid--2">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="card__media">
								<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 's247-card', array( 'loading' => 'lazy' ) ); ?></a>
							</div>
						<?php endif; ?>
						<h2 class="card__title">
							<a href="<?php the_permalink(); ?>" style="color:inherit;"><?php the_title(); ?></a>
						</h2>
						<p class="card__body"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						<a class="card__cta" href="<?php the_permalink(); ?>">
							<?php esc_html_e( 'Læs mere', 'studie247' ); ?>
							<?php echo studie247_icon( 'arrow-right', 18 ); ?>
						</a>
					</article>
				<?php endwhile; ?>
			</div>

			<nav class="pagination" style="margin-top: var(--sp-12);">
				<?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
			</nav>
		<?php else : ?>
			<h1><?php esc_html_e( 'Intet fundet', 'studie247' ); ?></h1>
			<p><?php esc_html_e( 'Prøv en søgning eller vend tilbage til forsiden.', 'studie247' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
