<?php
/**
 * Dashboard — CRM-modul (s247_customer CPT).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! current_user_can( 'edit_posts' ) ) { return; }
if ( ! studie247_can_view_dash( 'customers' ) && ! current_user_can( 'manage_options' ) ) {
	echo '<div class="sd-empty"><p>' . esc_html__( 'Din bruger har ikke adgang til CRM-sektionen.', 'studie247' ) . '</p></div>';
	return;
}

$fmt_dkk = function ( $n ) { return number_format( (int) $n, 0, ',', '.' ) . ' kr'; };
$can_revenue = studie247_can_view_dash( 'revenue' ) || current_user_can( 'manage_options' );

// Detalje-visning
$cust_id = isset( $_GET['customer'] ) ? (int) $_GET['customer'] : 0;
$cust    = $cust_id ? get_post( $cust_id ) : null;
if ( $cust && 's247_customer' !== $cust->post_type ) { $cust = null; }

if ( $cust ) :
	$c_name    = get_post_meta( $cust->ID, '_s247_cust_name', true );
	$c_email   = get_post_meta( $cust->ID, '_s247_cust_email', true );
	$c_phone   = get_post_meta( $cust->ID, '_s247_cust_phone', true );
	$c_company = get_post_meta( $cust->ID, '_s247_cust_company', true );
	$c_cvr     = get_post_meta( $cust->ID, '_s247_cust_cvr', true );
	$c_notes   = get_post_meta( $cust->ID, '_s247_cust_notes', true );
	$c_first   = get_post_meta( $cust->ID, '_s247_cust_first_seen', true );
	$c_last    = get_post_meta( $cust->ID, '_s247_cust_last_seen', true );
	$c_log     = get_post_meta( $cust->ID, '_s247_cust_log', true );
	if ( ! is_array( $c_log ) ) { $c_log = array(); }
	$c_news    = '1' === get_post_meta( $cust->ID, '_s247_cust_newsletter', true );
	$c_news_ts = get_post_meta( $cust->ID, '_s247_cust_newsletter_ts', true );

	$activity = studie247_customer_activity( $cust->ID );
	$studio_spend = 0;
	$rental_spend = 0;
	foreach ( $activity['bookings'] as $b ) {
		if ( 'publish' !== $b->post_status ) { continue; }
		$p   = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
		$pid = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
		if ( $pid ) { $rental_spend += $p; } else { $studio_spend += $p; }
	}
	$total_spend = $studio_spend + $rental_spend;

	// Byg samlet tidslinje (bookings + beskeder + log-noter sorteret DESC)
	$timeline = array();
	foreach ( $activity['bookings'] as $b ) {
		$date = get_post_meta( $b->ID, '_s247_date', true );
		$ts   = $date ? strtotime( $date ) : strtotime( $b->post_date_gmt );
		$timeline[] = array( 'ts' => $ts, 'kind' => 'booking', 'post' => $b );
	}
	foreach ( $activity['messages'] as $m ) {
		$timeline[] = array( 'ts' => strtotime( $m->post_date_gmt ), 'kind' => 'message', 'post' => $m );
	}
	foreach ( $c_log as $entry ) {
		$timeline[] = array( 'ts' => strtotime( $entry['time'] ), 'kind' => 'note', 'entry' => $entry );
	}
	usort( $timeline, function ( $a, $b ) { return $b['ts'] - $a['ts']; } );
	$saved = isset( $_GET['saved'] ) && '1' === $_GET['saved'];
?>

	<div class="sd-detail sd-crm-detail">
		<header class="sd-detail__head">
			<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=crm' ) ); ?>">← <?php esc_html_e( 'Tilbage til kunder', 'studie247' ); ?></a>
			<div class="sd-detail__titlewrap">
				<h2 class="sd-detail__title"><?php echo esc_html( $c_name ?: $c_email ?: '(uden navn)' ); ?></h2>
				<p class="sd-detail__sub">
					<?php if ( $c_first ) : ?>
						<?php printf( esc_html__( 'Kunde siden %s', 'studie247' ), esc_html( mysql2date( 'j. M Y', $c_first ) ) ); ?>
					<?php endif; ?>
					<?php if ( $c_last ) : ?>
						· <?php printf( esc_html__( 'sidst set %s', 'studie247' ), esc_html( mysql2date( 'j. M Y', $c_last ) ) ); ?>
					<?php endif; ?>
				</p>
			</div>
			<div class="sd-detail__actions">
				<?php if ( $c_email ) : ?>
					<a class="sd-btn" href="mailto:<?php echo esc_attr( $c_email ); ?>">✉ <?php esc_html_e( 'Mail', 'studie247' ); ?></a>
				<?php endif; ?>
				<?php if ( $c_phone ) : ?>
					<a class="sd-btn sd-btn--ghost" href="tel:<?php echo esc_attr( $c_phone ); ?>">☎ <?php esc_html_e( 'Ring', 'studie247' ); ?></a>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( $saved ) : ?><div class="sd-notice sd-notice--success">✓ <?php esc_html_e( 'Gemt.', 'studie247' ); ?></div><?php endif; ?>

		<div class="sd-crm-grid">
			<!-- Venstre: info + stamdata + noter -->
			<aside class="sd-crm-side">
				<form method="post" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="sd-panel">
					<?php wp_nonce_field( 's247_dash_cust_' . $cust->ID ); ?>
					<input type="hidden" name="s247_dash_save_customer" value="<?php echo (int) $cust->ID; ?>">
					<header class="sd-panel__head"><h2><?php esc_html_e( 'Kontaktinfo', 'studie247' ); ?></h2></header>
					<div class="sd-form">
						<label class="sd-field"><span><?php esc_html_e( 'Navn', 'studie247' ); ?></span><input type="text" name="_s247_cust_name" value="<?php echo esc_attr( $c_name ); ?>"></label>
						<label class="sd-field"><span>E-mail</span><input type="email" name="_s247_cust_email" value="<?php echo esc_attr( $c_email ); ?>"></label>
						<label class="sd-field"><span><?php esc_html_e( 'Telefon', 'studie247' ); ?></span><input type="tel" name="_s247_cust_phone" value="<?php echo esc_attr( $c_phone ); ?>"></label>
						<label class="sd-field"><span><?php esc_html_e( 'Virksomhed', 'studie247' ); ?></span><input type="text" name="_s247_cust_company" value="<?php echo esc_attr( $c_company ); ?>"></label>
						<label class="sd-field"><span>CVR</span><input type="text" name="_s247_cust_cvr" value="<?php echo esc_attr( $c_cvr ); ?>" inputmode="numeric" maxlength="8"></label>
						<label class="sd-field"><span><?php esc_html_e( 'Interne noter (permanent)', 'studie247' ); ?></span>
							<textarea name="_s247_cust_notes" rows="5" class="sd-textarea" placeholder="<?php esc_attr_e( 'Præferencer, aftaler, opfølgning …', 'studie247' ); ?>"><?php echo esc_textarea( $c_notes ); ?></textarea>
						</label>
					</div>
					<div class="sd-detail__footer">
						<button type="submit" class="sd-btn"><?php esc_html_e( 'Gem', 'studie247' ); ?></button>
					</div>
				</form>

				<div class="sd-panel sd-crm-summary">
					<header class="sd-panel__head"><h2><?php esc_html_e( 'Oversigt', 'studie247' ); ?></h2></header>
					<div class="sd-form">
						<div class="sd-crm-stat"><span><?php esc_html_e( 'Bookinger', 'studie247' ); ?></span><strong><?php echo count( $activity['bookings'] ); ?></strong></div>
						<div class="sd-crm-stat"><span><?php esc_html_e( 'Beskeder', 'studie247' ); ?></span><strong><?php echo count( $activity['messages'] ); ?></strong></div>
						<?php if ( $can_revenue ) : ?>
							<div class="sd-crm-stat"><span><?php esc_html_e( 'Studie — forbrug', 'studie247' ); ?></span><strong><?php echo esc_html( $fmt_dkk( $studio_spend ) ); ?></strong></div>
							<div class="sd-crm-stat"><span><?php esc_html_e( 'Udlejning — forbrug', 'studie247' ); ?></span><strong><?php echo esc_html( $fmt_dkk( $rental_spend ) ); ?></strong></div>
						<?php endif; ?>
						<div class="sd-crm-stat">
							<span><?php esc_html_e( 'Nyhedsbrev', 'studie247' ); ?></span>
							<strong>
								<?php if ( $c_news ) : ?>
									<span class="sd-badge sd-badge--accent">✓ <?php esc_html_e( 'Tilmeldt', 'studie247' ); ?></span>
									<?php if ( $c_news_ts ) : ?>
										<div class="sd-muted" style="font-size:11px;font-weight:400;margin-top:2px;"><?php echo esc_html( mysql2date( 'j. M Y', $c_news_ts ) ); ?></div>
									<?php endif; ?>
								<?php else : ?>
									<span class="sd-muted"><?php esc_html_e( 'Ikke tilmeldt', 'studie247' ); ?></span>
								<?php endif; ?>
							</strong>
						</div>
					</div>
				</div>
			</aside>

			<!-- Højre: tidslinje + note-add -->
			<div class="sd-crm-main">
				<form method="post" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="sd-panel sd-crm-addnote">
					<?php wp_nonce_field( 's247_dash_cust_log_' . $cust->ID ); ?>
					<input type="hidden" name="s247_dash_cust_log" value="<?php echo (int) $cust->ID; ?>">
					<header class="sd-panel__head"><h2><?php esc_html_e( 'Tilføj log-note', 'studie247' ); ?></h2></header>
					<div class="sd-form">
						<textarea name="log_text" rows="2" class="sd-textarea" placeholder="<?php esc_attr_e( 'Fx: Ringede — ønsker et tilbud på 3 måneders samarbejde', 'studie247' ); ?>" required></textarea>
					</div>
					<div class="sd-detail__footer">
						<button type="submit" class="sd-btn"><?php esc_html_e( 'Tilføj til log', 'studie247' ); ?></button>
					</div>
				</form>

				<div class="sd-panel">
					<header class="sd-panel__head">
						<h2><?php esc_html_e( 'Tidslinje', 'studie247' ); ?></h2>
						<span class="sd-panel__hint"><?php printf( esc_html__( '%d poster', 'studie247' ), count( $timeline ) ); ?></span>
					</header>
					<?php if ( empty( $timeline ) ) : ?>
						<p class="sd-panel__empty"><?php esc_html_e( 'Ingen aktivitet endnu.', 'studie247' ); ?></p>
					<?php else : ?>
						<ul class="sd-timeline">
							<?php foreach ( $timeline as $tl ) :
								if ( 'booking' === $tl['kind'] ) :
									$b = $tl['post'];
									$pid = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
									$kind_label = $pid ? __( 'Udlejning', 'studie247' ) : __( 'Studie-booking', 'studie247' );
									$kind_cls   = $pid ? 'rental' : 'studio';
									$status_map = array( 'pending' => __( 'Afventer', 'studie247' ), 'publish' => __( 'Godkendt', 'studie247' ), 'trash' => __( 'Afvist', 'studie247' ) );
									$status     = $status_map[ $b->post_status ] ?? $b->post_status;
									$date       = get_post_meta( $b->ID, '_s247_date', true );
									$dur        = get_post_meta( $b->ID, '_s247_duration', true );
									$price      = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
									$href       = esc_url( home_url( '/dashboard/?view=' . ( $pid ? 'rental&booking=' : 'bookings&booking=' ) . $b->ID ) );
							?>
								<li class="sd-timeline__item sd-timeline__item--<?php echo esc_attr( $kind_cls ); ?>">
									<div class="sd-timeline__dot"></div>
									<div class="sd-timeline__body">
										<div class="sd-timeline__head">
											<span class="sd-timeline__kind"><?php echo esc_html( $kind_label ); ?></span>
											<span class="sd-timeline__time"><?php echo esc_html( mysql2date( 'j. M Y', $b->post_date ) ); ?></span>
										</div>
										<a class="sd-timeline__title" href="<?php echo $href; ?>">
											<?php echo esc_html( $date ? date_i18n( 'j. M', strtotime( $date ) ) : '' ); ?>
											<?php if ( $dur ) : ?> · <?php echo esc_html( $dur ); ?><?php endif; ?>
											<?php if ( $pid ) : ?> · <?php echo esc_html( get_the_title( $pid ) ); ?><?php endif; ?>
										</a>
										<div class="sd-timeline__meta">
											<span class="sd-status sd-status--<?php echo esc_attr( $b->post_status ); ?>"><?php echo esc_html( $status ); ?></span>
											<?php if ( $can_revenue && $price ) : ?><span class="sd-mono"><?php echo esc_html( $fmt_dkk( $price ) ); ?></span><?php endif; ?>
										</div>
									</div>
								</li>
							<?php elseif ( 'message' === $tl['kind'] ) :
								$m = $tl['post'];
								$topic   = get_post_meta( $m->ID, '_s247_topic', true );
								$excerpt = wp_trim_words( (string) get_post_meta( $m->ID, '_s247_message', true ), 20, '…' );
								$handled = '1' === get_post_meta( $m->ID, '_s247_msg_handled', true );
								$href    = esc_url( home_url( '/dashboard/?view=messages&message=' . $m->ID ) );
							?>
								<li class="sd-timeline__item sd-timeline__item--message">
									<div class="sd-timeline__dot"></div>
									<div class="sd-timeline__body">
										<div class="sd-timeline__head">
											<span class="sd-timeline__kind"><?php esc_html_e( 'Besked', 'studie247' ); ?><?php if ( $topic ) : ?> · <?php echo esc_html( $topic ); ?><?php endif; ?></span>
											<span class="sd-timeline__time"><?php echo esc_html( mysql2date( 'j. M Y H:i', $m->post_date ) ); ?></span>
										</div>
										<a class="sd-timeline__title sd-timeline__title--quote" href="<?php echo $href; ?>">
											<?php echo esc_html( $excerpt ); ?>
										</a>
										<div class="sd-timeline__meta">
											<?php if ( $handled ) : ?>
												<span class="sd-status sd-status--publish">✓ <?php esc_html_e( 'Håndteret', 'studie247' ); ?></span>
											<?php else : ?>
												<span class="sd-status sd-status--pending"><?php esc_html_e( 'Ubehandlet', 'studie247' ); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</li>
							<?php elseif ( 'note' === $tl['kind'] ) :
								$e = $tl['entry'];
							?>
								<li class="sd-timeline__item sd-timeline__item--note">
									<div class="sd-timeline__dot"></div>
									<div class="sd-timeline__body">
										<div class="sd-timeline__head">
											<span class="sd-timeline__kind"><?php esc_html_e( 'Intern note', 'studie247' ); ?> · <?php echo esc_html( $e['user_name'] ); ?></span>
											<span class="sd-timeline__time"><?php echo esc_html( mysql2date( 'j. M Y H:i', $e['time'] ) ); ?></span>
										</div>
										<div class="sd-timeline__note"><?php echo nl2br( esc_html( $e['text'] ) ); ?></div>
									</div>
								</li>
							<?php endif; endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php
	return;
endif;

/* ───── Liste-visning ───── */
$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

$args = array(
	'post_type'      => 's247_customer',
	'posts_per_page' => 200,
	'orderby'        => 'meta_value',
	'meta_key'       => '_s247_cust_last_seen',
	'order'          => 'DESC',
);
if ( $search ) { $args['s'] = $search; }
$customers = get_posts( $args );

$total = (int) wp_count_posts( 's247_customer' )->publish;
?>

<div class="sd-toolbar">
	<div class="sd-crm-summary-bar">
		<span class="sd-crm-summary-bar__num"><?php echo (int) $total; ?></span>
		<span class="sd-crm-summary-bar__label"><?php esc_html_e( 'kunder total', 'studie247' ); ?></span>
	</div>
	<form class="sd-search" method="get" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
		<input type="hidden" name="view" value="crm">
		<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Søg navn, email, virksomhed …', 'studie247' ); ?>">
		<button type="submit" class="sd-search__btn" aria-label="<?php esc_attr_e( 'Søg', 'studie247' ); ?>">⌕</button>
	</form>
	<?php
	$export_url = wp_nonce_url(
		add_query_arg( array( 'export' => 'customers_csv' ), home_url( '/dashboard/' ) ),
		's247_export_customers'
	);
	?>
	<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( $export_url ); ?>" title="<?php esc_attr_e( 'Download CSV med alle kunder', 'studie247' ); ?>">
		⬇ <?php esc_html_e( 'Eksportér CSV', 'studie247' ); ?>
	</a>
</div>

<div class="sd-panel">
	<?php if ( empty( $customers ) ) : ?>
		<p class="sd-panel__empty"><?php echo $search ? sprintf( esc_html__( 'Ingen kunder matcher "%s".', 'studie247' ), esc_html( $search ) ) : esc_html__( 'Ingen kunder endnu.', 'studie247' ); ?></p>
	<?php else : ?>
		<table class="sd-table sd-table--list">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Kunde', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Kontakt', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Aktivitet', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Nyhedsbrev', 'studie247' ); ?></th>
					<?php if ( $can_revenue ) : ?>
						<th class="sd-table__right"><?php esc_html_e( 'Studie', 'studie247' ); ?></th>
						<th class="sd-table__right"><?php esc_html_e( 'Udlejning', 'studie247' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Sidst set', 'studie247' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $customers as $c ) :
					$c_name    = get_post_meta( $c->ID, '_s247_cust_name', true );
					$c_email   = get_post_meta( $c->ID, '_s247_cust_email', true );
					$c_phone   = get_post_meta( $c->ID, '_s247_cust_phone', true );
					$c_company = get_post_meta( $c->ID, '_s247_cust_company', true );
					$c_last    = get_post_meta( $c->ID, '_s247_cust_last_seen', true );
					$c_news    = '1' === get_post_meta( $c->ID, '_s247_cust_newsletter', true );

					$activity = studie247_customer_activity( $c->ID );
					$bk_count = count( $activity['bookings'] );
					$ms_count = count( $activity['messages'] );
					$spend_studio = 0;
					$spend_rental = 0;
					foreach ( $activity['bookings'] as $b ) {
						if ( 'publish' !== $b->post_status ) { continue; }
						$p   = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
						$pid = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
						if ( $pid ) { $spend_rental += $p; } else { $spend_studio += $p; }
					}
					$href = esc_url( home_url( '/dashboard/?view=crm&customer=' . $c->ID ) );
					$initials = strtoupper( mb_substr( $c_name ?: $c_email ?: '?', 0, 1 ) );
				?>
					<tr data-href="<?php echo $href; ?>">
						<td class="sd-table__name">
							<span class="sd-crm-avatar"><?php echo esc_html( $initials ); ?></span>
							<div>
								<a href="<?php echo $href; ?>"><?php echo esc_html( $c_name ?: '(uden navn)' ); ?></a>
								<?php if ( $c_company ) : ?><span class="sd-row-sub"><?php echo esc_html( $c_company ); ?></span><?php endif; ?>
							</div>
						</td>
						<td class="sd-muted">
							<?php if ( $c_email ) : ?><div><?php echo esc_html( $c_email ); ?></div><?php endif; ?>
							<?php if ( $c_phone ) : ?><div class="sd-mono"><?php echo esc_html( $c_phone ); ?></div><?php endif; ?>
						</td>
						<td class="sd-muted">
							<?php printf( esc_html__( '%1$d bookinger · %2$d beskeder', 'studie247' ), (int) $bk_count, (int) $ms_count ); ?>
						</td>
						<td>
							<?php if ( $c_news ) : ?>
								<span class="sd-badge sd-badge--accent">✓ <?php esc_html_e( 'Ja tak', 'studie247' ); ?></span>
							<?php else : ?>
								<span class="sd-muted">—</span>
							<?php endif; ?>
						</td>
						<?php if ( $can_revenue ) : ?>
							<td class="sd-table__right sd-mono"><?php echo $spend_studio ? esc_html( $fmt_dkk( $spend_studio ) ) : '—'; ?></td>
							<td class="sd-table__right sd-mono"><?php echo $spend_rental ? esc_html( $fmt_dkk( $spend_rental ) ) : '—'; ?></td>
						<?php endif; ?>
						<td class="sd-muted"><?php echo esc_html( $c_last ? mysql2date( 'j. M Y', $c_last ) : '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<script>
document.querySelectorAll('.sd-table--list tbody tr[data-href]').forEach(function(row){
	row.addEventListener('click', function(e){
		if (e.target.closest('a,button')) return;
		window.location = row.dataset.href;
	});
	row.style.cursor = 'pointer';
});
</script>
