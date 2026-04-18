<?php
/**
 * Single udlejningsgenstand — produkt-detalje.
 *
 * @package Studie247
 */

get_header();

while ( have_posts() ) : the_post();
	$pris_dag = get_post_meta( get_the_ID(), '_s247_pris_dag', true );
	$pris_uge = get_post_meta( get_the_ID(), '_s247_pris_uge', true );
	$deposit  = get_post_meta( get_the_ID(), '_s247_deposit', true );
	$sku      = get_post_meta( get_the_ID(), '_s247_sku', true );
	$in_stock = '1' === get_post_meta( get_the_ID(), '_s247_in_stock', true );
	$cats     = get_the_terms( get_the_ID(), 'udlejning_kategori' );
?>
<section class="section">
	<div class="wrap wrap--wide">
		<nav aria-label="Brødkrumme" style="margin-bottom: var(--sp-6);">
			<a href="<?php echo esc_url( home_url( '/udlejning/' ) ); ?>" style="font-size: var(--fs-sm); color: var(--color-ink-mute); text-decoration: none;">
				← <?php esc_html_e( 'Alt udstyr', 'studie247' ); ?>
			</a>
		</nav>

		<div class="single-udlejning">
			<div class="single-udlejning__media">
				<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 's247-hero' ); ?>
				<?php else : ?>
					<div class="product-card__placeholder" style="position:relative;height:100%;"><?php echo studie247_icon( 'camera', 80 ); ?></div>
				<?php endif; ?>
			</div>

			<div class="single-udlejning__body">
				<?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
					<span class="product-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
				<?php endif; ?>

				<h1 class="section-head__title" style="margin: 0;"><?php the_title(); ?></h1>

				<?php if ( $in_stock ) : ?>
					<span class="product-card__status" style="position:static;align-self:flex-start;"><?php esc_html_e( 'På lager', 'studie247' ); ?></span>
				<?php else : ?>
					<span class="product-card__status product-card__status--out" style="position:static;align-self:flex-start;"><?php esc_html_e( 'Udlejet', 'studie247' ); ?></span>
				<?php endif; ?>

				<div style="font-size: var(--fs-base); color: var(--color-ink-soft); line-height: var(--lh-base);">
					<?php the_content(); ?>
				</div>

				<div class="single-udlejning__price-box">
					<?php if ( $pris_dag ) : ?>
						<div class="single-udlejning__price-row">
							<span class="single-udlejning__price-label"><?php esc_html_e( 'Pris pr. dag', 'studie247' ); ?></span>
							<span class="single-udlejning__price-value"><?php echo esc_html( $pris_dag ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( $pris_uge ) : ?>
						<div class="single-udlejning__price-row">
							<span class="single-udlejning__price-label"><?php esc_html_e( 'Pris pr. uge', 'studie247' ); ?></span>
							<span class="single-udlejning__price-value"><?php echo esc_html( $pris_uge ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( $deposit ) : ?>
						<div class="single-udlejning__price-row">
							<span class="single-udlejning__price-label"><?php esc_html_e( 'Depositum', 'studie247' ); ?></span>
							<span class="single-udlejning__price-value" style="color: var(--color-ink-soft); font-family: var(--font-sans); font-style: normal; font-weight: 700; font-size: 1rem;"><?php echo esc_html( $deposit ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( $sku ) : ?>
						<div class="single-udlejning__price-row">
							<span class="single-udlejning__price-label"><?php esc_html_e( 'Vare-nr.', 'studie247' ); ?></span>
							<span style="font-family: var(--font-mono); font-size: var(--fs-sm); color: var(--color-ink-mute);"><?php echo esc_html( $sku ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<div style="display:flex; gap: var(--sp-3); flex-wrap: wrap;">
					<?php if ( $in_stock ) : ?>
						<?php studie247_button( __( 'Book produkt', 'studie247' ), home_url( '/booking-studie/?produkt=' . get_post_field( 'post_name' ) ), 'primary', 'btn--lg' ); ?>
					<?php endif; ?>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">
						<?php esc_html_e( 'Spørg ind', 'studie247' ); ?>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endwhile; ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
