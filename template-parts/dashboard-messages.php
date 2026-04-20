<?php
/**
 * Dashboard — Beskeder-modul (kontakt_besked CPT).
 *
 * Liste + detalje/edit-view. Støtter filtre (alle/ubehandlede/håndteret/
 * arkiv), fritekst-søgning, håndteret-toggle og in-page edit.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! current_user_can( 'edit_posts' ) ) { return; }
if ( ! studie247_can_view_dash( 'messages' ) && ! current_user_can( 'manage_options' ) ) {
	echo '<div class="sd-empty"><p>' . esc_html__( 'Din bruger har ikke adgang til denne sektion.', 'studie247' ) . '</p></div>';
	return;
}

// Filter + søgning
$filter = isset( $_GET['filter'] ) ? sanitize_key( $_GET['filter'] ) : 'unhandled';
$allowed_filters = array( 'unhandled', 'handled', 'archive', 'all' );
if ( ! in_array( $filter, $allowed_filters, true ) ) { $filter = 'unhandled'; }
$search = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

// Detalje-visning
$detail_id = isset( $_GET['message'] ) ? (int) $_GET['message'] : 0;
$detail    = $detail_id ? get_post( $detail_id ) : null;
if ( $detail && 'kontakt_besked' !== $detail->post_type ) { $detail = null; }

if ( $detail ) :
	$m_name    = get_post_meta( $detail->ID, '_s247_name', true );
	$m_email   = get_post_meta( $detail->ID, '_s247_email', true );
	$m_phone   = get_post_meta( $detail->ID, '_s247_phone', true );
	$m_topic   = get_post_meta( $detail->ID, '_s247_topic', true );
	$m_side    = get_post_meta( $detail->ID, '_s247_side', true );
	$m_message = get_post_meta( $detail->ID, '_s247_message', true );
	$m_source  = get_post_meta( $detail->ID, '_s247_source', true );
	$m_notes   = get_post_meta( $detail->ID, '_s247_internal_notes', true );
	$m_handled = '1' === get_post_meta( $detail->ID, '_s247_msg_handled', true );
	$m_handled_at = get_post_meta( $detail->ID, '_s247_msg_handled_at', true );
	$m_handled_by = (int) get_post_meta( $detail->ID, '_s247_msg_handled_by', true );
	$m_consent_ts = get_post_meta( $detail->ID, '_s247_consent_timestamp', true );
	$m_consent_ip = get_post_meta( $detail->ID, '_s247_consent_ip', true );
	$is_trash     = 'trash' === $detail->post_status;
	$saved        = isset( $_GET['saved'] ) && '1' === $_GET['saved'];

	$reply_subject = 'Re: ' . ( $m_topic ?: __( 'Din henvendelse', 'studie247' ) );
	$reply_body    = "Hej " . ( $m_name ?: '' ) . ",\n\nTak for din besked. ";
	$mailto = 'mailto:' . rawurlencode( $m_email ) . '?subject=' . rawurlencode( $reply_subject ) . '&body=' . rawurlencode( $reply_body );
?>
	<form method="post" action="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="sd-detail sd-edit">
		<?php wp_nonce_field( 's247_dash_msg_' . $detail->ID ); ?>
		<input type="hidden" name="s247_dash_save_message" value="<?php echo (int) $detail->ID; ?>">

		<header class="sd-detail__head">
			<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/dashboard/?view=messages' ) ); ?>">← <?php esc_html_e( 'Tilbage', 'studie247' ); ?></a>
			<div class="sd-detail__titlewrap">
				<?php if ( $m_handled ) : ?>
					<span class="sd-status sd-status--publish">✓ <?php esc_html_e( 'Håndteret', 'studie247' ); ?></span>
				<?php elseif ( $is_trash ) : ?>
					<span class="sd-status sd-status--trash"><?php esc_html_e( 'Arkiveret', 'studie247' ); ?></span>
				<?php else : ?>
					<span class="sd-status sd-status--pending"><?php esc_html_e( 'Ubehandlet', 'studie247' ); ?></span>
				<?php endif; ?>
				<h2 class="sd-detail__title"><?php echo esc_html( $m_name ?: '(uden navn)' ); ?></h2>
				<p class="sd-detail__sub">
					<?php echo esc_html( get_the_date( 'l j. F Y · H:i', $detail ) ); ?>
					<?php if ( $m_topic ) : ?> · <strong><?php echo esc_html( $m_topic ); ?></strong><?php endif; ?>
					<?php if ( $m_source ) : ?> · <span class="sd-muted">via <?php echo esc_html( '/' . $m_source . '/' ); ?></span><?php endif; ?>
				</p>
			</div>
			<div class="sd-detail__actions">
				<?php if ( $m_email ) : ?>
					<a class="sd-btn" href="<?php echo esc_url( $mailto ); ?>">✉ <?php esc_html_e( 'Svar', 'studie247' ); ?></a>
				<?php endif; ?>
				<button type="submit" class="sd-btn"><?php esc_html_e( 'Gem interne ændringer', 'studie247' ); ?></button>
			</div>
		</header>

		<?php if ( $saved ) : ?>
			<div class="sd-notice sd-notice--success">✓ <?php esc_html_e( 'Ændringer gemt.', 'studie247' ); ?></div>
		<?php endif; ?>

		<div class="sd-detail__grid">
			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Beskeden', 'studie247' ); ?></h2>
					<span class="sd-panel__hint">🔒 <?php esc_html_e( 'Kundens oprindelige ord — kan ikke ændres.', 'studie247' ); ?></span>
				</header>
				<div class="sd-readonly-message"><?php echo nl2br( esc_html( $m_message ?: '—' ) ); ?></div>
			</div>

			<div class="sd-panel">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Afsender', 'studie247' ); ?></h2>
					<span class="sd-panel__hint">🔒 <?php esc_html_e( 'Fra kontakt-form', 'studie247' ); ?></span>
				</header>
				<dl class="sd-dl">
					<div><dt><?php esc_html_e( 'Navn', 'studie247' ); ?></dt><dd><?php echo esc_html( $m_name ?: '—' ); ?></dd></div>
					<div><dt>E-mail</dt><dd><?php echo $m_email ? '<a href="mailto:' . esc_attr( $m_email ) . '">' . esc_html( $m_email ) . '</a>' : '—'; ?></dd></div>
					<div><dt><?php esc_html_e( 'Telefon', 'studie247' ); ?></dt><dd><?php echo $m_phone ? '<a href="tel:' . esc_attr( $m_phone ) . '">' . esc_html( $m_phone ) . '</a>' : '—'; ?></dd></div>
					<div><dt><?php esc_html_e( 'Emne', 'studie247' ); ?></dt><dd><?php echo esc_html( $m_topic ?: '—' ); ?></dd></div>
					<?php if ( $m_side ) : ?><div><dt><?php esc_html_e( 'Valgte', 'studie247' ); ?></dt><dd><?php echo esc_html( 'Side ' . $m_side ); ?></dd></div><?php endif; ?>
				</dl>
			</div>

			<div class="sd-panel">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Status', 'studie247' ); ?></h2></header>
				<div class="sd-form">
					<label class="sd-toggle">
						<input type="checkbox" name="_s247_msg_handled" value="1" <?php checked( $m_handled ); ?>>
						<span class="sd-toggle__track"><span class="sd-toggle__thumb"></span></span>
						<span class="sd-toggle__label">
							<strong><?php esc_html_e( 'Markér som håndteret', 'studie247' ); ?></strong>
							<em><?php esc_html_e( 'Fjerner den fra "Ubehandlede"-listen.', 'studie247' ); ?></em>
						</span>
					</label>
					<?php if ( $m_handled && $m_handled_at ) :
						$handler = $m_handled_by ? get_userdata( $m_handled_by ) : null;
					?>
						<p class="sd-hint">✓ <?php printf( esc_html__( 'Håndteret %s%s', 'studie247' ),
							esc_html( mysql2date( 'j. M Y H:i', $m_handled_at ) ),
							$handler ? ' af ' . esc_html( $handler->display_name ) : ''
						); ?></p>
					<?php endif; ?>
					<label class="sd-field"><span><?php esc_html_e( 'Arkivering', 'studie247' ); ?></span>
						<select name="_s247_post_status">
							<option value="publish" <?php selected( $detail->post_status, 'publish' ); ?>><?php esc_html_e( 'Aktiv', 'studie247' ); ?></option>
							<option value="trash" <?php selected( $detail->post_status, 'trash' ); ?>><?php esc_html_e( 'Arkiveret / slettet', 'studie247' ); ?></option>
						</select>
					</label>
				</div>
			</div>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head">
					<h2><?php esc_html_e( 'Interne noter', 'studie247' ); ?></h2>
					<span class="sd-panel__hint"><?php esc_html_e( 'Kun synlige for teamet.', 'studie247' ); ?></span>
				</header>
				<div class="sd-form">
					<textarea name="_s247_internal_notes" rows="4" class="sd-textarea" placeholder="<?php esc_attr_e( 'Noter, opfølgning, aftaler …', 'studie247' ); ?>"><?php echo esc_textarea( $m_notes ); ?></textarea>
				</div>
			</div>

			<div class="sd-panel sd-panel--wide">
				<header class="sd-panel__head"><h2><?php esc_html_e( 'Samtykke (GDPR)', 'studie247' ); ?></h2></header>
				<dl class="sd-dl sd-dl--row">
					<div><dt><?php esc_html_e( 'Accepteret', 'studie247' ); ?></dt><dd class="sd-mono"><?php echo esc_html( $m_consent_ts ?: '—' ); ?></dd></div>
					<div><dt>IP</dt><dd class="sd-mono"><?php echo esc_html( $m_consent_ip ?: '—' ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Kilde', 'studie247' ); ?></dt><dd><?php echo esc_html( $m_source ?: '—' ); ?></dd></div>
				</dl>
			</div>
		</div>

		<div class="sd-detail__footer">
			<button type="submit" class="sd-btn sd-btn--lg"><?php esc_html_e( 'Gem interne ændringer', 'studie247' ); ?></button>
		</div>
	</form>
<?php
	return;
endif;

/* ───── Liste-visning ───── */
$args = array(
	'post_type'      => 'kontakt_besked',
	'posts_per_page' => 150,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

switch ( $filter ) {
	case 'unhandled':
		$args['post_status'] = 'publish';
		$args['meta_query']  = array(
			'relation' => 'OR',
			array( 'key' => '_s247_msg_handled', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_s247_msg_handled', 'value' => '', 'compare' => '=' ),
		);
		break;
	case 'handled':
		$args['post_status'] = 'publish';
		$args['meta_query']  = array( array( 'key' => '_s247_msg_handled', 'value' => '1' ) );
		break;
	case 'archive':
		$args['post_status'] = 'trash';
		break;
	case 'all':
	default:
		$args['post_status'] = array( 'publish', 'trash' );
}
if ( $search ) { $args['s'] = $search; }

$messages = get_posts( $args );

// Tællere til filter-pills
$count_unhandled = count( get_posts( array(
	'post_type' => 'kontakt_besked',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'fields' => 'ids',
	'meta_query' => array(
		'relation' => 'OR',
		array( 'key' => '_s247_msg_handled', 'compare' => 'NOT EXISTS' ),
		array( 'key' => '_s247_msg_handled', 'value' => '', 'compare' => '=' ),
	),
) ) );
$count_handled = count( get_posts( array(
	'post_type' => 'kontakt_besked',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'fields' => 'ids',
	'meta_query' => array( array( 'key' => '_s247_msg_handled', 'value' => '1' ) ),
) ) );
$count_archive = (int) wp_count_posts( 'kontakt_besked' )->trash;
$count_all     = $count_unhandled + $count_handled + $count_archive;
?>

<div class="sd-toolbar">
	<div class="sd-filters">
		<?php
		$filters = array(
			'unhandled' => array( __( 'Ubehandlede', 'studie247' ), $count_unhandled, 'alert' ),
			'handled'   => array( __( 'Håndteret', 'studie247' ), $count_handled, '' ),
			'archive'   => array( __( 'Arkiv', 'studie247' ), $count_archive, '' ),
			'all'       => array( __( 'Alle', 'studie247' ), $count_all, '' ),
		);
		foreach ( $filters as $key => $row ) :
			$url = add_query_arg( array( 'view' => 'messages', 'filter' => $key ), home_url( '/dashboard/' ) );
			if ( $search ) { $url = add_query_arg( 'q', $search, $url ); }
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
		<input type="hidden" name="view" value="messages">
		<input type="hidden" name="filter" value="<?php echo esc_attr( $filter ); ?>">
		<input type="search" name="q" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Søg navn, email, besked …', 'studie247' ); ?>">
		<button type="submit" class="sd-search__btn" aria-label="<?php esc_attr_e( 'Søg', 'studie247' ); ?>">⌕</button>
	</form>
</div>

<div class="sd-panel">
	<?php if ( empty( $messages ) ) : ?>
		<p class="sd-panel__empty">
			<?php echo $search ? sprintf( esc_html__( 'Ingen beskeder matcher "%s".', 'studie247' ), esc_html( $search ) ) : esc_html__( 'Ingen beskeder her.', 'studie247' ); ?>
		</p>
	<?php else : ?>
		<table class="sd-table sd-table--list">
			<thead>
				<tr>
					<th></th>
					<th><?php esc_html_e( 'Afsender', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Emne', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Uddrag', 'studie247' ); ?></th>
					<th><?php esc_html_e( 'Modtaget', 'studie247' ); ?></th>
					<th class="sd-table__right"><?php esc_html_e( 'Handling', 'studie247' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $messages as $m ) :
					$name    = get_post_meta( $m->ID, '_s247_name', true );
					$topic   = get_post_meta( $m->ID, '_s247_topic', true );
					$msg     = get_post_meta( $m->ID, '_s247_message', true );
					$email   = get_post_meta( $m->ID, '_s247_email', true );
					$source  = get_post_meta( $m->ID, '_s247_source', true );
					$handled = '1' === get_post_meta( $m->ID, '_s247_msg_handled', true );
					$arch    = 'trash' === $m->post_status;
					$detail_href = esc_url( home_url( '/dashboard/?view=messages&message=' . $m->ID ) );
					$mailto = $email ? 'mailto:' . $email : '';
				?>
					<tr data-href="<?php echo $detail_href; ?>" class="<?php echo $handled ? 'sd-row--muted' : ''; ?>">
						<td class="sd-row-dot">
							<?php if ( $arch ) : ?>
								<span class="sd-dot sd-dot--trash" title="<?php esc_attr_e( 'Arkiveret', 'studie247' ); ?>"></span>
							<?php elseif ( $handled ) : ?>
								<span class="sd-dot sd-dot--ok" title="<?php esc_attr_e( 'Håndteret', 'studie247' ); ?>"></span>
							<?php else : ?>
								<span class="sd-dot sd-dot--new" title="<?php esc_attr_e( 'Ubehandlet', 'studie247' ); ?>"></span>
							<?php endif; ?>
						</td>
						<td class="sd-table__name">
							<a href="<?php echo $detail_href; ?>"><?php echo esc_html( $name ?: '—' ); ?></a>
							<?php if ( $email ) : ?><span class="sd-row-sub"><?php echo esc_html( $email ); ?></span><?php endif; ?>
						</td>
						<td><?php echo esc_html( $topic ?: '—' ); ?></td>
						<td class="sd-muted sd-row-excerpt"><?php echo esc_html( wp_trim_words( (string) $msg, 10, '…' ) ); ?></td>
						<td class="sd-muted"><?php echo esc_html( get_the_date( 'j. M H:i', $m ) ); ?></td>
						<td class="sd-table__right sd-actions">
							<?php if ( $mailto ) : ?>
								<a class="sd-btn sd-btn--sm" href="<?php echo esc_url( $mailto ); ?>" onclick="event.stopPropagation();" title="<?php esc_attr_e( 'Svar', 'studie247' ); ?>">✉</a>
							<?php endif; ?>
							<a class="sd-btn sd-btn--sm sd-btn--ghost" href="<?php echo $detail_href; ?>"><?php esc_html_e( 'Åbn', 'studie247' ); ?></a>
						</td>
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
