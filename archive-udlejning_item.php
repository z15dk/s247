<?php
/**
 * Archive: udlejning_item — /udlejning/ og /udlejning-kategori/{slug}/.
 *
 * Moderne hero + stats-bar, visuelle kategori-tiles (på hovedarkivet),
 * kompakte filter-chips og polerede produkt-kort.
 *
 * @package Studie247
 */

get_header();

$active_cat = '';
$is_term_archive = is_tax( 'udlejning_kategori' );
if ( $is_term_archive ) {
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

// Statistik til hero-baren.
$total_items = (int) wp_count_posts( 'udlejning_item' )->publish;
$total_cats  = is_array( $categories ) ? count( $categories ) : 0;
?>

<section class="shop-hero">
	<div class="shop-hero__bg" aria-hidden="true"></div>
	<div class="wrap wrap--wide">
		<div class="shop-hero__inner">
			<span class="shop-hero__eyebrow">
				<span class="shop-hero__pulse" aria-hidden="true"></span>
				<?php esc_html_e( 'Udstyrs-udlejning', 'studie247' ); ?>
			</span>
			<h1 class="shop-hero__title">
				<?php if ( $is_term_archive ) : ?>
					<?php single_term_title(); ?>
				<?php else : ?>
					<?php esc_html_e( 'Lej det', 'studie247' ); ?> <em><?php esc_html_e( 'rigtige grej', 'studie247' ); ?></em>
				<?php endif; ?>
			</h1>
			<p class="shop-hero__lead">
				<?php esc_html_e( 'Kameraer, lys, mikrofoner og grip — klar fra dag til dag. Reservér online, hent i studiet eller få leveret.', 'studie247' ); ?>
			</p>

			<dl class="shop-hero__stats">
				<div>
					<dt><?php esc_html_e( 'Varer klar', 'studie247' ); ?></dt>
					<dd><?php echo (int) $total_items; ?><span>+</span></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Kategorier', 'studie247' ); ?></dt>
					<dd><?php echo (int) $total_cats; ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Booking', 'studie247' ); ?></dt>
					<dd class="shop-hero__stats-small">24/7 <span><?php esc_html_e( 'online', 'studie247' ); ?></span></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'Levering', 'studie247' ); ?></dt>
					<dd class="shop-hero__stats-small"><?php esc_html_e( 'Gratis', 'studie247' ); ?> <span><?php esc_html_e( 'over 1.500 kr', 'studie247' ); ?></span></dd>
				</div>
			</dl>
		</div>
	</div>
</section>

<?php
// Kategori-tiles: kun på hoved-arkivet (ikke på en enkelt kategori-side).
if ( ! $is_term_archive && ! empty( $categories ) && ! is_wp_error( $categories ) ) :
?>
<section class="shop-cats">
	<div class="wrap wrap--wide">
		<header class="shop-cats__head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Bladr', 'studie247' ); ?></span>
			<h2 class="shop-cats__title"><?php esc_html_e( 'Find din kategori', 'studie247' ); ?></h2>
		</header>
		<div class="shop-cats__grid">
			<?php foreach ( $categories as $cat ) :
				// Hent første produkt i kategorien til baggrunds-billede.
				$preview = get_posts( array(
					'post_type'      => 'udlejning_item',
					'posts_per_page' => 1,
					'tax_query'      => array( array( 'taxonomy' => 'udlejning_kategori', 'field' => 'term_id', 'terms' => $cat->term_id ) ),
					'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ),
				) );
				$bg = ! empty( $preview ) ? get_the_post_thumbnail_url( $preview[0], 's247-card' ) : '';
				?>
				<a class="shop-cat-tile" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"<?php if ( $bg ) : ?> style="background-image: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.85) 100%), url('<?php echo esc_url( $bg ); ?>');"<?php endif; ?>>
					<span class="shop-cat-tile__count"><?php echo (int) $cat->count; ?></span>
					<span class="shop-cat-tile__name"><?php echo esc_html( $cat->name ); ?></span>
					<span class="shop-cat-tile__cta"><?php esc_html_e( 'Se alle', 'studie247' ); ?> <?php echo studie247_icon( 'arrow-right', 14 ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="shop">
	<div class="wrap wrap--wide">
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
