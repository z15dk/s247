<?php
/**
 * Dashboard — udlejnings-modul.
 * Viser alle udlejnings-varer med udlejnings-status (udlånt/tilgængelig).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! current_user_can( 'edit_posts' ) ) { return; }
if ( ! studie247_can_view_dash( 'rental' ) && ! current_user_can( 'manage_options' ) ) {
	echo '<div class="sd-empty"><p>' . esc_html__( 'Din bruger har ikke adgang til denne sektion.', 'studie247' ) . '</p></div>';
	return;
}

$fmt_dkk = function ( $n ) { return number_format( (int) $n, 0, ',', '.' ) . ' kr'; };

$duration_days = array(
	'1 dag'  => 1, '2 dage' => 2, '3 dage' => 3, '4 dage' => 4,
	'1 uge'  => 7, '2 uger' => 14,
);

/* ───── Item-detalje ───── */
$item_id = isset( $_GET['item'] ) ? (int) $_GET['item'] : 0;
$item    = $item_id ? get_post( $item_id ) : null;
if ( $item && 'udlejning_item' !== $item->post_type ) { $item = null; }

if ( $item ) :
	$today_ts = strtotime( date( 'Y-m-d' ) );
	$sku    = get_post_meta( $item->ID, '_s247_sku', true );
	$antal  = (int) get_post_meta( $item->ID, '_s247_antal', true );
	$pris_d = get_post_meta( $item->ID, '_s247_pris_dag', true );
	$pris_u = get_post_meta( $item->ID, '_s247_pris_uge', true );
	$deposit= get_post_meta( $item->ID, '_s247_deposit', true );
	$ejer   = get_post_meta( $item->ID, '_s247_ejer', true );
	$serie  = get_post_meta( $item->ID, '_s247_serienummer', true );
	$uid    = get_post_meta( $item->ID, '_s247_product_uid', true );
	$thumb  = get_the_post_thumbnail_url( $item->ID, 'medium' );
	$cats_list = get_the_terms( $item->ID, 'udlejning_kategori' );
	$cats_ids  = ( $cats_list && ! is_wp_error( $cats_list ) ) ? wp_list_pluck( $cats_list, 'term_id' ) : array();
	$all_cats  = get_terms( array( 'taxonomy' => 'udlejning_kategori', 'hide_empty' => false ) );
	$saved     = isset( $_GET['saved'] ) && '1' === $_GET['saved'];

	// Hent fuld udlejnings-historik for denne vare.
	$history = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => array( 'pending', 'publish', 'trash' ),
		'posts_per_page' => -1,
		'meta_query'     => array( array( 'key' => '_s247_produkt_id', 'value' => $item->ID, 'compare' => '=' ) ),
		'orderby'        => 'meta_value',
		'meta_key'       => '_s247_date',
		'order'          => 'DESC',
	) );
	$total_earnings = 0;
	$active_count   = 0;
	$completed      = 0;
	$upcoming       = 0;
	foreach ( $history as $h ) {
		if ( 'publish' !== $h->post_status ) { continue; }
		$start_ts = strtotime( get_post_meta( $h->ID, '_s247_date', true ) );
		$dur      = get_post_meta( $h->ID, '_s247_duration', true );
		$days     = $duration_days[ $dur ] ?? 1;
		$end_ts   = strtotime( '+' . ( $days - 1 ) . ' days', $start_ts );
		$total_earnings += (int) get_post_meta( $h->ID, '_s247_estimated_price', true );
		if ( $end_ts < $today_ts )       { $completed++; }
		elseif ( $start_ts > $today_ts ) { $upcoming++; }
		else                             { $active_count++; }
	}
?>

	<form method="post" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="sd-detail sd-edit">
		<?php wp_nonce_field( 's247_dash_item_' . $item->ID ); ?>
		<input type="hidden" name="s247_dash_save_item" value="<?php echo (int) $item->ID; ?>">

		<header class="sd-detail__head">
			<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=rental' ) ); ?>">← <?php esc_html_e( 'Tilbage til varer', 'studie247' ); ?></a>
			<div class="sd-detail__titlewrap">
				<?php if ( $uid ) : ?><span class="sd-mono sd-muted" style="font-size:11px;"><?php echo esc_html( $uid ); ?></span><?php endif; ?>
				<h2 class="sd-detail__title"><?php echo esc_html( $item->post_title ); ?></h2>
				<p class="sd-detail__sub">
					<?php echo (int) count( $history ); ?> <?php esc_html_e( 'udlejninger total', 'studie247' ); ?>
					· <?php echo (int) $active_count; ?> <?php esc_html_e( 'aktive', 'studie247' ); ?>
					· <?php echo (int) $upcoming; ?> <?php esc_html_e( 'kommende', 'studie247' ); ?>
					· <?php echo (int) $completed; ?> <?php esc_html_e( 'gennemførte', 'studie247' ); ?>
				</p>
			</div>
			<div class="sd-detail__actions">
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( get_permalink( $item->ID ) ); ?>" target="_blank"><?php esc_html_e( 'Se på siden', 'studie247' ); ?> ↗</a>
				<button type="submit" class="sd-btn"><?php esc_html_e( 'Gem ændringer', 'studie247' ); ?></button>
			</div>
		</header>

		<?php if ( $saved ) : ?>
			<div class="sd-notice sd-notice--success">✓ <?php esc_html_e( 'Ændringer gemt.', 'studie247' ); ?></div>
		<?php endif; ?>

		<div class="sd-detail__grid">
			<div class="sd-panel">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Stamdata', 'studie247' ); ?></h2></header>
				<div class="sd-form">
					<label class="sd-field"><span><?php esc_html_e( 'Navn', 'studie247' ); ?></span>
						<input type="text" name="post_title" value="<?php echo esc_attr( $item->post_title ); ?>"></label>
					<label class="sd-field"><span>SKU / Vare-nr.</span>
						<input type="text" name="_s247_sku" value="<?php echo esc_attr( $sku ); ?>"></label>
					<label class="sd-field"><span><?php esc_html_e( 'Antal (stock)', 'studie247' ); ?></span>
						<input type="number" name="_s247_antal" value="<?php echo (int) $antal; ?>" min="0" step="1"></label>
					<label class="sd-field"><span><?php esc_html_e( 'Ejer', 'studie247' ); ?></span>
						<input type="text" name="_s247_ejer" value="<?php echo esc_attr( $ejer ); ?>"></label>
					<label class="sd-field"><span><?php esc_html_e( 'Serienummer', 'studie247' ); ?></span>
						<input type="text" name="_s247_serienummer" value="<?php echo esc_attr( $serie ); ?>"></label>
				</div>
			</div>

			<div class="sd-panel">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Priser', 'studie247' ); ?></h2></header>
				<div class="sd-form">
					<label class="sd-field"><span><?php esc_html_e( 'Pris pr. dag', 'studie247' ); ?></span>
						<input type="text" name="_s247_pris_dag" value="<?php echo esc_attr( $pris_d ); ?>" placeholder="fx 299 kr"></label>
					<label class="sd-field"><span><?php esc_html_e( 'Pris pr. uge', 'studie247' ); ?></span>
						<input type="text" name="_s247_pris_uge" value="<?php echo esc_attr( $pris_u ); ?>" placeholder="fx 999 kr"></label>
					<label class="sd-field"><span><?php esc_html_e( 'Depositum', 'studie247' ); ?></span>
						<input type="text" name="_s247_deposit" value="<?php echo esc_attr( $deposit ); ?>"></label>
					<?php if ( $total_earnings ) : ?>
						<p class="sd-hint">💰 <?php printf( esc_html__( 'Total omsætning: %s', 'studie247' ), esc_html( $fmt_dkk( $total_earnings ) ) ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="sd-panel">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Kategorier', 'studie247' ); ?></h2></header>
				<div class="sd-form">
					<?php if ( ! empty( $all_cats ) && ! is_wp_error( $all_cats ) ) : ?>
						<div class="sd-checkboxes">
							<?php foreach ( $all_cats as $c ) : ?>
								<label class="sd-checkbox">
									<input type="checkbox" name="s247_category[]" value="<?php echo (int) $c->term_id; ?>" <?php checked( in_array( $c->term_id, $cats_ids, true ) ); ?>>
									<span><?php echo esc_html( $c->name ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="sd-muted"><?php esc_html_e( 'Ingen kategorier oprettet.', 'studie247' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Beskrivelse', 'studie247' ); ?></h2></header>
				<div class="sd-form">
					<label class="sd-field"><span><?php esc_html_e( 'Kort beskrivelse (excerpt)', 'studie247' ); ?></span>
						<textarea name="post_excerpt" rows="2" class="sd-textarea"><?php echo esc_textarea( $item->post_excerpt ); ?></textarea></label>
					<label class="sd-field"><span><?php esc_html_e( 'Lang beskrivelse', 'studie247' ); ?></span>
						<textarea name="post_content" rows="6" class="sd-textarea"><?php echo esc_textarea( $item->post_content ); ?></textarea></label>
				</div>
			</div>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Udlejnings-historik', 'studie247' ); ?></h2>
					<span class="sd-panel__hint"><?php printf( esc_html__( '%d total', 'studie247' ), count( $history ) ); ?></span>
				</header>
				<?php if ( empty( $history ) ) : ?>
					<p class="sd-panel__empty"><?php esc_html_e( 'Denne vare er aldrig blevet udlejet endnu.', 'studie247' ); ?></p>
				<?php else : ?>
					<table class="sd-table">
						<thead>
							<tr>
								<th></th>
								<th><?php esc_html_e( 'Kunde', 'studie247' ); ?></th>
								<th><?php esc_html_e( 'Udlånt', 'studie247' ); ?></th>
								<th><?php esc_html_e( 'Retur', 'studie247' ); ?></th>
								<th><?php esc_html_e( 'Varighed', 'studie247' ); ?></th>
								<th class="sd-table__right"><?php esc_html_e( 'Pris', 'studie247' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $history as $h ) :
								$h_name  = get_post_meta( $h->ID, '_s247_name', true ) ?: '—';
								$h_start = get_post_meta( $h->ID, '_s247_date', true );
								$h_dur   = get_post_meta( $h->ID, '_s247_duration', true );
								$h_price = (int) get_post_meta( $h->ID, '_s247_estimated_price', true );
								$h_days  = $duration_days[ $h_dur ] ?? 1;
								$h_start_ts = $h_start ? strtotime( $h_start ) : 0;
								$h_end_ts   = $h_start_ts ? strtotime( '+' . ( $h_days - 1 ) . ' days', $h_start_ts ) : 0;
								$h_end      = $h_end_ts ? date( 'Y-m-d', $h_end_ts ) : '';

								if ( 'pending' === $h->post_status ) {
									$tag_label = __( 'Afventer', 'studie247' ); $tag_cls = 'pending';
								} elseif ( 'trash' === $h->post_status ) {
									$tag_label = __( 'Afvist', 'studie247' ); $tag_cls = 'trash';
								} elseif ( $h_end_ts && $h_end_ts < $today_ts ) {
									$tag_label = __( 'Gennemført', 'studie247' ); $tag_cls = 'publish';
								} elseif ( $h_start_ts > $today_ts ) {
									$tag_label = __( 'Kommende', 'studie247' ); $tag_cls = 'upcoming';
								} else {
									$tag_label = __( 'Aktiv nu', 'studie247' ); $tag_cls = 'active';
								}
								$h_href = esc_url( home_url( '/dashboard/?view=bookings&booking=' . $h->ID ) );
							?>
								<tr data-href="<?php echo $h_href; ?>">
									<td><span class="sd-status sd-status--<?php echo esc_attr( $tag_cls ); ?>"><?php echo esc_html( $tag_label ); ?></span></td>
									<td><a href="<?php echo $h_href; ?>" class="sd-table__name-inline"><?php echo esc_html( $h_name ); ?></a></td>
									<td class="sd-muted"><?php echo esc_html( $h_start ? date_i18n( 'j. M Y', $h_start_ts ) : '—' ); ?></td>
									<td class="sd-muted"><?php echo esc_html( $h_end ? date_i18n( 'j. M Y', $h_end_ts ) : '—' ); ?></td>
									<td class="sd-muted"><?php echo esc_html( $h_dur ?: '—' ); ?></td>
									<td class="sd-table__right sd-mono"><?php echo $h_price ? esc_html( $fmt_dkk( $h_price ) ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<div class="sd-detail__footer">
			<button type="submit" class="sd-btn sd-btn--lg"><?php esc_html_e( 'Gem ændringer', 'studie247' ); ?></button>
		</div>
	</form>

	<script>
	document.querySelectorAll('.sd-table tbody tr[data-href]').forEach(function(row){
		row.addEventListener('click', function(e){
			if (e.target.closest('a,button')) return;
			window.location = row.dataset.href;
		});
		row.style.cursor = 'pointer';
	});
	</script>
<?php
	return;
endif;

/**
 * Hvor mange kopier af et produkt er udlånt LIGE NU?
 * Baseret på godkendte (publish) bookinger hvor i dag falder mellem
 * start-dato og start-dato + varighed. $duration_days er defineret i top.
 */
$today_ts = strtotime( date( 'Y-m-d' ) );

$active_by_product = array(); // product_id → ['count' => N, 'rentals' => [{name, start, end}, ...]]
$approved = get_posts( array(
	'post_type'      => 'booking',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'meta_query'     => array( array( 'key' => '_s247_produkt_id', 'compare' => 'EXISTS' ) ),
) );
foreach ( $approved as $b ) {
	$pid  = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
	if ( ! $pid ) { continue; }
	$d    = get_post_meta( $b->ID, '_s247_date', true );
	$dur  = get_post_meta( $b->ID, '_s247_duration', true );
	$days = $duration_days[ $dur ] ?? 1;
	if ( ! $d ) { continue; }
	$start_ts = strtotime( $d );
	$end_ts   = strtotime( '+' . ( $days - 1 ) . ' days', $start_ts );
	if ( $start_ts <= $today_ts && $today_ts <= $end_ts ) {
		if ( ! isset( $active_by_product[ $pid ] ) ) {
			$active_by_product[ $pid ] = array( 'count' => 0, 'rentals' => array() );
		}
		$active_by_product[ $pid ]['count']++;
		$active_by_product[ $pid ]['rentals'][] = array(
			'booking_id' => $b->ID,
			'name'       => get_post_meta( $b->ID, '_s247_name', true ) ?: '(uden navn)',
			'start'      => $d,
			'end'        => date( 'Y-m-d', $end_ts ),
			'days_left'  => max( 0, (int) round( ( $end_ts - $today_ts ) / 86400 ) ),
		);
	}
}

// Søgning
$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$filter = isset( $_GET['filter'] ) ? sanitize_key( $_GET['filter'] ) : 'all';
$cat    = isset( $_GET['cat'] ) ? sanitize_title( wp_unslash( $_GET['cat'] ) ) : '';

$args = array(
	'post_type'      => 'udlejning_item',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
);
if ( $search ) { $args['s'] = $search; }
if ( $cat ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'udlejning_kategori', 'field' => 'slug', 'terms' => $cat ) );
}

$items = get_posts( $args );

// Count-pr-status til filter-pills
$counts = array( 'all' => 0, 'available' => 0, 'partial' => 0, 'out' => 0 );
foreach ( $items as $it ) {
	$counts['all']++;
	$antal  = max( 1, (int) get_post_meta( $it->ID, '_s247_antal', true ) );
	$active = $active_by_product[ $it->ID ]['count'] ?? 0;
	if ( $active <= 0 ) { $counts['available']++; }
	elseif ( $active >= $antal ) { $counts['out']++; }
	else { $counts['partial']++; }
}

// Filtrér efter status
if ( 'all' !== $filter ) {
	$items = array_values( array_filter( $items, function ( $it ) use ( $active_by_product, $filter ) {
		$antal  = max( 1, (int) get_post_meta( $it->ID, '_s247_antal', true ) );
		$active = $active_by_product[ $it->ID ]['count'] ?? 0;
		switch ( $filter ) {
			case 'available': return $active <= 0;
			case 'out':       return $active >= $antal;
			case 'partial':   return $active > 0 && $active < $antal;
		}
		return true;
	} ) );
}

$categories = get_terms( array( 'taxonomy' => 'udlejning_kategori', 'hide_empty' => false ) );
?>

<div class="sd-toolbar">
	<div class="sd-filters">
		<?php
		$filters = array(
			'all'       => array( __( 'Alle', 'studie247' ), $counts['all'], '' ),
			'available' => array( __( 'Tilgængelige', 'studie247' ), $counts['available'], '' ),
			'partial'   => array( __( 'Delvist udlejet', 'studie247' ), $counts['partial'], '' ),
			'out'       => array( __( 'Alle udlånt', 'studie247' ), $counts['out'], 'alert' ),
		);
		foreach ( $filters as $key => $row ) :
			$url = add_query_arg( array( 'view' => 'rental', 'filter' => $key ), home_url( '/dashboard/' ) );
			if ( $search ) { $url = add_query_arg( 'q', $search, $url ); }
			if ( $cat )    { $url = add_query_arg( 'cat', $cat, $url ); }
			$is_active = $filter === $key;
			$alert     = 'alert' === $row[2] && $row[1] > 0;
		?>
			<a class="sd-filter <?php echo $is_active ? 'is-active' : ''; ?><?php echo $alert && ! $is_active ? ' sd-filter--alert' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $row[0] ); ?>
				<span class="sd-filter__count"><?php echo (int) $row[1]; ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<form class="sd-search" method="get" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
		<input type="hidden" name="view" value="rental">
		<input type="hidden" name="filter" value="<?php echo esc_attr( $filter ); ?>">
		<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Søg vare, SKU …', 'studie247' ); ?>">
		<button type="submit" class="sd-search__btn" aria-label="<?php esc_attr_e( 'Søg', 'studie247' ); ?>">⌕</button>
	</form>
</div>

<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
	<div class="sd-subfilters">
		<a class="sd-chip <?php echo '' === $cat ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'view' => 'rental', 'filter' => $filter ), home_url( '/dashboard/' ) ) ); ?>">
			<?php esc_html_e( 'Alle kategorier', 'studie247' ); ?>
		</a>
		<?php foreach ( $categories as $c ) : ?>
			<a class="sd-chip <?php echo $cat === $c->slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'view' => 'rental', 'filter' => $filter, 'cat' => $c->slug ), home_url( '/dashboard/' ) ) ); ?>">
				<?php echo esc_html( $c->name ); ?>
				<span class="sd-chip__count"><?php echo (int) $c->count; ?></span>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<div class="sd-panel">
	<?php if ( empty( $items ) ) : ?>
		<p class="sd-panel__empty"><?php echo $search ? sprintf( esc_html__( 'Ingen varer matcher "%s".', 'studie247' ), esc_html( $search ) ) : esc_html__( 'Ingen varer i denne status.', 'studie247' ); ?></p>
	<?php else : ?>
		<table class="sd-table sd-table--list">
			<thead>
				<tr>
					<th></th>
					<th><?php esc_html_e( 'Vare', 'studie247' ); ?></th>
					<th>SKU</th>
					<th><?php esc_html_e( 'Status', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Udlånt', 'studie247' ); ?></th>
					<th class="sd-table__right"><?php esc_html_e( 'Pris/dag', 'studie247' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $it ) :
					$antal  = max( 1, (int) get_post_meta( $it->ID, '_s247_antal', true ) );
					$active = $active_by_product[ $it->ID ]['count'] ?? 0;
					$free   = max( 0, $antal - $active );
					$sku    = get_post_meta( $it->ID, '_s247_sku', true );
					$pris   = get_post_meta( $it->ID, '_s247_pris_dag', true );
					$thumb  = get_the_post_thumbnail_url( $it->ID, array( 48, 48 ) );
					$cats   = get_the_terms( $it->ID, 'udlejning_kategori' );
					$cat_label = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';

					if ( $active <= 0 )               { $state = 'available'; $state_label = __( 'Tilgængelig', 'studie247' ); }
					elseif ( $active >= $antal )      { $state = 'out';       $state_label = __( 'Alle udlånt', 'studie247' ); }
					else                              { $state = 'partial';   $state_label = __( 'Delvist udlejet', 'studie247' ); }

					$detail_href = esc_url( home_url( '/dashboard/?view=rental&item=' . $it->ID ) );
				?>
					<tr data-href="<?php echo $detail_href; ?>">
						<td class="sd-item-thumb">
							<?php if ( $thumb ) : ?>
								<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy">
							<?php else : ?>
								<span class="sd-item-thumb__ph">📦</span>
							<?php endif; ?>
						</td>
						<td class="sd-table__name">
							<a href="<?php echo $detail_href; ?>"><?php echo esc_html( $it->post_title ); ?></a>
							<?php if ( $cat_label ) : ?><span class="sd-row-sub"><?php echo esc_html( $cat_label ); ?></span><?php endif; ?>
						</td>
						<td class="sd-mono sd-muted"><?php echo esc_html( $sku ?: '—' ); ?></td>
						<td><span class="sd-status sd-status--<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state_label ); ?></span></td>
						<td>
							<?php if ( $active > 0 ) : ?>
								<div class="sd-rental-count">
									<strong><?php echo (int) $active; ?></strong>
									<span class="sd-muted">/ <?php echo (int) $antal; ?></span>
									<?php $first = $active_by_product[ $it->ID ]['rentals'][0] ?? null;
									if ( $first ) : ?>
										<span class="sd-rental-hint">
											→ <?php echo esc_html( $first['name'] ); ?>
											<?php if ( $first['days_left'] > 0 ) : ?>
												<span class="sd-muted"><?php printf( esc_html( _n( '(%d dag tilbage)', '(%d dage tilbage)', $first['days_left'], 'studie247' ) ), $first['days_left'] ); ?></span>
											<?php endif; ?>
										</span>
									<?php endif; ?>
								</div>
							<?php else : ?>
								<span class="sd-muted"><?php echo (int) $free; ?> / <?php echo (int) $antal; ?></span>
							<?php endif; ?>
						</td>
						<td class="sd-table__right sd-mono"><?php echo esc_html( $pris ?: '—' ); ?></td>
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
