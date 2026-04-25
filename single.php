<?php
/**
 * Enkelt blogindlæg.
 *
 * @package Studie247
 */

get_header();
the_post();

$post_id      = get_the_ID();
$blog_url     = get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' );
$reading_time = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
$categories   = get_the_category();
?>

<article <?php post_class( 'single-post' ); ?>>

	<section class="section section--tight">
		<div class="wrap wrap--tight">
			<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Brødkrumme', 'studie247' ); ?>" style="margin-bottom:var(--sp-6);">
				<ol>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Forside', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog', 'studie247' ); ?></a></li>
					<li><?php the_title(); ?></li>
				</ol>
			</nav>

			<header class="section-head" style="text-align:left;">
				<?php if ( ! empty( $categories ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( $categories[0]->name ); ?></span>
				<?php endif; ?>
				<?php
				$parts = studie247_split_title( get_the_title() );
				if ( $parts[0] ) : ?>
					<h1 class="section-head__title">
						<?php echo esc_html( $parts[0] ); ?> <em><?php echo esc_html( $parts[1] ); ?></em>
					</h1>
				<?php else : ?>
					<h1 class="section-head__title"><em><?php echo esc_html( $parts[1] ); ?></em></h1>
				<?php endif; ?>

				<p class="single-post__meta" style="display:flex;flex-wrap:wrap;gap:var(--sp-4);align-items:center;color:var(--color-muted, #5a5a5a);font-size:var(--fs-sm);margin-top:var(--sp-4);">
					<span><?php echo esc_html( get_the_date( 'j. F Y' ) ); ?></span>
					<span aria-hidden="true">•</span>
					<span><?php echo esc_html( get_the_author() ); ?></span>
					<span aria-hidden="true">•</span>
					<span>
						<?php
						printf(
							/* translators: %d: minutter */
							esc_html( _n( '%d min læsetid', '%d min læsetid', $reading_time, 'studie247' ) ),
							(int) $reading_time
						);
						?>
					</span>
				</p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure style="aspect-ratio:16/9;border-radius:var(--radius-lg);overflow:hidden;margin:var(--sp-8) 0;">
					<?php the_post_thumbnail( 's247-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
				</figure>
			<?php endif; ?>

			<?php if ( has_excerpt() ) : ?>
				<p class="single-post__lead" style="font-size:var(--fs-lg);line-height:var(--lh-base);color:var(--color-muted, #404040);font-family:var(--font-serif);font-style:italic;margin:var(--sp-6) 0 var(--sp-8);">
					<?php echo esc_html( get_the_excerpt() ); ?>
				</p>
			<?php endif; ?>

			<div class="page-content">
				<?php the_content(); ?>
			</div>

			<?php
			$tag_list = get_the_tags();
			if ( ! empty( $tag_list ) ) : ?>
				<div class="single-post__tags" style="display:flex;flex-wrap:wrap;gap:var(--sp-2);margin-top:var(--sp-10);padding-top:var(--sp-6);border-top:1px solid rgba(40,40,40,0.1);">
					<?php foreach ( $tag_list as $tag ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" style="font-size:var(--fs-sm);padding:6px 12px;border-radius:999px;background:var(--s247-bone, #F4E9DD);color:var(--color-text, #282828);text-decoration:none;">
							#<?php echo esc_html( $tag->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<nav class="single-post__pager" aria-label="<?php esc_attr_e( 'Indlæg-navigation', 'studie247' ); ?>" style="display:flex;justify-content:space-between;gap:var(--sp-4);margin-top:var(--sp-10);padding-top:var(--sp-6);border-top:1px solid rgba(40,40,40,0.1);">
				<div>
					<?php
					$prev = get_previous_post();
					if ( $prev ) : ?>
						<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" style="display:inline-flex;flex-direction:column;gap:4px;text-decoration:none;color:inherit;">
							<span style="font-size:var(--fs-xs);color:var(--color-muted, #5a5a5a);text-transform:uppercase;letter-spacing:0.1em;">← <?php esc_html_e( 'Forrige', 'studie247' ); ?></span>
							<span style="font-weight:600;"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<div style="text-align:right;">
					<?php
					$next = get_next_post();
					if ( $next ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" style="display:inline-flex;flex-direction:column;gap:4px;text-decoration:none;color:inherit;">
							<span style="font-size:var(--fs-xs);color:var(--color-muted, #5a5a5a);text-transform:uppercase;letter-spacing:0.1em;"><?php esc_html_e( 'Næste', 'studie247' ); ?> →</span>
							<span style="font-weight:600;"><?php echo esc_html( get_the_title( $next ) ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</nav>
		</div>
	</section>

	<?php
	// Relaterede indlæg — samme kategori, eller seneste hvis ingen.
	$cat_ids = wp_list_pluck( $categories, 'term_id' );
	$related = new WP_Query( array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'category__in'        => ! empty( $cat_ids ) ? $cat_ids : array(),
		'orderby'             => 'date',
		'order'               => 'DESC',
	) );
	if ( ! $related->have_posts() ) {
		wp_reset_postdata();
		$related = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		) );
	}
	?>
	<?php if ( $related->have_posts() ) : ?>
		<section class="section">
			<div class="wrap">
				<header class="section-head">
					<span class="eyebrow"><?php esc_html_e( 'Læs også', 'studie247' ); ?></span>
					<h2 class="section-head__title">
						<?php esc_html_e( 'Mere fra', 'studie247' ); ?> <em><?php esc_html_e( 'studiet', 'studie247' ); ?></em>
					</h2>
				</header>
				<div class="grid grid--3">
					<?php while ( $related->have_posts() ) : $related->the_post(); ?>
						<article <?php post_class( 'card' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="card__media">
									<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 's247-card', array( 'loading' => 'lazy' ) ); ?></a>
								</div>
							<?php endif; ?>
							<h3 class="card__title">
								<a href="<?php the_permalink(); ?>" style="color:inherit;"><?php the_title(); ?></a>
							</h3>
							<p class="card__body"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
							<a class="card__cta" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Læs mere', 'studie247' ); ?>
								<?php echo studie247_icon( 'arrow-right', 18 ); ?>
							</a>
						</article>
					<?php endwhile; ?>
				</div>
			</div>
		</section>
	<?php endif; wp_reset_postdata(); ?>

</article>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
