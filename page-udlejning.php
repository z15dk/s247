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
				<?php while ( $products->have_posts() ) : $products->the_post(); ?>
					<?php get_template_part( 'template-parts/product-card' ); ?>
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
