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
 *  - _s247_bg_image      (attachment ID til baggrundsbillede på kort)
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
	$bg_id     = (int) get_post_meta( $post->ID, '_s247_bg_image', true );
	$bg_url    = $bg_id ? wp_get_attachment_image_url( $bg_id, 's247-card' ) : '';

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
	<p>
		<strong><?php esc_html_e( 'Baggrundsbillede på kort', 'studie247' ); ?></strong><br>
		<span class="description" style="display:block;margin-bottom:8px;">
			<?php esc_html_e( 'Vises som baggrund bag teksten på service-kortet på forsiden.', 'studie247' ); ?>
		</span>
		<span class="s247-bg-preview" style="display:<?php echo $bg_url ? 'block' : 'none'; ?>;margin:8px 0;">
			<img src="<?php echo esc_url( $bg_url ); ?>" style="max-width:240px;height:auto;border:1px solid #ccd0d4;">
		</span>
		<input type="hidden" id="s247_bg_image" name="s247_bg_image" value="<?php echo esc_attr( $bg_id ); ?>">
		<button type="button" class="button s247-bg-pick"><?php esc_html_e( 'Vælg billede', 'studie247' ); ?></button>
		<button type="button" class="button s247-bg-remove" style="<?php echo $bg_url ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Fjern', 'studie247' ); ?></button>
	</p>
	<script>
	(function($){
		$(function(){
			var frame;
			$('.s247-bg-pick').on('click', function(e){
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Vælg baggrundsbillede', 'studie247' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Brug dette billede', 'studie247' ) ); ?>' },
					library: { type: 'image' },
					multiple: false
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					$('#s247_bg_image').val(att.id);
					var url = (att.sizes && att.sizes['s247-card']) ? att.sizes['s247-card'].url : att.url;
					$('.s247-bg-preview').html('<img src="'+url+'" style="max-width:240px;height:auto;border:1px solid #ccd0d4;">').show();
					$('.s247-bg-remove').show();
				});
				frame.open();
			});
			$('.s247-bg-remove').on('click', function(e){
				e.preventDefault();
				$('#s247_bg_image').val('');
				$('.s247-bg-preview').hide().empty();
				$(this).hide();
			});
		});
	})(jQuery);
	</script>
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
	if ( isset( $_POST['s247_bg_image'] ) ) {
		$bg = absint( $_POST['s247_bg_image'] );
		if ( $bg ) {
			update_post_meta( $post_id, '_s247_bg_image', $bg );
		} else {
			delete_post_meta( $post_id, '_s247_bg_image' );
		}
	}
} );

// Enqueue media uploader on service edit screen.
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || 'service' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
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

/** ───────── Udlejning ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_udlejning_fields',
		__( 'Udlejnings-detaljer', 'studie247' ),
		'studie247_render_udlejning_meta',
		'udlejning_item',
		'normal',
		'high'
	);
} );

function studie247_render_udlejning_meta( $post ) {
	wp_nonce_field( 's247_udlejning_meta', 's247_udlejning_nonce' );
	$pris_dag    = get_post_meta( $post->ID, '_s247_pris_dag', true );
	$pris_uge    = get_post_meta( $post->ID, '_s247_pris_uge', true );
	$deposit     = get_post_meta( $post->ID, '_s247_deposit', true );
	$sku         = get_post_meta( $post->ID, '_s247_sku', true );
	$in_stock    = get_post_meta( $post->ID, '_s247_in_stock', true );
	$antal       = get_post_meta( $post->ID, '_s247_antal', true );
	$ejer        = get_post_meta( $post->ID, '_s247_ejer', true );
	$serienummer = get_post_meta( $post->ID, '_s247_serienummer', true );
	?>
	<p>
		<label for="s247_pris_dag"><strong><?php esc_html_e( 'Pris pr. dag (fx 299 kr)', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_pris_dag" name="s247_pris_dag" value="<?php echo esc_attr( $pris_dag ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_pris_uge"><strong><?php esc_html_e( 'Pris pr. uge (valgfrit)', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_pris_uge" name="s247_pris_uge" value="<?php echo esc_attr( $pris_uge ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_deposit"><strong><?php esc_html_e( 'Depositum (valgfrit)', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_deposit" name="s247_deposit" value="<?php echo esc_attr( $deposit ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_sku"><strong><?php esc_html_e( 'Vare-nr./SKU (valgfrit)', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_sku" name="s247_sku" value="<?php echo esc_attr( $sku ); ?>" style="width:100%">
	</p>
	<p>
		<label for="s247_antal"><strong><?php esc_html_e( 'Antal på lager', 'studie247' ); ?></strong></label><br>
		<input type="number" id="s247_antal" name="s247_antal" value="<?php echo esc_attr( $antal ); ?>" min="0" step="1" style="width:100px">
		<span style="color:#666;font-size:12px;">&nbsp;<?php esc_html_e( '(0 = udlejet / ikke tilgængelig)', 'studie247' ); ?></span>
	</p>
	<p>
		<label>
			<input type="checkbox" name="s247_in_stock" value="1" <?php checked( '1', $in_stock ); ?>>
			<?php esc_html_e( 'Kan lejes nu (bliver sat automatisk når Antal > 0)', 'studie247' ); ?>
		</label>
	</p>

	<hr style="margin:16px 0;">
	<p style="font-size:12px;color:#666;margin:0 0 8px;"><strong><?php esc_html_e( 'Kun til intern brug — vises ikke på forsiden', 'studie247' ); ?></strong></p>
	<p>
		<label for="s247_ejer"><strong><?php esc_html_e( 'Ejer', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_ejer" name="s247_ejer" value="<?php echo esc_attr( $ejer ); ?>" style="width:100%" placeholder="z-15">
	</p>
	<p>
		<label for="s247_serienummer"><strong><?php esc_html_e( 'Serienummer', 'studie247' ); ?></strong></label><br>
		<input type="text" id="s247_serienummer" name="s247_serienummer" value="<?php echo esc_attr( $serienummer ); ?>" style="width:100%">
	</p>
	<?php
}

add_action( 'save_post_udlejning_item', function ( $post_id ) {
	if ( ! isset( $_POST['s247_udlejning_nonce'] ) || ! wp_verify_nonce( $_POST['s247_udlejning_nonce'], 's247_udlejning_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( 's247_pris_dag', 's247_pris_uge', 's247_deposit', 's247_sku', 's247_ejer', 's247_serienummer' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
	if ( isset( $_POST['s247_antal'] ) && $_POST['s247_antal'] !== '' ) {
		$qty = max( 0, (int) $_POST['s247_antal'] );
		update_post_meta( $post_id, '_s247_antal', $qty );
		update_post_meta( $post_id, '_s247_in_stock', $qty > 0 ? '1' : '0' );
	} else {
		update_post_meta( $post_id, '_s247_in_stock', ! empty( $_POST['s247_in_stock'] ) ? '1' : '0' );
	}
} );
