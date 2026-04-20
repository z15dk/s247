<?php
/**
 * Enkelt service (fx "Podcast", "SoMe-videoer", "Fotoshoot", +++).
 *
 * Dynamisk layout: hero-video/billede + stort statement + foto-mosaik
 * + content + inkluderet-liste + CTA.
 *
 * @package Studie247
 */

get_header();
the_post();

$tagline    = get_post_meta( get_the_ID(), '_s247_tagline', true );
$icon       = get_post_meta( get_the_ID(), '_s247_icon', true );
$startpris  = get_post_meta( get_the_ID(), '_s247_startpris', true );
$included   = get_post_meta( get_the_ID(), '_s247_included', true );
$hero_video = get_post_meta( get_the_ID(), '_s247_hero_video', true );
$statement  = get_post_meta( get_the_ID(), '_s247_statement', true );

$included_items = array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", $included ?: '' ) ) );

// Saml foto-mosaik (kun uploadede)
$gallery_ids = array();
for ( $g = 1; $g <= 5; $g++ ) {
	$gid = (int) get_post_meta( get_the_ID(), "_s247_gallery_{$g}", true );
	if ( $gid ) { $gallery_ids[] = $gid; }
}
?>

<section class="service-hero">
	<div class="service-hero__media" aria-hidden="true">
		<?php if ( $hero_video ) : ?>
			<video autoplay muted loop playsinline <?php echo has_post_thumbnail() ? 'poster="' . esc_url( get_the_post_thumbnail_url( null, 's247-hero' ) ) . '"' : ''; ?>>
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php elseif ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 's247-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
		<?php else : ?>
			<div class="service-hero__placeholder"></div>
		<?php endif; ?>
		<div class="service-hero__overlay"></div>
	</div>
	<div class="wrap wrap--wide">
		<div class="service-hero__inner">
			<nav class="breadcrumb service-hero__crumb" aria-label="<?php esc_attr_e( 'Brødkrumme', 'studie247' ); ?>">
				<ol>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Forside', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'studie247' ); ?></a></li>
					<li><?php the_title(); ?></li>
				</ol>
			</nav>
			<?php if ( $icon ) : ?>
				<div class="service-hero__icon"><?php echo studie247_icon( $icon, 32 ); ?></div>
			<?php endif; ?>
			<h1 class="service-hero__title"><?php the_title(); ?></h1>
			<?php if ( $tagline ) : ?>
				<p class="service-hero__lead"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
			<div class="service-hero__actions">
				<?php studie247_button( __( 'Book denne service', 'studie247' ), home_url( '/booking-studie/?service=' . get_post_field( 'post_name' ) ), 'primary', 'btn--lg' ); ?>
				<?php if ( $startpris ) : ?>
					<span class="service-hero__price"><?php echo esc_html( $startpris ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php if ( $statement ) :
	// Omdan *frase* → <em>frase</em> for accent-kursiv.
	$statement_html = preg_replace( '/\*([^*]+)\*/', '<em>$1</em>', esc_html( $statement ) );
?>
<section class="service-statement">
	<div class="wrap wrap--wide">
		<p class="service-statement__text"><?php echo wp_kses_post( $statement_html ); ?></p>
	</div>
</section>
<?php endif; ?>

<?php if ( count( $gallery_ids ) >= 3 ) : ?>
<section class="service-mosaic">
	<div class="wrap wrap--wide">
		<div class="service-mosaic__grid">
			<?php foreach ( $gallery_ids as $idx => $gid ) : ?>
				<figure class="service-mosaic__item service-mosaic__item--<?php echo (int) ( $idx + 1 ); ?>">
					<?php echo wp_get_attachment_image( $gid, 's247-hero', false, array( 'loading' => 'lazy', 'class' => 'service-mosaic__img' ) ); ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section service-body">
	<div class="wrap wrap--wide">
		<div class="service-body__grid">
			<div class="service-body__content page-content">
				<?php the_content(); ?>
			</div>

			<?php if ( ! empty( $included_items ) ) : ?>
				<aside class="service-included">
					<h2 class="service-included__title">
						<?php esc_html_e( 'Det er', 'studie247' ); ?>
						<em><?php esc_html_e( 'inkluderet', 'studie247' ); ?></em>
					</h2>
					<ul class="service-included__list">
						<?php foreach ( $included_items as $item ) : ?>
							<li>
								<span class="service-included__check"><?php echo studie247_icon( 'check', 18 ); ?></span>
								<span><?php echo esc_html( $item ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php if ( $startpris ) : ?>
						<div class="service-included__price">
							<span class="service-included__price-label"><?php esc_html_e( 'Fra', 'studie247' ); ?></span>
							<span class="service-included__price-value"><?php echo esc_html( $startpris ); ?></span>
						</div>
					<?php endif; ?>
					<a class="btn btn--primary btn--lg" href="<?php echo esc_url( home_url( '/booking-studie/?service=' . get_post_field( 'post_name' ) ) ); ?>">
						<?php esc_html_e( 'Book nu', 'studie247' ); ?>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</a>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
