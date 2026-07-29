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

// Saml pakke-priser — kun de udfyldte rækker.
$packages = array();
for ( $p = 1; $p <= 4; $p++ ) {
	$pname  = get_post_meta( get_the_ID(), "_s247_pkg_{$p}_name",  true );
	$pprice = get_post_meta( get_the_ID(), "_s247_pkg_{$p}_price", true );
	$pdesc  = get_post_meta( get_the_ID(), "_s247_pkg_{$p}_desc",  true );
	$pinc   = get_post_meta( get_the_ID(), "_s247_pkg_{$p}_included", true );
	if ( ! ( $pname || $pprice || $pdesc || $pinc ) ) {
		continue;
	}
	$packages[] = array(
		'slot'      => $p,
		'name'      => $pname,
		'price'     => $pprice,
		'price_sub' => get_post_meta( get_the_ID(), "_s247_pkg_{$p}_price_sub", true ),
		'desc'      => $pdesc,
		'included'  => array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", $pinc ?: '' ) ) ),
		'featured'  => '1' === get_post_meta( get_the_ID(), "_s247_pkg_{$p}_featured", true ),
		'cta'       => get_post_meta( get_the_ID(), "_s247_pkg_{$p}_cta", true ) ?: __( 'Vælg denne pakke', 'studie247' ),
		'cta_url'   => get_post_meta( get_the_ID(), "_s247_pkg_{$p}_cta_url", true )
			?: home_url( '/booking-studie/?service=' . get_post_field( 'post_name' ) . '&pakke=' . $p ),
	);
}

// Saml eksempler — kun de udfyldte rækker. Slot 1+2 vises som inline
// teaser i artiklen; slot 3-6 i grid'et nederst.
$examples = array();
for ( $e = 1; $e <= 6; $e++ ) {
	$ex_title = get_post_meta( get_the_ID(), "_s247_ex_{$e}_title", true );
	$ex_desc  = get_post_meta( get_the_ID(), "_s247_ex_{$e}_desc",  true );
	$ex_img   = (int) get_post_meta( get_the_ID(), "_s247_ex_{$e}_img", true );
	$ex_url   = get_post_meta( get_the_ID(), "_s247_ex_{$e}_url",   true );
	if ( ! ( $ex_title || $ex_desc || $ex_img || $ex_url ) ) {
		continue;
	}
	$examples[] = array(
		'slot'   => $e,
		'type'   => get_post_meta( get_the_ID(), "_s247_ex_{$e}_type",   true ),
		'title'  => $ex_title,
		'desc'   => $ex_desc,
		'client' => get_post_meta( get_the_ID(), "_s247_ex_{$e}_client", true ),
		'url'    => $ex_url,
		'cta'    => get_post_meta( get_the_ID(), "_s247_ex_{$e}_cta",    true ),
		'img_id' => $ex_img,
	);
}
$inline_examples = array_filter( $examples, fn( $ex ) => in_array( $ex['slot'], array( 1, 2 ), true ) );
$grid_examples   = array_filter( $examples, fn( $ex ) => ! in_array( $ex['slot'], array( 1, 2 ), true ) );

/**
 * Render et eksempel-kort. $variant = 'card' (stort grid) eller 'teaser' (inline).
 */
function studie247_render_service_example( $ex, $variant = 'card' ) {
	$type_labels = array(
		'video' => __( 'Video', 'studie247' ),
		'audio' => __( 'Lyd', 'studie247' ),
		'image' => __( 'Foto', 'studie247' ),
		'case'  => __( 'Case', 'studie247' ),
	);
	$type_label = $type_labels[ $ex['type'] ] ?? '';
	$img_url    = $ex['img_id'] ? wp_get_attachment_image_url( $ex['img_id'], 's247-card' ) : '';
	$has_link   = ! empty( $ex['url'] );
	$tag        = $has_link ? 'a' : 'article';
	$attrs      = $has_link
		? sprintf( ' href="%s" target="_blank" rel="noopener"', esc_url( $ex['url'] ) )
		: '';
	$base_cls   = 'teaser' === $variant ? 'service-example service-example--teaser' : 'service-example';
	$type_cls   = ' service-example--' . esc_attr( $ex['type'] ?: 'case' );
	?>
	<<?php echo $tag; ?> class="<?php echo esc_attr( $base_cls . $type_cls ); ?>"<?php echo $attrs; // phpcs:ignore ?>>
		<div class="service-example__media">
			<?php if ( $img_url ) : ?>
				<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $ex['title'] ); ?>" loading="lazy">
			<?php else : ?>
				<div class="service-example__media-fallback"></div>
			<?php endif; ?>
			<?php if ( in_array( $ex['type'], array( 'video', 'audio' ), true ) ) : ?>
				<span class="service-example__play" aria-hidden="true">
					<?php echo studie247_icon( 'play', 'teaser' === $variant ? 22 : 28 ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $type_label ) : ?>
				<span class="service-example__tag"><?php echo esc_html( $type_label ); ?></span>
			<?php endif; ?>
		</div>
		<div class="service-example__body">
			<?php if ( $ex['client'] ) : ?>
				<span class="service-example__client"><?php echo esc_html( $ex['client'] ); ?></span>
			<?php endif; ?>
			<?php if ( $ex['title'] ) : ?>
				<h3 class="service-example__title"><?php echo esc_html( $ex['title'] ); ?></h3>
			<?php endif; ?>
			<?php if ( $ex['desc'] && 'teaser' !== $variant ) : ?>
				<p class="service-example__desc"><?php echo esc_html( $ex['desc'] ); ?></p>
			<?php endif; ?>
			<?php if ( $has_link ) :
				$cta = $ex['cta'] ?: __( 'Se eksempel', 'studie247' );
			?>
				<span class="service-example__cta">
					<?php echo esc_html( $cta ); ?>
					<?php echo studie247_icon( 'arrow-right', 16 ); ?>
				</span>
			<?php endif; ?>
		</div>
	</<?php echo $tag; ?>>
	<?php
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
				<?php
				ob_start();
				the_content();
				$_content = ob_get_clean();

				if ( ! empty( $inline_examples ) ) {
					ob_start();
					echo '<aside class="service-example-teaser" aria-label="' . esc_attr__( 'Eksempler fra studiet', 'studie247' ) . '">';
					echo '<span class="service-example-teaser__eyebrow">' . esc_html__( 'Eksempler fra studiet', 'studie247' ) . '</span>';
					echo '<div class="service-example-teaser__grid">';
					foreach ( $inline_examples as $ex ) {
						studie247_render_service_example( $ex, 'teaser' );
					}
					echo '</div></aside>';
					$teaser_html = ob_get_clean();

					// Indsæt efter 3. afsnit (eller efter indholdet hvis artiklen er kort).
					$parts = explode( '</p>', $_content );
					$total = count( $parts );
					if ( $total >= 4 ) {
						$parts[2] .= '</p>' . $teaser_html;
						$_content = implode( '</p>', $parts );
						$_content = preg_replace( '#</p>$#', '', $_content );
					} else {
						$_content .= $teaser_html;
					}
				}
				echo $_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
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

<?php if ( ! empty( $packages ) ) : ?>
<section class="section service-packages">
	<div class="wrap wrap--wide">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Pakker', 'studie247' ); ?></span>
			<h2 class="section-head__title">
				<?php esc_html_e( 'Vælg den', 'studie247' ); ?> <em><?php esc_html_e( 'der passer dig', 'studie247' ); ?></em>
			</h2>
		</header>
		<div class="service-packages__grid service-packages__grid--<?php echo (int) count( $packages ); ?>">
			<?php foreach ( $packages as $pkg ) :
				$cls = 'service-package' . ( $pkg['featured'] ? ' service-package--featured' : '' );
			?>
				<article class="<?php echo esc_attr( $cls ); ?>">
					<?php if ( $pkg['featured'] ) : ?>
						<span class="service-package__badge"><?php esc_html_e( 'Mest valgte', 'studie247' ); ?></span>
					<?php endif; ?>
					<?php if ( $pkg['name'] ) : ?>
						<h3 class="service-package__name"><?php echo esc_html( $pkg['name'] ); ?></h3>
					<?php endif; ?>
					<?php if ( $pkg['desc'] ) : ?>
						<p class="service-package__desc"><?php echo esc_html( $pkg['desc'] ); ?></p>
					<?php endif; ?>
					<?php if ( $pkg['price'] ) : ?>
						<div class="service-package__price-block">
							<span class="service-package__price"><?php echo esc_html( $pkg['price'] ); ?></span>
							<?php if ( $pkg['price_sub'] ) : ?>
								<span class="service-package__price-sub"><?php echo esc_html( $pkg['price_sub'] ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $pkg['included'] ) ) : ?>
						<ul class="service-package__list">
							<?php foreach ( $pkg['included'] as $line ) : ?>
								<li>
									<span class="service-package__check"><?php echo studie247_icon( 'check', 16 ); ?></span>
									<span><?php echo esc_html( $line ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<a class="btn <?php echo $pkg['featured'] ? 'btn--primary' : 'btn--ghost'; ?> service-package__cta" href="<?php echo esc_url( $pkg['cta_url'] ); ?>">
						<?php echo esc_html( $pkg['cta'] ); ?>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $grid_examples ) ) : ?>
<section class="section service-examples">
	<div class="wrap wrap--wide">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Flere cases', 'studie247' ); ?></span>
			<h2 class="section-head__title">
				<?php esc_html_e( 'Mere fra', 'studie247' ); ?> <em><?php esc_html_e( 'studiet', 'studie247' ); ?></em>
			</h2>
		</header>
		<div class="service-examples__grid">
			<?php foreach ( $grid_examples as $ex ) {
				studie247_render_service_example( $ex, 'card' );
			} ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
