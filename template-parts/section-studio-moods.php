<?php
/**
 * Studio moods — tab/fane panel below studio section.
 */
$title_a = get_theme_mod( 's247_moods_title_a', 'Tilpas studiet til dit' );
$title_b = get_theme_mod( 's247_moods_title_b', 'Brand' );

$mood_defaults = array(
	1 => array( 'label' => 'Lyst & let',              'icon' => 'sun' ),
	2 => array( 'label' => 'Afdæmpet & cinematisk',   'icon' => 'moon' ),
	3 => array( 'label' => 'Varmt & hyggeligt',       'icon' => 'home' ),
);

$moods = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$label = get_theme_mod( "s247_mood{$i}_label", $mood_defaults[ $i ]['label'] );
	$icon  = get_theme_mod( "s247_mood{$i}_icon",  $mood_defaults[ $i ]['icon'] );
	$image = get_theme_mod( "s247_mood{$i}_image" );
	if ( $label ) {
		$moods[] = array( 'label' => $label, 'icon' => $icon, 'image' => $image );
	}
}

if ( empty( $moods ) ) {
	return;
}
?>
<section class="section moods" aria-labelledby="moods-title" style="background: var(--color-bg-dark); color: var(--color-ink-on-dark);">
	<div class="wrap wrap--wide">
		<h2 id="moods-title" class="moods__title">
			<span class="moods__title-a"><?php echo esc_html( $title_a ); ?></span>
			<span class="moods__title-b"><?php echo esc_html( $title_b ); ?></span>
		</h2>

		<div class="moods__panel" data-moods>
			<div class="moods__tabs" role="tablist" aria-orientation="vertical">
				<?php foreach ( $moods as $idx => $mood ) : ?>
					<button
						type="button"
						role="tab"
						class="moods__tab<?php echo 0 === $idx ? ' is-active' : ''; ?>"
						aria-selected="<?php echo 0 === $idx ? 'true' : 'false'; ?>"
						aria-controls="mood-panel-<?php echo esc_attr( $idx ); ?>"
						id="mood-tab-<?php echo esc_attr( $idx ); ?>"
						data-mood-target="<?php echo esc_attr( $idx ); ?>"
					>
						<?php echo studie247_icon( $mood['icon'], 20 ); ?>
						<span><?php echo esc_html( $mood['label'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="moods__stage">
				<?php foreach ( $moods as $idx => $mood ) : ?>
					<div
						class="moods__image<?php echo 0 === $idx ? ' is-active' : ''; ?>"
						role="tabpanel"
						id="mood-panel-<?php echo esc_attr( $idx ); ?>"
						aria-labelledby="mood-tab-<?php echo esc_attr( $idx ); ?>"
						data-mood-panel="<?php echo esc_attr( $idx ); ?>"
						<?php echo 0 !== $idx ? 'hidden' : ''; ?>
					>
						<?php if ( $mood['image'] ) : ?>
							<img src="<?php echo esc_url( $mood['image'] ); ?>" alt="<?php echo esc_attr( $mood['label'] ); ?>" loading="lazy" decoding="async">
						<?php else : ?>
							<div class="moods__image-placeholder"><?php esc_html_e( 'Upload billede i Customizer', 'studie247' ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
