<?php
/**
 * Studie 247 — mail-system.
 *
 * Portabel SMTP-opsætning der læser credentials fra wp-config.php-konstanter,
 * så mail virker ens på tværs af servere. Tilføj følgende til wp-config.php
 * før linjen "That's all, stop editing!" — og udfyld værdierne fra din
 * SMTP-udbyder (Gmail/Google Workspace, Mailgun, SendGrid, Postmark, egen
 * server, osv.):
 *
 *   define( 'S247_SMTP_HOST',      'smtp.example.com' );
 *   define( 'S247_SMTP_PORT',      587 );
 *   define( 'S247_SMTP_USER',      'no-reply@s247.dk' );
 *   define( 'S247_SMTP_PASS',      'xxxxxxxxxxxxxxxx' );
 *   define( 'S247_SMTP_SECURE',    'tls' );              // 'tls', 'ssl' eller '' (ingen)
 *   define( 'S247_MAIL_FROM',      'no-reply@s247.dk' );
 *   define( 'S247_MAIL_FROM_NAME', 'Studie 247' );
 *
 * Test opsætningen under Værktøjer → Mail-test i wp-admin.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── SMTP-konfiguration via phpmailer_init ───────── */
add_action( 'phpmailer_init', function ( $phpmailer ) {
	if ( ! defined( 'S247_SMTP_HOST' ) || ! S247_SMTP_HOST ) {
		return; // Ingen SMTP konfigureret → fald tilbage til PHPs mail().
	}
	$phpmailer->isSMTP();
	$phpmailer->Host       = S247_SMTP_HOST;
	$phpmailer->Port       = defined( 'S247_SMTP_PORT' ) ? (int) S247_SMTP_PORT : 587;
	$phpmailer->CharSet    = 'UTF-8';

	if ( defined( 'S247_SMTP_USER' ) && defined( 'S247_SMTP_PASS' ) && S247_SMTP_USER ) {
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = S247_SMTP_USER;
		$phpmailer->Password = S247_SMTP_PASS;
	}
	if ( defined( 'S247_SMTP_SECURE' ) && S247_SMTP_SECURE ) {
		$phpmailer->SMTPSecure = S247_SMTP_SECURE;
	}
}, 10, 1 );

/* ───────── Ensartet afsender (From / From-name) ───────── */
add_filter( 'wp_mail_from', function ( $email ) {
	if ( defined( 'S247_MAIL_FROM' ) && S247_MAIL_FROM && is_email( S247_MAIL_FROM ) ) {
		return S247_MAIL_FROM;
	}
	return $email;
} );

add_filter( 'wp_mail_from_name', function ( $name ) {
	if ( defined( 'S247_MAIL_FROM_NAME' ) && S247_MAIL_FROM_NAME ) {
		return S247_MAIL_FROM_NAME;
	}
	return $name;
} );

/* ───────── Admin: test-mail side under Værktøjer ───────── */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'tools.php',
		__( 'Studie 247 — Mail-test', 'studie247' ),
		__( 'Mail-test', 'studie247' ),
		'manage_options',
		's247-mail-test',
		'studie247_mail_test_page'
	);
} );

function studie247_mail_test_page() {
	$sent      = false;
	$error_msg = '';
	$recipient = get_option( 'admin_email' );

	if ( isset( $_POST['s247_mail_test_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['s247_mail_test_nonce'] ), 's247_mail_test' ) ) {
		$recipient = sanitize_email( wp_unslash( $_POST['to'] ?? '' ) );
		if ( ! is_email( $recipient ) ) {
			$error_msg = __( 'Ugyldig modtager-mail.', 'studie247' );
		} else {
			// Fang evt. SMTP-fejl så vi kan vise dem i UI.
			$capture = function ( $err ) use ( &$error_msg ) {
				if ( $err instanceof WP_Error ) {
					$error_msg = $err->get_error_message();
				}
			};
			add_action( 'wp_mail_failed', $capture );

			$subject = '[Studie 247] Mail-test — ' . gmdate( 'Y-m-d H:i:s' );
			$body    = sprintf(
				"Dette er en test-mail sendt fra Studie 247.\n\nHost: %s\nTid: %s\n\nHvis du modtager denne mail, fungerer SMTP-opsætningen.\n\n— Studie 247",
				wp_parse_url( home_url(), PHP_URL_HOST ),
				gmdate( 'Y-m-d H:i:s' )
			);
			$sent = wp_mail( $recipient, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );

			remove_action( 'wp_mail_failed', $capture );
		}
	}

	// Læs aktuel konfiguration (uden at vise password).
	$cfg = array(
		'SMTP_HOST'      => defined( 'S247_SMTP_HOST' )      ? S247_SMTP_HOST      : '',
		'SMTP_PORT'      => defined( 'S247_SMTP_PORT' )      ? (int) S247_SMTP_PORT : 0,
		'SMTP_USER'      => defined( 'S247_SMTP_USER' )      ? S247_SMTP_USER      : '',
		'SMTP_SECURE'    => defined( 'S247_SMTP_SECURE' )    ? S247_SMTP_SECURE    : '',
		'MAIL_FROM'      => defined( 'S247_MAIL_FROM' )      ? S247_MAIL_FROM      : '',
		'MAIL_FROM_NAME' => defined( 'S247_MAIL_FROM_NAME' ) ? S247_MAIL_FROM_NAME : '',
	);
	$smtp_on  = ! empty( $cfg['SMTP_HOST'] );
	$pass_set = defined( 'S247_SMTP_PASS' ) && S247_SMTP_PASS;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mail-test', 'studie247' ); ?></h1>

		<?php if ( $sent ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php
				printf( esc_html__( 'Test-mail sendt til %s. Tjek indbakken (og evt. spam).', 'studie247' ), '<code>' . esc_html( $recipient ) . '</code>' );
			?></p></div>
		<?php elseif ( $error_msg ) : ?>
			<div class="notice notice-error is-dismissible"><p><strong><?php esc_html_e( 'Fejl:', 'studie247' ); ?></strong> <?php echo esc_html( $error_msg ); ?></p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Aktuel konfiguration', 'studie247' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><?php esc_html_e( 'SMTP-mode', 'studie247' ); ?></th>
				<td>
					<?php if ( $smtp_on ) : ?>
						<span style="color:#0a7c2f;font-weight:700;">● <?php esc_html_e( 'SMTP aktiv', 'studie247' ); ?></span>
					<?php else : ?>
						<span style="color:#b32d2e;font-weight:700;">● <?php esc_html_e( 'SMTP ikke konfigureret — bruger PHP\'s mail()', 'studie247' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<tr><th>S247_SMTP_HOST</th><td><code><?php echo esc_html( $cfg['SMTP_HOST'] ?: '(ikke sat)' ); ?></code></td></tr>
			<tr><th>S247_SMTP_PORT</th><td><code><?php echo esc_html( $cfg['SMTP_PORT'] ?: '(ikke sat)' ); ?></code></td></tr>
			<tr><th>S247_SMTP_USER</th><td><code><?php echo esc_html( $cfg['SMTP_USER'] ?: '(ikke sat)' ); ?></code></td></tr>
			<tr><th>S247_SMTP_PASS</th><td><code><?php echo $pass_set ? '••••••••' : '(ikke sat)'; ?></code></td></tr>
			<tr><th>S247_SMTP_SECURE</th><td><code><?php echo esc_html( $cfg['SMTP_SECURE'] ?: '(ikke sat)' ); ?></code></td></tr>
			<tr><th>S247_MAIL_FROM</th><td><code><?php echo esc_html( $cfg['MAIL_FROM'] ?: '(ikke sat)' ); ?></code></td></tr>
			<tr><th>S247_MAIL_FROM_NAME</th><td><code><?php echo esc_html( $cfg['MAIL_FROM_NAME'] ?: '(ikke sat)' ); ?></code></td></tr>
		</table>

		<p style="max-width:720px;color:#666;font-size:13px;">
			<?php esc_html_e( 'Disse konstanter sættes i wp-config.php. Når du flytter sitet til en ny server, kopier samme konstanter over — så fungerer mail-systemet uanset hvilken mail-server PHP kører på.', 'studie247' ); ?>
		</p>

		<h2 style="margin-top:2em;"><?php esc_html_e( 'Send test-mail', 'studie247' ); ?></h2>
		<form method="post" style="max-width:520px;">
			<?php wp_nonce_field( 's247_mail_test', 's247_mail_test_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="s247-mail-to"><?php esc_html_e( 'Send til', 'studie247' ); ?></label></th>
					<td><input type="email" id="s247-mail-to" name="to" class="regular-text" required value="<?php echo esc_attr( $recipient ); ?>"></td>
				</tr>
			</table>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Send test-mail', 'studie247' ); ?></button></p>
		</form>
	</div>
	<?php
}
