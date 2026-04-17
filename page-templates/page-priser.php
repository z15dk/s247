<?php
/**
 * Template Name: Priser
 *
 * @package Studie247
 */

get_header();
?>

<section class="section">
	<div class="wrap">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Gennemsigtige priser', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Priser,', 'studie247' ); ?> <em><?php esc_html_e( 'uden overraskelser', 'studie247' ); ?></em>
			</h1>
			<p class="section-head__lead">
				<?php esc_html_e( 'Vælg en pakke — eller lej studiet råt. Alt er inkluderet i prisen, medmindre andet er specificeret.', 'studie247' ); ?>
			</p>
		</header>

		<div class="grid grid--3">
			<?php
			$packages = array(
				array(
					'name'    => __( 'Lej studiet råt', 'studie247' ),
					'price'   => __( 'fra 1.500 kr / time', 'studie247' ),
					'body'    => __( 'Rå leje til dig med eget team og udstyr. Cyklorama, lys og lyd står klar.', 'studie247' ),
					'cta_url' => home_url( '/studiet/#raalej' ),
					'cta'     => __( 'Se detaljer', 'studie247' ),
					'accent'  => false,
				),
				array(
					'name'    => __( 'Service-pakke', 'studie247' ),
					'price'   => __( 'fra 4.995 kr', 'studie247' ),
					'body'    => __( 'Vi står for optagelsen — du behøver bare at møde op. Podcast, SoMe, kursus eller foto.', 'studie247' ),
					'cta_url' => home_url( '/services/' ),
					'cta'     => __( 'Se services', 'studie247' ),
					'accent'  => true,
				),
				array(
					'name'    => __( 'Skræddersyet', 'studie247' ),
					'price'   => __( 'på tilbud', 'studie247' ),
					'body'    => __( 'Større produktioner, længere forløb eller abonnement? Vi laver et tilbud til dig.', 'studie247' ),
					'cta_url' => home_url( '/kontakt/' ),
					'cta'     => __( 'Få et tilbud', 'studie247' ),
					'accent'  => false,
				),
			);
			foreach ( $packages as $pkg ) :
				$class = 'card' . ( $pkg['accent'] ? ' card--dark' : '' );
				?>
				<article class="<?php echo esc_attr( $class ); ?>" style="padding: var(--sp-8);">
					<h2 class="card__title"><?php echo esc_html( $pkg['name'] ); ?></h2>
					<span class="card__price"><?php echo esc_html( $pkg['price'] ); ?></span>
					<p class="card__body" style="<?php echo $pkg['accent'] ? 'color:rgba(244,233,221,0.75);' : ''; ?>"><?php echo esc_html( $pkg['body'] ); ?></p>
					<a class="btn <?php echo $pkg['accent'] ? 'btn--secondary btn--on-dark' : 'btn--primary'; ?>" href="<?php echo esc_url( $pkg['cta_url'] ); ?>">
						<?php echo esc_html( $pkg['cta'] ); ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php while ( have_posts() ) : the_post(); if ( get_the_content() ) : ?>
	<section class="section section--tight">
		<div class="wrap wrap--tight page-content"><?php the_content(); ?></div>
	</section>
<?php endif; endwhile; ?>

<?php get_template_part( 'template-parts/section', 'faq' ); ?>
<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
