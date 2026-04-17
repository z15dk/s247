<?php
/**
 * Cases / showreel — mixed-ratio editorial grid.
 */
$cases = studie247_get_cases( 6 );
if ( empty( $cases ) ) {
	return;
}
?>
<section class="section" aria-labelledby="cases-title">
	<div class="wrap wrap--wide">
		<header class="section-head" data-reveal>
			<div class="section-head__meta">
				<span class="section-num">04</span>
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Cases', 'studie247' ); ?></span>
			</div>
			<h2 id="cases-title" class="section-head__title">
				<?php esc_html_e( 'Udvalgt fra', 'studie247' ); ?> <em><?php esc_html_e( 'værktøjskassen', 'studie247' ); ?></em>
			</h2>
		</header>

		<div class="cases">
			<?php foreach ( $cases as $case ) : ?>
				<a class="case-item" href="<?php echo esc_url( get_permalink( $case ) ); ?>" data-reveal>
					<?php echo get_the_post_thumbnail( $case->ID, 's247-hero', array( 'loading' => 'lazy' ) ); ?>
					<div class="case-item__overlay">
						<h3 class="case-item__title"><?php echo esc_html( $case->post_title ); ?></h3>
						<span class="case-item__meta"><?php echo esc_html( get_the_date( 'Y', $case ) ); ?></span>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
