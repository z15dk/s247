<?php
/**
 * Dashboard-overview — stats-grid + lister (den oprindelige forside).
 * Forventer følgende variable fra caller:
 *   $pending_count, $publish_count, $trash_count, $total_bookings,
 *   $studio_pending_count, $rental_pending_count,
 *   $msg_count, $item_count, $top_products,
 *   $rev_month_studio, $rev_year_studio, $rev_month_rental, $rev_year_rental,
 *   $recent_pending, $recent_msgs, $fmt_dkk,
 *   $can_rental, $can_studio, $can_messages, $can_revenue, $can_customers,
 *   $can_studio_revenue, $can_rental_revenue,
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
		<?php if ( $can_studio ) : ?>
			<a class="sd-stat <?php echo $studio_pending_count ? 'sd-stat--alert' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings&status=pending' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Studie — afventer', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $studio_pending_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'forespørgsler', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_rental ) : ?>
			<a class="sd-stat <?php echo $rental_pending_count ? 'sd-stat--alert' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=rental' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Udlejning — afventer', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $rental_pending_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'forespørgsler', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_studio_revenue ) : ?>
			<a class="sd-stat sd-stat--accent" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings&status=publish' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Studie — omsætning', 'studie247' ); ?></span>
				<span class="sd-stat__num sd-stat__num--money"><?php echo esc_html( $fmt_dkk( $rev_month_studio ) ); ?></span>
				<span class="sd-stat__delta"><?php printf( esc_html__( 'YTD: %s', 'studie247' ), esc_html( $fmt_dkk( $rev_year_studio ) ) ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_rental_revenue ) : ?>
			<a class="sd-stat sd-stat--accent" href="<?php echo esc_url( home_url( '/dashboard/?view=rental' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Udlejning — omsætning', 'studie247' ); ?></span>
				<span class="sd-stat__num sd-stat__num--money"><?php echo esc_html( $fmt_dkk( $rev_month_rental ) ); ?></span>
				<span class="sd-stat__delta"><?php printf( esc_html__( 'YTD: %s', 'studie247' ), esc_html( $fmt_dkk( $rev_year_rental ) ) ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_messages ) : ?>
			<a class="sd-stat" href="<?php echo esc_url( home_url( '/dashboard/?view=messages' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Beskeder', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $msg_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'modtaget', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_rental ) : ?>
			<a class="sd-stat" href="<?php echo esc_url( home_url( '/dashboard/?view=rental' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Varer', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $item_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'i udlejning', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $can_customers ) :
			$cust_count = (int) wp_count_posts( 's247_customer' )->publish;
		?>
			<a class="sd-stat" href="<?php echo esc_url( home_url( '/dashboard/?view=crm' ) ); ?>">
				<span class="sd-stat__label"><?php esc_html_e( 'Kunder', 'studie247' ); ?></span>
				<span class="sd-stat__num"><?php echo (int) $cust_count; ?></span>
				<span class="sd-stat__delta"><?php esc_html_e( 'i CRM', 'studie247' ); ?></span>
			</a>
		<?php endif; ?>
	</section>

	<?php if ( $can_studio || $can_rental ) :
		$default_month = date( 'Y-m', strtotime( 'first day of last month' ) );
	?>
		<section class="sd-panel sd-export-month">
			<header class="sd-panel__head">
				<h2><?php esc_html_e( 'Månedsrapport (CSV)', 'studie247' ); ?></h2>
				<span class="sd-panel__hint"><?php esc_html_e( 'Træk alle bookinger i en valgt måned.', 'studie247' ); ?></span>
			</header>
			<form class="sd-form sd-export-month__form" method="get" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
				<input type="hidden" name="export" value="monthly_bookings">
				<?php wp_nonce_field( 's247_export_monthly', '_wpnonce' ); ?>
				<div class="sd-form__row">
					<div class="sd-field">
						<label for="s247_export_month"><?php esc_html_e( 'Måned', 'studie247' ); ?></label>
						<input type="month" id="s247_export_month" name="month" value="<?php echo esc_attr( $default_month ); ?>" required>
					</div>
					<div class="sd-field">
						<label for="s247_export_type"><?php esc_html_e( 'Type', 'studie247' ); ?></label>
						<select id="s247_export_type" name="type">
							<?php if ( $can_studio && $can_rental ) : ?>
								<option value="all"><?php esc_html_e( 'Studie + udlejning', 'studie247' ); ?></option>
							<?php endif; ?>
							<?php if ( $can_studio ) : ?>
								<option value="studio"><?php esc_html_e( 'Kun studie', 'studie247' ); ?></option>
							<?php endif; ?>
							<?php if ( $can_rental ) : ?>
								<option value="rental"><?php esc_html_e( 'Kun udlejning', 'studie247' ); ?></option>
							<?php endif; ?>
						</select>
					</div>
				</div>
				<div class="sd-form__actions">
					<button type="submit" class="sd-btn">⬇ <?php esc_html_e( 'Download CSV', 'studie247' ); ?></button>
				</div>
			</form>
		</section>
	<?php endif; ?>

	<section class="sd-grid">
		<?php if ( $can_studio || $can_rental ) : ?>
			<div class="sd-panel">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Afventende forespørgsler', 'studie247' ); ?></h2>
					<a class="sd-panel__more" href="<?php echo esc_url( home_url( '/dashboard/?view=' . ( $can_studio ? 'bookings&status=pending' : 'rental' ) ) ); ?>"><?php esc_html_e( 'Se alle', 'studie247' ); ?> →</a>
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
								<?php if ( $can_studio_revenue || $can_rental_revenue ) : ?><th class="sd-table__right"><?php esc_html_e( 'Pris', 'studie247' ); ?></th><?php endif; ?>
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
								<tr onclick="window.location='<?php echo esc_url( home_url( '/dashboard/?view=' . ( $pid ? 'rental&booking=' : 'bookings&booking=' ) . $b->ID ) ); ?>'">
									<td><span class="sd-badge <?php echo $pid ? 'sd-badge--accent' : 'sd-badge--neutral'; ?>"><?php echo $pid ? esc_html__( 'Udstyr', 'studie247' ) : esc_html__( 'Studie', 'studie247' ); ?></span></td>
									<td><?php echo esc_html( $name ?: '—' ); ?></td>
									<td class="sd-muted"><?php echo esc_html( $date ? date_i18n( 'j. M', strtotime( $date ) ) : '—' ); ?></td>
									<?php if ( $can_studio_revenue || $can_rental_revenue ) :
										$row_shows_price = ( $pid ? $can_rental_revenue : $can_studio_revenue );
									?>
										<td class="sd-table__right sd-mono"><?php echo ( $row_shows_price && $price ) ? esc_html( $fmt_dkk( $price ) ) : '—'; ?></td>
									<?php endif; ?>
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
					<a class="sd-panel__more" href="<?php echo esc_url( home_url( '/dashboard/?view=messages' ) ); ?>"><?php esc_html_e( 'Alle', 'studie247' ); ?> →</a>
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
								<a href="<?php echo esc_url( home_url( '/dashboard/?view=messages&message=' . $m->ID ) ); ?>">
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

	</section>

	<?php
	/* ───── Udlejnings-statistik ───── */
	if ( $can_rental ) :
		$duration_days_map = array( '1 dag'=>1,'2 dage'=>2,'3 dage'=>3,'4 dage'=>4,'1 uge'=>7,'2 uger'=>14 );
		$today_ts = strtotime( date('Y-m-d') );
		$month_st = date('Y-m-01');
		$year_st  = date('Y-01-01');
		$next_7   = strtotime( '+7 days' );

		$rental_bookings = get_posts( array(
			'post_type' => 'booking', 'posts_per_page' => -1,
			'post_status' => array( 'pending', 'publish', 'trash' ),
			'meta_query' => array( array( 'key' => '_s247_produkt_id', 'compare' => 'EXISTS' ) ),
		) );
		$r_total_count = count( $rental_bookings );
		$r_approved = 0; $r_active = 0; $r_upcoming = 0; $r_completed = 0;
		$r_rev_month = 0; $r_rev_year = 0;
		$r_cat_revenue = array(); $r_cat_count = array(); $r_prod_count = array(); $r_mtd_count = 0;
		foreach ( $rental_bookings as $b ) {
			if ( 'publish' !== $b->post_status ) { continue; }
			$r_approved++;
			$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
			$d     = get_post_meta( $b->ID, '_s247_date', true );
			$dur   = get_post_meta( $b->ID, '_s247_duration', true );
			$days  = $duration_days_map[ $dur ] ?? 1;
			$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
			$start = $d ? strtotime( $d ) : 0;
			$end   = $start ? strtotime( '+' . ( $days - 1 ) . ' days', $start ) : 0;
			if ( $d && $d >= $month_st ) { $r_rev_month += $price; $r_mtd_count++; }
			if ( $d && $d >= $year_st )  { $r_rev_year  += $price; }
			if ( $start && $end ) {
				if ( $end < $today_ts )                              { $r_completed++; }
				elseif ( $start > $today_ts && $start <= $next_7 )    { $r_upcoming++; }
				elseif ( $start <= $today_ts && $today_ts <= $end )   { $r_active++; }
			}
			if ( $pid ) {
				$r_prod_count[ $pid ] = ( $r_prod_count[ $pid ] ?? 0 ) + 1;
				$cats = get_the_terms( $pid, 'udlejning_kategori' );
				if ( $cats && ! is_wp_error( $cats ) ) {
					foreach ( $cats as $c ) {
						$r_cat_revenue[ $c->name ] = ( $r_cat_revenue[ $c->name ] ?? 0 ) + $price;
						$r_cat_count[ $c->name ]   = ( $r_cat_count[ $c->name ] ?? 0 ) + 1;
					}
				}
			}
		}
		arsort( $r_prod_count ); arsort( $r_cat_count );
		$r_top_items = array_slice( $r_prod_count, 0, 5, true );
		$r_max_item  = $r_top_items ? max( $r_top_items ) : 1;
		$r_max_cat   = $r_cat_count ? max( $r_cat_count ) : 1;
	?>
		<section class="sd-panel sd-panel--wide sd-statsblock">
			<header class="sd-panel__head">
				<h2><?php esc_html_e( 'Udlejnings-statistik', 'studie247' ); ?></h2>
				<a class="sd-panel__more" href="<?php echo esc_url( home_url( '/dashboard/?view=rental' ) ); ?>"><?php esc_html_e( 'Se alle', 'studie247' ); ?> →</a>
			</header>

			<div class="sd-ministats">
				<div><span class="sd-ministat__label"><?php esc_html_e( 'Aktive lige nu', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) $r_active; ?></span></div>
				<div><span class="sd-ministat__label"><?php esc_html_e( 'Næste 7 dage', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) $r_upcoming; ?></span></div>
				<div><span class="sd-ministat__label"><?php esc_html_e( 'Udlejet MTD', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) $r_mtd_count; ?></span></div>
				<div><span class="sd-ministat__label"><?php esc_html_e( 'Gennemførte', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) $r_completed; ?></span></div>
				<?php if ( $can_rental_revenue ) : ?>
					<div><span class="sd-ministat__label"><?php esc_html_e( 'Oms. MTD', 'studie247' ); ?></span><span class="sd-ministat__num sd-ministat__num--money"><?php echo esc_html( $fmt_dkk( $r_rev_month ) ); ?></span></div>
					<div><span class="sd-ministat__label"><?php esc_html_e( 'Oms. YTD', 'studie247' ); ?></span><span class="sd-ministat__num sd-ministat__num--money"><?php echo esc_html( $fmt_dkk( $r_rev_year ) ); ?></span></div>
				<?php endif; ?>
			</div>

			<div class="sd-stats-row">
				<div class="sd-stats-col">
					<h3 class="sd-stats-h3"><?php esc_html_e( 'Mest udlejede varer', 'studie247' ); ?></h3>
					<?php if ( empty( $r_top_items ) ) : ?>
						<p class="sd-muted"><?php esc_html_e( 'Ingen udlejninger endnu.', 'studie247' ); ?></p>
					<?php else : ?>
						<ol class="sd-barlist">
							<?php foreach ( $r_top_items as $pid => $cnt ) : $pct = round( $cnt / $r_max_item * 100 ); ?>
								<li>
									<a class="sd-barlist__name" href="<?php echo esc_url( home_url( '/dashboard/?view=rental&item=' . $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
									<span class="sd-barlist__bar"><span style="width:<?php echo (int) $pct; ?>%;"></span></span>
									<span class="sd-barlist__val"><?php echo (int) $cnt; ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>

				<div class="sd-stats-col">
					<h3 class="sd-stats-h3"><?php esc_html_e( 'Kategorier', 'studie247' ); ?></h3>
					<?php if ( empty( $r_cat_count ) ) : ?>
						<p class="sd-muted">—</p>
					<?php else : ?>
						<ol class="sd-barlist">
							<?php foreach ( $r_cat_count as $cname => $ccnt ) : $pct = round( $ccnt / $r_max_cat * 100 ); ?>
								<li>
									<span class="sd-barlist__name"><?php echo esc_html( $cname ); ?></span>
									<span class="sd-barlist__bar"><span style="width:<?php echo (int) $pct; ?>%;"></span></span>
									<span class="sd-barlist__val">
										<?php echo (int) $ccnt; ?>
										<?php if ( $can_rental_revenue && isset( $r_cat_revenue[ $cname ] ) ) : ?>
											<span class="sd-muted"> · <?php echo esc_html( $fmt_dkk( $r_cat_revenue[ $cname ] ) ); ?></span>
										<?php endif; ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	/* ───── Studie-statistik ───── */
	if ( $can_studio ) :
		$studio_bookings = get_posts( array(
			'post_type' => 'booking', 'posts_per_page' => -1,
			'post_status' => array( 'pending', 'publish', 'trash' ),
			'meta_query' => array(
				'relation' => 'OR',
				array( 'key' => '_s247_produkt', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_s247_produkt', 'value' => '', 'compare' => '=' ),
			),
		) );
		$s_total = count( $studio_bookings ); $s_approved = 0; $s_mtd = 0; $s_ytd = 0;
		$s_rev_month = 0; $s_rev_year = 0;
		$s_use_counts = array(); $s_edit_counts = array(); $s_dur_counts = array();
		$s_weekday = array_fill( 1, 7, 0 );
		$month_st = date('Y-m-01'); $year_st = date('Y-01-01');
		$use_labels = array(
			'podcast' => 'Podcast', 'kursusvideo' => 'Kursusvideo',
			'undervisningsvideo' => 'Undervisningsvideo',
			'some-content' => 'SoMe Content', 'annonce-video' => 'Annonce-video',
		);
		$wd_names = array( 1 => 'Man', 2 => 'Tir', 3 => 'Ons', 4 => 'Tor', 5 => 'Fre', 6 => 'Lør', 7 => 'Søn' );
		foreach ( $studio_bookings as $b ) {
			if ( 'publish' !== $b->post_status ) { continue; }
			$s_approved++;
			$d = get_post_meta( $b->ID, '_s247_date', true );
			$p = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
			$use = get_post_meta( $b->ID, '_s247_use_type', true );
			$edt = get_post_meta( $b->ID, '_s247_edit_type', true );
			$dur = get_post_meta( $b->ID, '_s247_duration', true );
			if ( $d && $d >= $month_st ) { $s_rev_month += $p; $s_mtd++; }
			if ( $d && $d >= $year_st )  { $s_rev_year  += $p; $s_ytd++; }
			if ( $use ) { $s_use_counts[ $use ] = ( $s_use_counts[ $use ] ?? 0 ) + 1; }
			if ( $edt ) { $s_edit_counts[ $edt ] = ( $s_edit_counts[ $edt ] ?? 0 ) + 1; }
			if ( $dur ) { $s_dur_counts[ $dur ] = ( $s_dur_counts[ $dur ] ?? 0 ) + 1; }
			if ( $d ) { $s_weekday[ (int) date( 'N', strtotime( $d ) ) ]++; }
		}
		$s_max_use = $s_use_counts ? max( $s_use_counts ) : 1;
		$s_max_wd  = max( max( $s_weekday ), 1 );
		$s_red   = $s_edit_counts['redigering'] ?? 0;
		$s_files = $s_edit_counts['kun-filer'] ?? 0;
		$s_sum   = $s_red + $s_files;
	?>
		<section class="sd-panel sd-panel--wide sd-statsblock">
			<header class="sd-panel__head">
				<h2><?php esc_html_e( 'Studie-statistik', 'studie247' ); ?></h2>
				<a class="sd-panel__more" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings' ) ); ?>"><?php esc_html_e( 'Se alle', 'studie247' ); ?> →</a>
			</header>

			<div class="sd-ministats">
				<div><span class="sd-ministat__label"><?php esc_html_e( 'MTD', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) $s_mtd; ?></span></div>
				<div><span class="sd-ministat__label"><?php esc_html_e( 'YTD', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) $s_ytd; ?></span></div>
				<?php if ( $s_sum ) : ?>
					<div><span class="sd-ministat__label"><?php esc_html_e( 'Vælger redigering', 'studie247' ); ?></span><span class="sd-ministat__num"><?php echo (int) round( $s_red / $s_sum * 100 ); ?>%</span></div>
				<?php endif; ?>
				<?php if ( $can_studio_revenue ) : ?>
					<div><span class="sd-ministat__label"><?php esc_html_e( 'Oms. MTD', 'studie247' ); ?></span><span class="sd-ministat__num sd-ministat__num--money"><?php echo esc_html( $fmt_dkk( $s_rev_month ) ); ?></span></div>
					<div><span class="sd-ministat__label"><?php esc_html_e( 'Oms. YTD', 'studie247' ); ?></span><span class="sd-ministat__num sd-ministat__num--money"><?php echo esc_html( $fmt_dkk( $s_rev_year ) ); ?></span></div>
				<?php endif; ?>
			</div>

			<div class="sd-stats-row">
				<div class="sd-stats-col">
					<h3 class="sd-stats-h3"><?php esc_html_e( 'Booking-formål', 'studie247' ); ?></h3>
					<?php if ( empty( $s_use_counts ) ) : ?>
						<p class="sd-muted">—</p>
					<?php else : ?>
						<ol class="sd-barlist">
							<?php foreach ( $s_use_counts as $k => $cnt ) : $pct = round( $cnt / $s_max_use * 100 ); ?>
								<li>
									<span class="sd-barlist__name"><?php echo esc_html( $use_labels[ $k ] ?? $k ); ?></span>
									<span class="sd-barlist__bar"><span style="width:<?php echo (int) $pct; ?>%;"></span></span>
									<span class="sd-barlist__val"><?php echo (int) $cnt; ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>

				<div class="sd-stats-col">
					<h3 class="sd-stats-h3"><?php esc_html_e( 'Travleste ugedage', 'studie247' ); ?></h3>
					<div class="sd-weekdays">
						<?php foreach ( $wd_names as $wd_num => $wd_name ) :
							$c = $s_weekday[ $wd_num ];
							$h = max( 6, round( $c / $s_max_wd * 100 ) );
						?>
							<div class="sd-weekday">
								<div class="sd-weekday__bar"><span style="height:<?php echo (int) $h; ?>%;"></span></div>
								<span class="sd-weekday__num"><?php echo (int) $c; ?></span>
								<span class="sd-weekday__name"><?php echo esc_html( $wd_name ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<?php if ( ! empty( $s_dur_counts ) ) : ?>
					<div class="sd-stats-col">
						<h3 class="sd-stats-h3"><?php esc_html_e( 'Varighed', 'studie247' ); ?></h3>
						<ol class="sd-barlist">
							<?php $s_max_dur = max( $s_dur_counts ); foreach ( $s_dur_counts as $k => $cnt ) : $pct = round( $cnt / $s_max_dur * 100 ); ?>
								<li>
									<span class="sd-barlist__name"><?php echo esc_html( $k ); ?></span>
									<span class="sd-barlist__bar"><span style="width:<?php echo (int) $pct; ?>%;"></span></span>
									<span class="sd-barlist__val"><?php echo (int) $cnt; ?></span>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

<?php endif;
