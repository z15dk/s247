<?php
/**
 * Cookie-banner — EU/GDPR-kompatibel.
 *
 * Viser et banner i bunden af siden ved første besøg. Tre valg:
 * "Acceptér alle", "Afvis alle", "Indstillinger" (granulær opt-in
 * pr. kategori). Samtykke gemmes i cookie + localStorage i 12 mdr.
 *
 * Tekster redigeres i Customizer → Cookie-banner.
 *
 * Cookie-registry: Ugentlig WP-cron scanner aktive plugins og
 * enqueuede scripts for kendte cookies (Google Analytics, Meta Pixel
 * m.fl.) og opdaterer listen. Admin kan tilføje/fjerne manuelt i
 * Værktøjer → Cookies. Shortcode [s247_cookies_table] viser listen
 * fx på /cookies/-siden.
 *
 * Andre scripts kan tjekke samtykke via:
 *   window.s247Consent.has('analytics')   → boolean
 *   window.s247Consent.open()             → åbn indstillinger-modal
 *   document.addEventListener('s247-consent-changed', cb)
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kendte cookies (provider, formål, varighed) grupperet pr. kategori.
 * Scanneren matcher detekterede services mod denne liste.
 */
function studie247_cookies_known() {
	return array(
		// Altid til stede — teknisk nødvendige.
		'_base_necessary' => array(
			array( 'name' => 's247_consent',        'category' => 'necessary', 'provider' => 'Studie 247',    'purpose' => 'Gemmer dit cookie-samtykke.',                                   'duration' => '12 måneder' ),
			array( 'name' => 'wordpress_test_cookie','category' => 'necessary','provider' => 'WordPress',     'purpose' => 'Bruges til at teste om din browser accepterer cookies.',        'duration' => 'Session' ),
			array( 'name' => 'wordpress_logged_in_*','category' => 'necessary','provider' => 'WordPress',     'purpose' => 'Holder dig logget ind.',                                        'duration' => '15 dage' ),
			array( 'name' => 'wp-settings-*',        'category' => 'necessary','provider' => 'WordPress',     'purpose' => 'Husker dine præferencer i wp-admin.',                            'duration' => '1 år' ),
			array( 'name' => 'PHPSESSID',            'category' => 'necessary','provider' => 'Server',        'purpose' => 'Holder din session mens du browser.',                            'duration' => 'Session' ),
		),
		'ga' => array(
			array( 'name' => '_ga',      'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Anvendes til at skelne mellem brugere.',          'duration' => '2 år' ),
			array( 'name' => '_ga_*',    'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Anvendes til at holde session-state.',            'duration' => '2 år' ),
			array( 'name' => '_gid',     'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Anvendes til at skelne mellem brugere.',          'duration' => '24 timer' ),
			array( 'name' => '_gat*',    'category' => 'analytics', 'provider' => 'Google Analytics', 'purpose' => 'Begrænser request-frekvensen.',                    'duration' => '1 minut' ),
		),
		'meta_pixel' => array(
			array( 'name' => '_fbp',     'category' => 'marketing', 'provider' => 'Meta (Facebook) Pixel', 'purpose' => 'Unikt bruger-ID for konverteringssporing.',   'duration' => '3 måneder' ),
			array( 'name' => 'fr',       'category' => 'marketing', 'provider' => 'Meta (Facebook)',       'purpose' => 'Leveret af Facebook til annoncering.',       'duration' => '3 måneder' ),
		),
		'tiktok' => array(
			array( 'name' => '_ttp',     'category' => 'marketing', 'provider' => 'TikTok Pixel', 'purpose' => 'Sporer interaktioner til annoncemåling.', 'duration' => '13 måneder' ),
		),
		'linkedin' => array(
			array( 'name' => 'li_sugr',  'category' => 'marketing', 'provider' => 'LinkedIn Insight', 'purpose' => 'Browser-ID til retargeting.',                'duration' => '3 måneder' ),
			array( 'name' => 'lidc',     'category' => 'marketing', 'provider' => 'LinkedIn',         'purpose' => 'Routing.',                                    'duration' => '1 dag' ),
		),
		'hotjar' => array(
			array( 'name' => '_hjSessionUser_*',  'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Sporer bruger-adfærd på tværs af sessioner.', 'duration' => '1 år' ),
			array( 'name' => '_hjSession_*',      'category' => 'analytics', 'provider' => 'Hotjar', 'purpose' => 'Holder session-data.',                        'duration' => '30 minutter' ),
		),
		'clarity' => array(
			array( 'name' => '_clck',    'category' => 'analytics', 'provider' => 'Microsoft Clarity', 'purpose' => 'Bruger-ID og præferencer.', 'duration' => '1 år' ),
			array( 'name' => '_clsk',    'category' => 'analytics', 'provider' => 'Microsoft Clarity', 'purpose' => 'Session-data.',              'duration' => '1 dag' ),
		),
		'woocommerce' => array(
			array( 'name' => 'woocommerce_cart_hash',  'category' => 'necessary', 'provider' => 'WooCommerce', 'purpose' => 'Holder styr på indhold i kurven.', 'duration' => 'Session' ),
			array( 'name' => 'woocommerce_items_in_cart','category' => 'necessary','provider' => 'WooCommerce','purpose' => 'Antal varer i kurven.',            'duration' => 'Session' ),
			array( 'name' => 'wp_woocommerce_session_*','category' => 'necessary','provider' => 'WooCommerce', 'purpose' => 'Session-data for kunden.',        'duration' => '48 timer' ),
		),
	);
}

/**
 * Scan det aktive site for services der typisk sætter cookies.
 * Kører ugentligt via WP-cron + manuelt fra admin-siden.
 */
function studie247_cookies_scan() {
	$known    = studie247_cookies_known();
	$detected = array();

	// Altid-nødvendige cookies (WP + vores egen).
	foreach ( $known['_base_necessary'] as $c ) { $detected[] = $c; }

	// Tjek aktive plugins for kendte tracking-integrationer.
	$plugins = get_option( 'active_plugins', array() );
	foreach ( $plugins as $p ) {
		if ( false !== strpos( $p, 'woocommerce' ) ) {
			foreach ( $known['woocommerce'] as $c ) { $detected[] = $c; }
		}
		if ( false !== strpos( $p, 'google-analytics' ) || false !== strpos( $p, 'ga-google-analytics' ) || false !== strpos( $p, 'monsterinsights' ) || false !== strpos( $p, 'site-kit' ) ) {
			foreach ( $known['ga'] as $c ) { $detected[] = $c; }
		}
		if ( false !== strpos( $p, 'facebook-for-wordpress' ) || false !== strpos( $p, 'pixel-caffeine' ) || false !== strpos( $p, 'meta-pixel' ) ) {
			foreach ( $known['meta_pixel'] as $c ) { $detected[] = $c; }
		}
	}

	// Scan forsidens HTML for kendte tracking-URLs (fanger inline scripts der ikke går gennem wp_enqueue).
	$home   = home_url( '/' );
	$resp   = wp_remote_get( $home, array( 'timeout' => 10, 'redirection' => 3, 'user-agent' => 'Studie247-CookieScanner/1.0' ) );
	$html   = is_wp_error( $resp ) ? '' : wp_remote_retrieve_body( $resp );
	$checks = array(
		'ga'         => array( 'googletagmanager.com/gtag', 'google-analytics.com/analytics.js', 'google-analytics.com/ga.js', 'gtag(' ),
		'meta_pixel' => array( 'connect.facebook.net', 'fbq(', 'fbevents.js' ),
		'tiktok'     => array( 'analytics.tiktok.com', 'ttq.', 'ttq(' ),
		'linkedin'   => array( 'snap.licdn.com', 'px.ads.linkedin.com' ),
		'hotjar'     => array( 'static.hotjar.com', '_hjSettings' ),
		'clarity'    => array( 'clarity.ms/tag' ),
	);
	foreach ( $checks as $key => $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $html, $needle ) ) {
				foreach ( $known[ $key ] as $c ) { $detected[] = $c; }
				break;
			}
		}
	}

	// Flet med admin-tilføjede custom cookies.
	$custom = get_option( 's247_cookies_custom', array() );
	if ( is_array( $custom ) ) {
		foreach ( $custom as $c ) { $detected[] = $c; }
	}

	// Tillad andre moduler (fx cookie-auto-detect) at flette cookies ind.
	$detected = apply_filters( 's247_cookies_scan_merge', $detected );

	// De-dup på (name, provider).
	$out = array();
	foreach ( $detected as $c ) {
		$key = strtolower( ( $c['name'] ?? '' ) . '|' . ( $c['provider'] ?? '' ) );
		if ( ! isset( $out[ $key ] ) ) { $out[ $key ] = $c; }
	}
	$out = array_values( $out );

	update_option( 's247_cookies_detected', $out );
	update_option( 's247_cookies_last_scan', time() );

	if ( function_exists( 'studie247_audit_log' ) ) {
		studie247_audit_log( sprintf( __( 'cookie-scan — %d cookies fundet', 'studie247' ), count( $out ) ), 0, 'cookies' );
	}

	return $out;
}

/**
 * Planlæg ugentlig scanning.
 */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'studie247_cookies_weekly_scan' ) ) {
		wp_schedule_event( time() + 60, 'weekly', 'studie247_cookies_weekly_scan' );
	}
} );
add_action( 'studie247_cookies_weekly_scan', 'studie247_cookies_scan' );

// Kør første scan når temaet aktiveres (ingen scan endnu).
add_action( 'after_setup_theme', function () {
	if ( ! get_option( 's247_cookies_last_scan' ) ) {
		// Forsink lidt så after_setup_theme ikke bliver tung.
		wp_schedule_single_event( time() + 5, 'studie247_cookies_weekly_scan' );
	}
}, 50 );

/**
 * Hent nuværende liste (detected + custom, pr. kategori).
 */
function studie247_cookies_get_list() {
	$list = get_option( 's247_cookies_detected', array() );
	if ( ! is_array( $list ) || empty( $list ) ) {
		$list = studie247_cookies_scan();
	}
	$grouped = array( 'necessary' => array(), 'analytics' => array(), 'marketing' => array() );
	foreach ( $list as $c ) {
		$cat = isset( $c['category'] ) && isset( $grouped[ $c['category'] ] ) ? $c['category'] : 'necessary';
		$grouped[ $cat ][] = $c;
	}
	return $grouped;
}

/**
 * Shortcode [s247_cookies_table] — viser alle cookies i en pæn tabel.
 * Bruges typisk på /cookies/-siden.
 */
add_shortcode( 's247_cookies_table', function () {
	$grouped = studie247_cookies_get_list();
	$labels  = array(
		'necessary' => __( 'Nødvendige cookies', 'studie247' ),
		'analytics' => __( 'Statistik-cookies', 'studie247' ),
		'marketing' => __( 'Marketing-cookies', 'studie247' ),
	);
	$last_scan = (int) get_option( 's247_cookies_last_scan' );

	ob_start(); ?>
	<div class="s247-cookies-table">
		<?php foreach ( $grouped as $cat => $items ) : if ( empty( $items ) ) continue; ?>
			<h3><?php echo esc_html( $labels[ $cat ] ); ?></h3>
			<table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
				<thead>
					<tr style="text-align:left;border-bottom:2px solid currentColor;">
						<th style="padding:8px 10px;"><?php esc_html_e( 'Navn', 'studie247' ); ?></th>
						<th style="padding:8px 10px;"><?php esc_html_e( 'Udbyder', 'studie247' ); ?></th>
						<th style="padding:8px 10px;"><?php esc_html_e( 'Formål', 'studie247' ); ?></th>
						<th style="padding:8px 10px;"><?php esc_html_e( 'Varighed', 'studie247' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $c ) : ?>
						<tr style="border-bottom:1px solid rgba(0,0,0,0.08);">
							<td style="padding:8px 10px;font-family:monospace;font-size:13px;"><?php echo esc_html( $c['name'] ?? '' ); ?></td>
							<td style="padding:8px 10px;"><?php echo esc_html( $c['provider'] ?? '' ); ?></td>
							<td style="padding:8px 10px;"><?php echo esc_html( $c['purpose'] ?? '' ); ?></td>
							<td style="padding:8px 10px;white-space:nowrap;"><?php echo esc_html( $c['duration'] ?? '' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endforeach; ?>
		<?php if ( $last_scan ) : ?>
			<p style="font-size:12px;color:#666;"><?php printf( esc_html__( 'Sidst scannet: %s', 'studie247' ), esc_html( date_i18n( 'j. M Y H:i', $last_scan ) ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
} );

/**
 * Admin-side: Værktøjer → Cookies (manuelt scan + custom cookies).
 */
add_action( 'admin_menu', function () {
	add_submenu_page(
		'tools.php',
		__( 'Studie 247 — Cookies', 'studie247' ),
		__( 'Cookies', 'studie247' ),
		'manage_options',
		's247-cookies',
		'studie247_cookies_admin_page'
	);
} );

function studie247_cookies_admin_page() {
	if ( isset( $_POST['s247_cookies_scan_now'] ) && check_admin_referer( 's247_cookies' ) ) {
		studie247_cookies_scan();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Scan kørt.', 'studie247' ) . '</p></div>';
	}
	if ( isset( $_POST['s247_cookies_save_custom'] ) && check_admin_referer( 's247_cookies' ) ) {
		$in = isset( $_POST['cc'] ) && is_array( $_POST['cc'] ) ? wp_unslash( $_POST['cc'] ) : array();
		$out = array();
		foreach ( $in as $row ) {
			$name = sanitize_text_field( $row['name'] ?? '' );
			if ( ! $name ) { continue; }
			$out[] = array(
				'name'     => $name,
				'category' => in_array( ( $row['category'] ?? '' ), array( 'necessary', 'analytics', 'marketing' ), true ) ? $row['category'] : 'necessary',
				'provider' => sanitize_text_field( $row['provider'] ?? '' ),
				'purpose'  => sanitize_text_field( $row['purpose'] ?? '' ),
				'duration' => sanitize_text_field( $row['duration'] ?? '' ),
			);
		}
		update_option( 's247_cookies_custom', $out );
		studie247_cookies_scan();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Custom cookies gemt + scan kørt.', 'studie247' ) . '</p></div>';
	}

	$list      = get_option( 's247_cookies_detected', array() );
	$custom    = get_option( 's247_cookies_custom', array() );
	$last_scan = (int) get_option( 's247_cookies_last_scan' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Cookies', 'studie247' ); ?></h1>
		<p><?php esc_html_e( 'Cookies scannes automatisk én gang om ugen. Du kan også scanne manuelt og tilføje custom cookies herunder. Listen bruges i banneret og via shortcode', 'studie247' ); ?> <code>[s247_cookies_table]</code>.</p>

		<form method="post" style="margin:16px 0;">
			<?php wp_nonce_field( 's247_cookies' ); ?>
			<button type="submit" name="s247_cookies_scan_now" value="1" class="button button-primary">🔍 <?php esc_html_e( 'Scan nu', 'studie247' ); ?></button>
			<?php if ( $last_scan ) : ?>
				<span style="margin-left:12px;color:#666;"><?php printf( esc_html__( 'Sidste scan: %s', 'studie247' ), esc_html( date_i18n( 'j. M Y H:i', $last_scan ) ) ); ?></span>
			<?php endif; ?>
		</form>

		<h2><?php esc_html_e( 'Detekteret', 'studie247' ); ?> (<?php echo (int) count( $list ); ?>)</h2>
		<table class="widefat striped">
			<thead><tr><th>Navn</th><th>Kategori</th><th>Udbyder</th><th>Formål</th><th>Varighed</th></tr></thead>
			<tbody>
				<?php foreach ( $list as $c ) : ?>
					<tr>
						<td><code><?php echo esc_html( $c['name'] ?? '' ); ?></code></td>
						<td><?php echo esc_html( $c['category'] ?? '' ); ?></td>
						<td><?php echo esc_html( $c['provider'] ?? '' ); ?></td>
						<td><?php echo esc_html( $c['purpose'] ?? '' ); ?></td>
						<td><?php echo esc_html( $c['duration'] ?? '' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'Tilføj custom cookies', 'studie247' ); ?></h2>
		<p style="color:#666;"><?php esc_html_e( 'Brug dette hvis du har tilføjet tracking/udbydere som scanneren ikke kender.', 'studie247' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 's247_cookies' ); ?>
			<table class="widefat" id="s247-cc-table">
				<thead><tr><th>Navn</th><th>Kategori</th><th>Udbyder</th><th>Formål</th><th>Varighed</th><th></th></tr></thead>
				<tbody>
					<?php
					$rows = $custom ? $custom : array();
					$rows[] = array(); // tom række til ny
					foreach ( $rows as $i => $row ) : ?>
						<tr>
							<td><input type="text" name="cc[<?php echo $i; ?>][name]"     value="<?php echo esc_attr( $row['name']     ?? '' ); ?>" placeholder="fx _pk_id"></td>
							<td>
								<select name="cc[<?php echo $i; ?>][category]">
									<option value="necessary" <?php selected( ( $row['category'] ?? '' ), 'necessary' ); ?>>necessary</option>
									<option value="analytics" <?php selected( ( $row['category'] ?? '' ), 'analytics' ); ?>>analytics</option>
									<option value="marketing" <?php selected( ( $row['category'] ?? '' ), 'marketing' ); ?>>marketing</option>
								</select>
							</td>
							<td><input type="text" name="cc[<?php echo $i; ?>][provider]" value="<?php echo esc_attr( $row['provider'] ?? '' ); ?>" placeholder="fx Matomo"></td>
							<td><input type="text" name="cc[<?php echo $i; ?>][purpose]"  value="<?php echo esc_attr( $row['purpose']  ?? '' ); ?>" placeholder="kort beskrivelse"></td>
							<td><input type="text" name="cc[<?php echo $i; ?>][duration]" value="<?php echo esc_attr( $row['duration'] ?? '' ); ?>" placeholder="fx 1 år"></td>
							<td></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="submit" name="s247_cookies_save_custom" value="1" class="button button-primary"><?php esc_html_e( 'Gem custom cookies + kør scan', 'studie247' ); ?></button></p>
		</form>
	</div>
	<?php
}

/**
 * Customizer-felter: tekster der vises i banneret.
 */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 's247_cookie_banner', array(
		'title'    => __( 'Cookie-banner', 'studie247' ),
		'priority' => 95,
	) );

	$fields = array(
		's247_cookie_title'       => array( 'type' => 'text',     'label' => __( 'Overskrift', 'studie247' ),            'default' => 'Vi bruger cookies' ),
		's247_cookie_intro'       => array( 'type' => 'textarea', 'label' => __( 'Intro-tekst (HTML tilladt)', 'studie247' ), 'default' => 'Vi bruger cookies til at forbedre din oplevelse på sitet og til statistik. Nødvendige cookies er altid aktive. Du kan til enhver tid ændre dit valg i footeren.' ),
		's247_cookie_btn_accept'  => array( 'type' => 'text',     'label' => __( 'Knap: Acceptér alle', 'studie247' ),    'default' => 'Acceptér alle' ),
		's247_cookie_btn_reject'  => array( 'type' => 'text',     'label' => __( 'Knap: Afvis alle', 'studie247' ),       'default' => 'Afvis alle' ),
		's247_cookie_btn_options' => array( 'type' => 'text',     'label' => __( 'Knap: Indstillinger', 'studie247' ),    'default' => 'Indstillinger' ),
		's247_cookie_cat_nec'     => array( 'type' => 'textarea', 'label' => __( 'Kategori: Nødvendige — beskrivelse', 'studie247' ), 'default' => 'Kræves for at sitet fungerer (login, sessions, consent). Kan ikke slås fra.' ),
		's247_cookie_cat_stats'   => array( 'type' => 'textarea', 'label' => __( 'Kategori: Statistik — beskrivelse', 'studie247' ),  'default' => 'Hjælper os med at forstå hvordan sitet bruges, så vi kan forbedre det.' ),
		's247_cookie_cat_mkt'     => array( 'type' => 'textarea', 'label' => __( 'Kategori: Marketing — beskrivelse', 'studie247' ),  'default' => 'Bruges til at vise relevante annoncer fra os når du besøger andre sites.' ),
		's247_cookie_policy_url'  => array( 'type' => 'url',      'label' => __( 'Link til cookie-politik', 'studie247' ),'default' => '/cookies/' ),
	);

	foreach ( $fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => ( 'textarea' === $cfg['type'] ) ? 'wp_kses_post' : ( 'url' === $cfg['type'] ? 'esc_url_raw' : 'sanitize_text_field' ),
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $cfg['label'],
			'section' => 's247_cookie_banner',
			'type'    => $cfg['type'],
		) );
	}
} );

/**
 * Blok tracking-embeds (YouTube, Vimeo, Google Maps, Facebook osv.)
 * i the_content indtil brugeren har givet samtykke til den rette
 * kategori. Iframe'en gemmes i data-src; JS aktiverer den kun hvis
 * samtykke eksisterer — ellers vises en placeholder med "Tillad"-knap.
 *
 * Selv-hostede <video>-tags påvirkes IKKE (de bruger ingen cookies).
 */
add_filter( 'the_content', 'studie247_cookie_gate_embeds', 50 );
function studie247_cookie_gate_embeds( $html ) {
	if ( is_admin() || ! is_string( $html ) || '' === $html ) { return $html; }

	// Mapping: domæne → (kategori, label)
	$gates = array(
		'youtube.com/embed'        => array( 'marketing', 'YouTube-video' ),
		'youtube-nocookie.com'     => array( 'marketing', 'YouTube-video' ),
		'youtu.be'                 => array( 'marketing', 'YouTube-video' ),
		'player.vimeo.com'         => array( 'marketing', 'Vimeo-video' ),
		'vimeo.com/video'          => array( 'marketing', 'Vimeo-video' ),
		'google.com/maps/embed'    => array( 'marketing', 'Google Maps' ),
		'maps.google.com'          => array( 'marketing', 'Google Maps' ),
		'facebook.com/plugins'     => array( 'marketing', 'Facebook embed' ),
		'instagram.com/embed'      => array( 'marketing', 'Instagram-opslag' ),
		'twitter.com/embed'        => array( 'marketing', 'Twitter/X-opslag' ),
		'platform.twitter.com'     => array( 'marketing', 'Twitter/X-opslag' ),
		'open.spotify.com/embed'   => array( 'marketing', 'Spotify-embed' ),
		'w.soundcloud.com'         => array( 'marketing', 'SoundCloud-embed' ),
		'tiktok.com/embed'         => array( 'marketing', 'TikTok-video' ),
	);

	return preg_replace_callback( '#<iframe\b[^>]*\bsrc=(["\'])([^"\']+)\1[^>]*>.*?</iframe>#is', function ( $m ) use ( $gates ) {
		$src = $m[2];
		foreach ( $gates as $needle => $info ) {
			if ( false !== strpos( $src, $needle ) ) {
				list( $category, $label ) = $info;
				$esc_src = esc_url( $src );
				$esc_lbl = esc_html( $label );
				// Bevar øvrige iframe-attributter (width, height, allow etc.) men flyt src → data-src.
				$tag = preg_replace( '#\bsrc=(["\'])[^"\']+\1#i', 'data-src="' . $esc_src . '" src="about:blank" loading="lazy"', $m[0] );
				return '<div class="s247-embed-gate" data-category="' . esc_attr( $category ) . '" data-label="' . esc_attr( $label ) . '">'
					. '<div class="s247-embed-gate__placeholder">'
					. '<span class="s247-embed-gate__icon">🍪</span>'
					. '<p class="s247-embed-gate__text">' . sprintf( esc_html__( '%s kræver marketing-cookies for at afspille.', 'studie247' ), $esc_lbl ) . '</p>'
					. '<button type="button" class="s247-embed-gate__btn" data-s247-embed-accept>' . esc_html__( 'Tillad og afspil', 'studie247' ) . '</button>'
					. '</div>'
					. '<div class="s247-embed-gate__iframe" hidden>' . $tag . '</div>'
					. '</div>';
			}
		}
		return $m[0];
	}, $html );
}

/**
 * Render banner HTML + inline CSS/JS i footeren.
 * Inline fordi banneret skal kunne vises før noget andet JS
 * loader (og vi vil ikke lave en ekstra request).
 */
add_action( 'wp_footer', 'studie247_cookie_banner_render', 99 );
function studie247_cookie_banner_render() {
	$title       = get_theme_mod( 's247_cookie_title',       'Vi bruger cookies' );
	$intro       = get_theme_mod( 's247_cookie_intro',       'Vi bruger cookies til at forbedre din oplevelse på sitet og til statistik. Nødvendige cookies er altid aktive. Du kan til enhver tid ændre dit valg i footeren.' );
	$btn_accept  = get_theme_mod( 's247_cookie_btn_accept',  'Acceptér alle' );
	$btn_reject  = get_theme_mod( 's247_cookie_btn_reject',  'Afvis alle' );
	$btn_options = get_theme_mod( 's247_cookie_btn_options', 'Indstillinger' );
	$cat_nec     = get_theme_mod( 's247_cookie_cat_nec',     'Kræves for at sitet fungerer. Kan ikke slås fra.' );
	$cat_stats   = get_theme_mod( 's247_cookie_cat_stats',   'Hjælper os med at forstå hvordan sitet bruges.' );
	$cat_mkt     = get_theme_mod( 's247_cookie_cat_mkt',     'Bruges til at vise relevante annoncer.' );
	$policy_url  = get_theme_mod( 's247_cookie_policy_url',  '/cookies/' );
	?>
	<div id="s247-cookie" class="s247-cookie" role="dialog" aria-label="<?php esc_attr_e( 'Cookie-samtykke', 'studie247' ); ?>" aria-describedby="s247-cookie-intro" hidden>
		<div class="s247-cookie__card">
			<div class="s247-cookie__head">
				<h2 class="s247-cookie__title"><?php echo esc_html( $title ); ?></h2>
			</div>
			<div id="s247-cookie-intro" class="s247-cookie__intro">
				<?php echo wp_kses_post( wpautop( $intro ) ); ?>
				<?php if ( $policy_url ) : ?>
					<p><a href="<?php echo esc_url( $policy_url ); ?>"><?php esc_html_e( 'Læs mere om cookies →', 'studie247' ); ?></a></p>
				<?php endif; ?>
			</div>

			<?php
			$cookies_grouped = studie247_cookies_get_list();
			$cat_config = array(
				'necessary' => array( 'label' => __( 'Nødvendige', 'studie247' ), 'desc' => $cat_nec,   'always' => true ),
				'analytics' => array( 'label' => __( 'Statistik', 'studie247' ),  'desc' => $cat_stats, 'always' => false ),
				'marketing' => array( 'label' => __( 'Marketing', 'studie247' ),  'desc' => $cat_mkt,   'always' => false ),
			);
			?>
			<div class="s247-cookie__options" hidden data-s247-cookie-options>
				<?php foreach ( $cat_config as $key => $cfg ) : $items = $cookies_grouped[ $key ] ?? array(); ?>
					<div class="s247-cookie__cat-wrap">
						<label class="s247-cookie__cat">
							<input type="checkbox" data-key="<?php echo esc_attr( $key ); ?>" <?php if ( $cfg['always'] ) echo 'checked disabled'; ?>>
							<div>
								<strong><?php echo esc_html( $cfg['label'] ); ?><?php if ( $items ) : ?> <span class="s247-cookie__count">(<?php echo (int) count( $items ); ?>)</span><?php endif; ?></strong>
								<span><?php echo esc_html( $cfg['desc'] ); ?></span>
							</div>
						</label>
						<?php if ( $items ) : ?>
							<details class="s247-cookie__details">
								<summary><?php esc_html_e( 'Se cookies i brug', 'studie247' ); ?></summary>
								<table>
									<thead><tr><th><?php esc_html_e( 'Navn', 'studie247' ); ?></th><th><?php esc_html_e( 'Udbyder', 'studie247' ); ?></th><th><?php esc_html_e( 'Varighed', 'studie247' ); ?></th></tr></thead>
									<tbody>
										<?php foreach ( $items as $c ) : ?>
											<tr>
												<td><code><?php echo esc_html( $c['name'] ?? '' ); ?></code></td>
												<td><?php echo esc_html( $c['provider'] ?? '' ); ?></td>
												<td><?php echo esc_html( $c['duration'] ?? '' ); ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</details>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="s247-cookie__actions">
				<button type="button" class="s247-cookie__btn s247-cookie__btn--ghost" data-s247-cookie="reject"><?php echo esc_html( $btn_reject ); ?></button>
				<button type="button" class="s247-cookie__btn s247-cookie__btn--ghost" data-s247-cookie="toggle-options"><?php echo esc_html( $btn_options ); ?></button>
				<button type="button" class="s247-cookie__btn s247-cookie__btn--primary" data-s247-cookie="accept"><?php echo esc_html( $btn_accept ); ?></button>
			</div>
			<div class="s247-cookie__actions s247-cookie__actions--options" hidden data-s247-cookie-save-wrap>
				<button type="button" class="s247-cookie__btn s247-cookie__btn--primary" data-s247-cookie="save-custom"><?php esc_html_e( 'Gem mine valg', 'studie247' ); ?></button>
			</div>
		</div>
	</div>

	<style>
	.s247-cookie{position:fixed;inset:auto 0 0 0;z-index:10000;padding:16px;display:flex;justify-content:center;pointer-events:none;}
	.s247-cookie[hidden]{display:none;}
	.s247-cookie__card{pointer-events:auto;background:#FBF5EC;color:#282828;border:1px solid rgba(40,40,40,0.12);border-radius:14px;max-width:640px;width:100%;padding:22px 24px;box-shadow:0 16px 40px rgba(16,24,40,0.18);font-family:'Inter','Helvetica Neue',Arial,sans-serif;font-size:14px;line-height:1.55;}
	.s247-cookie__title{margin:0 0 8px;font-size:18px;font-weight:700;letter-spacing:-0.01em;}
	.s247-cookie__intro{margin:0 0 14px;color:#404040;}
	.s247-cookie__intro p{margin:0 0 8px;}
	.s247-cookie__intro a{color:#9E2B25;font-weight:500;text-decoration:underline;}
	.s247-cookie__options{display:grid;gap:14px;margin:0 0 14px;padding:14px;background:#F4E9DD;border-radius:10px;max-height:50vh;overflow-y:auto;}
	.s247-cookie__cat-wrap{border-bottom:1px solid rgba(40,40,40,0.08);padding-bottom:10px;}
	.s247-cookie__cat-wrap:last-child{border-bottom:0;padding-bottom:0;}
	.s247-cookie__count{font-weight:400;color:#9E2B25;margin-left:4px;}
	.s247-cookie__details{margin-top:8px;font-size:12px;}
	.s247-cookie__details summary{cursor:pointer;color:#9E2B25;font-weight:500;padding:4px 0;user-select:none;}
	.s247-cookie__details[open] summary{margin-bottom:6px;}
	.s247-cookie__details table{width:100%;border-collapse:collapse;font-size:11px;background:#FBF5EC;border-radius:6px;overflow:hidden;}
	.s247-cookie__details th{text-align:left;padding:6px 8px;background:rgba(40,40,40,0.04);font-weight:600;}
	.s247-cookie__details td{padding:6px 8px;border-top:1px solid rgba(40,40,40,0.06);}
	.s247-cookie__details code{font-family:'SF Mono',Menlo,monospace;font-size:10px;background:rgba(158,43,37,0.08);padding:1px 4px;border-radius:3px;color:#282828;}
	.s247-cookie__cat{display:flex;gap:10px;align-items:flex-start;cursor:pointer;padding:6px 0;}
	.s247-cookie__cat input{margin-top:3px;accent-color:#9E2B25;width:18px;height:18px;flex-shrink:0;}
	.s247-cookie__cat strong{display:block;font-size:13px;margin-bottom:2px;}
	.s247-cookie__cat span{display:block;color:#666;font-size:12px;line-height:1.5;}
	.s247-cookie__actions{display:flex;gap:8px;flex-wrap:wrap;}
	.s247-cookie__actions--options{margin-top:10px;}
	.s247-cookie__btn{font:inherit;padding:10px 16px;border-radius:8px;border:1px solid transparent;cursor:pointer;font-weight:600;font-size:13px;min-height:40px;flex:1 1 auto;}
	.s247-cookie__btn--primary{background:#9E2B25;color:#FBF5EC;border-color:#9E2B25;}
	.s247-cookie__btn--primary:hover{background:#7E2019;}
	.s247-cookie__btn--ghost{background:#FBF5EC;color:#282828;border-color:rgba(40,40,40,0.18);}
	.s247-cookie__btn--ghost:hover{background:#F4E9DD;}
	@media (min-width:720px){
		.s247-cookie__actions{flex-wrap:nowrap;}
		.s247-cookie__btn{flex:0 0 auto;}
		.s247-cookie__btn--primary{margin-left:auto;}
	}
	@media (max-width:479px){
		.s247-cookie{padding:8px;}
		.s247-cookie__card{padding:16px 18px;}
	}

	/* Embed-gate (YouTube/Vimeo/Maps blokeret uden samtykke) */
	.s247-embed-gate{position:relative;width:100%;aspect-ratio:16/9;background:#F4E9DD;border:1px solid rgba(40,40,40,0.15);border-radius:12px;overflow:hidden;margin:16px 0;}
	.s247-embed-gate__placeholder{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:12px;padding:24px;text-align:center;color:#282828;}
	.s247-embed-gate__icon{font-size:42px;line-height:1;}
	.s247-embed-gate__text{margin:0;font-size:15px;line-height:1.5;max-width:400px;color:#404040;}
	.s247-embed-gate__btn{background:#9E2B25;color:#FBF5EC;border:0;padding:11px 18px;border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;font-family:inherit;}
	.s247-embed-gate__btn:hover{background:#7E2019;}
	.s247-embed-gate__iframe,.s247-embed-gate__iframe iframe{width:100%;height:100%;border:0;}
	.s247-embed-gate.is-active .s247-embed-gate__placeholder{display:none;}
	.s247-embed-gate.is-active .s247-embed-gate__iframe{display:block;position:absolute;inset:0;}
	</style>

	<script>
	(function(){
		var COOKIE = 's247_consent';
		var DAYS = 365;
		var el = document.getElementById('s247-cookie');
		if (!el) return;

		function read() {
			var m = document.cookie.match(new RegExp('(?:^|; )' + COOKIE + '=([^;]*)'));
			if (!m) return null;
			try { return JSON.parse(decodeURIComponent(m[1])); } catch(e){ return null; }
		}
		function write(v) {
			var d = new Date(); d.setTime(d.getTime() + DAYS*86400*1000);
			document.cookie = COOKIE + '=' + encodeURIComponent(JSON.stringify(v)) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
			try { localStorage.setItem(COOKIE, JSON.stringify(v)); } catch(e){}
			document.dispatchEvent(new CustomEvent('s247-consent-changed', { detail: v }));
		}
		function show(){ el.hidden = false; }
		function hide(){ el.hidden = true; }
		function opts(show){
			el.querySelector('[data-s247-cookie-options]').hidden = !show;
			el.querySelector('[data-s247-cookie-save-wrap]').hidden = !show;
		}
		function setChecks(v){
			el.querySelectorAll('input[type=checkbox][data-key]').forEach(function(cb){
				if (cb.dataset.key === 'necessary') { cb.checked = true; return; }
				cb.checked = !!v[cb.dataset.key];
			});
		}

		var current = read();
		if (!current) { show(); }
		else { setChecks(current); }

		el.addEventListener('click', function(e){
			var btn = e.target.closest('[data-s247-cookie]');
			if (!btn) return;
			var action = btn.dataset.s247Cookie;
			var v = { necessary: true, analytics: false, marketing: false, ts: Date.now() };
			if (action === 'accept')       { v.analytics = true; v.marketing = true; write(v); hide(); }
			else if (action === 'reject')  { write(v); hide(); }
			else if (action === 'save-custom') {
				el.querySelectorAll('input[type=checkbox][data-key]').forEach(function(cb){
					if (cb.dataset.key !== 'necessary') v[cb.dataset.key] = cb.checked;
				});
				write(v); hide();
			}
			else if (action === 'toggle-options') {
				var optsEl = el.querySelector('[data-s247-cookie-options]');
				opts(optsEl.hidden);
			}
		});

		// Publik API så andre scripts kan integrere.
		window.s247Consent = {
			has: function(key){
				var v = read(); return v ? !!v[key] : false;
			},
			open: function(){
				setChecks(read() || {});
				opts(true);
				show();
			},
			reset: function(){
				document.cookie = COOKIE + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
				try { localStorage.removeItem(COOKIE); } catch(e){}
				show();
			}
		};

		// Footer-link for at åbne indstillinger igen
		document.querySelectorAll('[data-open-cookie-settings]').forEach(function(a){
			a.addEventListener('click', function(e){ e.preventDefault(); window.s247Consent.open(); });
		});

		// ───────── Embed-gate ─────────
		// Aktiverer YouTube/Vimeo/Maps iframes når samtykke gives.
		function activateAllowedEmbeds() {
			var consent = read();
			if (!consent) return;
			document.querySelectorAll('.s247-embed-gate').forEach(function(gate){
				if (gate.classList.contains('is-active')) return;
				var cat = gate.dataset.category || 'marketing';
				if (!consent[cat]) return;
				var wrap = gate.querySelector('.s247-embed-gate__iframe');
				var iframe = wrap && wrap.querySelector('iframe');
				if (!iframe) return;
				var dataSrc = iframe.getAttribute('data-src');
				if (dataSrc) { iframe.setAttribute('src', dataSrc); iframe.removeAttribute('data-src'); }
				wrap.hidden = false;
				gate.classList.add('is-active');
			});
		}
		// Klik på "Tillad og afspil"-knappen i en gate → åbn consent-modal med den rigtige kategori forudvalgt.
		document.addEventListener('click', function(e){
			var btn = e.target.closest('[data-s247-embed-accept]');
			if (!btn) return;
			e.preventDefault();
			var gate = btn.closest('.s247-embed-gate');
			var cat  = gate ? gate.dataset.category : 'marketing';
			// Åbn indstillinger med kategorien pre-ticked.
			var existing = read() || { necessary: true };
			existing[cat] = true;
			setChecks(existing);
			opts(true); show();
		});
		// Kør ved load og ved hver samtykke-ændring.
		activateAllowedEmbeds();
		document.addEventListener('s247-consent-changed', activateAllowedEmbeds);
	})();
	</script>
	<?php
}
