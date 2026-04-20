<?php
/**
 * Dashboard-tilladelser: admin kan pr. bruger vælge hvilke sektioner
 * af /dashboard/ brugeren må se.
 *
 * Sektioner styret via user-meta:
 *   _s247_dash_view_rental      — udlejnings-stats + vare-top
 *   _s247_dash_view_studio      — studie-booking-stats
 *   _s247_dash_view_messages    — kontakt-beskeder
 *   _s247_dash_view_revenue     — omsætning (måned/år)
 *   _s247_dash_view_pending     — pending-listen
 *
 * Administratorer (manage_options) ser alt uanset flag.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alle dashboard-sektioner og deres label.
 */
function studie247_dash_sections() {
	return array(
		'rental'    => __( 'Udlejning (stats + top-varer)', 'studie247' ),
		'studio'    => __( 'Studie-bookinger', 'studie247' ),
		'messages'  => __( 'Kontakt-beskeder', 'studie247' ),
		'revenue'   => __( 'Omsætning (måned/år)', 'studie247' ),
		'pending'   => __( 'Afventende forespørgsler', 'studie247' ),
		'customers' => __( 'Kunder (CRM)', 'studie247' ),
	);
}

/**
 * Må denne bruger se en given dashboard-sektion?
 */
function studie247_can_view_dash( $section, $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) { return false; }
	// Administratorer ser alt.
	if ( user_can( $user_id, 'manage_options' ) ) { return true; }
	if ( ! user_can( $user_id, 'edit_posts' ) ) { return false; }
	return '1' === get_user_meta( $user_id, '_s247_dash_view_' . $section, true );
}

/* ───────── User-profil UI ───────── */
add_action( 'show_user_profile', 'studie247_render_dash_perms' );
add_action( 'edit_user_profile', 'studie247_render_dash_perms' );

function studie247_render_dash_perms( $user ) {
	// Kun admins kan redigere andres dashboard-tilladelser.
	// På egen profil vises kun en read-only-visning.
	$is_admin_editing = current_user_can( 'edit_users' ) && get_current_user_id() !== (int) $user->ID;
	$can_edit         = current_user_can( 'manage_options' );
	?>
	<h2><?php esc_html_e( 'Studie 247 — Dashboard-tilladelser', 'studie247' ); ?></h2>
	<p class="description" style="max-width:620px;">
		<?php esc_html_e( 'Bestem hvilke sektioner af /dashboard/ denne bruger kan se. Administratorer ser altid alt, uanset flueben.', 'studie247' ); ?>
	</p>
	<table class="form-table" role="presentation">
		<?php foreach ( studie247_dash_sections() as $key => $label ) :
			$val = '1' === get_user_meta( $user->ID, '_s247_dash_view_' . $key, true );
			?>
			<tr>
				<th scope="row"><?php echo esc_html( $label ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="s247_dash_view[<?php echo esc_attr( $key ); ?>]" value="1"
							<?php checked( $val ); ?>
							<?php disabled( ! $can_edit ); ?>>
						<?php esc_html_e( 'Må se', 'studie247' ); ?>
					</label>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php if ( ! $can_edit ) : ?>
		<p><em style="color:#666;"><?php esc_html_e( 'Kun administratorer kan ændre disse indstillinger.', 'studie247' ); ?></em></p>
	<?php endif;
}

/* ───────── Gem user-profil UI ───────── */
add_action( 'personal_options_update',  'studie247_save_dash_perms' );
add_action( 'edit_user_profile_update', 'studie247_save_dash_perms' );

function studie247_save_dash_perms( $user_id ) {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$values = isset( $_POST['s247_dash_view'] ) && is_array( $_POST['s247_dash_view'] )
		? array_map( 'sanitize_key', array_keys( wp_unslash( $_POST['s247_dash_view'] ) ) )
		: array();

	foreach ( array_keys( studie247_dash_sections() ) as $key ) {
		if ( in_array( $key, $values, true ) ) {
			update_user_meta( $user_id, '_s247_dash_view_' . $key, '1' );
		} else {
			delete_user_meta( $user_id, '_s247_dash_view_' . $key );
		}
	}
}

/* ───────── Bloker wp-admin for ikke-admins ─────────
 * Almindelige brugere skal bruge /dashboard/ — ikke wp-admin.
 * Admin-ajax (fx frontend-kald) går stadig igennem.
 */
add_action( 'admin_init', 'studie247_block_non_admin_wp_admin' );
function studie247_block_non_admin_wp_admin() {
	if ( wp_doing_ajax() ) { return; }
	if ( ! is_user_logged_in() ) { return; }
	if ( current_user_can( 'manage_options' ) ) { return; }
	wp_safe_redirect( home_url( '/dashboard/' ) );
	exit;
}

/* Skjul admin-bar på frontend for ikke-admins. */
add_filter( 'show_admin_bar', 'studie247_hide_admin_bar_for_non_admins' );
function studie247_hide_admin_bar_for_non_admins( $show ) {
	if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	return $show;
}
