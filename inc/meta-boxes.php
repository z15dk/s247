<?php
/**
 * Native meta boxes (no ACF dependency).
 *
 * Service CPT fields:
 *  - _s247_tagline       (undertitel)
 *  - _s247_icon          (slug for inline SVG: video, mic, academic, camera, +++)
 *  - _s247_startpris     (fx "fra 1.499 kr")
 *  - _s247_cta_text      (knapetikette)
 *  - _s247_included      (én pr. linje — hvad der er inkluderet)
 *
 * Testimonial CPT fields:
 *  - _s247_author_name
 *  - _s247_author_company
 *
 * Case CPT fields:
 *  - _s247_case_service  (relateret service-ID)
 *  - _s247_case_video    (embed URL)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** ───────── Service ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_service_fields',
		__( 'Service-detaljer', 'studie247' ),
		'studie247_render_service_meta',
		'service',
		'normal',
		'high'
	);
} );

function studie247_render_service_meta( $post ) {
	wp_nonce_field( 's247_service_meta', 's247_service_nonce' );
	$tagline   = get_post_meta( $post->ID, '_s247_tagline', true );
	$icon      = get_post_meta( $post->ID, '_s247_icon', true );
	$startpris = get_post_meta( $post->ID, '_s247_startpris', true );
	$cta_text  = get_post_meta( $post->ID, '_s247_cta_text', true );
	$included  = get_post_meta( $post->ID, '_s247_included', true );

	$icons = array(
		''         => __( '— Vælg ikon —', 'studie247' ),
		'video'    => 'Video',
		'mic'      => 'Mikrofon (podcast)',
		'academic' => 'Akademisk hue (kursus)',
		'camera'   => 'Kamera (foto)',
		'sparkle'  => 'Sparkle',
		'play'     => 'Play',
	);
	?>
	<p>
		<label for="s247_tagline"><strong><?php esc_html_e( 'Undertitel', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_tagline" name="s247_tagline" value="<?php echo esc_attr( $tagline ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_icon"><strong><?php esc_html_e( 'Ikon', 'studie247' ); ?></strong></label><br>
		<select id="s247_icon" name="s247_icon">
			<?php foreach ( $icons as $k => $v ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $icon, $k ); ?>><?php echo esc_html( $v ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="s247_startpris"><strong><?php esc_html_e( 'Startpris (fx "fra 1.499 kr")', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_startpris" name="s247_startpris" value="<?php echo esc_attr( $startpris ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_cta_text"><strong><?php esc_html_e( 'CTA-tekst', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_cta_text" name="s247_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" placeholder="Læs mere" style="width:100%">
	</p>
	<p>
		<label for="s247_included"><strong><?php esc_html_e( 'Inkluderet (én pr. linje)', 'studie247' ); ?></strong></label><br>
		<textarea id="s247_included" name="s247_included" rows="6" style="width:100%"><?php echo esc_textarea( $included ); ?></textarea>
	</p>
	<?php
}

add_action( 'save_post_service', function ( $post_id ) {
	if ( ! isset( $_POST['s247_service_nonce'] ) || ! wp_verify_nonce( $_POST['s247_service_nonce'], 's247_service_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = array( 's247_tagline', 's247_icon', 's247_startpris', 's247_cta_text', 's247_included' );
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
} );

/** ───────── Testimonial ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_testimonial_fields',
		__( 'Forfatter', 'studie247' ),
		'studie247_render_testimonial_meta',
		'testimonial',
		'side',
		'high'
	);
} );

function studie247_render_testimonial_meta( $post ) {
	wp_nonce_field( 's247_testimonial_meta', 's247_testimonial_nonce' );
	$name    = get_post_meta( $post->ID, '_s247_author_name', true );
	$company = get_post_meta( $post->ID, '_s247_author_company', true );
	?>
	<p>
		<label for="s247_author_name"><strong><?php esc_html_e( 'Navn', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_author_name" name="s247_author_name" value="<?php echo esc_attr( $name ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_author_company"><strong><?php esc_html_e( 'Virksomhed', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_author_company" name="s247_author_company" value="<?php echo esc_attr( $company ); ?>" style="width:100%">
	</p>
	<?php
}

add_action( 'save_post_testimonial', function ( $post_id ) {
	if ( ! isset( $_POST['s247_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['s247_testimonial_nonce'], 's247_testimonial_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( 's247_author_name', 's247_author_company' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
} );
