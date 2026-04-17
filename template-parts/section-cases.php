<?php
/**
 * Cases / showreel grid.
 */
$cases = studie247_get_cases( 6 );
if ( empty( $cases ) ) {
	return;
}
?>
<section class="section" aria-labelledby="cases-title" style="background: var(--color-surface);">
	<div class="wrap wrap--wide">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Udvalgte projekter', 'studie247' ); ?></span>
			<h2 id="cases-title" class="section-head__title">
				<?php esc_html_e( 'Kigge mere i', 'studie247' ); ?> <em><?php esc_html_e( 'værktøjskassen', 'studie247' ); ?></em>
			</h2>
		</header>

		<div class="cases">
			<?php foreach ( $cases as $case ) : ?>
				<a class="case-item" href="<?php echo esc_url( get_permalink( $case ) ); ?>">
					<?php echo get_the_post_thumbnail( $case->ID, 's247-card', array( 'loading' => 'lazy' ) ); ?>
					<div class="case-item__overlay">
						<h3 class="case-item__title"><?php echo esc_html( $case->post_title ); ?></h3>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
