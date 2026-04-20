<?php
/**
 * Dashboard-overview — stats-grid + lister (den oprindelige forside).
 * Forventer følgende variable fra caller:
 *   $pending_count, $publish_count, $trash_count, $total_bookings,
 *   $msg_count, $item_count, $rev_month, $rev_year, $top_products,
 *   $recent_pending, $recent_msgs, $fmt_dkk,
 *   $can_rental, $can_studio, $can_messages, $can_revenue, $can_pending,
 *   $has_any
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! isset( $has_any ) ) { return; }
?>

<?php if ( ! $has_any ) : ?>
	<div class="sd-empty">
		<p><?php esc_html_e( 'Din bruger har ingen sektioner aktiveret endnu. En admin kan give dig adgang under Brugere → din profil.', 'studie247' ); ?></p>
	</div>
<?php else : ?>

	<section class="sd-stats">
		<?php if ( $can_pending ) : ?>
			<a class="sd-stat <?php echo $pending_count ? 'sd-stat--alert' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings&status=pending' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Afventer', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $pending_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'forespørgsler', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_studio ) : ?>
			<a class="sd-stat" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings&status=publish' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Godkendte', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $publish_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'total alle tider', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_revenue ) : ?>
			<div class="sd-stat sd-stat--accent">
				<span class="sd-stat__label"><?php esc_html_e( 'Omsætning', 'studie247' ); ?></span>
				<span class="sd-stat__num sd-stat__num--money"><?php echo esc_html( $fmt_dkk( $rev_month ) ); ?></span>
				<span class="sd-stat__delta"><?php printf( esc_html__( 'YTD: %s', 'studie247' ), esc_html( $fmt_dkk( $rev_year ) ) ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( $can_messages ) : ?>
			<a class="sd-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kontakt_besked' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Beskeder', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $msg_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'modtaget', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_rental ) : ?>
			<a class="sd-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=udlejning_item' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Varer', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $item_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'i udlejning', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
	</section>

	<section class="sd-grid">
		<?php if ( $can_pending ) : ?>
			<div class="sd-panel">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Afventende forespørgsler', 'studie247' ); ?></h2>
					<a class="sd-panel__more" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings&status=pending' ) ); ?>"><?php esc_html_e( 'Se alle', 'studie247' ); ?> →</a>
				</header>
				<?php if ( empty( $recent_pending ) ) : ?>
					<p class="sd-panel__empty">✓ <?php esc_html_e( 'Intet at godkende lige nu.', 'studie247' ); ?></p>
				<?php else : ?>
					<table class="sd-table">
						<thead>
							<tr>
								<th>Type</th>
								<th><?php esc_html_e( 'Kunde', 'studie247' ); ?></th>
								<th><?php esc_html_e( 'Dato', 'studie247' ); ?></th>
								<?php if ( $can_revenue ) : ?><th class="sd-table__right"><?php esc_html_e( 'Pris', 'studie247' ); ?></th><?php endif; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent_pending as $b ) :
								$name  = get_post_meta( $b->ID, '_s247_name', true );
								$date  = get_post_meta( $b->ID, '_s247_date', true );
								$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
								$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
								if ( $pid && ! $can_rental && ! current_user_can( 'manage_options' ) ) { continue; }
								if ( ! $pid && ! $can_studio && ! current_user_can( 'manage_options' ) ) { continue; }
							?>
								<tr onclick="window.location='<?php echo esc_url( home_url( '/dashboard/?view=bookings&booking=' . $b->ID ) ); ?>'">
									<td><span class="sd-badge <?php echo $pid ? 'sd-badge--accent' : 'sd-badge--neutral'; ?>"><?php echo $pid ? esc_html__( 'Udstyr', 'studie247' ) : esc_html__( 'Studie', 'studie247' ); ?></span></td>
									<td><?php echo esc_html( $name ?: '—' ); ?></td>
									<td class="sd-muted"><?php echo esc_html( $date ? date_i18n( 'j. M', strtotime( $date ) ) : '—' ); ?></td>
									<?php if ( $can_revenue ) : ?><td class="sd-table__right sd-mono"><?php echo $price ? esc_html( $fmt_dkk( $price ) ) : '—'; ?></td><?php endif; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $can_messages ) : ?>
			<div class="sd-panel">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Seneste beskeder', 'studie247' ); ?></h2>
					<a class="sd-panel__more" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kontakt_besked' ) ); ?>"><?php esc_html_e( 'Alle', 'studie247' ); ?> →</a>
				</header>
				<?php if ( empty( $recent_msgs ) ) : ?>
					<p class="sd-panel__empty"><?php esc_html_e( 'Ingen endnu.', 'studie247' ); ?></p>
				<?php else : ?>
					<ul class="sd-list">
						<?php foreach ( $recent_msgs as $m ) :
							$name  = get_post_meta( $m->ID, '_s247_name', true );
							$topic = get_post_meta( $m->ID, '_s247_topic', true );
						?>
							<li>
								<a href="<?php echo esc_url( get_edit_post_link( $m->ID ) ); ?>">
									<span class="sd-list__dot"></span>
									<span class="sd-list__body">
										<span class="sd-list__name"><?php echo esc_html( $name ?: '—' ); ?></span>
										<span class="sd-list__meta"><?php echo esc_html( $topic ?: __( 'Besked', 'studie247' ) ); ?> · <?php echo esc_html( get_the_date( 'j. M', $m ) ); ?></span>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $can_rental && ! empty( $top_products ) ) : ?>
			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Mest udlejede varer', 'studie247' ); ?></h2>
					<span class="sd-panel__hint"><?php esc_html_e( 'Top 5 alle tider', 'studie247' ); ?></span>
				</header>
				<table class="sd-table">
					<thead>
						<tr><th>#</th><th><?php esc_html_e( 'Vare', 'studie247' ); ?></th><th class="sd-table__right"><?php esc_html_e( 'Udlejninger', 'studie247' ); ?></th></tr>
					</thead>
					<tbody>
						<?php $rank = 0; foreach ( $top_products as $pid => $cnt ) : $rank++; ?>
							<tr onclick="window.location='<?php echo esc_url( get_edit_post_link( $pid ) ); ?>'">
								<td class="sd-rank"><?php printf( '%02d', $rank ); ?></td>
								<td><?php echo esc_html( get_the_title( $pid ) ); ?></td>
								<td class="sd-table__right sd-mono"><?php echo (int) $cnt; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>

<?php endif;
