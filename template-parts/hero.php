<?php
/**
 * Hero — split layout: video venstre, massivt STUDIE 247 display højre.
 */
$hero_image = get_theme_mod( 's247_hero_image' );
$hero_video = get_theme_mod( 's247_hero_video' );
?>
<section class="hero hero--split" aria-labelledby="hero-title">
	<div class="hero__video" aria-hidden="true">
		<?php if ( $hero_video ) : ?>
			<video autoplay muted loop playsinline poster="<?php echo esc_url( $hero_image ); ?>">
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php elseif ( $hero_image ) : ?>
			<img src="<?php echo esc_url( $hero_image ); ?>" alt="" loading="eager" fetchpriority="high">
		<?php else : ?>
			<div class="hero__video-placeholder" aria-hidden="true">
				<?php echo studie247_icon( 'play', 64 ); ?>
				<span><?php esc_html_e( 'Upload video i Customizer → s247_hero_video', 'studie247' ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<div class="hero__stage">
		<div class="hero__top">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hero__brand" aria-label="<?php esc_attr_e( 'Studie 247 forside', 'studie247' ); ?>">
				<span class="logo__word">STUDIE</span>
				<span class="logo__num">247</span>
			</a>
			<button type="button" class="hero-menu-toggle" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php esc_attr_e( 'Åbn menu', 'studie247' ); ?>">
				<?php echo studie247_icon( 'menu', 32 ); ?>
			</button>
		</div>

		<h1 id="hero-title" class="hero__display">
			<span class="hero__display-line"><?php esc_html_e( 'STUDIE', 'studie247' ); ?></span>
			<span class="hero__display-line hero__display-line--italic"><em>247</em></span>
		</h1>

		<div class="hero__below">
			<p class="hero__tagline">
				<?php echo wp_kses_post( __( 'Optag. Skab. <em>Udgiv</em> — døgnet rundt.', 'studie247' ) ); ?>
			</p>
			<p class="hero__sub">
				<?php esc_html_e( 'Vi producerer podcasts, SoMe-videoer, online kurser og fotoshoots — fra idé til færdigt resultat.', 'studie247' ); ?>
			</p>
			<div class="hero__actions">
				<a class="btn btn--outline btn--lg" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">
					<?php esc_html_e( 'Kontakt', 'studie247' ); ?>
				</a>
				<a class="btn btn--ghost" href="#services">
					<?php esc_html_e( 'Se services', 'studie247' ); ?>
					<?php echo studie247_icon( 'arrow-right', 16 ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<div class="marquee" aria-hidden="true">
	<div class="marquee__track">
		<span class="marquee__item"><?php esc_html_e( 'Optag', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Skab', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Udgiv', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Podcast', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Video', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Foto', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Kursus', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Optag', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Skab', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Udgiv', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Podcast', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Video', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Foto', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Kursus', 'studie247' ); ?></span>
	</div>
</div>
