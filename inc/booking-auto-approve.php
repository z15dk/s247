<?php
/**
 * Auto-godkendelse af bookinger fra bestemte e-mail-adresser.
 *
 * Admin kan konfigurere en liste af e-mails under Bookinger → Auto-godkendelse.
 * Når en booking oprettes fra front-enden med en e-mail på listen, og
 * tidspunktet er ledigt (overlap-tjek sker allerede før insert i
 * page-booking-studie.php), sættes status automatisk til "publish" og
 * bookingen markeres som intern (pris nulstilles).
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const S247_AUTO_APPROVE_OPTION = 's247_auto_approve_emails';

/**
 * Hent listen af auto-godkendelses-e-mails som array af lowercase strings.
 */
function studie247_auto_approve_emails() {
	$raw = get_option( S247_AUTO_APPROVE_OPTION, '' );
	if ( ! $raw ) { return array(); }
	$out = array();
	foreach ( preg_split( '/[\s,;]+/', $raw ) as $e ) {
		$e = strtolower( trim( $e ) );
		if ( $e && is_email( $e ) ) { $out[] = $e; }
	}
	return array_values( array_unique( $out ) );
}

/**
 * Tjek om en booking skal auto-godkendes, og gør det hvis ja.
 * Kaldes fra booking-formularen efter meta er gemt.
 *
 * Returnerer true hvis bookingen blev auto-godkendt, ellers false.
 */
function studie247_maybe_auto_approve_booking( $booking_id, $email ) {
	if ( ! $booking_id || 'booking' !== get_post_type( $booking_id ) ) { return false; }
	$email = strtolower( trim( (string) $email ) );
	if ( ! $email || ! is_email( $email ) ) { return false; }

	if ( ! in_array( $email, studie247_auto_approve_emails(), true ) ) { return false; }

	// Ledig-tjek er allerede håndteret af formularens overlap-check før
	// insert (page-booking-studie.php), men vi markerer intern + godkender.
	if ( function_exists( 'studie247_apply_internal_state' ) ) {
		studie247_apply_internal_state( $booking_id, true );
	}
	wp_update_post( array(
		'ID'          => $booking_id,
		'post_status' => 'publish',
	) );
	if ( function_exists( 'studie247_send_booking_confirmation' ) ) {
		studie247_send_booking_confirmation( $booking_id, 'approved' );
	}
	return true;
}

/* ───────── Settings-side under Bookinger ───────── */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=booking',
		__( 'Auto-godkendelse', 'studie247' ),
		__( 'Auto-godkendelse', 'studie247' ),
		'manage_options',
		's247-auto-approve',
		'studie247_auto_approve_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( 's247_auto_approve', S247_AUTO_APPROVE_OPTION, array(
		'type'              => 'string',
		'sanitize_callback' => function ( $value ) {
			$clean = array();
			foreach ( preg_split( '/[\s,;]+/', (string) $value ) as $e ) {
				$e = strtolower( trim( $e ) );
				if ( $e && is_email( $e ) ) { $clean[] = $e; }
			}
			return implode( "\n", array_unique( $clean ) );
		},
		'default'           => '',
	) );
} );

function studie247_auto_approve_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$value = get_option( S247_AUTO_APPROVE_OPTION, '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Auto-godkendelse af bookinger', 'studie247' ); ?></h1>
		<p style="max-width:640px;">
			<?php esc_html_e( 'Bookinger der oprettes med en e-mail-adresse på listen nedenfor bliver automatisk godkendt (hvis tidspunktet er ledigt) og markeret som intern brug — prisen nulstilles til 0 kr.', 'studie247' ); ?>
		</p>
		<p style="max-width:640px;color:#666;">
			<?php esc_html_e( 'Brug dette til dine egne/virksomhedens mail-adresser, så interne test-bookinger og personalets tider går direkte igennem uden manuel godkendelse.', 'studie247' ); ?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 's247_auto_approve' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="s247_auto_approve_emails"><?php esc_html_e( 'E-mail-adresser', 'studie247' ); ?></label></th>
					<td>
						<textarea
							id="s247_auto_approve_emails"
							name="<?php echo esc_attr( S247_AUTO_APPROVE_OPTION ); ?>"
							rows="8"
							cols="50"
							class="large-text code"
							placeholder="rune@s247.dk&#10;info@s247.dk"><?php echo esc_textarea( $value ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Én e-mail pr. linje. Ugyldige adresser filtreres automatisk væk ved gem.', 'studie247' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<?php $list = studie247_auto_approve_emails(); if ( $list ) : ?>
			<h2><?php esc_html_e( 'Aktive adresser', 'studie247' ); ?></h2>
			<ul style="margin-left:20px;list-style:disc;">
				<?php foreach ( $list as $e ) : ?>
					<li><code><?php echo esc_html( $e ); ?></code></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}
