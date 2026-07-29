<?php
/**
 * Analytics — Google Analytics 4 + Microsoft Clarity.
 *
 * Scripts indlæses KUN hvis brugeren har givet samtykke til
 * 'analytics' via cookie-banneret. Indstillinger i Customizer →
 * Analytics.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── Customizer ───────── */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 's247_analytics', array(
		'title'    => __( 'Analytics', 'studie247' ),
		'priority' => 92,
	) );

	$fields = array(
		's247_ga4_id' => array(
			'label'       => __( 'Google Analytics 4 — Measurement ID', 'studie247' ),
			'description' => __( 'Fx G-XXXXXXXXXX. Indlæses kun hvis brugeren accepterer statistik-cookies.', 'studie247' ),
			'default'     => '',
		),
		's247_clarity_id' => array(
			'label'       => __( 'Microsoft Clarity — Project ID', 'studie247' ),
			'description' => __( 'Fx abcd1234. Indlæses kun hvis brugeren accepterer statistik-cookies.', 'studie247' ),
			'default'     => '',
		),
		's247_meta_pixel_id' => array(
			'label'       => __( 'Meta Pixel ID (Facebook/Instagram)', 'studie247' ),
			'description' => __( 'Fx 123456789012345. Indlæses kun hvis brugeren accepterer marketing-cookies.', 'studie247' ),
			'default'     => '',
		),
	);

	foreach ( $fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $cfg['default'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'       => $cfg['label'],
			'description' => $cfg['description'],
			'section'     => 's247_analytics',
			'type'        => 'text',
		) );
	}
} );

/* ───────── Renderer ───────── */
add_action( 'wp_footer', 'studie247_analytics_bootstrap', 95 );
function studie247_analytics_bootstrap() {
	$ga4     = trim( (string) get_theme_mod( 's247_ga4_id' ) );
	$clarity = trim( (string) get_theme_mod( 's247_clarity_id' ) );
	$pixel   = trim( (string) get_theme_mod( 's247_meta_pixel_id' ) );

	if ( ! $ga4 && ! $clarity && ! $pixel ) { return; }

	$config = array(
		'ga4'     => $ga4,
		'clarity' => $clarity,
		'pixel'   => $pixel,
	);
	?>
	<script>
	(function(){
		var cfg = <?php echo wp_json_encode( $config ); ?>;
		var loaded = { ga4: false, clarity: false, pixel: false };

		function hasConsent(key) {
			return window.s247Consent && window.s247Consent.has(key);
		}

		function loadGA4() {
			if (loaded.ga4 || !cfg.ga4) return;
			loaded.ga4 = true;
			var s = document.createElement('script');
			s.async = true;
			s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.ga4);
			document.head.appendChild(s);
			window.dataLayer = window.dataLayer || [];
			window.gtag = function(){ window.dataLayer.push(arguments); };
			window.gtag('js', new Date());
			window.gtag('config', cfg.ga4, { anonymize_ip: true });
		}

		function loadClarity() {
			if (loaded.clarity || !cfg.clarity) return;
			loaded.clarity = true;
			(function(c,l,a,r,i,t,y){
				c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
				t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;
				y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
			})(window, document, 'clarity', 'script', cfg.clarity);
		}

		function loadMetaPixel() {
			if (loaded.pixel || !cfg.pixel) return;
			loaded.pixel = true;
			!function(f,b,e,v,n,t,s){
				if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
				if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];
				t=b.createElement(e);t.async=!0;t.src=v;
				s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s);
			}(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
			window.fbq('init', cfg.pixel);
			window.fbq('track', 'PageView');
		}

		function sync() {
			if (hasConsent('analytics')) { loadGA4(); loadClarity(); }
			if (hasConsent('marketing')) { loadMetaPixel(); }
		}

		sync();
		document.addEventListener('s247-consent-changed', sync);
	})();
	</script>
	<?php
}
