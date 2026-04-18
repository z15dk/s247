<?php
/**
 * Site header with sticky nav.
 *
 * @package Studie247
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="theme-color" content="#F4E9DD">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Spring til indhold', 'studie247' ); ?></a>

<header class="site-header" data-site-header>
	<div class="wrap wrap--wide site-header__inner">
		<div class="site-header__brand">
			<?php studie247_logo(); ?>
		</div>

		<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Hovedmenu', 'studie247' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav nav--primary',
					'fallback_cb'    => false,
					'depth'          => 1,
					'link_class'     => 'nav__link',
				) );
			} else {
				studie247_primary_menu_fallback();
			}
			?>
		</nav>

		<div class="site-header__cta">
			<a class="site-header__phone" href="tel:+4500000000" aria-label="<?php esc_attr_e( 'Ring til os', 'studie247' ); ?>">
				<?php echo studie247_icon( 'phone', 18 ); ?>
				<span><?php esc_html_e( '+45 00 00 00 00', 'studie247' ); ?></span>
			</a>
			<?php studie247_button( __( 'Book nu', 'studie247' ), home_url( '/booking-studie/' ), 'primary', 'btn--sm' ); ?>
			<button type="button" class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav" data-nav-toggle>
				<span class="screen-reader-text"><?php esc_html_e( 'Åbn menu', 'studie247' ); ?></span>
				<?php echo studie247_icon( 'menu', 28 ); ?>
			</button>
		</div>
	</div>
</header>

<div id="mobile-nav" class="mobile-nav" data-open="false" aria-hidden="true">
	<div class="mobile-nav__top">
		<?php studie247_logo(); ?>
		<button type="button" class="mobile-nav__close" data-nav-close aria-label="<?php esc_attr_e( 'Luk menu', 'studie247' ); ?>">
			<?php echo studie247_icon( 'close', 28 ); ?>
		</button>
	</div>
	<ul class="mobile-nav__list">
		<?php
		$menu_items = array(
			array( 'Services',  '/services/',  false ),
			array( 'Studiet',   '/studiet/',   false ),
			array( 'Priser',    '/priser/',    false ),
			array( 'Udlejning', '/udlejning/', true  ),
			array( 'Om',        '/om/',        false ),
			array( 'Kontakt',   '/kontakt/',   false ),
		);
		foreach ( $menu_items as $item ) :
			list( $label, $url, $accent ) = $item;
			$class = 'mobile-nav__link' . ( $accent ? ' mobile-nav__link--accent' : '' );
		?>
			<li>
				<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( home_url( $url ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>

<main id="main" class="site-main">
