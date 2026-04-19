<?php
/**
 * Mail-skabeloner: de 3 automatiske kunde-mails med redigerbar tekst
 * + shortcodes. Admin-side under Værktøjer → Mail-skabeloner.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Standard-skabeloner og tilgængelige placeholders pr. skabelon.
 * Placeholder-navne er lowercase-a_z_-format og skrives som {navn}.
 */
function studie247_mail_templates_config() {
	return array(
		'contact' => array(
			'label'       => __( 'Kontakt-form — kvittering', 'studie247' ),
			'description' => __( 'Sendes til kunden efter udfyldt kontakt-formular.', 'studie247' ),
			'subject'     => 'Tak for din henvendelse — Studie 247',
			'body'        => "Hej {navn},\n\nTak for at du kontaktede os. Vi vender tilbage til dig hurtigst muligt — typisk inden for 24 timer på hverdage.\n\nDu valgte: {side}\n\nDin besked:\n{besked}\n\n— Studie 247\ninfo@s247.dk",
			'placeholders' => array(
				'navn'    => 'Kundens navn',
				'email'   => 'Kundens email',
				'side'    => 'Valgt "side" (A/B) hvis relevant',
				'besked'  => 'Beskedens indhold',
			),
		),
		'booking_received' => array(
			'label'       => __( 'Booking modtaget', 'studie247' ),
			'description' => __( 'Sendes når en kunde indsender en booking/lejeforespørgsel — før admin godkender.', 'studie247' ),
			'subject'     => 'Tak for din booking af {booking_label} — Studie 247',
			'body'        => "Hej {navn},\n\nTak for din booking af {booking_label}. Vi vender tilbage med en endelig bekræftelse hurtigst muligt — typisk inden for 24 timer på hverdage.\n\nProdukt: {produkt}\nDato: {dato}\nStart: {start}\nVarighed: {varighed}\nEstimeret pris: {pris}\n\nFormål: {formaal}\nØnsker: {oensker}\nPodcast-type: {podcast_type}\nTilkøb: {tilkoeb}\nAntal videoer: {antal_videoer}\nVarighed pr. video: {video_varighed}\nFormat: {format}\n\nDine noter:\n{noter}\n\nDin booking er foreløbigt reserveret og afventer godkendelse.\n\n— Studie 247\ninfo@s247.dk",
			'placeholders' => array(
				'navn'           => 'Kundens navn',
				'email'          => 'Kundens email',
				'telefon'        => 'Kundens telefonnummer',
				'booking_label'  => 'Produkt-navn eller "studiet"',
				'produkt'        => 'Produkt-navn (tomt for studie-booking)',
				'dato'           => 'Dato på dansk (fx "lørdag 25. april 2026")',
				'start'          => 'Start-tidspunkt (fx "10:00")',
				'varighed'       => 'Varighed (fx "6 timer" / "3 dage")',
				'pris'           => 'Estimeret lejepris i DKK',
				'noter'          => 'Kundens noter',
				'formaal'        => 'Formål (Podcast / Kursusvideo / …)',
				'oensker'        => 'Redigering / Kun filerne',
				'podcast_type'   => 'Lyd-podcast / Video-podcast',
				'tilkoeb'        => 'Tilkøb (musikjingle)',
				'antal_videoer'  => 'Antal videoer',
				'video_varighed' => 'Varighed pr. video i min',
				'format'         => 'SoMe-format (16:9/9:16/4:5/1:1)',
			),
		),
		'booking_approved' => array(
			'label'       => __( 'Booking godkendt', 'studie247' ),
			'description' => __( 'Sendes når admin trykker "Godkend" i wp-admin.', 'studie247' ),
			'subject'     => 'Din booking er godkendt — Studie 247',
			'body'        => "Hej {navn},\n\nDin booking er godkendt. Vi glæder os til at se dig.\n\nProdukt: {produkt}\nDato: {dato}\nStart: {start}\nVarighed: {varighed}\n\nHar du spørgsmål inden da, så ring eller skriv.\n\n— Studie 247\ninfo@s247.dk",
			'placeholders' => array(
				'navn'     => 'Kundens navn',
				'email'    => 'Kundens email',
				'produkt'  => 'Produkt-navn (tomt for studie-booking)',
				'dato'     => 'Dato på dansk',
				'start'    => 'Start-tidspunkt',
				'varighed' => 'Varighed',
			),
		),
	);
}

/**
 * Hent en skabelons subject + body fra options (eller default).
 */
function studie247_get_mail_template( $key ) {
	$config  = studie247_mail_templates_config();
	$default = isset( $config[ $key ] ) ? $config[ $key ] : array( 'subject' => '', 'body' => '' );
	$all     = get_option( 'studie247_mail_templates', array() );
	$saved   = isset( $all[ $key ] ) ? $all[ $key ] : array();
	return array(
		'subject' => isset( $saved['subject'] ) && $saved['subject'] !== '' ? $saved['subject'] : $default['subject'],
		'body'    => isset( $saved['body']    ) && $saved['body']    !== '' ? $saved['body']    : $default['body'],
	);
}

/**
 * Render en skabelon med værdier. Linjer hvor en placeholder er tom
 * fjernes, så optional-felter (produkt/pris/formål osv.) ikke
 * efterlader fx "Produkt: ".
 */
function studie247_render_mail_template( $key, $vars ) {
	$tpl     = studie247_get_mail_template( $key );
	$subject = $tpl['subject'];
	$body    = $tpl['body'];

	// Drop linjer hvor en af placeholderne er tom (alle vars uanset om brugt).
	$lines = preg_split( '/\r\n|\r|\n/', $body );
	$kept  = array();
	foreach ( $lines as $line ) {
		$skip = false;
		if ( preg_match_all( '/\{([a-z_]+)\}/', $line, $m ) ) {
			foreach ( $m[1] as $ph ) {
				if ( array_key_exists( $ph, $vars ) && (string) $vars[ $ph ] === '' ) {
					$skip = true;
					break;
				}
			}
		}
		if ( ! $skip ) { $kept[] = $line; }
	}
	$body = implode( "\n", $kept );

	// Erstat placeholders.
	foreach ( $vars as $k => $v ) {
		$subject = str_replace( '{' . $k . '}', (string) $v, $subject );
		$body    = str_replace( '{' . $k . '}', (string) $v, $body );
	}

	// Kollaps 3+ blanke linjer → 2.
	$body = preg_replace( "/\n{3,}/", "\n\n", $body );

	return array( 'subject' => $subject, 'body' => $body );
}

/* ───────── Admin-side: Værktøjer → Mail-skabeloner ───────── */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'tools.php',
		__( 'Studie 247 — Mail-skabeloner', 'studie247' ),
		__( 'Mail-skabeloner', 'studie247' ),
		'manage_options',
		's247-mail-templates',
		'studie247_mail_templates_page'
	);
} );

function studie247_mail_templates_page() {
	$config = studie247_mail_templates_config();
	$saved  = 0;
	$reset  = '';

	// Gem.
	if ( isset( $_POST['s247_mt_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['s247_mt_nonce'] ), 's247_mail_templates' ) ) {
		$in  = isset( $_POST['tpl'] ) && is_array( $_POST['tpl'] ) ? wp_unslash( $_POST['tpl'] ) : array();
		$all = array();
		foreach ( $config as $key => $_cfg ) {
			$all[ $key ] = array(
				'subject' => sanitize_text_field( $in[ $key ]['subject'] ?? '' ),
				'body'    => wp_kses_post( $in[ $key ]['body'] ?? '' ),
			);
		}
		update_option( 'studie247_mail_templates', $all );
		$saved = 1;
	}

	// Nulstil enkelt skabelon.
	if ( isset( $_GET['reset'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 's247_mt_reset' ) ) {
		$reset_key = sanitize_key( wp_unslash( $_GET['reset'] ) );
		if ( isset( $config[ $reset_key ] ) ) {
			$all = get_option( 'studie247_mail_templates', array() );
			unset( $all[ $reset_key ] );
			update_option( 'studie247_mail_templates', $all );
			$reset = $reset_key;
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Mail-skabeloner', 'studie247' ); ?></h1>
		<p style="max-width:720px;color:#666;">
			<?php esc_html_e( 'Rediger ordlyden på de 3 automatiske mails der sendes til kunder. Brug shortcodes som {navn}, {produkt} osv. for at indsætte dynamiske værdier. Linjer hvor en shortcode er tom (fx "Produkt:" på en studie-booking) fjernes automatisk.', 'studie247' ); ?>
		</p>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Skabeloner gemt.', 'studie247' ); ?></p></div>
		<?php endif; ?>
		<?php if ( $reset ) : ?>
			<div class="notice notice-info is-dismissible"><p><?php printf( esc_html__( 'Skabelonen "%s" nulstillet til standard.', 'studie247' ), esc_html( $config[ $reset ]['label'] ?? $reset ) ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="">
			<?php wp_nonce_field( 's247_mail_templates', 's247_mt_nonce' ); ?>

			<?php foreach ( $config as $key => $cfg ) :
				$cur = studie247_get_mail_template( $key );
				$reset_url = wp_nonce_url( add_query_arg( array( 'reset' => $key ), admin_url( 'tools.php?page=s247-mail-templates' ) ), 's247_mt_reset' );
			?>
				<div style="background:#fff;border:1px solid #ccd0d4;padding:20px;margin-bottom:24px;border-radius:4px;">
					<h2 style="margin-top:0;display:flex;justify-content:space-between;align-items:center;gap:12px;">
						<span><?php echo esc_html( $cfg['label'] ); ?></span>
						<a href="<?php echo esc_url( $reset_url ); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e( 'Nulstil til standard-skabelon?', 'studie247' ); ?>');"><?php esc_html_e( 'Nulstil', 'studie247' ); ?></a>
					</h2>
					<p style="color:#666;margin-top:0;"><?php echo esc_html( $cfg['description'] ); ?></p>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="tpl-<?php echo esc_attr( $key ); ?>-subject"><?php esc_html_e( 'Emne', 'studie247' ); ?></label></th>
							<td><input type="text" class="large-text" id="tpl-<?php echo esc_attr( $key ); ?>-subject" name="tpl[<?php echo esc_attr( $key ); ?>][subject]" value="<?php echo esc_attr( $cur['subject'] ); ?>"></td>
						</tr>
						<tr>
							<th scope="row"><label for="tpl-<?php echo esc_attr( $key ); ?>-body"><?php esc_html_e( 'Tekst', 'studie247' ); ?></label></th>
							<td>
								<textarea class="large-text code" id="tpl-<?php echo esc_attr( $key ); ?>-body" name="tpl[<?php echo esc_attr( $key ); ?>][body]" rows="12" style="font-family:inherit;"><?php echo esc_textarea( $cur['body'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Shortcodes', 'studie247' ); ?></th>
							<td>
								<p style="margin-top:0;color:#666;"><?php esc_html_e( 'Klik på en shortcode for at kopiere. Tomme værdier fjerner linjen automatisk.', 'studie247' ); ?></p>
								<ul style="margin:0;font-family:monospace;font-size:13px;line-height:1.6;">
									<?php foreach ( $cfg['placeholders'] as $ph => $desc ) : ?>
										<li>
											<code style="background:#f3f4f6;padding:2px 6px;border-radius:3px;cursor:copy;" onclick="navigator.clipboard&&navigator.clipboard.writeText('{<?php echo esc_js( $ph ); ?>}');this.style.background='#d1e7dd';setTimeout(()=>this.style.background='#f3f4f6',400);">{<?php echo esc_html( $ph ); ?>}</code>
											<span style="color:#666;"> — <?php echo esc_html( $desc ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</td>
						</tr>
					</table>
				</div>
			<?php endforeach; ?>

			<p><button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Gem alle skabeloner', 'studie247' ); ?></button></p>
		</form>
	</div>
	<?php
}
