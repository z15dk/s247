<?php
/**
 * Admin brand — custom login + wp-admin styling til Studie 247.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── Login-siden ───────── */
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style(
		'studie247-login',
		STUDIE247_URI . '/assets/css/login.css',
		array(),
		STUDIE247_VERSION
	);
	wp_enqueue_style(
		'studie247-login-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:ital,wght@1,400;1,700&display=swap',
		array(),
		null
	);
} );

add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );

add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );

/* ───────── wp-admin styling ───────── */
add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style(
		'studie247-admin',
		STUDIE247_URI . '/assets/css/admin.css',
		array(),
		STUDIE247_VERSION
	);
	wp_enqueue_style(
		'studie247-admin-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:ital,wght@1,400;1,700&display=swap',
		array(),
		null
	);
} );

/* ───────── Admin bar — også på front-end for logged-in brugere ───────── */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin_bar_showing() ) {
		wp_enqueue_style(
			'studie247-admin-bar',
			STUDIE247_URI . '/assets/css/admin.css',
			array(),
			STUDIE247_VERSION
		);
	}
} );

/* ───────── Dashboard widget — Studie 247 velkomst ───────── */
add_action( 'wp_dashboard_setup', function () {
	// Fjern WP's standard widgets for et renere dashboard.
	global $wp_meta_boxes;
	$remove = array(
		'dashboard_primary',
		'dashboard_secondary',
		'dashboard_quick_press',
		'dashboard_recent_drafts',
		'dashboard_incoming_links',
		'dashboard_plugins',
		'dashboard_right_now',
		'dashboard_activity',
		'welcome_panel',
	);
	foreach ( $remove as $id ) {
		remove_meta_box( $id, 'dashboard', 'normal' );
		remove_meta_box( $id, 'dashboard', 'side' );
	}

	// Tilføj custom welcome widget.
	wp_add_dashboard_widget(
		's247_welcome',
		__( 'Velkommen til Studie 247', 'studie247' ),
		'studie247_dashboard_widget'
	);
} );

function studie247_dashboard_widget() {
	$current_user = wp_get_current_user();
	$first_name   = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
	?>
	<div class="s247-dash">
		<div class="s247-dash__hero">
			<span class="s247-dash__eyebrow"><?php esc_html_e( 'Velkommen tilbage', 'studie247' ); ?></span>
			<h2 class="s247-dash__title">
				<?php echo esc_html( $first_name ); ?> <em>—</em> <?php esc_html_e( 'godt at se dig.', 'studie247' ); ?>
			</h2>
			<p class="s247-dash__lead">
				<?php esc_html_e( 'Her er en hurtig indgang til det du oftest redigerer.', 'studie247' ); ?>
			</p>
		</div>
		<div class="s247-dash__grid">
			<a class="s247-dash__card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=service' ) ); ?>">
				<span class="s247-dash__card-num">01</span>
				<span class="s247-dash__card-title"><?php esc_html_e( 'Services', 'studie247' ); ?></span>
				<span class="s247-dash__card-hint"><?php esc_html_e( 'Rediger eller tilføj services', 'studie247' ); ?></span>
			</a>
			<a class="s247-dash__card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=udlejning_item' ) ); ?>">
				<span class="s247-dash__card-num">02</span>
				<span class="s247-dash__card-title"><?php esc_html_e( 'Udlejning', 'studie247' ); ?></span>
				<span class="s247-dash__card-hint"><?php esc_html_e( 'Shop-inventar og priser', 'studie247' ); ?></span>
			</a>
			<a class="s247-dash__card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=case' ) ); ?>">
				<span class="s247-dash__card-num">03</span>
				<span class="s247-dash__card-title"><?php esc_html_e( 'Cases', 'studie247' ); ?></span>
				<span class="s247-dash__card-hint"><?php esc_html_e( 'Tilføj nye projekter', 'studie247' ); ?></span>
			</a>
			<a class="s247-dash__card" href="<?php echo esc_url( admin_url( 'edit.php?post_type=testimonial' ) ); ?>">
				<span class="s247-dash__card-num">04</span>
				<span class="s247-dash__card-title"><?php esc_html_e( 'Kundeudsagn', 'studie247' ); ?></span>
				<span class="s247-dash__card-hint"><?php esc_html_e( 'Føj anbefalinger til', 'studie247' ); ?></span>
			</a>
			<a class="s247-dash__card" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">
				<span class="s247-dash__card-num">05</span>
				<span class="s247-dash__card-title"><?php esc_html_e( 'Tilpas', 'studie247' ); ?></span>
				<span class="s247-dash__card-hint"><?php esc_html_e( 'Hero, studio, team, moods', 'studie247' ); ?></span>
			</a>
			<a class="s247-dash__card" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">
				<span class="s247-dash__card-num">06</span>
				<span class="s247-dash__card-title"><?php esc_html_e( 'Se siden', 'studie247' ); ?></span>
				<span class="s247-dash__card-hint"><?php esc_html_e( 'Åbn forsiden i nyt vindue', 'studie247' ); ?></span>
			</a>
		</div>
	</div>
	<?php
}

/* ───────── Dashboard fulde-bredde widget ───────── */
add_action( 'admin_head-index.php', function () {
	echo '<style>#s247_welcome { max-width: 100%; }</style>';
} );

/* ───────── Admin footer-tekst ───────── */
add_filter( 'admin_footer_text', function () {
	return 'Studie 247 — <em>Optag. Skab. Udgiv 247.</em>';
} );

add_filter( 'update_footer', '__return_empty_string', 11 );
