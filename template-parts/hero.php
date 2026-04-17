<?php
/**
 * Hero section — editorial, cinematic.
 */
$hero_image = get_theme_mod( 's247_hero_image' );
$hero_video = get_theme_mod( 's247_hero_video' );
?>
<section class="hero" aria-labelledby="hero-title">
	<div class="hero__media" aria-hidden="true">
		<?php if ( $hero_video ) : ?>
			<video autoplay muted loop playsinline poster="<?php echo esc_url( $hero_image ); ?>">
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php elseif ( $hero_image ) : ?>
			<img src="<?php echo esc_url( $hero_image ); ?>" alt="" loading="eager" fetchpriority="high">
		<?php else : ?>
			<div style="width:100%;height:100%;background:radial-gradient(circle at 30% 20%, #3a2a26, #1e1e1e 70%);"></div>
		<?php endif; ?>
	</div>

	<div class="wrap wrap--wide">
		<div class="hero__inner">
			<div class="hero__main" data-reveal>
				<span class="eyebrow hero__eyebrow">
					<?php esc_html_e( 'Studie · Produktion · Post', 'studie247' ); ?>
				</span>

				<h1 id="hero-title" class="hero__title">
					<?php esc_html_e( 'Optag. Skab.', 'studie247' ); ?>
					<em><?php esc_html_e( 'Udgiv 247.', 'studie247' ); ?></em>
				</h1>

				<p class="hero__lead">
					<?php echo wp_kses_post( __( 'Vi producerer podcasts, SoMe-videoer, online kurser og fotoshoots — <em>fra idé til færdigt resultat.</em> Ét studie. Ét team. Intet besvær.', 'studie247' ) ); ?>
				</p>

				<div class="hero__actions">
					<?php
					studie247_button( __( 'Se services', 'studie247' ), '#services', 'primary', 'btn--lg' );
					?>
					<a class="btn btn--ghost btn--on-dark btn--lg" href="<?php echo esc_url( home_url( '/book/' ) ); ?>">
						<?php esc_html_e( 'Book studie', 'studie247' ); ?>
						<?php echo studie247_icon( 'arrow-right', 18 ); ?>
					</a>
				</div>
			</div>

			<aside class="hero__aside" data-reveal style="--reveal-delay: 200ms;">
				<div class="stat">
					<span class="stat__num">24/7</span>
					<span class="stat__label"><?php esc_html_e( 'Adgang', 'studie247' ); ?></span>
				</div>
				<div class="stat">
					<span class="stat__num">200<span style="font-size:0.5em; vertical-align:0.5em;">m²</span></span>
					<span class="stat__label"><?php esc_html_e( 'Studie', 'studie247' ); ?></span>
				</div>
				<div class="stat">
					<span class="stat__num">4</span>
					<span class="stat__label"><?php esc_html_e( 'Service-pakker', 'studie247' ); ?></span>
				</div>
			</aside>
		</div>
	</div>

	<span class="hero__scroll" aria-hidden="true">
		<?php esc_html_e( 'Scroll', 'studie247' ); ?>
	</span>
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
		<!-- duplicate for seamless loop -->
		<span class="marquee__item"><?php esc_html_e( 'Optag', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Skab', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Udgiv', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Podcast', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Video', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Foto', 'studie247' ); ?></span>
		<span class="marquee__item"><?php esc_html_e( 'Kursus', 'studie247' ); ?></span>
	</div>
</div>
