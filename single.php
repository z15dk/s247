<?php
/**
 * Enkelt blogindlæg.
 *
 * Layout: 2-kolonner. Hovedindhold (~900px) + sidebar (~500px) med
 * de 4 seneste indlæg og et "Book vores studie"-kort.
 *
 * @package Studie247
 */

get_header();
the_post();

$post_id      = get_the_ID();
$blog_url     = get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' );
$reading_time = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
$categories   = get_the_category();

// Sidebar: 4 seneste indlæg, ekskl. den vi læser.
$latest_posts = get_posts( array(
	'post_type'           => 'post',
	'posts_per_page'      => 4,
	'post__not_in'        => array( $post_id ),
	'ignore_sticky_posts' => true,
	'orderby'             => 'date',
	'order'               => 'DESC',
) );

// Book-CTA billede: customizer override → booking-studie-side featured →
// page-studiet featured → fallback til ingen billede (gradient).
$book_img = get_theme_mod( 's247_blog_book_image', '' );
if ( ! $book_img ) {
	$booking_page = get_page_by_path( 'booking-studie' );
	if ( $booking_page && has_post_thumbnail( $booking_page->ID ) ) {
		$book_img = get_the_post_thumbnail_url( $booking_page->ID, 's247-card' );
	}
}
if ( ! $book_img ) {
	$studiet_page = get_page_by_path( 'studiet' );
	if ( $studiet_page && has_post_thumbnail( $studiet_page->ID ) ) {
		$book_img = get_the_post_thumbnail_url( $studiet_page->ID, 's247-card' );
	}
}
?>

<article <?php post_class( 'single-post' ); ?>>

	<section class="section section--tight">
		<div class="wrap wrap--wide">
			<div class="single-layout">

				<div class="single-layout__main">
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

						<p class="single-post__meta">
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
						<figure class="single-post__hero">
							<?php the_post_thumbnail( 's247-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
						</figure>
					<?php endif; ?>

					<?php if ( has_excerpt() ) : ?>
						<p class="single-post__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<div class="page-content">
						<?php the_content(); ?>
					</div>

					<?php
					$tag_list = get_the_tags();
					if ( ! empty( $tag_list ) ) : ?>
						<div class="single-post__tags">
							<?php foreach ( $tag_list as $tag ) : ?>
								<a class="single-post__tag" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>">
									#<?php echo esc_html( $tag->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<nav class="single-post__pager" aria-label="<?php esc_attr_e( 'Indlæg-navigation', 'studie247' ); ?>">
						<div>
							<?php
							$prev = get_previous_post();
							if ( $prev ) : ?>
								<a class="single-post__pager-link" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
									<span class="single-post__pager-label">← <?php esc_html_e( 'Forrige', 'studie247' ); ?></span>
									<span class="single-post__pager-title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
								</a>
							<?php endif; ?>
						</div>
						<div style="text-align:right;">
							<?php
							$next = get_next_post();
							if ( $next ) : ?>
								<a class="single-post__pager-link" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
									<span class="single-post__pager-label"><?php esc_html_e( 'Næste', 'studie247' ); ?> →</span>
									<span class="single-post__pager-title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
								</a>
							<?php endif; ?>
						</div>
					</nav>
				</div><!-- /.single-layout__main -->

				<aside class="single-layout__aside" aria-label="<?php esc_attr_e( 'Sidebar', 'studie247' ); ?>">

					<?php if ( ! empty( $latest_posts ) ) : ?>
						<section class="blog-sidebar-block">
							<h2 class="blog-sidebar-block__title">
								<?php esc_html_e( 'Seneste', 'studie247' ); ?> <em><?php esc_html_e( 'artikler', 'studie247' ); ?></em>
							</h2>
							<ul class="blog-sidebar-list">
								<?php foreach ( $latest_posts as $lp ) : ?>
									<li class="blog-sidebar-list__item">
										<a class="blog-sidebar-list__link" href="<?php echo esc_url( get_permalink( $lp ) ); ?>">
											<?php if ( has_post_thumbnail( $lp ) ) : ?>
												<span class="blog-sidebar-list__media">
													<?php echo get_the_post_thumbnail( $lp, 's247-card', array( 'loading' => 'lazy' ) ); ?>
												</span>
											<?php endif; ?>
											<span class="blog-sidebar-list__body">
												<span class="blog-sidebar-list__date"><?php echo esc_html( get_the_date( 'j. M Y', $lp ) ); ?></span>
												<span class="blog-sidebar-list__title"><?php echo esc_html( get_the_title( $lp ) ); ?></span>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<a class="blog-book-card" href="<?php echo esc_url( home_url( '/booking-studie/' ) ); ?>">
						<div class="blog-book-card__media" aria-hidden="true">
							<?php if ( $book_img ) : ?>
								<img src="<?php echo esc_url( $book_img ); ?>" alt="" loading="lazy">
							<?php endif; ?>
							<div class="blog-book-card__overlay"></div>
						</div>
						<div class="blog-book-card__content">
							<span class="eyebrow eyebrow--on-dark"><?php esc_html_e( 'Klar til at skabe?', 'studie247' ); ?></span>
							<h3 class="blog-book-card__title">
								<?php esc_html_e( 'Book vores', 'studie247' ); ?> <em><?php esc_html_e( 'studie', 'studie247' ); ?></em>
							</h3>
							<span class="blog-book-card__cta">
								<?php esc_html_e( 'Se ledige tider', 'studie247' ); ?>
								<?php echo studie247_icon( 'arrow-right', 18 ); ?>
							</span>
						</div>
					</a>

				</aside><!-- /.single-layout__aside -->

			</div><!-- /.single-layout -->
		</div>
	</section>

	<?php
	// Relaterede indlæg — altid op til 3: først samme kategori,
	// derefter top op med seneste indlæg hvis kategorien har for få.
	$cat_ids     = wp_list_pluck( $categories, 'term_id' );
	$exclude_ids = array_merge( array( $post_id ), wp_list_pluck( $latest_posts, 'ID' ) );
	$related_ids = array();

	if ( ! empty( $cat_ids ) ) {
		$related_ids = get_posts( array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => $exclude_ids,
			'ignore_sticky_posts' => true,
			'category__in'        => $cat_ids,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'fields'              => 'ids',
		) );
	}

	if ( count( $related_ids ) < 3 ) {
		$fillers = get_posts( array(
			'post_type'           => 'post',
			'posts_per_page'      => 3 - count( $related_ids ),
			'post__not_in'        => array_merge( $exclude_ids, $related_ids ),
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'fields'              => 'ids',
		) );
		$related_ids = array_merge( $related_ids, $fillers );
	}

	$related = $related_ids ? new WP_Query( array(
		'post_type'           => 'post',
		'post__in'            => $related_ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) ) : null;
	?>
	<?php if ( $related && $related->have_posts() ) : ?>
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
