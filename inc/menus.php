<?php
/**
 * Navigation menus.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	register_nav_menus( array(
		'primary' => __( 'Hovedmenu', 'studie247' ),
		'footer'  => __( 'Footer-menu', 'studie247' ),
		'legal'   => __( 'Legal-menu (footer nederst)', 'studie247' ),
	) );
} );

/**
 * Fallback for primary menu when user hasn't assigned one yet.
 * Keeps "Udlejning" placeholder in place from day one.
 */
function studie247_primary_menu_fallback() {
	$items = array(
		array( 'label' => __( 'Services', 'studie247' ),  'url' => home_url( '/services/' ) ),
		array( 'label' => __( 'Studiet', 'studie247' ),   'url' => home_url( '/studiet/' ) ),
		array( 'label' => __( 'Udlejning', 'studie247' ), 'url' => home_url( '/udlejning/' ) ),
		array( 'label' => __( 'Om', 'studie247' ),        'url' => home_url( '/om/' ) ),
		array( 'label' => __( 'Kontakt', 'studie247' ),   'url' => home_url( '/kontakt/' ) ),
	);

	echo '<ul class="nav nav--primary">';
	foreach ( $items as $item ) {
		$current = ( trailingslashit( home_url( $_SERVER['REQUEST_URI'] ?? '/' ) ) === trailingslashit( $item['url'] ) ) ? ' nav__link--current' : '';
		printf(
			'<li class="nav__item"><a class="nav__link%s" href="%s">%s</a></li>',
			esc_attr( $current ),
			esc_url( $item['url'] ),
			esc_html( $item['label'] )
		);
	}
	echo '</ul>';
}
