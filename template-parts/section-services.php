<?php
/**
 * Services section — editorial, numbered cards, dynamisk fra CPT "service".
 */

$services = studie247_get_services();
?>
<section id="services" class="section" aria-labelledby="services-title">
	<div class="wrap">
		<header class="section-head" data-reveal>
			<div class="section-head__meta">
				<span class="section-num">02</span>
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php echo esc_html( get_theme_mod( 's247_services_eyebrow', __( 'Services', 'studie247' ) ) ); ?></span>
			</div>
			<h2 id="services-title" class="section-head__title">
				<?php echo esc_html( get_theme_mod( 's247_services_title_a', __( 'Hvad vi', 'studie247' ) ) ); ?> <em><?php echo esc_html( get_theme_mod( 's247_services_title_b', __( 'laver for dig', 'studie247' ) ) ); ?></em>
			</h2>
			<p class="section-head__lead">
				<?php echo wp_kses_post( get_theme_mod( 's247_services_lead', __( 'Vi skræddersyer hver produktion — men alting starter samme sted: <em>vores studie og vores team.</em>', 'studie247' ) ) ); ?>
			</p>
		</header>

		<?php if ( empty( $services ) ) : ?>
			<div class="card" style="padding: var(--sp-10);">
				<h3><?php esc_html_e( 'Tilføj dine services', 'studie247' ); ?></h3>
				<p><?php esc_html_e( 'Gå til WP-admin → Services → Tilføj ny for at udfylde denne sektion.', 'studie247' ); ?></p>
			</div>
		<?php else : ?>
			<div class="services-grid">
				<?php
				$i = 0;
				foreach ( $services as $service ) :
					$i++;
					$icon      = get_post_meta( $service->ID, '_s247_icon', true );
					$tagline   = get_post_meta( $service->ID, '_s247_tagline', true );
					$startpris = get_post_meta( $service->ID, '_s247_startpris', true );
					$cta_text  = get_post_meta( $service->ID, '_s247_cta_text', true ) ?: __( 'Læs mere', 'studie247' );
					$bg_id     = (int) get_post_meta( $service->ID, '_s247_bg_image', true );
					$bg_url    = $bg_id ? wp_get_attachment_image_url( $bg_id, 's247-card' ) : '';
					$card_class = 'card service-card' . ( $bg_url ? ' service-card--has-bg' : '' );
					$card_style = sprintf( '--reveal-delay: %dms;', 80 * $i );
					if ( $bg_url ) {
						$card_style .= ' background-image: url(' . esc_url( $bg_url ) . ');';
					}
					?>
					<article class="<?php echo esc_attr( $card_class ); ?>" data-reveal style="<?php echo esc_attr( $card_style ); ?>">
						<div class="service-card__head">
							<?php if ( has_post_thumbnail( $service->ID ) ) : ?>
								<div class="card__icon" style="width:72px;height:72px;padding:0;overflow:hidden;background:transparent;">
									<?php echo get_the_post_thumbnail( $service->ID, array( 72, 72 ), array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
								</div>
							<?php elseif ( $icon ) : ?>
								<div class="card__icon"><?php echo studie247_icon( $icon, 32 ); ?></div>
							<?php endif; ?>
							<span class="card__number"><?php printf( '%02d', $i ); ?></span>
						</div>

						<div class="service-card__title-wrap">
							<h3 class="card__title"><?php echo esc_html( $service->post_title ); ?></h3>
							<?php if ( $tagline ) : ?>
								<p class="card__body"><?php echo esc_html( $tagline ); ?></p>
							<?php elseif ( $service->post_excerpt ) : ?>
								<p class="card__body"><?php echo esc_html( $service->post_excerpt ); ?></p>
							<?php endif; ?>
						</div>

						<div style="display:flex;justify-content:space-between;align-items:baseline;gap:var(--sp-4);">
							<?php if ( $startpris ) : ?>
								<span class="card__price"><?php echo esc_html( $startpris ); ?></span>
							<?php else : ?>
								<span></span>
							<?php endif; ?>
							<a class="card__cta" href="<?php echo esc_url( get_permalink( $service ) ); ?>">
								<span><?php echo esc_html( $cta_text ); ?></span>
								<?php echo studie247_icon( 'arrow-right', 16 ); ?>
							</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
