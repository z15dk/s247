<?php
/**
 * Dashboard — bookings-modul (kun studie-bookinger, ingen udstyr).
 *
 * Understøtter filter efter status, fritekst-søgning på navn/email,
 * sortering efter dato, og en detalje-side (?booking=ID) med godkend/
 * afvis-genveje.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! current_user_can( 'edit_posts' ) ) { return; }
if ( ! studie247_can_view_dash( 'studio' ) && ! studie247_can_view_dash( 'pending' ) && ! current_user_can( 'manage_options' ) ) {
	echo '<div class="sd-empty"><p>' . esc_html__( 'Din bruger har ikke adgang til denne sektion.', 'studie247' ) . '</p></div>';
	return;
}

$can_revenue = studie247_can_view_dash( 'revenue' ) || current_user_can( 'manage_options' );
$fmt_dkk = function ( $n ) { return number_format( (int) $n, 0, ',', '.' ) . ' kr'; };

// Status-filter
$status_param = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : 'all';
$allowed_statuses = array( 'all', 'pending', 'publish', 'trash' );
if ( ! in_array( $status_param, $allowed_statuses, true ) ) { $status_param = 'all'; }

// Søgning
$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

// Detalje-visning
$detail_id = isset( $_GET['booking'] ) ? (int) $_GET['booking'] : 0;
$detail    = $detail_id ? get_post( $detail_id ) : null;
if ( $detail && 'booking' !== $detail->post_type ) { $detail = null; }

// Hvis detalje vist → render det og stop.
if ( $detail ) :
	$d_name    = get_post_meta( $detail->ID, '_s247_name', true );
	$d_email   = get_post_meta( $detail->ID, '_s247_email', true );
	$d_phone   = get_post_meta( $detail->ID, '_s247_phone', true );
	$d_company = get_post_meta( $detail->ID, '_s247_company', true );
	$d_cvr     = get_post_meta( $detail->ID, '_s247_cvr', true );
	$d_date    = get_post_meta( $detail->ID, '_s247_date', true );
	$d_start   = get_post_meta( $detail->ID, '_s247_start', true );
	$d_dur     = get_post_meta( $detail->ID, '_s247_duration', true );
	$d_notes   = get_post_meta( $detail->ID, '_s247_notes', true );
	$d_pid     = (int) get_post_meta( $detail->ID, '_s247_produkt_id', true );
	$d_price   = (int) get_post_meta( $detail->ID, '_s247_estimated_price', true );
	$d_internal= '1' === get_post_meta( $detail->ID, '_s247_internal', true );
	$d_use_type     = get_post_meta( $detail->ID, '_s247_use_type', true );
	$d_edit_type    = get_post_meta( $detail->ID, '_s247_edit_type', true );
	$d_podcast_type = get_post_meta( $detail->ID, '_s247_podcast_type', true );
	$d_tilkoeb      = get_post_meta( $detail->ID, '_s247_tilkoeb', true );
	$d_video_count  = (int) get_post_meta( $detail->ID, '_s247_video_count', true );
	$d_video_dur    = (int) get_post_meta( $detail->ID, '_s247_video_duration', true );
	$d_format       = get_post_meta( $detail->ID, '_s247_format', true );
	$d_newsletter   = '1' === get_post_meta( $detail->ID, '_s247_newsletter_optin', true );
	$d_consent_ts   = get_post_meta( $detail->ID, '_s247_consent_timestamp', true );
	$d_consent_ip   = get_post_meta( $detail->ID, '_s247_consent_ip', true );

	$use_type_map = array(
		'podcast' => 'Podcast', 'kursusvideo' => 'Kursusvideo',
		'undervisningsvideo' => 'Undervisningsvideo',
		'some-content' => 'SoMe Content', 'annonce-video' => 'Annonce-video',
	);
	$edit_map = array( 'redigering' => 'Redigering', 'kun-filer' => 'Kun filerne' );
	$podcast_map = array( 'lyd' => 'Lyd-podcast', 'video' => 'Video-podcast' );
	$tilkoeb_map = array( 'jingle-standard' => 'Jingle — standard', 'jingle-skraeddersyet' => 'Jingle — skræddersyet' );

	$status_label = array(
		'pending' => array( 'Afventer godkendelse', 'pending' ),
		'publish' => array( 'Godkendt', 'publish' ),
		'trash'   => array( 'Afvist / aflyst', 'trash' ),
	);
	$sl = $status_label[ $detail->post_status ] ?? array( $detail->post_status, 'default' );

	$approve_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=s247_approve_booking&booking=' . $detail->ID ),
		's247_approve_' . $detail->ID
	);
	$reject_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=s247_reject_booking&booking=' . $detail->ID ),
		's247_reject_' . $detail->ID
	);
?>
	<div class="sd-detail">
		<header class="sd-detail__head">
			<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings' ) ); ?>">← <?php esc_html_e( 'Tilbage til liste', 'studie247' ); ?></a>
			<div class="sd-detail__titlewrap">
				<span class="sd-status sd-status--<?php echo esc_attr( $sl[1] ); ?>"><?php echo esc_html( $sl[0] ); ?></span>
				<h2 class="sd-detail__title"><?php echo esc_html( $d_name ?: '(uden navn)' ); ?></h2>
				<p class="sd-detail__sub">
					<?php echo esc_html( $d_date ? date_i18n( 'l j. F Y', strtotime( $d_date ) ) : '—' ); ?>
					<?php if ( $d_start ) : ?> · <?php echo esc_html( $d_start ); ?><?php endif; ?>
					<?php if ( $d_dur ) : ?> · <?php echo esc_html( $d_dur ); ?><?php endif; ?>
				</p>
			</div>
			<div class="sd-detail__actions">
				<?php if ( 'pending' === $detail->post_status ) : ?>
					<a class="sd-btn" href="<?php echo esc_url( $approve_url ); ?>">✓ <?php esc_html_e( 'Godkend', 'studie247' ); ?></a>
					<a class="sd-btn sd-btn--danger" href="<?php echo esc_url( $reject_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Afvis denne forespørgsel?', 'studie247' ); ?>');">✕ <?php esc_html_e( 'Afvis', 'studie247' ); ?></a>
				<?php elseif ( 'publish' === $detail->post_status ) : ?>
					<a class="sd-btn sd-btn--danger" href="<?php echo esc_url( $reject_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Aflys denne booking?', 'studie247' ); ?>');">✕ <?php esc_html_e( 'Aflys', 'studie247' ); ?></a>
				<?php endif; ?>
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( get_edit_post_link( $detail->ID ) ); ?>"><?php esc_html_e( 'Rediger i wp-admin', 'studie247' ); ?> ↗</a>
			</div>
		</header>

		<div class="sd-detail__grid">
			<div class="sd-panel">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Kunde', 'studie247' ); ?></h2></header>
				<dl class="sd-dl">
					<div><dt><?php esc_html_e( 'Navn', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_name ?: '—' ); ?></dd></div>
					<div><dt>Email</dt><dd><?php echo $d_email ? '<a href="mailto:' . esc_attr( $d_email ) . '">' . esc_html( $d_email ) . '</a>' : '—'; ?></dd></div>
					<div><dt><?php esc_html_e( 'Telefon', 'studie247' ); ?></dt><dd><?php echo $d_phone ? '<a href="tel:' . esc_attr( $d_phone ) . '">' . esc_html( $d_phone ) . '</a>' : '—'; ?></dd></div>
					<?php if ( $d_company ) : ?><div><dt><?php esc_html_e( 'Virksomhed', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_company ); ?></dd></div><?php endif; ?>
					<?php if ( $d_cvr ) : ?><div><dt>CVR</dt><dd class="sd-mono"><?php echo esc_html( $d_cvr ); ?></dd></div><?php endif; ?>
					<?php if ( $d_newsletter ) : ?><div><dt><?php esc_html_e( 'Nyhedsbrev', 'studie247' ); ?></dt><dd>✓ <?php esc_html_e( 'tilmeldt', 'studie247' ); ?></dd></div><?php endif; ?>
				</dl>
			</div>

			<div class="sd-panel">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Formål', 'studie247' ); ?></h2></header>
				<dl class="sd-dl">
					<?php if ( $d_use_type && isset( $use_type_map[ $d_use_type ] ) ) : ?><div><dt><?php esc_html_e( 'Type', 'studie247' ); ?></dt><dd><?php echo esc_html( $use_type_map[ $d_use_type ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_edit_type && isset( $edit_map[ $d_edit_type ] ) ) : ?><div><dt><?php esc_html_e( 'Ønsker', 'studie247' ); ?></dt><dd><?php echo esc_html( $edit_map[ $d_edit_type ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_podcast_type && isset( $podcast_map[ $d_podcast_type ] ) ) : ?><div><dt>Podcast</dt><dd><?php echo esc_html( $podcast_map[ $d_podcast_type ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_tilkoeb && isset( $tilkoeb_map[ $d_tilkoeb ] ) ) : ?><div><dt>Tilkøb</dt><dd><?php echo esc_html( $tilkoeb_map[ $d_tilkoeb ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_video_count ) : ?><div><dt><?php esc_html_e( 'Antal videoer', 'studie247' ); ?></dt><dd><?php echo (int) $d_video_count; ?></dd></div><?php endif; ?>
					<?php if ( $d_video_dur ) : ?><div><dt><?php esc_html_e( 'Varighed/video', 'studie247' ); ?></dt><dd><?php echo (int) $d_video_dur; ?> min</dd></div><?php endif; ?>
					<?php if ( $d_format ) : ?><div><dt>Format</dt><dd><?php echo esc_html( $d_format ); ?></dd></div><?php endif; ?>
					<?php if ( $d_pid ) : ?><div><dt><?php esc_html_e( 'Udstyr', 'studie247' ); ?></dt><dd><?php echo esc_html( get_the_title( $d_pid ) ); ?></dd></div><?php endif; ?>
					<?php if ( ! $d_use_type && ! $d_pid ) : ?><div><dt>—</dt><dd class="sd-muted"><?php esc_html_e( 'Ingen formåls-detaljer angivet', 'studie247' ); ?></dd></div><?php endif; ?>
				</dl>
			</div>

			<?php if ( $can_revenue ) : ?>
				<div class="sd-panel">
					<header class="sd-panel__head"><h2><?php esc_html_e( 'Økonomi', 'studie247' ); ?></h2></header>
					<dl class="sd-dl">
						<div><dt><?php esc_html_e( 'Estimeret pris', 'studie247' ); ?></dt>
							<dd class="sd-mono"><?php echo $d_price ? esc_html( $fmt_dkk( $d_price ) ) : '—'; ?></dd></div>
						<?php if ( $d_internal ) : ?><div><dt><?php esc_html_e( 'Intern brug', 'studie247' ); ?></dt><dd>✓ <?php esc_html_e( 'ja', 'studie247' ); ?></dd></div><?php endif; ?>
					</dl>
				</div>
			<?php endif; ?>

			<?php if ( $d_notes ) : ?>
				<div class="sd-panel sd-panel--wide">
					<header class="sd-panel__head"><h2><?php esc_html_e( 'Kundens noter', 'studie247' ); ?></h2></header>
					<div class="sd-notes"><?php echo nl2br( esc_html( $d_notes ) ); ?></div>
				</div>
			<?php endif; ?>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Samtykke (GDPR)', 'studie247' ); ?></h2></header>
				<dl class="sd-dl sd-dl--row">
					<div><dt><?php esc_html_e( 'Accepteret', 'studie247' ); ?></dt><dd class="sd-mono"><?php echo esc_html( $d_consent_ts ?: '—' ); ?></dd></div>
					<div><dt>IP</dt><dd class="sd-mono"><?php echo esc_html( $d_consent_ip ?: '—' ); ?></dd></div>
				</dl>
			</div>
		</div>
	</div>
<?php
	return; // Detalje renderet — stop før tabel.
endif;

/* ───── Liste-visning ───── */
// Byg query-args
$args = array(
	'post_type'      => 'booking',
	'posts_per_page' => 100,
	'orderby'        => 'meta_value',
	'meta_key'       => '_s247_date',
	'order'          => 'DESC',
	'meta_query'     => array(
		// Studie-bookinger = ingen produkt eller tom produkt.
		'relation' => 'OR',
		array( 'key' => '_s247_produkt', 'compare' => 'NOT EXISTS' ),
		array( 'key' => '_s247_produkt', 'value' => '', 'compare' => '=' ),
	),
);
if ( 'all' === $status_param ) {
	$args['post_status'] = array( 'pending', 'publish', 'trash' );
} else {
	$args['post_status'] = $status_param;
}
if ( $search ) { $args['s'] = $search; }

$bookings = get_posts( $args );

// Tæl pr. status (til filter-pills)
$counts = array(
	'all'     => 0,
	'pending' => 0,
	'publish' => 0,
	'trash'   => 0,
);
$all_studio = get_posts( array(
	'post_type'      => 'booking',
	'post_status'    => array( 'pending', 'publish', 'trash' ),
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'meta_query'     => array(
		'relation' => 'OR',
		array( 'key' => '_s247_produkt', 'compare' => 'NOT EXISTS' ),
		array( 'key' => '_s247_produkt', 'value' => '', 'compare' => '=' ),
	),
) );
foreach ( $all_studio as $id ) {
	$status = get_post_status( $id );
	$counts['all']++;
	if ( isset( $counts[ $status ] ) ) { $counts[ $status ]++; }
}
?>

<div class="sd-toolbar">
	<div class="sd-filters">
		<?php
		$filter_items = array(
			'all'     => array( __( 'Alle', 'studie247' ), $counts['all'] ),
			'pending' => array( __( 'Afventer', 'studie247' ), $counts['pending'] ),
			'publish' => array( __( 'Godkendt', 'studie247' ), $counts['publish'] ),
			'trash'   => array( __( 'Afvist', 'studie247' ), $counts['trash'] ),
		);
		foreach ( $filter_items as $key => $row ) :
			$url = add_query_arg( array( 'view' => 'bookings', 'status' => $key ), home_url( '/dashboard/' ) );
			if ( $search ) { $url = add_query_arg( 'q', $search, $url ); }
			$is_active = $status_param === $key;
		?>
			<a class="sd-filter <?php echo $is_active ? 'is-active' : ''; ?><?php echo 'pending' === $key && $counts['pending'] > 0 ? ' sd-filter--alert' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $row[0] ); ?>
				<span class="sd-filter__count"><?php echo (int) $row[1]; ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<form class="sd-search" method="get" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
		<input type="hidden" name="view" value="bookings">
		<?php if ( 'all' !== $status_param ) : ?><input type="hidden" name="status" value="<?php echo esc_attr( $status_param ); ?>"><?php endif; ?>
		<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Søg navn, email eller telefon …', 'studie247' ); ?>">
		<button type="submit" class="sd-search__btn" aria-label="<?php esc_attr_e( 'Søg', 'studie247' ); ?>">⌕</button>
	</form>
</div>

<div class="sd-panel">
	<?php if ( empty( $bookings ) ) : ?>
		<p class="sd-panel__empty">
			<?php
			if ( $search ) {
				printf( esc_html__( 'Ingen bookinger matcher "%s".', 'studie247' ), esc_html( $search ) );
			} else {
				esc_html_e( 'Ingen bookinger i denne status.', 'studie247' );
			}
			?>
		</p>
	<?php else : ?>
		<table class="sd-table sd-table--list">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Status', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Kunde', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Dato', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Tid', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Formål', 'studie247' ); ?></th>
					<?php if ( $can_revenue ) : ?><th class="sd-table__right"><?php esc_html_e( 'Pris', 'studie247' ); ?></th><?php endif; ?>
					<th class="sd-table__right"><?php esc_html_e( 'Handling', 'studie247' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$use_type_map_row = array(
					'podcast' => 'Podcast', 'kursusvideo' => 'Kursus',
					'undervisningsvideo' => 'Undervisning',
					'some-content' => 'SoMe', 'annonce-video' => 'Annonce',
				);
				foreach ( $bookings as $b ) :
					$name   = get_post_meta( $b->ID, '_s247_name', true );
					$date   = get_post_meta( $b->ID, '_s247_date', true );
					$start  = get_post_meta( $b->ID, '_s247_start', true );
					$dur    = get_post_meta( $b->ID, '_s247_duration', true );
					$price  = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
					$use_t  = get_post_meta( $b->ID, '_s247_use_type', true );
					$internal = '1' === get_post_meta( $b->ID, '_s247_internal', true );
					$s      = $b->post_status;
					$s_label_map = array( 'pending' => __( 'Afventer', 'studie247' ), 'publish' => __( 'Godkendt', 'studie247' ), 'trash' => __( 'Afvist', 'studie247' ) );
					$s_label     = $s_label_map[ $s ] ?? $s;
					$approve_url = wp_nonce_url( admin_url( 'admin-post.php?action=s247_approve_booking&booking=' . $b->ID ), 's247_approve_' . $b->ID );
					$reject_url  = wp_nonce_url( admin_url( 'admin-post.php?action=s247_reject_booking&booking=' . $b->ID ), 's247_reject_' . $b->ID );
					$detail_href = esc_url( home_url( '/dashboard/?view=bookings&booking=' . $b->ID ) );
				?>
					<tr data-href="<?php echo $detail_href; ?>">
						<td><span class="sd-status sd-status--<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $s_label ); ?></span></td>
						<td class="sd-table__name">
							<a href="<?php echo $detail_href; ?>"><?php echo esc_html( $name ?: '—' ); ?></a>
							<?php if ( $internal ) : ?><span class="sd-tag">INTERN</span><?php endif; ?>
						</td>
						<td class="sd-muted"><?php echo esc_html( $date ? date_i18n( 'j. M Y', strtotime( $date ) ) : '—' ); ?></td>
						<td class="sd-muted"><?php echo esc_html( $start ? $start . ' · ' . $dur : '—' ); ?></td>
						<td class="sd-muted"><?php echo esc_html( $use_t && isset( $use_type_map_row[ $use_t ] ) ? $use_type_map_row[ $use_t ] : '—' ); ?></td>
						<?php if ( $can_revenue ) : ?>
							<td class="sd-table__right sd-mono"><?php echo $internal ? '<span class="sd-muted">0 kr</span>' : ( $price ? esc_html( $fmt_dkk( $price ) ) : '—' ); ?></td>
						<?php endif; ?>
						<td class="sd-table__right sd-actions">
							<?php if ( 'pending' === $s ) : ?>
								<a class="sd-btn sd-btn--sm" href="<?php echo esc_url( $approve_url ); ?>" onclick="event.stopPropagation();">✓</a>
								<a class="sd-btn sd-btn--sm sd-btn--danger" href="<?php echo esc_url( $reject_url ); ?>" onclick="event.stopPropagation();return confirm('<?php esc_attr_e( 'Afvis?', 'studie247' ); ?>');">✕</a>
							<?php elseif ( 'publish' === $s ) : ?>
								<a class="sd-btn sd-btn--sm sd-btn--danger" href="<?php echo esc_url( $reject_url ); ?>" onclick="event.stopPropagation();return confirm('<?php esc_attr_e( 'Aflys?', 'studie247' ); ?>');">✕</a>
							<?php else : ?>
								<a class="sd-btn sd-btn--sm" href="<?php echo esc_url( $approve_url ); ?>" onclick="event.stopPropagation();">↩</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

<script>
// Klik på række → gå til detalje (men ikke når man klikker på en knap/link inden i).
document.querySelectorAll('.sd-table--list tbody tr[data-href]').forEach(function(row){
	row.addEventListener('click', function(e){
		if (e.target.closest('a,button')) return;
		window.location = row.dataset.href;
	});
	row.style.cursor = 'pointer';
});
</script>
