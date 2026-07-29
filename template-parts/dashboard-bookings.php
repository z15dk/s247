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
if ( ! studie247_can_view_dash( 'studio' ) && ! current_user_can( 'manage_options' ) ) {
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
	<?php
	$saved = isset( $_GET['saved'] ) && '1' === $_GET['saved'];
	$status_options = array(
		'pending' => __( 'Afventer godkendelse', 'studie247' ),
		'publish' => __( 'Godkendt', 'studie247' ),
		'trash'   => __( 'Afvist / aflyst', 'studie247' ),
	);
	$use_type_options = array(
		'' => __( '— Ikke angivet —', 'studie247' ),
		'podcast' => 'Podcast', 'kursusvideo' => 'Kursusvideo',
		'undervisningsvideo' => 'Undervisningsvideo',
		'some-content' => 'SoMe Content', 'annonce-video' => 'Annonce-video',
	);
	$edit_options = array( '' => __( '— Ikke angivet —', 'studie247' ), 'redigering' => 'Redigering', 'kun-filer' => 'Kun filerne' );
	$podcast_options = array( '' => '—', 'lyd' => 'Lyd-podcast', 'video' => 'Video-podcast' );
	$tilkoeb_options = array( '' => __( 'Ingen', 'studie247' ), 'jingle-standard' => 'Jingle — standard', 'jingle-skraeddersyet' => 'Jingle — skræddersyet' );
	$format_options  = array( '' => '—', '16:9' => '16:9', '9:16' => '9:16', '4:5' => '4:5', '1:1' => '1:1' );
	?>
	<form method="post" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="sd-detail sd-edit">
		<?php wp_nonce_field( 's247_dash_edit_' . $detail->ID ); ?>
		<input type="hidden" name="s247_dash_save_booking" value="<?php echo (int) $detail->ID; ?>">

		<header class="sd-detail__head">
			<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings' ) ); ?>">← <?php esc_html_e( 'Tilbage', 'studie247' ); ?></a>
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
					<a class="sd-btn" href="<?php echo esc_url( $approve_url ); ?>">✓ <?php esc_html_e( 'Godkend + mail', 'studie247' ); ?></a>
					<a class="sd-btn sd-btn--danger" href="<?php echo esc_url( $reject_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Afvis + send mail?', 'studie247' ); ?>');">✕ <?php esc_html_e( 'Afvis + mail', 'studie247' ); ?></a>
				<?php elseif ( 'publish' === $detail->post_status ) : ?>
					<a class="sd-btn sd-btn--danger" href="<?php echo esc_url( $reject_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Aflys + send mail?', 'studie247' ); ?>');">✕ <?php esc_html_e( 'Aflys + mail', 'studie247' ); ?></a>
				<?php endif; ?>
				<button type="submit" class="sd-btn"><?php esc_html_e( 'Gem interne ændringer', 'studie247' ); ?></button>
			</div>
		</header>

		<?php if ( $saved ) : ?>
			<div class="sd-notice sd-notice--success">✓ <?php esc_html_e( 'Ændringer gemt.', 'studie247' ); ?></div>
		<?php endif; ?>

		<div class="sd-detail__grid">
			<div class="sd-panel">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Kunde', 'studie247' ); ?></h2>
					<span class="sd-panel__hint">🔒 <?php esc_html_e( 'Fra booking', 'studie247' ); ?></span>
				</header>
				<dl class="sd-dl">
					<div><dt><?php esc_html_e( 'Navn', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_name ?: '—' ); ?></dd></div>
					<div><dt>E-mail</dt><dd><?php echo $d_email ? '<a href="mailto:' . esc_attr( $d_email ) . '">' . esc_html( $d_email ) . '</a>' : '—'; ?></dd></div>
					<div><dt><?php esc_html_e( 'Telefon', 'studie247' ); ?></dt><dd><?php echo $d_phone ? '<a href="tel:' . esc_attr( $d_phone ) . '">' . esc_html( $d_phone ) . '</a>' : '—'; ?></dd></div>
					<?php if ( $d_company ) : ?><div><dt><?php esc_html_e( 'Virksomhed', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_company ); ?></dd></div><?php endif; ?>
					<?php if ( $d_cvr ) : ?><div><dt>CVR</dt><dd class="sd-mono"><?php echo esc_html( $d_cvr ); ?></dd></div><?php endif; ?>
					<?php if ( $d_newsletter ) : ?><div><dt><?php esc_html_e( 'Nyhedsbrev', 'studie247' ); ?></dt><dd>✓ <?php esc_html_e( 'tilmeldt', 'studie247' ); ?></dd></div><?php endif; ?>
				</dl>
			</div>

			<div class="sd-panel">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Tidsplan', 'studie247' ); ?></h2>
					<span class="sd-panel__hint">🔒 <?php esc_html_e( 'Kundens valg', 'studie247' ); ?></span>
				</header>
				<dl class="sd-dl">
					<div><dt><?php esc_html_e( 'Dato', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_date ? date_i18n( 'l j. F Y', strtotime( $d_date ) ) : '—' ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Start-tid', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_start ?: '—' ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Varighed', 'studie247' ); ?></dt><dd><?php echo esc_html( $d_dur ?: '—' ); ?></dd></div>
				</dl>
				<div class="sd-form">
					<label class="sd-field"><span><?php esc_html_e( 'Status (intern)', 'studie247' ); ?></span>
						<select name="_s247_post_status">
							<?php foreach ( $status_options as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $detail->post_status, $val ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select></label>
				</div>
				<p class="sd-hint"><?php esc_html_e( 'Skift status uden mail her, eller brug "Godkend/Afvis + mail"-knapperne ovenfor hvis kunden skal have besked.', 'studie247' ); ?></p>
			</div>

			<?php if ( $can_revenue ) : ?>
				<div class="sd-panel">
					<header class="sd-panel__head"><h2><?php esc_html_e( 'Økonomi', 'studie247' ); ?></h2></header>
					<div class="sd-form">
						<label class="sd-field"><span><?php esc_html_e( 'Estimeret pris (kr)', 'studie247' ); ?></span>
							<input type="number" name="_s247_estimated_price" value="<?php echo (int) $d_price; ?>" min="0" step="1" <?php echo $d_internal ? 'disabled' : ''; ?>></label>
						<label class="sd-toggle">
							<input type="checkbox" name="_s247_internal" value="1" <?php checked( $d_internal ); ?>>
							<span class="sd-toggle__track"><span class="sd-toggle__thumb"></span></span>
							<span class="sd-toggle__label">
								<strong><?php esc_html_e( 'Intern brug', 'studie247' ); ?></strong>
								<em><?php esc_html_e( 'Nulstiller pris til 0 kr. Oprindelig pris gendannes når fluebenet fjernes.', 'studie247' ); ?></em>
							</span>
						</label>
						<?php
						$backup_price = (int) get_post_meta( $detail->ID, '_s247_estimated_price_original', true );
						if ( $d_internal && $backup_price ) :
						?>
							<p class="sd-hint">💾 <?php printf( esc_html__( 'Oprindelig pris gemt: %s', 'studie247' ), esc_html( $fmt_dkk( $backup_price ) ) ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Formål', 'studie247' ); ?></h2>
					<span class="sd-panel__hint">🔒 <?php esc_html_e( 'Kundens valg', 'studie247' ); ?></span>
				</header>
				<dl class="sd-dl sd-dl--row">
					<?php
					$use_map_read = array(
						'podcast' => 'Podcast', 'kursusvideo' => 'Kursusvideo',
						'undervisningsvideo' => 'Undervisningsvideo',
						'some-content' => 'SoMe Content', 'annonce-video' => 'Annonce-video',
					);
					$edit_map_read    = array( 'redigering' => 'Redigering', 'kun-filer' => 'Kun filerne' );
					$podcast_map_read = array( 'lyd' => 'Lyd-podcast', 'video' => 'Video-podcast' );
					$tilkoeb_map_read = array( 'jingle-standard' => 'Jingle — standard', 'jingle-skraeddersyet' => 'Jingle — skræddersyet' );
					?>
					<?php if ( $d_use_type && isset( $use_map_read[ $d_use_type ] ) ) : ?><div><dt><?php esc_html_e( 'Type', 'studie247' ); ?></dt><dd><?php echo esc_html( $use_map_read[ $d_use_type ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_edit_type && isset( $edit_map_read[ $d_edit_type ] ) ) : ?><div><dt><?php esc_html_e( 'Ønsker', 'studie247' ); ?></dt><dd><?php echo esc_html( $edit_map_read[ $d_edit_type ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_podcast_type && isset( $podcast_map_read[ $d_podcast_type ] ) ) : ?><div><dt><?php esc_html_e( 'Podcast-type', 'studie247' ); ?></dt><dd><?php echo esc_html( $podcast_map_read[ $d_podcast_type ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_tilkoeb && isset( $tilkoeb_map_read[ $d_tilkoeb ] ) ) : ?><div><dt><?php esc_html_e( 'Tilkøb', 'studie247' ); ?></dt><dd><?php echo esc_html( $tilkoeb_map_read[ $d_tilkoeb ] ); ?></dd></div><?php endif; ?>
					<?php if ( $d_video_count ) : ?><div><dt><?php esc_html_e( 'Antal videoer', 'studie247' ); ?></dt><dd><?php echo (int) $d_video_count; ?></dd></div><?php endif; ?>
					<?php if ( $d_video_dur ) : ?><div><dt><?php esc_html_e( 'Varighed/video', 'studie247' ); ?></dt><dd><?php echo (int) $d_video_dur; ?> min</dd></div><?php endif; ?>
					<?php if ( $d_format ) : ?><div><dt>Format</dt><dd><?php echo esc_html( $d_format ); ?></dd></div><?php endif; ?>
					<?php if ( $d_pid ) : ?><div><dt><?php esc_html_e( 'Udstyr', 'studie247' ); ?></dt><dd><?php echo esc_html( get_the_title( $d_pid ) ); ?></dd></div><?php endif; ?>
					<?php if ( ! $d_use_type && ! $d_pid && ! $d_edit_type ) : ?><div><dt>—</dt><dd class="sd-muted"><?php esc_html_e( 'Ingen formåls-detaljer angivet', 'studie247' ); ?></dd></div><?php endif; ?>
				</dl>
			</div>

			<?php if ( $d_notes ) : ?>
				<div class="sd-panel sd-panel--wide">
					<header class="sd-panel__head">
						<h2><?php esc_html_e( 'Kundens noter', 'studie247' ); ?></h2>
						<span class="sd-panel__hint">🔒 <?php esc_html_e( 'Kundens oprindelige ord', 'studie247' ); ?></span>
					</header>
					<div class="sd-readonly-message"><?php echo nl2br( esc_html( $d_notes ) ); ?></div>
				</div>
			<?php endif; ?>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Samtykke (GDPR)', 'studie247' ); ?></h2></header>
				<dl class="sd-dl sd-dl--row">
					<div><dt><?php esc_html_e( 'Accepteret', 'studie247' ); ?></dt><dd class="sd-mono"><?php echo esc_html( $d_consent_ts ?: '—' ); ?></dd></div>
					<div><dt>IP</dt><dd class="sd-mono"><?php echo esc_html( $d_consent_ip ?: '—' ); ?></dd></div>
					<?php if ( $d_newsletter ) : ?>
						<div><dt><?php esc_html_e( 'Nyhedsbrev', 'studie247' ); ?></dt><dd>✓ <?php esc_html_e( 'tilmeldt', 'studie247' ); ?></dd></div>
					<?php endif; ?>
				</dl>
			</div>
		</div>

		<div class="sd-detail__footer">
			<button type="submit" class="sd-btn sd-btn--lg"><?php esc_html_e( 'Gem interne ændringer', 'studie247' ); ?></button>
		</div>
	</form>
<?php
	return; // Detalje renderet — stop før tabel.
endif;

/* ───── Månedskalender øverst ───── */
$cal_ym = isset( $_GET['cal_m'] ) ? sanitize_text_field( wp_unslash( $_GET['cal_m'] ) ) : date( 'Y-m' );
if ( ! preg_match( '/^\d{4}-\d{2}$/', $cal_ym ) ) { $cal_ym = date( 'Y-m' ); }
$cal_first_ts = strtotime( $cal_ym . '-01' );
$cal_days     = (int) date( 't', $cal_first_ts );
$cal_first_wd = (int) date( 'N', $cal_first_ts ); // 1=man .. 7=søn
$cal_prev_ym  = date( 'Y-m', strtotime( '-1 month', $cal_first_ts ) );
$cal_next_ym  = date( 'Y-m', strtotime( '+1 month', $cal_first_ts ) );
$cal_label    = ucfirst( date_i18n( 'F Y', $cal_first_ts ) );
$cal_today    = date( 'Y-m-d' );

// Hent alle studie-bookinger i måneden.
$cal_start = $cal_ym . '-01';
$cal_end   = date( 'Y-m-t', $cal_first_ts );
$cal_posts = get_posts( array(
	'post_type'      => 'booking',
	'post_status'    => array( 'pending', 'publish' ),
	'posts_per_page' => -1,
	'meta_query'     => array(
		'relation' => 'AND',
		array(
			'relation' => 'OR',
			array( 'key' => '_s247_produkt', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_s247_produkt', 'value' => '', 'compare' => '=' ),
		),
		array( 'key' => '_s247_date', 'value' => array( $cal_start, $cal_end ), 'type' => 'DATE', 'compare' => 'BETWEEN' ),
	),
) );
$cal_map = array(); // iso-date → array of {name, status, id}
foreach ( $cal_posts as $p ) {
	$d = get_post_meta( $p->ID, '_s247_date', true );
	if ( ! $d ) { continue; }
	$cal_map[ $d ][] = array(
		'id'     => $p->ID,
		'name'   => get_post_meta( $p->ID, '_s247_name', true ) ?: '(uden navn)',
		'start'  => get_post_meta( $p->ID, '_s247_start', true ),
		'status' => $p->post_status,
	);
}
?>

<div class="sd-cal">
	<header class="sd-cal__head">
		<div class="sd-cal__title"><?php echo esc_html( $cal_label ); ?></div>
		<div class="sd-cal__nav">
			<a class="sd-cal__arrow" href="<?php echo esc_url( add_query_arg( array( 'view' => 'bookings', 'cal_m' => $cal_prev_ym ), home_url( '/dashboard/' ) ) ); ?>" aria-label="<?php esc_attr_e( 'Forrige måned', 'studie247' ); ?>">‹</a>
			<?php if ( $cal_ym !== date( 'Y-m' ) ) : ?>
				<a class="sd-cal__today" href="<?php echo esc_url( add_query_arg( array( 'view' => 'bookings' ), home_url( '/dashboard/' ) ) ); ?>"><?php esc_html_e( 'I dag', 'studie247' ); ?></a>
			<?php endif; ?>
			<a class="sd-cal__arrow" href="<?php echo esc_url( add_query_arg( array( 'view' => 'bookings', 'cal_m' => $cal_next_ym ), home_url( '/dashboard/' ) ) ); ?>" aria-label="<?php esc_attr_e( 'Næste måned', 'studie247' ); ?>">›</a>
		</div>
	</header>
	<div class="sd-cal__weeknames" aria-hidden="true">
		<span>Man</span><span>Tir</span><span>Ons</span><span>Tor</span><span>Fre</span><span>Lør</span><span>Søn</span>
	</div>
	<div class="sd-cal__grid">
		<?php
		// Tomme celler før måneden starter
		for ( $e = 1; $e < $cal_first_wd; $e++ ) {
			echo '<div class="sd-cal__cell sd-cal__cell--empty"></div>';
		}
		for ( $d = 1; $d <= $cal_days; $d++ ) {
			$iso = sprintf( '%s-%02d', $cal_ym, $d );
			$ts  = strtotime( $iso );
			$classes = array( 'sd-cal__cell' );
			if ( $iso === $cal_today ) { $classes[] = 'sd-cal__cell--today'; }
			if ( $ts < strtotime( $cal_today ) ) { $classes[] = 'sd-cal__cell--past'; }
			$wd = (int) date( 'N', $ts );
			if ( $wd >= 6 ) { $classes[] = 'sd-cal__cell--weekend'; }
			$has = ! empty( $cal_map[ $iso ] );
			if ( $has ) { $classes[] = 'sd-cal__cell--has'; }
			?>
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<span class="sd-cal__num"><?php echo (int) $d; ?></span>
				<?php if ( $has ) : ?>
					<div class="sd-cal__bookings">
						<?php foreach ( $cal_map[ $iso ] as $bk ) : ?>
							<a class="sd-cal__booking sd-cal__booking--<?php echo esc_attr( $bk['status'] ); ?>"
								href="<?php echo esc_url( home_url( '/dashboard/?view=bookings&booking=' . $bk['id'] ) ); ?>"
								title="<?php echo esc_attr( $bk['name'] . ( $bk['start'] ? ' · ' . $bk['start'] : '' ) ); ?>">
								<?php if ( $bk['start'] ) : ?><span class="sd-cal__time"><?php echo esc_html( substr( $bk['start'], 0, 5 ) ); ?></span><?php endif; ?>
								<span class="sd-cal__name"><?php echo esc_html( $bk['name'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php } ?>
	</div>
	<div class="sd-cal__legend">
		<span><span class="sd-cal__legend-dot sd-cal__legend-dot--publish"></span> <?php esc_html_e( 'Godkendt', 'studie247' ); ?></span>
		<span><span class="sd-cal__legend-dot sd-cal__legend-dot--pending"></span> <?php esc_html_e( 'Afventer', 'studie247' ); ?></span>
	</div>
</div>

<?php
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
