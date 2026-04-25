<?php
/**
 * Enkelt blogindlæg.
 *
 * Layout: header + hero fylder hele wrap-bredden (1480px). Kun
 * artikelteksten splittes i 2 kolonner sammen med sidebaren.
 *
 * @package Studie247
 */

get_header();
the_post();

$post_id      = get_the_ID();
$blog_url     = get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' );
$reading_time = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
$categories   = get_the_category();

// Sidebar — seneste artikler.
$latest_count = max( 1, (int) get_theme_mod( 's247_blog_latest_count', 4 ) );
$latest_posts = get_posts( array(
	'post_type'           => 'post',
	'posts_per_page'      => $latest_count,
	'post__not_in'        => array( $post_id ),
	'ignore_sticky_posts' => true,
	'orderby'             => 'date',
	'order'               => 'DESC',
) );

// Book-CTA billede.
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

// Udstyr-rotator.
$rental_show     = (bool) get_theme_mod( 's247_blog_rental_show', 1 );
$rental_count    = max( 1, (int) get_theme_mod( 's247_blog_rental_count', 6 ) );
$rental_interval = max( 1500, (int) get_theme_mod( 's247_blog_rental_interval', 5000 ) );
$rental_items    = $rental_show ? get_posts( array(
	'post_type'           => 'udlejning_item',
	'posts_per_page'      => $rental_count,
	'meta_query'          => array(
		array(
			'key'     => '_s247_in_stock',
			'value'   => '1',
			'compare' => '=',
		),
	),
	'orderby'             => 'rand',
	'ignore_sticky_posts' => true,
) ) : array();

$book_eyebrow  = get_theme_mod( 's247_blog_book_eyebrow', __( 'Klar til at skabe?', 'studie247' ) );
$book_title_a  = get_theme_mod( 's247_blog_book_title_a', __( 'Book vores', 'studie247' ) );
$book_title_b  = get_theme_mod( 's247_blog_book_title_b', __( 'studie', 'studie247' ) );
$book_cta_text = get_theme_mod( 's247_blog_book_cta', __( 'Se ledige tider', 'studie247' ) );
$book_url      = get_theme_mod( 's247_blog_book_url', '/booking-studie/' );
$latest_a      = get_theme_mod( 's247_blog_latest_title_a', __( 'Seneste', 'studie247' ) );
$latest_b      = get_theme_mod( 's247_blog_latest_title_b', __( 'artikler', 'studie247' ) );
$rental_a      = get_theme_mod( 's247_blog_rental_title_a', __( 'Lej', 'studie247' ) );
$rental_b      = get_theme_mod( 's247_blog_rental_title_b', __( 'udstyr', 'studie247' ) );
$rental_cta    = get_theme_mod( 's247_blog_rental_cta', __( 'Lej dette', 'studie247' ) );
$show_related  = (bool) get_theme_mod( 's247_blog_show_related', 1 );
$related_a     = get_theme_mod( 's247_blog_related_title_a', __( 'Mere fra', 'studie247' ) );
$related_b     = get_theme_mod( 's247_blog_related_title_b', __( 'studiet', 'studie247' ) );
?>

<article <?php post_class( 'single-post' ); ?>>

	<section class="section section--tight single-post__head-section">
		<div class="wrap wrap--wide">
			<nav class="breadcrumb single-post__crumb" aria-label="<?php esc_attr_e( 'Brødkrumme', 'studie247' ); ?>">
				<ol>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Forside', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog', 'studie247' ); ?></a></li>
					<li><?php the_title(); ?></li>
				</ol>
			</nav>

			<header class="single-post__head">
				<?php if ( ! empty( $categories ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( $categories[0]->name ); ?></span>
				<?php endif; ?>
				<?php
				$parts = studie247_split_title( get_the_title() );
				if ( $parts[0] ) : ?>
					<h1 class="single-post__title">
						<?php echo esc_html( $parts[0] ); ?> <em><?php echo esc_html( $parts[1] ); ?></em>
					</h1>
				<?php else : ?>
					<h1 class="single-post__title"><em><?php echo esc_html( $parts[1] ); ?></em></h1>
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
				<figure class="single-post__hero single-post__hero--wide">
					<?php the_post_thumbnail( 's247-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				</figure>
			<?php endif; ?>

			<?php if ( has_excerpt() ) : ?>
				<p class="single-post__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section--tight single-post__body-section">
		<div class="wrap wrap--wide">
			<div class="single-layout">

				<div class="single-layout__main">
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
								<?php echo esc_html( $latest_a ); ?> <em><?php echo esc_html( $latest_b ); ?></em>
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

					<a class="blog-book-card" href="<?php echo esc_url( $book_url ); ?>">
						<div class="blog-book-card__media" aria-hidden="true">
							<?php if ( $book_img ) : ?>
								<img src="<?php echo esc_url( $book_img ); ?>" alt="" loading="lazy">
							<?php endif; ?>
							<div class="blog-book-card__overlay"></div>
						</div>
						<div class="blog-book-card__content">
							<span class="eyebrow eyebrow--on-dark"><?php echo esc_html( $book_eyebrow ); ?></span>
							<h3 class="blog-book-card__title">
								<?php echo esc_html( $book_title_a ); ?> <em><?php echo esc_html( $book_title_b ); ?></em>
							</h3>
							<span class="blog-book-card__cta">
								<?php echo esc_html( $book_cta_text ); ?>
								<?php echo studie247_icon( 'arrow-right', 18 ); ?>
							</span>
						</div>
					</a>

					<?php if ( ! empty( $rental_items ) ) : ?>
						<section class="blog-rental-rotator" data-rotator data-rotator-interval="<?php echo (int) $rental_interval; ?>">
							<header class="blog-rental-rotator__head">
								<h2 class="blog-rental-rotator__title">
									<?php echo esc_html( $rental_a ); ?> <em><?php echo esc_html( $rental_b ); ?></em>
								</h2>
							</header>
							<div class="blog-rental-rotator__stage">
								<?php foreach ( $rental_items as $idx => $item ) :
									$pris = get_post_meta( $item->ID, '_s247_pris_dag', true );
								?>
									<a class="blog-rental-slide<?php echo 0 === $idx ? ' is-active' : ''; ?>"
									   href="<?php echo esc_url( get_permalink( $item ) ); ?>"
									   data-rotator-slide
									   aria-hidden="<?php echo 0 === $idx ? 'false' : 'true'; ?>">
										<div class="blog-rental-slide__media">
											<?php if ( has_post_thumbnail( $item ) ) : ?>
												<?php echo get_the_post_thumbnail( $item, 's247-card', array( 'loading' => 'lazy' ) ); ?>
											<?php else : ?>
												<div class="blog-rental-slide__placeholder"></div>
											<?php endif; ?>
										</div>
										<div class="blog-rental-slide__body">
											<h3 class="blog-rental-slide__name"><?php echo esc_html( get_the_title( $item ) ); ?></h3>
											<?php if ( $pris ) : ?>
												<p class="blog-rental-slide__price">
													<span class="blog-rental-slide__price-num"><?php echo esc_html( $pris ); ?></span>
													<span class="blog-rental-slide__price-unit"><?php esc_html_e( '/ dag', 'studie247' ); ?></span>
												</p>
											<?php endif; ?>
											<span class="blog-rental-slide__cta">
												<?php echo esc_html( $rental_cta ); ?>
												<?php echo studie247_icon( 'arrow-right', 16 ); ?>
											</span>
										</div>
									</a>
								<?php endforeach; ?>
							</div>
							<?php if ( count( $rental_items ) > 1 ) : ?>
								<div class="blog-rental-rotator__dots" role="tablist">
									<?php foreach ( $rental_items as $idx => $_item ) : ?>
										<button type="button"
										        class="blog-rental-rotator__dot<?php echo 0 === $idx ? ' is-active' : ''; ?>"
										        data-rotator-dot="<?php echo (int) $idx; ?>"
										        aria-label="<?php echo esc_attr( sprintf( __( 'Vis udstyr %d', 'studie247' ), $idx + 1 ) ); ?>"></button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

				</aside><!-- /.single-layout__aside -->

			</div><!-- /.single-layout -->
		</div>
	</section>

	<?php
	if ( $show_related ) :
		// Relaterede indlæg — altid op til 3.
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
							<?php echo esc_html( $related_a ); ?> <em><?php echo esc_html( $related_b ); ?></em>
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
	<?php endif; // $show_related ?>

</article>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
