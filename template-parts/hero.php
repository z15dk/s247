<?php
/**
 * Hero — split layout: video venstre, massivt STUDIE 247 display højre.
 */
$hero_image         = get_theme_mod( 's247_hero_image' );
$hero_video         = get_theme_mod( 's247_hero_video' );
$hero_display_image = get_theme_mod( 's247_hero_display_image' );
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
				<?php if ( has_custom_logo() ) :
					$logo_id  = get_theme_mod( 'custom_logo' );
					$logo_src = $logo_id ? wp_get_attachment_image_src( $logo_id, 'full' ) : false;
					if ( $logo_src ) : ?>
						<img src="<?php echo esc_url( $logo_src[0] ); ?>" alt="<?php esc_attr_e( 'Studie 247', 'studie247' ); ?>" class="hero__brand-img">
					<?php endif;
				else : ?>
					<span class="logo__word">STUDIE</span>
					<span class="logo__num">247</span>
				<?php endif; ?>
			</a>
			<button type="button" class="hero-menu-toggle" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php esc_attr_e( 'Åbn menu', 'studie247' ); ?>">
				<?php echo studie247_icon( 'menu', 32 ); ?>
			</button>
		</div>

		<?php if ( $hero_display_image ) : ?>
			<h1 id="hero-title" class="hero__display hero__display--image">
				<img src="<?php echo esc_url( $hero_display_image ); ?>" alt="<?php esc_attr_e( 'Studie 247', 'studie247' ); ?>" loading="eager" decoding="async">
			</h1>
		<?php else : ?>
			<h1 id="hero-title" class="hero__display">
				<span class="hero__display-line"><?php esc_html_e( 'STUDIE', 'studie247' ); ?></span>
				<span class="hero__display-line hero__display-line--italic"><em>247</em></span>
			</h1>
		<?php endif; ?>

		<div class="hero__below">
			<p class="hero__tagline">
				<?php echo wp_kses_post( __( 'Optag. Skab. <em>Udgiv</em> — døgnet rundt.', 'studie247' ) ); ?>
			</p>
			<p class="hero__sub">
				<?php esc_html_e( 'Vi producerer podcasts, SoMe-videoer, online kurser og fotoshoots — fra idé til færdigt resultat.', 'studie247' ); ?>
			</p>
			<div class="hero__actions">
				<a class="btn btn--ghost" href="#services">
					<?php esc_html_e( 'Se services', 'studie247' ); ?>
					<?php echo studie247_icon( 'arrow-right', 16 ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
$marquee_raw   = get_theme_mod( 's247_marquee_items', "Optag\nSkab\nUdgiv\nPodcast\nVideo\nFoto\nKursus" );
$marquee_lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $marquee_raw ) ) ) );
if ( ! empty( $marquee_lines ) ) :
	// Dupliker så animationen har nok indhold til at loope uden hul.
	$marquee_items = array_merge( $marquee_lines, $marquee_lines );
?>
<div class="marquee" aria-hidden="true">
	<div class="marquee__track">
		<?php foreach ( $marquee_items as $item ) :
			if ( preg_match( '#^https?://#i', $item ) ) : ?>
			<span class="marquee__item marquee__item--image">
				<img src="<?php echo esc_url( $item ); ?>" alt="" loading="lazy" decoding="async">
			</span>
		<?php else : ?>
			<span class="marquee__item"><?php echo esc_html( $item ); ?></span>
		<?php endif; endforeach; ?>
	</div>
</div>
<?php endif; ?>
