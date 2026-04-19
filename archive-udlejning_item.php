<?php
/**
 * Archive: udlejning_item — /udlejning/ og /udlejning-kategori/{slug}/.
 *
 * Ren CPT-arkiv med shop-grid og kategori-filtre. Holder design i sync
 * med page-udlejning.php ved at genbruge template-parts/product-card.php.
 *
 * @package Studie247
 */

get_header();

$active_cat = '';
if ( is_tax( 'udlejning_kategori' ) ) {
	$term = get_queried_object();
	if ( $term && ! is_wp_error( $term ) ) {
		$active_cat = $term->slug;
	}
}

$base_url   = get_post_type_archive_link( 'udlejning_item' );
$categories = get_terms( array(
	'taxonomy'   => 'udlejning_kategori',
	'hide_empty' => true,
) );
?>

<section class="shop">
	<div class="wrap wrap--wide">
		<header class="shop__head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Udstyrs-udlejning', 'studie247' ); ?></span>
			<h1 class="shop__title">
				<?php if ( is_tax( 'udlejning_kategori' ) ) : ?>
					<?php single_term_title(); ?>
				<?php else : ?>
					<?php esc_html_e( 'Lej det', 'studie247' ); ?> <em><?php esc_html_e( 'rigtige grej', 'studie247' ); ?></em>
				<?php endif; ?>
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
						href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
					>
						<?php echo esc_html( $cat->name ); ?>
						<span class="shop__filter-count"><?php echo (int) $cat->count; ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="shop__grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'template-parts/product-card' ); ?>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<div class="shop__empty">
				<p><?php esc_html_e( 'Ingen udstyr matcher filteret endnu.', 'studie247' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
