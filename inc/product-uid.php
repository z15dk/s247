<?php
/**
 * Unik produkt-ID (S247-0001 osv.) for udlejning_item.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sørg for at et udlejnings-produkt har et unikt S247-xxxx ID.
 * Returnerer UID som streng.
 */
function studie247_ensure_product_uid( $post_id ) {
	$uid = get_post_meta( $post_id, '_s247_uid', true );
	if ( $uid ) { return $uid; }

	$counter = (int) get_option( 's247_uid_counter', 0 );
	$counter++;
	update_option( 's247_uid_counter', $counter );

	$uid = sprintf( 'S247-%04d', $counter );
	update_post_meta( $post_id, '_s247_uid', $uid );
	return $uid;
}

/**
 * Auto-tildel UID når et produkt oprettes eller gemmes første gang.
 */
add_action( 'save_post_udlejning_item', function ( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( 'auto-draft' === $post->post_status ) { return; }
	studie247_ensure_product_uid( $post_id );
}, 5, 2 );

/* ───────── Kolonne i produkt-listen ───────── */
add_filter( 'manage_udlejning_item_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		if ( 'title' === $k ) {
			$new['s247_uid'] = __( 'ID', 'studie247' );
		}
		$new[ $k ] = $v;
	}
	return $new;
} );

add_action( 'manage_udlejning_item_posts_custom_column', function ( $col, $post_id ) {
	if ( 's247_uid' === $col ) {
		$uid = get_post_meta( $post_id, '_s247_uid', true );
		if ( ! $uid ) { $uid = studie247_ensure_product_uid( $post_id ); }
		echo '<code style="font-size:12px;background:#f0f0f1;padding:2px 6px;border-radius:4px;">' . esc_html( $uid ) . '</code>';
	}
}, 10, 2 );

/* ───────── Meta-box på produktet: UID + historik-genveje ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_product_uid',
		__( 'Produkt-ID', 'studie247' ),
		'studie247_render_product_uid',
		'udlejning_item',
		'side',
		'high'
	);
} );

function studie247_render_product_uid( $post ) {
	$uid = get_post_meta( $post->ID, '_s247_uid', true );
	if ( ! $uid ) { $uid = studie247_ensure_product_uid( $post->ID ); }

	$booking_count = count( get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => array( 'publish', 'pending', 'trash' ),
		'posts_per_page' => -1,
		'meta_query'     => array( array( 'key' => '_s247_produkt_id', 'value' => $post->ID ) ),
		'fields'         => 'ids',
	) ) );

	$search_url = admin_url( 'edit.php?s=' . rawurlencode( $uid ) );
	?>
	<p style="margin:0 0 10px;font-size:24px;font-family:monospace;font-weight:700;color:#9E2B25;letter-spacing:-0.01em;">
		<?php echo esc_html( $uid ); ?>
	</p>
	<p style="margin:0 0 12px;color:#666;font-size:12px;">
		<?php esc_html_e( 'Unik reference — brug den når du logger fejl, skader, service eller kundeklager.', 'studie247' ); ?>
	</p>
	<p style="margin:0 0 6px;font-size:12px;">
		<strong><?php esc_html_e( 'Bookinger:', 'studie247' ); ?></strong> <?php echo (int) $booking_count; ?>
	</p>
	<p style="margin:0;">
		<a class="button button-small" href="<?php echo esc_url( $search_url ); ?>" target="_blank">
			<?php esc_html_e( 'Søg alt med dette ID', 'studie247' ); ?> →
		</a>
	</p>
	<?php
}

/* ───────── Vis UID på produkt-detalje siden (intern note for logged-in admin) ───────── */
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! is_singular( 'udlejning_item' ) ) { return; }
	$uid = get_post_meta( get_the_ID(), '_s247_uid', true );
	if ( ! $uid ) { return; }
	$bar->add_node( array(
		'id'    => 's247_product_uid',
		'title' => '📦 ' . $uid,
		'href'  => get_edit_post_link( get_the_ID() ),
	) );
}, 100 );

/* ───────── Tilføj UID til søgning ───────── */
add_filter( 'posts_search', function ( $search, $query ) {
	if ( is_admin() && $query->is_main_query() && 'udlejning_item' === $query->get( 'post_type' ) ) {
		$s = $query->get( 's' );
		if ( $s && preg_match( '/^S247-?\d+$/i', $s ) ) {
			global $wpdb;
			$like   = '%' . $wpdb->esc_like( strtoupper( str_replace( ' ', '', $s ) ) ) . '%';
			$search = $wpdb->prepare(
				" AND ({$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_s247_uid' AND meta_value LIKE %s))",
				$like
			);
		}
	}
	return $search;
}, 10, 2 );
