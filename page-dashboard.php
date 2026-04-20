<?php
/**
 * Template Name: Dashboard
 *
 * Bag log-in: viser stats for bookinger, udlejning og beskeder. Kun
 * brugere med edit_posts (Redaktør+) får adgang — alle andre ser WP-
 * login-formen.
 *
 * @package Studie247
 */

get_header();
?>

<section class="dash">
	<div class="wrap wrap--wide">

		<?php if ( ! is_user_logged_in() ) : ?>
			<div class="dash-gate">
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Privat område', 'studie247' ); ?></span>
				<h1 class="dash-gate__title"><?php esc_html_e( 'Log ind for at se dashboardet', 'studie247' ); ?></h1>
				<p class="dash-gate__lead"><?php esc_html_e( 'Dashboardet viser statistik for bookinger, udlejning og kontakt-beskeder. Kun medlemmer af teamet har adgang.', 'studie247' ); ?></p>
				<div class="dash-gate__form">
					<?php
					wp_login_form( array(
						'redirect'       => esc_url( home_url( $_SERVER['REQUEST_URI'] ?? '/dashboard/' ) ),
						'label_username' => __( 'Brugernavn eller e-mail', 'studie247' ),
						'label_password' => __( 'Adgangskode', 'studie247' ),
						'label_remember' => __( 'Husk mig', 'studie247' ),
						'label_log_in'   => __( 'Log ind', 'studie247' ),
					) );
					?>
				</div>
			</div>

		<?php elseif ( ! current_user_can( 'edit_posts' ) ) : ?>
			<div class="dash-gate">
				<h1 class="dash-gate__title"><?php esc_html_e( 'Manglende rettigheder', 'studie247' ); ?></h1>
				<p class="dash-gate__lead"><?php esc_html_e( 'Din bruger har ikke adgang til dashboardet. Kontakt en administrator hvis det er en fejl.', 'studie247' ); ?></p>
				<p><a class="btn btn--ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/dashboard/' ) ) ); ?>"><?php esc_html_e( 'Log ud', 'studie247' ); ?> <?php echo studie247_icon( 'arrow-right', 16 ); ?></a></p>
			</div>

		<?php else :
			/* ─── Beregn stats ─── */
			$current_user    = wp_get_current_user();
			$pending_count   = (int) wp_count_posts( 'booking' )->pending;
			$publish_count   = (int) wp_count_posts( 'booking' )->publish;
			$trash_count     = (int) wp_count_posts( 'booking' )->trash;
			$total_bookings  = $pending_count + $publish_count + $trash_count;

			$msg_count       = (int) wp_count_posts( 'kontakt_besked' )->publish;
			$item_count      = (int) wp_count_posts( 'udlejning_item' )->publish;

			// Omsætning denne måned + år (fra godkendte bookinger).
			$month_start = date( 'Y-m-01' );
			$year_start  = date( 'Y-01-01' );
			$approved = get_posts( array(
				'post_type'      => 'booking',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			) );
			$rev_month = 0;
			$rev_year  = 0;
			$prod_counts = array();
			foreach ( $approved as $b ) {
				$d     = get_post_meta( $b->ID, '_s247_date', true );
				$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
				$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
				if ( $d && $d >= $year_start ) { $rev_year += $price; }
				if ( $d && $d >= $month_start ) { $rev_month += $price; }
				if ( $pid ) {
					if ( ! isset( $prod_counts[ $pid ] ) ) { $prod_counts[ $pid ] = 0; }
					$prod_counts[ $pid ]++;
				}
			}
			arsort( $prod_counts );
			$top_products = array_slice( $prod_counts, 0, 5, true );

			// Seneste pending bookinger.
			$recent_pending = get_posts( array(
				'post_type'      => 'booking',
				'post_status'    => 'pending',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );
			// Seneste kontakt-beskeder.
			$recent_msgs = get_posts( array(
				'post_type'      => 'kontakt_besked',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );

			$fmt_dkk = function ( $n ) { return number_format( (int) $n, 0, ',', '.' ) . ' kr'; };
		?>

			<header class="dash-head">
				<div>
					<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Dashboard', 'studie247' ); ?></span>
					<h1 class="dash-head__title">
						<?php printf( esc_html__( 'Hej %s', 'studie247' ), esc_html( $current_user->display_name ) ); ?>
						<em><?php esc_html_e( 'her er status', 'studie247' ); ?></em>
					</h1>
				</div>
				<div class="dash-head__actions">
					<a class="btn btn--ghost" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'wp-admin', 'studie247' ); ?> <?php echo studie247_icon( 'arrow-right', 16 ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log ud', 'studie247' ); ?></a>
				</div>
			</header>

			<?php
			$can_rental   = studie247_can_view_dash( 'rental' );
			$can_studio   = studie247_can_view_dash( 'studio' );
			$can_messages = studie247_can_view_dash( 'messages' );
			$can_revenue  = studie247_can_view_dash( 'revenue' );
			$can_pending  = studie247_can_view_dash( 'pending' );
			$has_any      = $can_rental || $can_studio || $can_messages || $can_revenue || $can_pending;
			?>

			<?php if ( ! $has_any ) : ?>
				<div class="dash-gate" style="margin-top:var(--sp-8);">
					<p class="dash-gate__lead"><?php esc_html_e( 'Din bruger har adgang til dashboardet, men ingen sektioner er aktiveret endnu. En administrator kan give dig adgang under Brugere → din profil → Studie 247 — Dashboard-tilladelser.', 'studie247' ); ?></p>
				</div>
			<?php else : ?>

			<div class="dash-grid">
				<?php if ( $can_pending ) : ?>
					<a class="dash-card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=booking&post_status=pending' ) ); ?>">
						<span class="dash-card__label"><?php esc_html_e( 'Afventer godkendelse', 'studie247' ); ?></span>
						<span class="dash-card__num"><?php echo (int) $pending_count; ?></span>
						<span class="dash-card__hint"><?php esc_html_e( 'Gå til wp-admin →', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_studio ) : ?>
					<a class="dash-card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=booking' ) ); ?>">
						<span class="dash-card__label"><?php esc_html_e( 'Godkendte bookinger', 'studie247' ); ?></span>
						<span class="dash-card__num"><?php echo (int) $publish_count; ?></span>
						<span class="dash-card__hint"><?php esc_html_e( 'Total alle tider', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_revenue ) : ?>
					<div class="dash-card dash-card--revenue">
						<span class="dash-card__label"><?php esc_html_e( 'Omsætning denne måned', 'studie247' ); ?></span>
						<span class="dash-card__num dash-card__num--sm"><?php echo esc_html( $fmt_dkk( $rev_month ) ); ?></span>
						<span class="dash-card__hint"><?php printf( esc_html__( 'År-til-dato: %s', 'studie247' ), esc_html( $fmt_dkk( $rev_year ) ) ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( $can_messages ) : ?>
					<a class="dash-card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kontakt_besked' ) ); ?>">
						<span class="dash-card__label"><?php esc_html_e( 'Kontakt-beskeder', 'studie247' ); ?></span>
						<span class="dash-card__num"><?php echo (int) $msg_count; ?></span>
						<span class="dash-card__hint"><?php esc_html_e( 'Gå til beskeder →', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_rental ) : ?>
					<a class="dash-card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=udlejning_item' ) ); ?>">
						<span class="dash-card__label"><?php esc_html_e( 'Varer i udlejning', 'studie247' ); ?></span>
						<span class="dash-card__num"><?php echo (int) $item_count; ?></span>
						<span class="dash-card__hint"><?php esc_html_e( 'Admin →', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_studio || $can_pending ) : ?>
					<div class="dash-card dash-card--info">
						<span class="dash-card__label"><?php esc_html_e( 'Samlet antal forespørgsler', 'studie247' ); ?></span>
						<span class="dash-card__num"><?php echo (int) $total_bookings; ?></span>
						<span class="dash-card__hint"><?php printf( esc_html__( 'Afvist/aflyst: %d', 'studie247' ), (int) $trash_count ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="dash-lists">
				<?php if ( $can_pending ) : ?>
					<section class="dash-list">
						<h2 class="dash-list__title"><?php esc_html_e( 'Seneste afventende bookinger', 'studie247' ); ?></h2>
						<?php if ( empty( $recent_pending ) ) : ?>
							<p class="dash-list__empty"><?php esc_html_e( 'Ingen afventende — alt er up to date. ✓', 'studie247' ); ?></p>
						<?php else : ?>
							<ul class="dash-list__items">
								<?php foreach ( $recent_pending as $b ) :
									$name  = get_post_meta( $b->ID, '_s247_name', true );
									$date  = get_post_meta( $b->ID, '_s247_date', true );
									$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
									$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
									$type  = $pid ? __( 'Udstyr', 'studie247' ) : __( 'Studie', 'studie247' );
									// Respektér evt. begrænsninger pr. type
									if ( $pid && ! $can_rental && ! current_user_can( 'manage_options' ) ) { continue; }
									if ( ! $pid && ! $can_studio && ! current_user_can( 'manage_options' ) ) { continue; }
								?>
									<li>
										<a href="<?php echo esc_url( get_edit_post_link( $b->ID ) ); ?>">
											<span class="dash-list__badge<?php echo $pid ? ' dash-list__badge--rental' : ''; ?>"><?php echo esc_html( $type ); ?></span>
											<span class="dash-list__name"><?php echo esc_html( $name ?: '—' ); ?></span>
											<span class="dash-list__meta">
												<?php echo esc_html( $date ? date_i18n( 'j. M', strtotime( $date ) ) : '—' ); ?>
												<?php if ( $pid ) : ?> · <?php echo esc_html( get_the_title( $pid ) ); ?><?php endif; ?>
											</span>
											<?php if ( $can_revenue ) : ?>
												<span class="dash-list__price"><?php echo $price ? esc_html( $fmt_dkk( $price ) ) : '—'; ?></span>
											<?php endif; ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<?php if ( $can_messages ) : ?>
					<section class="dash-list">
						<h2 class="dash-list__title"><?php esc_html_e( 'Seneste beskeder', 'studie247' ); ?></h2>
						<?php if ( empty( $recent_msgs ) ) : ?>
							<p class="dash-list__empty"><?php esc_html_e( 'Ingen beskeder endnu.', 'studie247' ); ?></p>
						<?php else : ?>
							<ul class="dash-list__items">
								<?php foreach ( $recent_msgs as $m ) :
									$name  = get_post_meta( $m->ID, '_s247_name', true );
									$topic = get_post_meta( $m->ID, '_s247_topic', true );
								?>
									<li>
										<a href="<?php echo esc_url( get_edit_post_link( $m->ID ) ); ?>">
											<span class="dash-list__badge dash-list__badge--msg"><?php echo esc_html( $topic ?: __( 'Besked', 'studie247' ) ); ?></span>
											<span class="dash-list__name"><?php echo esc_html( $name ?: '—' ); ?></span>
											<span class="dash-list__meta"><?php echo esc_html( get_the_date( 'j. M H:i', $m ) ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<?php if ( $can_rental && ! empty( $top_products ) ) : ?>
					<section class="dash-list dash-list--wide">
						<h2 class="dash-list__title"><?php esc_html_e( 'Top 5 udlejede varer', 'studie247' ); ?></h2>
						<ul class="dash-list__items">
							<?php $rank = 0; foreach ( $top_products as $pid => $cnt ) : $rank++; ?>
								<li>
									<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>">
										<span class="dash-list__rank"><?php printf( '%02d', $rank ); ?></span>
										<span class="dash-list__name"><?php echo esc_html( get_the_title( $pid ) ); ?></span>
										<span class="dash-list__meta"><?php printf( esc_html( _n( '%d udlejning', '%d udlejninger', $cnt, 'studie247' ) ), (int) $cnt ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</div>

			<?php endif; // $has_any ?>

		<?php endif; ?>

	</div>
</section>

<?php get_footer();
