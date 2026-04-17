<?php
/**
 * Testimonials.
 */
$testimonials = studie247_get_testimonials( 3 );
if ( empty( $testimonials ) ) {
	return;
}
?>
<section class="section" aria-labelledby="testimonials-title">
	<div class="wrap">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Hvad siger', 'studie247' ); ?></span>
			<h2 id="testimonials-title" class="section-head__title">
				<?php esc_html_e( 'Vores', 'studie247' ); ?> <em><?php esc_html_e( 'kunder', 'studie247' ); ?></em>
			</h2>
		</header>

		<div class="testimonials">
			<?php foreach ( $testimonials as $t ) :
				$name    = get_post_meta( $t->ID, '_s247_author_name', true );
				$company = get_post_meta( $t->ID, '_s247_author_company', true );
				?>
				<figure class="testimonial">
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
