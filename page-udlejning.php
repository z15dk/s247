<?php
/**
 * Template Name: Udlejning (shop)
 *
 * @package Studie247
 */

get_header();

$active_cat = isset( $_GET['kat'] ) ? sanitize_title( wp_unslash( $_GET['kat'] ) ) : '';

$query_args = array(
	'post_type'      => 'udlejning_item',
	'posts_per_page' => -1,
	'orderby'        => 'menu_order title',
	'order'          => 'ASC',
);
if ( $active_cat ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'udlejning_kategori',
			'field'    => 'slug',
			'terms'    => $active_cat,
		),
	);
}

$products   = new WP_Query( $query_args );
$categories = get_terms( array(
	'taxonomy'   => 'udlejning_kategori',
	'hide_empty' => true,
) );
$base_url = get_permalink();
?>

<section class="shop">
	<div class="wrap wrap--wide">
		<header class="shop__head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Udstyrs-udlejning', 'studie247' ); ?></span>
			<h1 class="shop__title">
				<?php esc_html_e( 'Lej det', 'studie247' ); ?> <em><?php esc_html_e( 'rigtige grej', 'studie247' ); ?></em>
			</h1>
			<p class="shop__lead">
				<?php esc_html_e( 'Kameraer, lys, mikrofoner og grip — klar fra dag til dag. Reservér online, hent i studiet eller få leveret.', 'studie247' ); ?>
			</p>
		</header>

		<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<nav class="shop__filters" aria-label="<?php esc_attr_e( 'Filtrér efter kategori', 'studie247' ); ?>">
				<a class="shop__filter<?php echo '' === $active_cat ? ' is-active' : ''; ?>" href="<?php echo esc_url( $base_url ); ?>">
					<?php esc_html_e( 'Alt udstyr', 'studie247' ); ?>
				</a>
				<?php foreach ( $categories as $cat ) : ?>
					<a
						class="shop__filter<?php echo $active_cat === $cat->slug ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( add_query_arg( 'kat', $cat->slug, $base_url ) ); ?>"
					>
						<?php echo esc_html( $cat->name ); ?>
						<span class="shop__filter-count"><?php echo (int) $cat->count; ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( $products->have_posts() ) : ?>
			<div class="shop__grid">
				<?php while ( $products->have_posts() ) : $products->the_post();
					$pris_dag = get_post_meta( get_the_ID(), '_s247_pris_dag', true );
					$pris_uge = get_post_meta( get_the_ID(), '_s247_pris_uge', true );
					$in_stock = '1' === get_post_meta( get_the_ID(), '_s247_in_stock', true );
					$cats     = get_the_terms( get_the_ID(), 'udlejning_kategori' );
				?>
					<article class="product-card">
						<a class="product-card__link" href="<?php the_permalink(); ?>">
							<div class="product-card__media">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 's247-square', array( 'loading' => 'lazy', 'class' => 'product-card__img' ) ); ?>
								<?php else : ?>
									<div class="product-card__placeholder">
										<?php echo studie247_icon( 'camera', 48 ); ?>
									</div>
								<?php endif; ?>
								<?php if ( ! $in_stock ) : ?>
									<span class="product-card__status product-card__status--out"><?php esc_html_e( 'Udlejet', 'studie247' ); ?></span>
								<?php else : ?>
									<span class="product-card__status"><?php esc_html_e( 'På lager', 'studie247' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="product-card__body">
								<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
									<span class="product-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
								<?php endif; ?>
								<h3 class="product-card__title"><?php the_title(); ?></h3>
								<?php if ( get_the_excerpt() ) : ?>
									<p class="product-card__desc"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14 ) ); ?></p>
								<?php endif; ?>
								<div class="product-card__footer">
									<?php if ( $pris_dag ) : ?>
										<div class="product-card__price">
											<span class="product-card__price-num"><?php echo esc_html( $pris_dag ); ?></span>
											<span class="product-card__price-unit"><?php esc_html_e( '/ dag', 'studie247' ); ?></span>
										</div>
									<?php endif; ?>
									<span class="product-card__cta">
										<?php esc_html_e( 'Lej', 'studie247' ); ?>
										<?php echo studie247_icon( 'arrow-right', 14 ); ?>
									</span>
								</div>
							</div>
						</a>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<div class="shop__empty">
				<p><?php esc_html_e( 'Ingen udstyr matcher filteret endnu.', 'studie247' ); ?></p>
				<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
					<p style="color:var(--color-ink-mute);font-size:var(--fs-sm);margin-top:var(--sp-3);">
						<?php esc_html_e( 'Admin: tilføj udstyr under Udlejning → Tilføj udstyr. Husk et billede og en kategori.', 'studie247' ); ?>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
