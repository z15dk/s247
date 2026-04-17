<?php
/**
 * Testimonials — editorial quotes.
 */
$testimonials = studie247_get_testimonials( 3 );
if ( empty( $testimonials ) ) {
	return;
}
?>
<section class="section" aria-labelledby="testimonials-title" style="background: var(--color-surface-deep);">
	<div class="wrap">
		<header class="section-head" data-reveal>
			<div class="section-head__meta">
				<span class="section-num">05</span>
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Kundeudsagn', 'studie247' ); ?></span>
			</div>
			<h2 id="testimonials-title" class="section-head__title">
				<?php esc_html_e( 'Hvad de', 'studie247' ); ?> <em><?php esc_html_e( 'siger', 'studie247' ); ?></em>
			</h2>
		</header>

		<div class="testimonials">
			<?php $i = 0; foreach ( $testimonials as $t ) :
				$i++;
				$name    = get_post_meta( $t->ID, '_s247_author_name', true );
				$company = get_post_meta( $t->ID, '_s247_author_company', true );
				?>
				<figure class="testimonial" data-reveal style="--reveal-delay: <?php echo esc_attr( 80 * $i ); ?>ms;">
					<blockquote class="testimonial__quote">
						<?php echo wp_kses_post( wpautop( $t->post_content ) ); ?>
					</blockquote>
					<figcaption class="testimonial__author">
						<?php if ( $name ) : ?>
							<span class="testimonial__name"><?php echo esc_html( $name ); ?></span>
						<?php endif; ?>
						<?php if ( $company ) : ?>
							<span class="testimonial__company"><?php echo esc_html( $company ); ?></span>
						<?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
