<?php
/**
 * Hero section.
 */
?>
<section class="hero" aria-labelledby="hero-title">
	<div class="hero__media" aria-hidden="true">
		<?php
		$hero_image = get_theme_mod( 's247_hero_image' );
		$hero_video = get_theme_mod( 's247_hero_video' );
		if ( $hero_video ) :
			?>
			<video autoplay muted loop playsinline poster="<?php echo esc_url( $hero_image ); ?>">
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php elseif ( $hero_image ) : ?>
			<img src="<?php echo esc_url( $hero_image ); ?>" alt="" loading="eager" fetchpriority="high">
		<?php else : ?>
			<div style="width:100%;height:100%;background:linear-gradient(135deg,#282828 0%,#3a2a26 100%);"></div>
		<?php endif; ?>
	</div>

	<div class="wrap wrap--wide">
		<div class="hero__inner">
			<span class="eyebrow hero__eyebrow"><?php esc_html_e( 'Studie 247 · Aarhus', 'studie247' ); ?></span>
			<h1 id="hero-title" class="hero__title">
				<?php esc_html_e( 'Optag. Skab.', 'studie247' ); ?><br>
				<em><?php esc_html_e( 'Udgiv 247.', 'studie247' ); ?></em>
			</h1>
			<p class="hero__lead">
				<?php esc_html_e( 'Vi hjælper dig med at producere indhold der virker — fra idé til færdigt resultat. Podcast, SoMe-videoer, online kursus eller fotoshoot: ét sted, ét team.', 'studie247' ); ?>
			</p>
			<div class="hero__actions">
				<?php
				studie247_button( __( 'Se services', 'studie247' ), '#services', 'primary', 'btn--lg' );
				studie247_button( __( 'Book nu', 'studie247' ), home_url( '/book/' ), 'secondary', 'btn--lg btn--on-dark' );
				?>
			</div>
		</div>
	</div>
</section>
