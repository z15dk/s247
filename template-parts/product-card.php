<?php
/**
 * Product-card til udlejning_item — bruges på /udlejning/ arkivet og på
 * Udlejning-side-templaten. Forventer at være inde i en post-loop.
 *
 * @package Studie247
 */

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
				<?php if ( $pris_dag || $pris_uge ) : ?>
					<div class="product-card__prices">
						<?php if ( $pris_dag ) : ?>
							<div class="product-card__price">
								<span class="product-card__price-num"><?php echo esc_html( $pris_dag ); ?></span>
								<span class="product-card__price-unit"><?php esc_html_e( '/ dag', 'studie247' ); ?></span>
							</div>
						<?php endif; ?>
						<?php if ( $pris_uge ) : ?>
							<div class="product-card__price product-card__price--sub">
								<span class="product-card__price-num"><?php echo esc_html( $pris_uge ); ?></span>
								<span class="product-card__price-unit"><?php esc_html_e( '/ uge', 'studie247' ); ?></span>
							</div>
						<?php endif; ?>
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
