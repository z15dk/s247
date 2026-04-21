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

			<div class="s247-cookie__options" hidden data-s247-cookie-options>
				<label class="s247-cookie__cat">
					<input type="checkbox" checked disabled data-key="necessary">
					<div>
						<strong><?php esc_html_e( 'Nødvendige', 'studie247' ); ?></strong>
						<span><?php echo esc_html( $cat_nec ); ?></span>
					</div>
				</label>
				<label class="s247-cookie__cat">
					<input type="checkbox" data-key="analytics">
					<div>
						<strong><?php esc_html_e( 'Statistik', 'studie247' ); ?></strong>
						<span><?php echo esc_html( $cat_stats ); ?></span>
					</div>
				</label>
				<label class="s247-cookie__cat">
					<input type="checkbox" data-key="marketing">
					<div>
						<strong><?php esc_html_e( 'Marketing', 'studie247' ); ?></strong>
						<span><?php echo esc_html( $cat_mkt ); ?></span>
					</div>
				</label>
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
	.s247-cookie__options{display:grid;gap:8px;margin:0 0 14px;padding:14px;background:#F4E9DD;border-radius:10px;}
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
	})();
	</script>
	<?php
}
