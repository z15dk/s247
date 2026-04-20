<?php
/**
 * Template Name: Dashboard
 *
 * Moderne dashboard med egen shell — ingen site-header/footer.
 * Bag login-gate, sektioner styret af per-bruger tilladelser.
 *
 * @package Studie247
 */

// Ingen get_header() — vi bygger egen minimal shell.
nocache_headers();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'Dashboard — Studie 247', 'studie247' ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 's247-dash-app' ); ?>>

<?php if ( ! is_user_logged_in() ) : ?>

<div class="sd-login">
	<div class="sd-login__card">
		<div class="sd-brand"><span>STUDIE</span><span class="sd-brand__num">247</span></div>
		<h1 class="sd-login__title"><?php esc_html_e( 'Log ind', 'studie247' ); ?></h1>
		<p class="sd-login__lead"><?php esc_html_e( 'Dashboard-adgang er forbeholdt holdet.', 'studie247' ); ?></p>
		<?php
		wp_login_form( array(
			'redirect'       => esc_url( home_url( '/dashboard/' ) ),
			'label_username' => __( 'Brugernavn eller e-mail', 'studie247' ),
			'label_password' => __( 'Adgangskode', 'studie247' ),
			'label_remember' => __( 'Husk mig', 'studie247' ),
			'label_log_in'   => __( 'Log ind', 'studie247' ),
		) );
		?>
	</div>
</div>

<?php elseif ( ! current_user_can( 'edit_posts' ) ) : ?>

<div class="sd-login">
	<div class="sd-login__card">
		<h1 class="sd-login__title"><?php esc_html_e( 'Ingen adgang', 'studie247' ); ?></h1>
		<p class="sd-login__lead"><?php esc_html_e( 'Din bruger har ikke rettigheder til dashboardet.', 'studie247' ); ?></p>
		<p><a class="sd-btn" href="<?php echo esc_url( wp_logout_url( home_url( '/dashboard/' ) ) ); ?>"><?php esc_html_e( 'Log ud', 'studie247' ); ?></a></p>
	</div>
</div>

<?php else :
	$current_view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'overview';
	$user            = wp_get_current_user();
	$pending_count   = (int) wp_count_posts( 'booking' )->pending;
	$publish_count   = (int) wp_count_posts( 'booking' )->publish;
	$trash_count     = (int) wp_count_posts( 'booking' )->trash;
	$total_bookings  = $pending_count + $publish_count + $trash_count;
	$msg_count       = (int) wp_count_posts( 'kontakt_besked' )->publish;
	$item_count      = (int) wp_count_posts( 'udlejning_item' )->publish;

	$month_start = date( 'Y-m-01' );
	$year_start  = date( 'Y-01-01' );
	$approved = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	) );
	$rev_month = 0;
	$rev_year  = 0;
	$prod_counts = array();
	foreach ( $approved as $b ) {
		$d     = get_post_meta( $b->ID, '_s247_date', true );
		$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
		$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
		if ( $d && $d >= $year_start )  { $rev_year  += $price; }
		if ( $d && $d >= $month_start ) { $rev_month += $price; }
		if ( $pid ) {
			if ( ! isset( $prod_counts[ $pid ] ) ) { $prod_counts[ $pid ] = 0; }
			$prod_counts[ $pid ]++;
		}
	}
	arsort( $prod_counts );
	$top_products = array_slice( $prod_counts, 0, 5, true );

	$recent_pending = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'pending',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	$recent_msgs = get_posts( array(
		'post_type'      => 'kontakt_besked',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$fmt_dkk = function ( $n ) { return number_format( (int) $n, 0, ',', '.' ) . ' kr'; };
	$can_rental   = studie247_can_view_dash( 'rental' );
	$can_studio   = studie247_can_view_dash( 'studio' );
	$can_messages = studie247_can_view_dash( 'messages' );
	$can_revenue  = studie247_can_view_dash( 'revenue' );
	$can_pending  = studie247_can_view_dash( 'pending' );
	$has_any      = $can_rental || $can_studio || $can_messages || $can_revenue || $can_pending;
	$first_name   = trim( explode( ' ', trim( $user->display_name ) )[0] ) ?: $user->display_name;
	$initials     = strtoupper( substr( $first_name, 0, 1 ) );
?>

<div class="sd-shell">

	<aside class="sd-side">
		<div class="sd-brand"><span>STUDIE</span><span class="sd-brand__num">247</span></div>

		<nav class="sd-nav">
			<a class="sd-nav__item <?php echo 'overview' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
				<span class="sd-nav__dot"></span> <?php esc_html_e( 'Oversigt', 'studie247' ); ?>
			</a>
			<?php if ( $can_pending || $can_studio ) : ?>
				<a class="sd-nav__item <?php echo 'bookings' === $current_view ? 'is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/dashboard/?view=bookings' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Studie-bookinger', 'studie247' ); ?>
					<?php if ( $pending_count ) : ?><span class="sd-nav__badge"><?php echo (int) $pending_count; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<?php if ( $can_rental ) : ?>
				<a class="sd-nav__item" href="<?php echo esc_url( admin_url( 'edit.php?post_type=udlejning_item' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Udlejning', 'studie247' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $can_messages ) : ?>
				<a class="sd-nav__item" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kontakt_besked' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Beskeder', 'studie247' ); ?>
					<?php if ( $msg_count ) : ?><span class="sd-nav__badge sd-nav__badge--muted"><?php echo (int) $msg_count; ?></span><?php endif; ?>
				</a>
			<?php endif; ?>
			<a class="sd-nav__item" href="<?php echo esc_url( admin_url() ); ?>">
				<span class="sd-nav__dot"></span> <?php esc_html_e( 'WP-admin', 'studie247' ); ?>
			</a>
		</nav>

		<div class="sd-side__footer">
			<div class="sd-user">
				<span class="sd-user__avatar"><?php echo esc_html( $initials ); ?></span>
				<div class="sd-user__meta">
					<span class="sd-user__name"><?php echo esc_html( $user->display_name ); ?></span>
					<span class="sd-user__role"><?php echo esc_html( current_user_can( 'manage_options' ) ? __( 'Administrator', 'studie247' ) : __( 'Redaktør', 'studie247' ) ); ?></span>
				</div>
				<a class="sd-user__logout" href="<?php echo esc_url( wp_logout_url( home_url( '/dashboard/' ) ) ); ?>" title="<?php esc_attr_e( 'Log ud', 'studie247' ); ?>">↪</a>
			</div>
		</div>
	</aside>

	<main class="sd-main">

		<header class="sd-topbar">
			<div>
				<?php
				$crumb_map = array(
					'overview' => __( 'Dashboard / Oversigt', 'studie247' ),
					'bookings' => __( 'Dashboard / Studie-bookinger', 'studie247' ),
				);
				$title_map = array(
					'overview' => sprintf( __( 'Hej %s', 'studie247' ), $first_name ),
					'bookings' => __( 'Studie-bookinger', 'studie247' ),
				);
				?>
				<span class="sd-breadcrumb"><?php echo esc_html( $crumb_map[ $current_view ] ?? $crumb_map['overview'] ); ?></span>
				<h1 class="sd-topbar__title">
					<?php echo esc_html( $title_map[ $current_view ] ?? $title_map['overview'] ); ?>
					<span class="sd-topbar__title-sub"><?php echo esc_html( date_i18n( 'l j. F', current_time( 'timestamp' ) ) ); ?></span>
				</h1>
			</div>
			<div class="sd-topbar__actions">
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Se sitet', 'studie247' ); ?></a>
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'wp-admin', 'studie247' ); ?></a>
			</div>
		</header>

		<?php
		if ( 'bookings' === $current_view ) {
			include STUDIE247_DIR . '/template-parts/dashboard-bookings.php';
		} else {
			include STUDIE247_DIR . '/template-parts/dashboard-overview.php';
		}
		?>


	</main>

</div>

<?php endif; // logged-in + edit_posts ?>

<?php wp_footer(); ?>
</body>
</html>
