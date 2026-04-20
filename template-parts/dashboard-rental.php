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

/**
 * Hvor mange kopier af et produkt er udlånt LIGE NU?
 * Baseret på godkendte (publish) bookinger hvor i dag falder mellem
 * start-dato og start-dato + varighed.
 */
$duration_days = array(
	'1 dag'  => 1, '2 dage' => 2, '3 dage' => 3, '4 dage' => 4,
	'1 uge'  => 7, '2 uger' => 14,
);
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

					// TODO: item-detail route (kommer senere)
					$detail_href = esc_url( get_edit_post_link( $it->ID ) );
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
