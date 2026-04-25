<?php
/**
 * Auto-detektering af cookies via browser.
 *
 * En tynd JS-snippet læser document.cookie på front-enden og POST'er
 * cookie-NAVNE (aldrig værdier) til /wp-json/s247/v1/cookies/report.
 * Server-siden matcher hvert navn mod en udvidet ordbog; ukendte
 * cookies registreres med kategori=analytics (kræver samtykke) så de
 * ikke falder ind under "nødvendige" ved et uheld.
 *
 * Den eksisterende server-side scan (cookie-banner.php) kører stadig og
 * bidrager med sit output — denne fil udvider med reel browser-data.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Udvidet ordbog over kendte cookies. Suffikset `_*` matcher præfikser
 * (fx `_ga_XXXX` matcher `_ga_*`).
 */
function studie247_cookie_lexicon() {
	return array(
		// Google Analytics / gtag
		'_ga'        => array( 'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Skelner mellem brugere.', 'duration' => '2 år' ),
		'_ga_*'      => array( 'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Holder session-state (GA4).', 'duration' => '2 år' ),
		'_gid'       => array( 'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Skelner mellem brugere.', 'duration' => '24 timer' ),
		'_gat'       => array( 'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Begrænser request-frekvensen.', 'duration' => '1 minut' ),
		'_gat_*'     => array( 'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Begrænser request-frekvensen.', 'duration' => '1 minut' ),
		'_gcl_au'    => array( 'category' => 'marketing', 'provider' => 'Google Ads',       'purpose' => 'Konverteringssporing (Google Ads).', 'duration' => '3 måneder' ),
		'_gcl_aw'    => array( 'category' => 'marketing', 'provider' => 'Google Ads',       'purpose' => 'Konverteringssporing (Google Ads).', 'duration' => '3 måneder' ),
		'_gcl_dc'    => array( 'category' => 'marketing', 'provider' => 'Google Ads',       'purpose' => 'DoubleClick konverteringssporing.', 'duration' => '3 måneder' ),
		'AMP_TOKEN'  => array( 'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'AMP-klient-ID tjeneste.', 'duration' => '30 sek. – 1 år' ),

		// Google Ads / DoubleClick
		'IDE'        => array( 'category' => 'marketing', 'provider' => 'Google DoubleClick', 'purpose' => 'Annonce-profilering og remarketing.', 'duration' => '1 år' ),
		'test_cookie'=> array( 'category' => 'marketing', 'provider' => 'Google DoubleClick', 'purpose' => 'Tester om browseren accepterer cookies.', 'duration' => '15 minutter' ),
		'NID'        => array( 'category' => 'marketing', 'provider' => 'Google',            'purpose' => 'Brugerpræferencer til annoncer.', 'duration' => '6 måneder' ),
		'ANID'       => array( 'category' => 'marketing', 'provider' => 'Google',            'purpose' => 'Annoncemåling.', 'duration' => '1 år' ),
		'1P_JAR'     => array( 'category' => 'marketing', 'provider' => 'Google',            'purpose' => 'Indsamler statistik og måler konverteringer.', 'duration' => '1 måned' ),
		'DV'         => array( 'category' => 'marketing', 'provider' => 'Google',            'purpose' => 'Annonce-profilering.', 'duration' => 'Session' ),
		'CONSENT'    => array( 'category' => 'necessary', 'provider' => 'Google',            'purpose' => 'Gemmer dit Google-samtykke.', 'duration' => '2 år' ),
		'SOCS'       => array( 'category' => 'necessary', 'provider' => 'Google',            'purpose' => 'Google cookie-samtykke.', 'duration' => '13 måneder' ),

		// Meta / Facebook
		'_fbp'       => array( 'category' => 'marketing', 'provider' => 'Meta Pixel',       'purpose' => 'Unikt bruger-ID til konverteringssporing.', 'duration' => '3 måneder' ),
		'_fbc'       => array( 'category' => 'marketing', 'provider' => 'Meta Pixel',       'purpose' => 'Gemmer click-ID fra annoncer.', 'duration' => '2 år' ),
		'fr'         => array( 'category' => 'marketing', 'provider' => 'Meta (Facebook)',  'purpose' => 'Annoncelevering/remarketing.', 'duration' => '3 måneder' ),
		'datr'       => array( 'category' => 'marketing', 'provider' => 'Meta (Facebook)',  'purpose' => 'Identificerer browseren.', 'duration' => '2 år' ),
		'sb'         => array( 'category' => 'marketing', 'provider' => 'Meta (Facebook)',  'purpose' => 'Login-understøttelse.', 'duration' => '2 år' ),

		// TikTok
		'_ttp'       => array( 'category' => 'marketing', 'provider' => 'TikTok Pixel',     'purpose' => 'Sporer interaktioner til annoncemåling.', 'duration' => '13 måneder' ),
		'ttwid'      => array( 'category' => 'marketing', 'provider' => 'TikTok',           'purpose' => 'Brugeridentifikation.', 'duration' => '1 år' ),

		// LinkedIn
		'li_sugr'    => array( 'category' => 'marketing', 'provider' => 'LinkedIn Insight', 'purpose' => 'Browser-ID til retargeting.', 'duration' => '3 måneder' ),
		'lidc'       => array( 'category' => 'marketing', 'provider' => 'LinkedIn',         'purpose' => 'Routing.', 'duration' => '1 dag' ),
		'bcookie'    => array( 'category' => 'marketing', 'provider' => 'LinkedIn',         'purpose' => 'Browser-ID.', 'duration' => '1 år' ),
		'bscookie'   => array( 'category' => 'marketing', 'provider' => 'LinkedIn',         'purpose' => 'Sikker browser-ID.', 'duration' => '1 år' ),
		'UserMatchHistory' => array( 'category' => 'marketing', 'provider' => 'LinkedIn',    'purpose' => 'Annonce-ID-synkronisering.', 'duration' => '1 måned' ),
		'AnalyticsSyncHistory' => array( 'category' => 'marketing', 'provider' => 'LinkedIn', 'purpose' => 'Sync af analyse-data.', 'duration' => '1 måned' ),

		// Microsoft Clarity
		'_clck'      => array( 'category' => 'analytics', 'provider' => 'Microsoft Clarity', 'purpose' => 'Bruger-ID og præferencer.', 'duration' => '1 år' ),
		'_clsk'      => array( 'category' => 'analytics', 'provider' => 'Microsoft Clarity', 'purpose' => 'Session-data.', 'duration' => '1 dag' ),
		'CLID'       => array( 'category' => 'analytics', 'provider' => 'Microsoft Clarity', 'purpose' => 'Bruger-ID.', 'duration' => '1 år' ),
		'MR'         => array( 'category' => 'marketing', 'provider' => 'Microsoft',         'purpose' => 'Bruger-ID til Bing.', 'duration' => '7 dage' ),
		'MUID'       => array( 'category' => 'marketing', 'provider' => 'Microsoft',         'purpose' => 'Universelt bruger-ID (Bing/MSN).', 'duration' => '1 år' ),
		'SM'         => array( 'category' => 'marketing', 'provider' => 'Microsoft',         'purpose' => 'Sync-cookie.', 'duration' => 'Session' ),

		// Hotjar
		'_hjSessionUser_*' => array( 'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Sporer brugeradfærd på tværs af sessioner.', 'duration' => '1 år' ),
		'_hjSession_*'     => array( 'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Holder session-data.', 'duration' => '30 minutter' ),
		'_hjIncludedInSessionSample_*' => array( 'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Bestemmer om brugeren er i Hotjar-sample.', 'duration' => '30 minutter' ),
		'_hjFirstSeen'     => array( 'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Markerer første besøg.', 'duration' => 'Session' ),
		'_hjAbsoluteSessionInProgress' => array( 'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Tæller unikke Hotjar-sessioner.', 'duration' => '30 minutter' ),

		// YouTube / embedded
		'VISITOR_INFO1_LIVE' => array( 'category' => 'marketing', 'provider' => 'YouTube', 'purpose' => 'Måler båndbredde til YouTube-afspilning.', 'duration' => '6 måneder' ),
		'VISITOR_PRIVACY_METADATA' => array( 'category' => 'marketing', 'provider' => 'YouTube', 'purpose' => 'Gemmer brugerens privacy-valg.', 'duration' => '6 måneder' ),
		'YSC'        => array( 'category' => 'marketing', 'provider' => 'YouTube',           'purpose' => 'Session-ID til YouTube.', 'duration' => 'Session' ),
		'PREF'       => array( 'category' => 'marketing', 'provider' => 'YouTube/Google',    'purpose' => 'Gemmer YouTube-præferencer.', 'duration' => '8 måneder' ),
		'__Secure-YEC' => array( 'category' => 'marketing', 'provider' => 'YouTube',         'purpose' => 'Sikker YouTube-cookie.', 'duration' => '13 måneder' ),
		'__Secure-ROLLOUT_TOKEN' => array( 'category' => 'marketing', 'provider' => 'YouTube', 'purpose' => 'YouTube feature-rollout.', 'duration' => '6 måneder' ),

		// Vimeo
		'vuid'       => array( 'category' => 'analytics', 'provider' => 'Vimeo',             'purpose' => 'Brugeridentifikation.', 'duration' => '2 år' ),
		'player'     => array( 'category' => 'necessary', 'provider' => 'Vimeo',             'purpose' => 'Afspiller-indstillinger.', 'duration' => '1 år' ),

		// Cloudflare
		'__cf_bm'    => array( 'category' => 'necessary', 'provider' => 'Cloudflare',        'purpose' => 'Bot-beskyttelse.', 'duration' => '30 minutter' ),
		'cf_clearance' => array( 'category' => 'necessary', 'provider' => 'Cloudflare',       'purpose' => 'Challenge-verifikation.', 'duration' => '1 år' ),
		'__cfduid'   => array( 'category' => 'necessary', 'provider' => 'Cloudflare',        'purpose' => 'Identificerer individuelle klienter bag delt IP.', 'duration' => '1 måned' ),
		'_cfuvid'    => array( 'category' => 'necessary', 'provider' => 'Cloudflare',        'purpose' => 'Bot-detection og rate-limiting.', 'duration' => 'Session' ),

		// WordPress / basis
		's247_consent' => array( 'category' => 'necessary', 'provider' => 'Studie 247',      'purpose' => 'Gemmer dit cookie-samtykke.', 'duration' => '12 måneder' ),
		'wordpress_test_cookie' => array( 'category' => 'necessary', 'provider' => 'WordPress', 'purpose' => 'Tester om browseren accepterer cookies.', 'duration' => 'Session' ),
		'wordpress_logged_in_*' => array( 'category' => 'necessary', 'provider' => 'WordPress', 'purpose' => 'Holder dig logget ind.', 'duration' => '15 dage' ),
		'wordpress_sec_*' => array( 'category' => 'necessary', 'provider' => 'WordPress',     'purpose' => 'Sikker login-session.', 'duration' => '15 dage' ),
		'wp-settings-*' => array( 'category' => 'necessary', 'provider' => 'WordPress',      'purpose' => 'Husker dine præferencer i wp-admin.', 'duration' => '1 år' ),
		'wp-settings-time-*' => array( 'category' => 'necessary', 'provider' => 'WordPress', 'purpose' => 'Tidsstempel for wp-admin-præferencer.', 'duration' => '1 år' ),
		'PHPSESSID'  => array( 'category' => 'necessary', 'provider' => 'Server',            'purpose' => 'Holder din session mens du browser.', 'duration' => 'Session' ),
		'comment_author_*' => array( 'category' => 'necessary', 'provider' => 'WordPress',   'purpose' => 'Husker kommentar-afsender.', 'duration' => '1 år' ),
		'comment_author_email_*' => array( 'category' => 'necessary', 'provider' => 'WordPress', 'purpose' => 'Husker kommentar-email.', 'duration' => '1 år' ),
		'comment_author_url_*' => array( 'category' => 'necessary', 'provider' => 'WordPress', 'purpose' => 'Husker kommentar-URL.', 'duration' => '1 år' ),

		// WooCommerce
		'woocommerce_cart_hash'    => array( 'category' => 'necessary', 'provider' => 'WooCommerce', 'purpose' => 'Holder styr på indhold i kurven.', 'duration' => 'Session' ),
		'woocommerce_items_in_cart'=> array( 'category' => 'necessary', 'provider' => 'WooCommerce', 'purpose' => 'Antal varer i kurven.', 'duration' => 'Session' ),
		'wp_woocommerce_session_*' => array( 'category' => 'necessary', 'provider' => 'WooCommerce', 'purpose' => 'Session-data for kunden.', 'duration' => '48 timer' ),
		'woocommerce_recently_viewed' => array( 'category' => 'necessary', 'provider' => 'WooCommerce', 'purpose' => 'Senest viste produkter.', 'duration' => 'Session' ),

		// Cookie-banners / consent (hvis scripts er aktive)
		'cookieyes-consent' => array( 'category' => 'necessary', 'provider' => 'CookieYes',  'purpose' => 'Gemmer cookie-samtykke.', 'duration' => '1 år' ),
		'borlabs-cookie'    => array( 'category' => 'necessary', 'provider' => 'Borlabs',    'purpose' => 'Gemmer cookie-samtykke.', 'duration' => '1 år' ),

		// Stripe
		'__stripe_mid' => array( 'category' => 'necessary', 'provider' => 'Stripe',          'purpose' => 'Betalingssvindel-beskyttelse.', 'duration' => '1 år' ),
		'__stripe_sid' => array( 'category' => 'necessary', 'provider' => 'Stripe',          'purpose' => 'Session til betaling.', 'duration' => '30 minutter' ),
		'm'            => array( 'category' => 'necessary', 'provider' => 'Stripe',          'purpose' => 'Betalingsflow.', 'duration' => '2 år' ),

		// HubSpot
		'__hstc'      => array( 'category' => 'analytics', 'provider' => 'HubSpot',          'purpose' => 'Sporer besøgende.', 'duration' => '6 måneder' ),
		'hubspotutk'  => array( 'category' => 'analytics', 'provider' => 'HubSpot',          'purpose' => 'Brugeridentifikation.', 'duration' => '6 måneder' ),
		'__hssc'      => array( 'category' => 'analytics', 'provider' => 'HubSpot',          'purpose' => 'Tæller sessioner.', 'duration' => '30 minutter' ),
		'__hssrc'     => array( 'category' => 'analytics', 'provider' => 'HubSpot',          'purpose' => 'Identificerer session-genstart.', 'duration' => 'Session' ),
	);
}

/**
 * Slå en cookie op i ordbogen — understøtter præfiks-match med `_*`.
 * Returnerer fuld cookie-record eller null hvis ukendt.
 */
function studie247_cookie_lookup( $name ) {
	$lex = studie247_cookie_lexicon();
	if ( isset( $lex[ $name ] ) ) {
		return array_merge( array( 'name' => $name ), $lex[ $name ] );
	}
	// Præfiks-match.
	foreach ( $lex as $pattern => $meta ) {
		if ( substr( $pattern, -2 ) !== '_*' ) { continue; }
		$prefix = substr( $pattern, 0, -1 ); // "_ga_*" → "_ga_"
		if ( 0 === strpos( $name, $prefix ) ) {
			return array_merge( array( 'name' => $name ), $meta );
		}
	}
	return null;
}

/* ───────── REST-endpoint: modtag cookie-navne fra browseren ───────── */
add_action( 'rest_api_init', function () {
	register_rest_route( 's247/v1', '/cookies/report', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'permission_callback' => '__return_true',
		'callback'            => 'studie247_cookies_report_callback',
		'args'                => array(
			'names' => array(
				'required' => true,
				'type'     => 'array',
			),
		),
	) );
} );

/**
 * Modtager en liste af cookie-NAVNE (aldrig værdier) og opdaterer
 * den auto-opdagede liste i s247_cookies_seen.
 */
function studie247_cookies_report_callback( WP_REST_Request $req ) {
	$names = $req->get_param( 'names' );
	if ( ! is_array( $names ) ) {
		return new WP_Error( 's247_bad_input', 'names skal være en array.', array( 'status' => 400 ) );
	}

	$seen    = get_option( 's247_cookies_seen', array() );
	if ( ! is_array( $seen ) ) { $seen = array(); }

	$changed = false;
	foreach ( $names as $raw ) {
		$name = (string) $raw;
		// Cookie-navne: kun ASCII-safe tegn, max 128 tegn.
		$name = preg_replace( '/[^A-Za-z0-9_\-\.]/', '', $name );
		if ( '' === $name || strlen( $name ) > 128 ) { continue; }
		if ( isset( $seen[ $name ] ) ) { continue; } // allerede kendt

		$meta = studie247_cookie_lookup( $name );
		if ( null === $meta ) {
			// Ukendt → default til analytics (kræver samtykke — sikkert valg).
			$meta = array(
				'name'     => $name,
				'category' => 'analytics',
				'provider' => __( '(ukendt)', 'studie247' ),
				'purpose'  => __( 'Automatisk opdaget — ikke endnu kategoriseret af administrator.', 'studie247' ),
				'duration' => '—',
			);
		}
		$seen[ $name ] = $meta;
		$changed = true;
	}

	if ( $changed ) {
		update_option( 's247_cookies_seen', $seen );
		// Flet straks ind i den aktive liste.
		if ( function_exists( 'studie247_cookies_scan' ) ) {
			studie247_cookies_scan();
		}
	}

	return array( 'ok' => true, 'known' => count( $seen ) );
}

/* ───────── Filter: flet auto-opdagede cookies ind i scan-resultatet ───── */
add_filter( 's247_cookies_scan_merge', function ( $detected ) {
	$seen = get_option( 's247_cookies_seen', array() );
	if ( ! is_array( $seen ) ) { return $detected; }
	foreach ( $seen as $c ) { $detected[] = $c; }
	return $detected;
} );

/* ───────── Frontend-JS: læs document.cookie og rapportér navne ───────── */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() ) { return; }

	$js = <<<JS
(function(){
  try {
    var reported = sessionStorage.getItem('s247_cookies_reported');
    var run = function(){
      var raw = document.cookie || '';
      if (!raw) return;
      var names = raw.split(';').map(function(p){
        var idx = p.indexOf('=');
        var n = (idx >= 0 ? p.substring(0, idx) : p).trim();
        return n;
      }).filter(Boolean);
      if (!names.length) return;
      // Dedupe + rate-limit: samme sæt i samme session skal ikke sendes 2x.
      names.sort();
      var sig = names.join('|');
      if (reported === sig) return;
      fetch('%REST%', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({names: names})
      }).then(function(){
        try { sessionStorage.setItem('s247_cookies_reported', sig); } catch(e){}
      }).catch(function(){});
    };
    // Lille delay så third-party scripts når at sætte deres cookies først.
    if (window.requestIdleCallback) {
      requestIdleCallback(function(){ setTimeout(run, 1500); });
    } else {
      setTimeout(run, 2500);
    }
  } catch(e){}
})();
JS;
	$js = str_replace( '%REST%', esc_url_raw( rest_url( 's247/v1/cookies/report' ) ), $js );

	wp_register_script( 's247-cookie-autodetect', '', array(), null, true );
	wp_enqueue_script( 's247-cookie-autodetect' );
	wp_add_inline_script( 's247-cookie-autodetect', $js );
} );
