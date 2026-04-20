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
			<a class="sd-nav__item is-active" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
				<span class="sd-nav__dot"></span> <?php esc_html_e( 'Oversigt', 'studie247' ); ?>
			</a>
			<?php if ( $can_pending || $can_studio ) : ?>
				<a class="sd-nav__item" href="<?php echo esc_url( admin_url( 'edit.php?post_type=booking' ) ); ?>">
					<span class="sd-nav__dot"></span> <?php esc_html_e( 'Bookinger', 'studie247' ); ?>
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
				<span class="sd-breadcrumb"><?php esc_html_e( 'Dashboard / Oversigt', 'studie247' ); ?></span>
				<h1 class="sd-topbar__title">
					<?php printf( esc_html__( 'Hej %s', 'studie247' ), esc_html( $first_name ) ); ?>
					<span class="sd-topbar__title-sub"><?php echo esc_html( date_i18n( 'l j. F', current_time( 'timestamp' ) ) ); ?></span>
				</h1>
			</div>
			<div class="sd-topbar__actions">
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Se sitet', 'studie247' ); ?></a>
				<a class="sd-btn sd-btn--ghost" href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'wp-admin', 'studie247' ); ?></a>
			</div>
		</header>

		<?php if ( ! $has_any ) : ?>
			<div class="sd-empty">
				<p><?php esc_html_e( 'Din bruger har ingen sektioner aktiveret endnu. En admin kan give dig adgang under Brugere → din profil.', 'studie247' ); ?></p>
			</div>
		<?php else : ?>

			<section class="sd-stats">
				<?php if ( $can_pending ) : ?>
					<a class="sd-stat <?php echo $pending_count ? 'sd-stat--alert' : ''; ?>" href="<?php echo esc_url( admin_url( 'edit.php?post_type=booking&post_status=pending' ) ); ?>">
						<span class="sd-stat__label"><?php esc_html_e( 'Afventer', 'studie247' ); ?></span>
						<span class="sd-stat__num"><?php echo (int) $pending_count; ?></span>
						<span class="sd-stat__delta"><?php esc_html_e( 'forespørgsler', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_studio ) : ?>
					<a class="sd-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=booking' ) ); ?>">
						<span class="sd-stat__label"><?php esc_html_e( 'Godkendte', 'studie247' ); ?></span>
						<span class="sd-stat__num"><?php echo (int) $publish_count; ?></span>
						<span class="sd-stat__delta"><?php esc_html_e( 'total alle tider', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_revenue ) : ?>
					<div class="sd-stat sd-stat--accent">
						<span class="sd-stat__label"><?php esc_html_e( 'Omsætning', 'studie247' ); ?></span>
						<span class="sd-stat__num sd-stat__num--money"><?php echo esc_html( $fmt_dkk( $rev_month ) ); ?></span>
						<span class="sd-stat__delta"><?php printf( esc_html__( 'YTD: %s', 'studie247' ), esc_html( $fmt_dkk( $rev_year ) ) ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( $can_messages ) : ?>
					<a class="sd-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kontakt_besked' ) ); ?>">
						<span class="sd-stat__label"><?php esc_html_e( 'Beskeder', 'studie247' ); ?></span>
						<span class="sd-stat__num"><?php echo (int) $msg_count; ?></span>
						<span class="sd-stat__delta"><?php esc_html_e( 'modtaget', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $can_rental ) : ?>
					<a class="sd-stat" href="<?php echo esc_url( admin_url( 'edit.php?post_type=udlejning_item' ) ); ?>">
						<span class="sd-stat__label"><?php esc_html_e( 'Varer', 'studie247' ); ?></span>
						<span class="sd-stat__num"><?php echo (int) $item_count; ?></span>
						<span class="sd-stat__delta"><?php esc_html_e( 'i udlejning', 'studie247' ); ?></span>
					</a>
				<?php endif; ?>
			</section>

			<section class="sd-grid">
				<?php if ( $can_pending ) : ?>
					<div class="sd-panel">
						<header class="sd-panel__head">
							<h2><?php esc_html_e( 'Afventende forespørgsler', 'studie247' ); ?></h2>
							<a class="sd-panel__more" href="<?php echo esc_url( admin_url( 'edit.php?post_type=booking&post_status=pending' ) ); ?>"><?php esc_html_e( 'Se alle', 'studie247' ); ?> →</a>
						</header>
						<?php if ( empty( $recent_pending ) ) : ?>
							<p class="sd-panel__empty">✓ <?php esc_html_e( 'Intet at godkende lige nu.', 'studie247' ); ?></p>
						<?php else : ?>
							<table class="sd-table">
								<thead>
									<tr>
										<th>Type</th>
										<th><?php esc_html_e( 'Kunde', 'studie247' ); ?></th>
										<th><?php esc_html_e( 'Dato', 'studie247' ); ?></th>
										<?php if ( $can_revenue ) : ?><th class="sd-table__right"><?php esc_html_e( 'Pris', 'studie247' ); ?></th><?php endif; ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $recent_pending as $b ) :
										$name  = get_post_meta( $b->ID, '_s247_name', true );
										$date  = get_post_meta( $b->ID, '_s247_date', true );
										$pid   = (int) get_post_meta( $b->ID, '_s247_produkt_id', true );
										$price = (int) get_post_meta( $b->ID, '_s247_estimated_price', true );
										if ( $pid && ! $can_rental && ! current_user_can( 'manage_options' ) ) { continue; }
										if ( ! $pid && ! $can_studio && ! current_user_can( 'manage_options' ) ) { continue; }
									?>
										<tr onclick="window.location='<?php echo esc_url( get_edit_post_link( $b->ID ) ); ?>'">
											<td><span class="sd-badge <?php echo $pid ? 'sd-badge--accent' : 'sd-badge--neutral'; ?>"><?php echo $pid ? esc_html__( 'Udstyr', 'studie247' ) : esc_html__( 'Studie', 'studie247' ); ?></span></td>
											<td><?php echo esc_html( $name ?: '—' ); ?></td>
											<td class="sd-muted"><?php echo esc_html( $date ? date_i18n( 'j. M', strtotime( $date ) ) : '—' ); ?></td>
											<?php if ( $can_revenue ) : ?><td class="sd-table__right sd-mono"><?php echo $price ? esc_html( $fmt_dkk( $price ) ) : '—'; ?></td><?php endif; ?>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $can_messages ) : ?>
					<div class="sd-panel">
						<header class="sd-panel__head">
							<h2><?php esc_html_e( 'Seneste beskeder', 'studie247' ); ?></h2>
							<a class="sd-panel__more" href="<?php echo esc_url( admin_url( 'edit.php?post_type=kontakt_besked' ) ); ?>"><?php esc_html_e( 'Alle', 'studie247' ); ?> →</a>
						</header>
						<?php if ( empty( $recent_msgs ) ) : ?>
							<p class="sd-panel__empty"><?php esc_html_e( 'Ingen endnu.', 'studie247' ); ?></p>
						<?php else : ?>
							<ul class="sd-list">
								<?php foreach ( $recent_msgs as $m ) :
									$name  = get_post_meta( $m->ID, '_s247_name', true );
									$topic = get_post_meta( $m->ID, '_s247_topic', true );
								?>
									<li>
										<a href="<?php echo esc_url( get_edit_post_link( $m->ID ) ); ?>">
											<span class="sd-list__dot"></span>
											<span class="sd-list__body">
												<span class="sd-list__name"><?php echo esc_html( $name ?: '—' ); ?></span>
												<span class="sd-list__meta"><?php echo esc_html( $topic ?: __( 'Besked', 'studie247' ) ); ?> · <?php echo esc_html( get_the_date( 'j. M', $m ) ); ?></span>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $can_rental && ! empty( $top_products ) ) : ?>
					<div class="sd-panel sd-panel--wide">
						<header class="sd-panel__head">
							<h2><?php esc_html_e( 'Mest udlejede varer', 'studie247' ); ?></h2>
							<span class="sd-panel__hint"><?php esc_html_e( 'Top 5 alle tider', 'studie247' ); ?></span>
						</header>
						<table class="sd-table">
							<thead>
								<tr><th>#</th><th><?php esc_html_e( 'Vare', 'studie247' ); ?></th><th class="sd-table__right"><?php esc_html_e( 'Udlejninger', 'studie247' ); ?></th></tr>
							</thead>
							<tbody>
								<?php $rank = 0; foreach ( $top_products as $pid => $cnt ) : $rank++; ?>
									<tr onclick="window.location='<?php echo esc_url( get_edit_post_link( $pid ) ); ?>'">
										<td class="sd-rank"><?php printf( '%02d', $rank ); ?></td>
										<td><?php echo esc_html( get_the_title( $pid ) ); ?></td>
										<td class="sd-table__right sd-mono"><?php echo (int) $cnt; ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</section>

		<?php endif; ?>

	</main>

</div>

<?php endif; // logged-in + edit_posts ?>

<?php wp_footer(); ?>
</body>
</html>
