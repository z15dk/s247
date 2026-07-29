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
 * Hvis body'en ligner dobbelt-escaped HTML (indeholder "&lt;" men
 * ingen rigtige HTML-tags), decode entities så vi ikke gemmer tekst
 * der senere sendes som synlig `&lt;` i mails.
 */
function studie247_mail_unescape_if_needed( $body ) {
	$body = (string) $body;
	if ( '' === $body ) { return $body; }
	$looks_escaped = ( false !== strpos( $body, '&lt;' ) );
	$has_real_tags = (bool) preg_match( '/<[a-z][a-z0-9]*(\s[^>]*)?>/i', $body );
	if ( $looks_escaped && ! $has_real_tags ) {
		$body = html_entity_decode( $body, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}
	return $body;
}

/**
 * Default HTML for contact-form kvittering — brand-designet med
 * et let legende tone-of-voice (kaffe-pause-twist).
 */
function studie247_default_contact_html() {
	return <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4E9DD;padding:32px 16px;font-family:'Helvetica Neue',Arial,sans-serif;color:#282828;">
<tr><td align="center">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#FBF5EC;border:1px solid rgba(40,40,40,0.1);border-radius:14px;overflow:hidden;">
    <tr>
      <td style="background:#282828;padding:28px 32px;text-align:left;">
        <span style="color:#F4E9DD;font-size:13px;letter-spacing:0.18em;text-transform:uppercase;font-weight:600;">Studie 247</span>
        <h1 style="margin:6px 0 0;color:#F4E9DD;font-size:32px;line-height:1.1;font-family:Georgia,serif;font-weight:400;">
          Beskeden er <em style="color:#E89B6B;">landet.</em>
        </h1>
      </td>
    </tr>
    <tr>
      <td style="padding:32px 32px 6px;">
        <p style="margin:0 0 14px;font-size:16px;line-height:1.55;">Hej <strong>{navn}</strong>,</p>
        <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#404040;">Tak for at du skrev til os. Vi vender tilbage hurtigst muligt — typisk inden for <strong>24 timer</strong> på hverdage.</p>
        <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#404040;">I mellemtiden: hent en kop kaffe ☕ — vi er på sagen.</p>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4E9DD;border-radius:10px;">
          <tr><td style="padding:18px 22px;">
            <div style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:#9E2B25;font-weight:700;margin-bottom:10px;">Din henvendelse</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;line-height:1.7;">
              <tr><td style="color:#666;padding:4px 0;width:120px;vertical-align:top;">Emne</td><td style="color:#282828;font-weight:600;">{side}</td></tr>
              <tr><td style="color:#666;padding:8px 0 4px;vertical-align:top;border-top:1px dashed rgba(40,40,40,0.15);">Besked</td><td style="color:#282828;padding-top:8px;border-top:1px dashed rgba(40,40,40,0.15);">{besked}</td></tr>
            </table>
          </td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 24px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td style="font-size:13px;line-height:1.6;color:#666;font-style:italic;border-left:3px solid #E89B6B;padding:6px 0 6px 14px;">
              P.S. Vi bider ikke. Men vi kan godt lide at være skarpe.
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="background:#282828;padding:22px 32px;text-align:center;">
        <p style="margin:0;color:#F4E9DD;font-size:13px;line-height:1.6;">
          Studie 247 · <a href="mailto:info@s247.dk" style="color:#E89B6B;text-decoration:none;">info@s247.dk</a><br>
          <a href="https://s247.dk/" style="color:#F4E9DD;text-decoration:underline;opacity:0.75;">s247.dk</a>
        </p>
      </td>
    </tr>
  </table>
</td></tr>
</table>
HTML;
}

/**
 * Default HTML for booking_received — brand-designet mail med
 * bone-baggrund, rød accent og serif-overskrifter. Inline-styles
 * for bred mail-klient-kompatibilitet (Outlook, Gmail, Apple Mail).
 */
function studie247_default_booking_received_html() {
	return <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4E9DD;padding:32px 16px;font-family:'Helvetica Neue',Arial,sans-serif;color:#282828;">
<tr><td align="center">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#FBF5EC;border:1px solid rgba(40,40,40,0.1);border-radius:14px;overflow:hidden;">
    <tr>
      <td style="background:#282828;padding:28px 32px;text-align:left;">
        <span style="color:#F4E9DD;font-size:13px;letter-spacing:0.18em;text-transform:uppercase;font-weight:600;">Studie 247</span>
        <h1 style="margin:6px 0 0;color:#F4E9DD;font-size:32px;line-height:1.1;font-family:Georgia,serif;font-weight:400;">
          Tak for din <em style="color:#E89B6B;">booking</em>
        </h1>
      </td>
    </tr>
    <tr>
      <td style="padding:32px 32px 8px;">
        <p style="margin:0 0 14px;font-size:16px;line-height:1.55;">Hej <strong>{navn}</strong>,</p>
        <p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:#404040;">Vi har modtaget din booking af <strong>{booking_label}</strong>. Vi vender tilbage med en endelig bekræftelse hurtigst muligt — typisk inden for 24 timer på hverdage.</p>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F4E9DD;border-radius:10px;padding:4px;">
          <tr><td style="padding:18px 22px;">
            <div style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:#9E2B25;font-weight:700;margin-bottom:10px;">Detaljer</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;line-height:1.7;">
              <tr><td style="color:#666;padding:4px 0;width:130px;">Produkt</td><td style="color:#282828;font-weight:600;">{produkt}</td></tr>
              <tr><td style="color:#666;padding:4px 0;">Dato</td><td style="color:#282828;font-weight:600;">{dato}</td></tr>
              <tr><td style="color:#666;padding:4px 0;">Start</td><td style="color:#282828;font-weight:600;">{start}</td></tr>
              <tr><td style="color:#666;padding:4px 0;">Varighed</td><td style="color:#282828;font-weight:600;">{varighed}</td></tr>
              <tr><td style="color:#666;padding:4px 0;font-size:12px;font-style:italic;">Heraf aftenpris ({aftenpris_timer} t × {aftenpris_sats} kr)</td><td style="color:#666;font-size:12px;font-style:italic;">+{aftenpris_tillaeg}</td></tr>
              <tr><td style="color:#666;padding:4px 0;border-top:1px dashed rgba(40,40,40,0.15);">Estimeret pris</td><td style="color:#9E2B25;font-weight:700;font-size:18px;border-top:1px dashed rgba(40,40,40,0.15);padding-top:8px;">{pris}</td></tr>
            </table>
          </td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FBF5EC;border:1px solid rgba(40,40,40,0.08);border-radius:10px;">
          <tr><td style="padding:16px 22px;">
            <div style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:#666;font-weight:700;margin-bottom:8px;">Virksomhed</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;line-height:1.6;">
              <tr><td style="color:#666;width:130px;padding:2px 0;">Navn</td><td style="color:#282828;">{virksomhed}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">CVR</td><td style="color:#282828;font-family:monospace;">{cvr}</td></tr>
            </table>
          </td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FBF5EC;border:1px solid rgba(40,40,40,0.08);border-radius:10px;">
          <tr><td style="padding:16px 22px;">
            <div style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:#666;font-weight:700;margin-bottom:8px;">Produktion</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;line-height:1.6;">
              <tr><td style="color:#666;width:130px;padding:2px 0;">Formål</td><td style="color:#282828;">{formaal}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">Ønsker</td><td style="color:#282828;">{oensker}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">Podcast-type</td><td style="color:#282828;">{podcast_type}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">Tilkøb</td><td style="color:#282828;">{tilkoeb}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">Antal videoer</td><td style="color:#282828;">{antal_videoer}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">Varighed pr. video</td><td style="color:#282828;">{video_varighed}</td></tr>
              <tr><td style="color:#666;padding:2px 0;">Format</td><td style="color:#282828;">{format}</td></tr>
            </table>
          </td></tr>
        </table>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 24px;">
        <div style="font-size:11px;letter-spacing:0.14em;text-transform:uppercase;color:#666;font-weight:700;margin-bottom:8px;">Dine noter</div>
        <div style="background:#FBF5EC;border-left:3px solid #9E2B25;padding:14px 18px;border-radius:0 8px 8px 0;font-size:14px;line-height:1.6;color:#404040;font-style:italic;">{noter}</div>
      </td>
    </tr>
    <tr>
      <td style="padding:0 32px 28px;">
        <div style="background:#FEF3CF;border:1px solid #F5D58A;border-radius:8px;padding:14px 18px;font-size:13px;line-height:1.55;color:#6B4A0A;">
          <strong>⏳ Afventer godkendelse.</strong> Din booking er foreløbigt reserveret. Du får en ny mail når vi har bekræftet.
        </div>
      </td>
    </tr>
    <tr>
      <td style="background:#282828;padding:22px 32px;text-align:center;">
        <div style="color:#F4E9DD;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;font-weight:600;margin-bottom:4px;">Studie 247</div>
        <div style="color:rgba(244,233,221,0.6);font-size:12px;">Optag. Skab. Udgiv — døgnet rundt.</div>
        <div style="margin-top:12px;"><a href="mailto:info@s247.dk" style="color:#E89B6B;text-decoration:none;font-size:13px;">info@s247.dk</a></div>
      </td>
    </tr>
  </table>
</td></tr>
</table>
HTML;
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
			'body'        => studie247_default_contact_html(),
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
			'body'        => studie247_default_booking_received_html(),
			'placeholders' => array(
				'navn'           => 'Kundens navn',
				'email'          => 'Kundens email',
				'telefon'        => 'Kundens telefonnummer',
				'virksomhed'     => 'Virksomhedsnavn (valgfrit)',
				'cvr'            => 'CVR-nummer (valgfrit)',
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
			'body'        => "<p>Hej {navn},</p>\n<p>Din booking er godkendt. Vi glæder os til at se dig.</p>\n<p>Produkt: {produkt}<br>Dato: {dato}<br>Start: {start}<br>Varighed: {varighed}</p>\n<p><em>Inkl. aftenpris-tillæg: {aftenpris_timer} t × {aftenpris_sats} kr = {aftenpris_tillaeg}</em></p>\n<p><strong>Tilføj til din kalender:</strong><br><a href=\"{kalender_google}\">Google Calendar</a> &nbsp;·&nbsp; <a href=\"{kalender_outlook}\">Outlook</a> &nbsp;·&nbsp; <a href=\"{kalender_ics}\">Apple / Andre (.ics)</a></p>\n<p>Har du spørgsmål inden da, så ring eller skriv.</p>\n<p>— Studie 247<br><a href=\"mailto:info@s247.dk\">info@s247.dk</a></p>",
			'placeholders' => array(
				'navn'            => 'Kundens navn',
				'email'           => 'Kundens email',
				'produkt'         => 'Produkt-navn (tomt for studie-booking)',
				'dato'            => 'Dato på dansk',
				'start'           => 'Start-tidspunkt',
				'varighed'        => 'Varighed',
				'virksomhed'      => 'Virksomhedsnavn (valgfrit)',
				'cvr'             => 'CVR-nummer (valgfrit)',
				'kalender_ics'    => 'Link til .ics-download (Apple/Andre kalendere)',
				'kalender_google' => 'Link til "Tilføj til Google Calendar"',
				'kalender_outlook'=> 'Link til "Tilføj til Outlook"',
				'aftenpris_timer'   => 'Antal timer efter kl 20:00 (tomt = ingen tillæg)',
				'aftenpris_tillaeg' => 'Samlet aftenpris-tillæg i kr',
				'aftenpris_sats'    => 'Sats (kr pr. påbegyndt time efter 20:00)',
			),
		),
		'user_welcome' => array(
			'label'       => __( 'Velkomstmail — ny bruger', 'studie247' ),
			'description' => __( 'Sendes når en admin opretter en ny bruger i dashboardet (Brugere → Opret).', 'studie247' ),
			'subject'     => 'Velkommen til Studie 247',
			'body'        => "<p>Hej {fornavn},</p>\n<p>Du har nu adgang til Studie 247's dashboard. Log ind her:</p>\n<p><a href=\"{login_url}\">{login_url}</a></p>\n<p><strong>Brugernavn:</strong> {brugernavn}<br><strong>Adgangskode:</strong> {adgangskode}</p>\n<p>Af sikkerhedshensyn bør du skifte adgangskoden til noget personligt ved første login.</p>\n<p>— Studie 247</p>",
			'placeholders' => array(
				'fornavn'     => 'Brugerens fornavn',
				'efternavn'   => 'Brugerens efternavn',
				'brugernavn'  => 'Brugernavn (email)',
				'email'       => 'Email-adresse',
				'adgangskode' => 'Den genererede adgangskode',
				'login_url'   => 'Link til /dashboard/',
				'rolle'       => 'Tildelt rolle (Administrator/Redaktør)',
			),
		),
		'user_password_reset' => array(
			'label'       => __( 'Adgangskode ændret — bruger', 'studie247' ),
			'description' => __( 'Sendes når admin ændrer en brugers adgangskode via dashboardet og klikker "Send ny kode på mail".', 'studie247' ),
			'subject'     => 'Din adgangskode er ændret — Studie 247',
			'body'        => "<p>Hej {fornavn},</p>\n<p>Din adgangskode til Studie 247's dashboard er blevet ændret af en administrator.</p>\n<p>Log ind på:<br><a href=\"{login_url}\">{login_url}</a></p>\n<p><strong>Brugernavn:</strong> {brugernavn}<br><strong>Ny adgangskode:</strong> {adgangskode}</p>\n<p>Af sikkerhedshensyn bør du skifte den til noget personligt ved næste login.</p>\n<p>— Studie 247</p>",
			'placeholders' => array(
				'fornavn'     => 'Brugerens fornavn (eller display-navn)',
				'brugernavn'  => 'Brugernavn (email)',
				'email'       => 'Email-adresse',
				'adgangskode' => 'Den nye adgangskode',
				'login_url'   => 'Link til /dashboard/',
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
 * Render en skabelon med værdier. Linjer (eller <br>/<p>-fragmenter)
 * hvor en placeholder er tom fjernes, så optional-felter (produkt,
 * pris osv.) ikke efterlader fx "Produkt: ".
 *
 * Virker med både plain tekst og HTML (wp_editor-output).
 */
function studie247_render_mail_template( $key, $vars ) {
	$tpl     = studie247_get_mail_template( $key );
	$subject = $tpl['subject'];
	$body    = $tpl['body'];

	// Normalisér: behandl <br> og </p> som linjeskift så strip-logikken
	// virker ens uanset om skabelonen er HTML eller plain.
	$normalized = preg_replace( '#<br\s*/?>#i', "\n", $body );
	$normalized = preg_replace( '#</p>#i', "</p>\n", $normalized );
	$lines = preg_split( '/\r\n|\r|\n/', $normalized );
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

	// Erstat placeholders. Understøtter både {x} og {{x}} samt URL-encoded
	// varianter (TinyMCE kan pakke {x} som %7Bx%7D når det ligger i en href).
	foreach ( $vars as $k => $v ) {
		$search = array(
			'{{' . $k . '}}',
			'{' . $k . '}',
			'%7B%7B' . $k . '%7D%7D',
			'%7b%7b' . $k . '%7d%7d',
			'%7B' . $k . '%7D',
			'%7b' . $k . '%7d',
		);
		$subject = str_replace( $search, (string) $v, $subject );
		$body    = str_replace( $search, (string) $v, $body );
	}

	// Ryd tomme <p></p> og kollaps 3+ blanke linjer → 2.
	$body = preg_replace( '#<p>\s*</p>#i', '', $body );
	$body = preg_replace( "/\n{3,}/", "\n\n", $body );

	return array( 'subject' => $subject, 'body' => $body );
}

/**
 * Er skabelonens body HTML? Bruges til at sætte Content-Type på mailen.
 */
function studie247_mail_template_is_html( $key ) {
	$tpl = studie247_get_mail_template( $key );
	return (bool) preg_match( '/<[a-z][a-z0-9]*(\s[^>]*)?>/i', $tpl['body'] );
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
				'body'    => wp_kses_post( studie247_mail_unescape_if_needed( $in[ $key ]['body'] ?? '' ) ),
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
							<th scope="row"><label for="tpl-<?php echo esc_attr( $key ); ?>-body"><?php esc_html_e( 'Indhold', 'studie247' ); ?></label></th>
							<td>
								<?php
								$editor_id = 'tpl_' . $key . '_body';
								wp_editor(
									$cur['body'],
									$editor_id,
									array(
										'textarea_name' => 'tpl[' . $key . '][body]',
										'textarea_rows' => 14,
										'media_buttons' => false,
										'teeny'         => false,
										'tinymce'       => array(
											'toolbar1' => 'formatselect,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,forecolor,hr,|,removeformat,undo,redo',
											'toolbar2' => '',
											'block_formats' => 'Paragraf=p; Overskrift 2=h2; Overskrift 3=h3',
										),
										'quicktags'     => true,
										'default_editor' => 'tinymce',
									)
								);
								?>
								<p style="color:#666;margin-top:8px;">
									<?php esc_html_e( 'Skift mellem Visuel (WYSIWYG) og Tekst (HTML) i fanerne øverst. Brug shortcodes som {navn} i både overskrifter og links.', 'studie247' ); ?>
								</p>
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
