<?php
/**
 * Services section — dynamisk fra CPT "service".
 * Listen skalerer automatisk når der tilføjes flere services i admin.
 */

$services = studie247_get_services();
?>
<section id="services" class="section" aria-labelledby="services-title">
	<div class="wrap">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Hvad vi tilbyder', 'studie247' ); ?></span>
			<h2 id="services-title" class="section-head__title">
				<?php esc_html_e( 'Vores', 'studie247' ); ?> <em><?php esc_html_e( 'services', 'studie247' ); ?></em>
			</h2>
			<p class="section-head__lead">
				<?php esc_html_e( 'Vi skræddersyer hver produktion til dig — men alting starter samme sted: vores studie og vores team.', 'studie247' ); ?>
			</p>
		</header>

		<?php if ( empty( $services ) ) : ?>
			<div class="card" style="padding: var(--sp-8);">
				<h3><?php esc_html_e( 'Tilføj dine services', 'studie247' ); ?></h3>
				<p><?php esc_html_e( 'Gå til WP-admin → Services → Tilføj ny for at udfylde denne sektion (SoMe-videoer, Podcast, Online kursus, Fotoshoot, osv.).', 'studie247' ); ?></p>
			</div>
		<?php else : ?>
			<div class="services-grid">
				<?php foreach ( $services as $service ) :
					$icon      = get_post_meta( $service->ID, '_s247_icon', true );
					$tagline   = get_post_meta( $service->ID, '_s247_tagline', true );
					$startpris = get_post_meta( $service->ID, '_s247_startpris', true );
					$cta_text  = get_post_meta( $service->ID, '_s247_cta_text', true ) ?: __( 'Læs mere', 'studie247' );
					?>
					<article class="card service-card">
						<?php if ( has_post_thumbnail( $service->ID ) ) : ?>
							<div class="card__media">
								<?php echo get_the_post_thumbnail( $service->ID, 's247-card', array( 'loading' => 'lazy' ) ); ?>
							</div>
						<?php elseif ( $icon ) : ?>
							<div class="card__icon"><?php echo studie247_icon( $icon, 28 ); ?></div>
						<?php endif; ?>

						<h3 class="card__title"><?php echo esc_html( $service->post_title ); ?></h3>

						<?php if ( $tagline ) : ?>
							<p class="card__body"><?php echo esc_html( $tagline ); ?></p>
						<?php elseif ( $service->post_excerpt ) : ?>
							<p class="card__body"><?php echo esc_html( $service->post_excerpt ); ?></p>
						<?php endif; ?>

						<?php if ( $startpris ) : ?>
							<span class="card__price"><?php echo esc_html( $startpris ); ?></span>
						<?php endif; ?>

						<a class="card__cta" href="<?php echo esc_url( get_permalink( $service ) ); ?>">
							<span><?php echo esc_html( $cta_text ); ?></span>
							<?php echo studie247_icon( 'arrow-right', 18 ); ?>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
