<?php
/**
 * Booking-inspektion: dokumentation af produkt-tilstand før og efter udlejning.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────── Meta-bokse på booking ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_booking_out',
		__( 'Udleverings-tjek (før udlejning)', 'studie247' ),
		'studie247_render_booking_out',
		'booking',
		'normal',
		'high'
	);
	add_meta_box(
		's247_booking_in',
		__( 'Returnerings-tjek (efter udlejning)', 'studie247' ),
		'studie247_render_booking_in',
		'booking',
		'normal',
		'default'
	);
} );

/**
 * Helper — render gallery picker.
 */
function studie247_render_gallery_picker( $field, $value ) {
	$ids   = array_filter( array_map( 'intval', explode( ',', (string) $value ) ) );
	$thumb_html = '';
	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_url( $id, 'thumbnail' );
		if ( $src ) {
			$thumb_html .= '<span class="s247-gal__thumb" data-id="' . esc_attr( $id ) . '"><img src="' . esc_url( $src ) . '" alt=""><button type="button" class="s247-gal__rm" aria-label="Fjern">×</button></span>';
		}
	}
	?>
	<div class="s247-gallery" data-field="<?php echo esc_attr( $field ); ?>">
		<div class="s247-gal__list"><?php echo $thumb_html; // already escaped ?></div>
		<input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-input>
		<button type="button" class="button s247-gal__add"><?php esc_html_e( 'Tilføj billeder', 'studie247' ); ?></button>
	</div>
	<?php
}

function studie247_render_booking_out( $post ) {
	wp_nonce_field( 's247_booking_check', 's247_booking_check_nonce' );
	$status     = get_post_meta( $post->ID, '_s247_check_out_status', true );
	$contents   = get_post_meta( $post->ID, '_s247_check_out_contents', true );
	$notes      = get_post_meta( $post->ID, '_s247_check_out_notes', true );
	$photos     = get_post_meta( $post->ID, '_s247_check_out_photos', true );
	$by         = get_post_meta( $post->ID, '_s247_check_out_by', true );
	$at         = get_post_meta( $post->ID, '_s247_check_out_at', true );

	$statuses = array(
		''             => __( '— Vælg —', 'studie247' ),
		'perfekt'      => __( '✓ Perfekt stand', 'studie247' ),
		'let_slid'     => __( '○ Let slid (normal brug)', 'studie247' ),
		'mindre_skade' => __( '! Mindre skade dokumenteret', 'studie247' ),
		'skal_repareres' => __( '✕ Skal repareres', 'studie247' ),
	);
	?>
	<style>
		.s247-row { margin: 0 0 16px; }
		.s247-row label { display: block; font-weight: 600; margin-bottom: 6px; }
		.s247-row input[type=text],
		.s247-row select,
		.s247-row textarea { width: 100%; }
		.s247-gallery { padding: 12px; background: #fafafa; border: 1px solid #ddd; border-radius: 6px; }
		.s247-gal__list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; min-height: 60px; }
		.s247-gal__thumb { position: relative; display: inline-block; }
		.s247-gal__thumb img { display: block; width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; }
		.s247-gal__rm { position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; line-height: 18px; font-size: 16px; border-radius: 50%; background: #9E2B25; color: #fff; border: 2px solid #fff; cursor: pointer; padding: 0; }
		.s247-meta-line { font-size: 12px; color: #666; margin-top: 6px; }
	</style>
	<div class="s247-row">
		<label for="s247_check_out_status"><?php esc_html_e( 'Status', 'studie247' ); ?></label>
		<select id="s247_check_out_status" name="s247_check_out_status">
			<?php foreach ( $statuses as $k => $label ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="s247-row">
		<label for="s247_check_out_contents"><?php esc_html_e( 'Hvad blev udleveret (én pr. linje)', 'studie247' ); ?></label>
		<textarea id="s247_check_out_contents" name="s247_check_out_contents" rows="3" placeholder="Kamera-hus
2 batterier
Lader
SD-kort 128 GB
Original taske"><?php echo esc_textarea( $contents ); ?></textarea>
	</div>
	<div class="s247-row">
		<label for="s247_check_out_notes"><?php esc_html_e( 'Noter om tilstand', 'studie247' ); ?></label>
		<textarea id="s247_check_out_notes" name="s247_check_out_notes" rows="3" placeholder="Mindre ridse på top-grebet, ellers OK."><?php echo esc_textarea( $notes ); ?></textarea>
	</div>
	<div class="s247-row">
		<label><?php esc_html_e( 'Fotos før udlevering', 'studie247' ); ?></label>
		<?php studie247_render_gallery_picker( 's247_check_out_photos', $photos ); ?>
	</div>
	<?php if ( $by || $at ) : ?>
		<p class="s247-meta-line">
			<?php
			if ( $by ) {
				$user = get_userdata( (int) $by );
				printf( esc_html__( 'Tjekket af %s', 'studie247' ), esc_html( $user ? $user->display_name : '#' . $by ) );
			}
			if ( $at ) {
				echo ' · ' . esc_html( date_i18n( 'j. M Y H:i', (int) $at ) );
			}
			?>
		</p>
	<?php endif; ?>
	<?php
}

function studie247_render_booking_in( $post ) {
	$status   = get_post_meta( $post->ID, '_s247_check_in_status', true );
	$ok       = get_post_meta( $post->ID, '_s247_check_in_ok', true );
	$notes    = get_post_meta( $post->ID, '_s247_check_in_notes', true );
	$photos   = get_post_meta( $post->ID, '_s247_check_in_photos', true );
	$by       = get_post_meta( $post->ID, '_s247_check_in_by', true );
	$at       = get_post_meta( $post->ID, '_s247_check_in_at', true );

	$statuses = array(
		''             => __( '— Ikke returneret endnu —', 'studie247' ),
		'perfekt'      => __( '✓ Returneret intakt', 'studie247' ),
		'let_slid'     => __( '○ Returneret med normalt slid', 'studie247' ),
		'mindre_skade' => __( '! Ny skade — mindre', 'studie247' ),
		'skade'        => __( '✕ Større skade — kunde faktureres', 'studie247' ),
		'mangler'      => __( '? Manglende dele', 'studie247' ),
	);
	?>
	<div class="s247-row">
		<label for="s247_check_in_status"><?php esc_html_e( 'Returnerings-status', 'studie247' ); ?></label>
		<select id="s247_check_in_status" name="s247_check_in_status">
			<?php foreach ( $statuses as $k => $label ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="s247-row">
		<label>
			<input type="checkbox" name="s247_check_in_ok" value="1" <?php checked( '1', $ok ); ?>>
			<?php esc_html_e( 'Alt udleveret er kommet retur', 'studie247' ); ?>
		</label>
	</div>
	<div class="s247-row">
		<label for="s247_check_in_notes"><?php esc_html_e( 'Noter ved retur', 'studie247' ); ?></label>
		<textarea id="s247_check_in_notes" name="s247_check_in_notes" rows="3" placeholder="Ny ridse på linsekransen — sammenlign med fotos før udlevering."><?php echo esc_textarea( $notes ); ?></textarea>
	</div>
	<div class="s247-row">
		<label><?php esc_html_e( 'Fotos efter retur', 'studie247' ); ?></label>
		<?php studie247_render_gallery_picker( 's247_check_in_photos', $photos ); ?>
	</div>
	<?php if ( $by || $at ) : ?>
		<p class="s247-meta-line">
			<?php
			if ( $by ) {
				$user = get_userdata( (int) $by );
				printf( esc_html__( 'Modtaget af %s', 'studie247' ), esc_html( $user ? $user->display_name : '#' . $by ) );
			}
			if ( $at ) {
				echo ' · ' . esc_html( date_i18n( 'j. M Y H:i', (int) $at ) );
			}
			?>
		</p>
	<?php endif; ?>
	<?php
}

/* ───────── Save ───────── */
add_action( 'save_post_booking', function ( $post_id ) {
	if ( ! isset( $_POST['s247_booking_check_nonce'] ) || ! wp_verify_nonce( $_POST['s247_booking_check_nonce'], 's247_booking_check' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// OUT (før udlejning)
	$out_fields = array( 's247_check_out_status', 's247_check_out_contents', 's247_check_out_notes', 's247_check_out_photos' );
	$out_changed = false;
	foreach ( $out_fields as $f ) {
		if ( isset( $_POST[ $f ] ) ) {
			$new = sanitize_textarea_field( wp_unslash( $_POST[ $f ] ) );
			if ( $new !== get_post_meta( $post_id, '_' . $f, true ) ) {
				$out_changed = true;
			}
			update_post_meta( $post_id, '_' . $f, $new );
		}
	}
	if ( $out_changed && ! empty( $_POST['s247_check_out_status'] ) ) {
		update_post_meta( $post_id, '_s247_check_out_by', get_current_user_id() );
		update_post_meta( $post_id, '_s247_check_out_at', time() );
	}

	// IN (efter retur)
	$in_fields = array( 's247_check_in_status', 's247_check_in_notes', 's247_check_in_photos' );
	$in_changed = false;
	foreach ( $in_fields as $f ) {
		if ( isset( $_POST[ $f ] ) ) {
			$new = sanitize_textarea_field( wp_unslash( $_POST[ $f ] ) );
			if ( $new !== get_post_meta( $post_id, '_' . $f, true ) ) {
				$in_changed = true;
			}
			update_post_meta( $post_id, '_' . $f, $new );
		}
	}
	update_post_meta( $post_id, '_s247_check_in_ok', ! empty( $_POST['s247_check_in_ok'] ) ? '1' : '0' );

	if ( $in_changed && ! empty( $_POST['s247_check_in_status'] ) ) {
		update_post_meta( $post_id, '_s247_check_in_by', get_current_user_id() );
		update_post_meta( $post_id, '_s247_check_in_at', time() );
	}
} );

/* ───────── Enqueue media uploader ───────── */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen ) { return; }
	if ( ! in_array( $screen->post_type, array( 'booking', 'udlejning_item' ), true ) ) { return; }
	wp_enqueue_media();
	add_action( 'admin_print_footer_scripts', 'studie247_gallery_picker_js', 99 );
} );

function studie247_gallery_picker_js() {
	?>
	<script>
	(function($){
		$(function(){
			$('.s247-gallery').each(function(){
				var $g    = $(this);
				var $list = $g.find('.s247-gal__list');
				var $in   = $g.find('[data-input]');

				function getIds() {
					var v = ($in.val() || '').trim();
					return v ? v.split(',').map(function(x){ return parseInt(x,10); }).filter(Boolean) : [];
				}
				function setIds(ids) { $in.val(ids.join(',')); }
				function thumbHtml(id, url) {
					return '<span class="s247-gal__thumb" data-id="' + id + '"><img src="' + url + '" alt=""><button type="button" class="s247-gal__rm" aria-label="Fjern">×</button></span>';
				}

				$g.on('click', '.s247-gal__rm', function(e){
					e.preventDefault();
					var id = parseInt($(this).closest('.s247-gal__thumb').data('id'), 10);
					setIds(getIds().filter(function(x){ return x !== id; }));
					$(this).closest('.s247-gal__thumb').remove();
				});

				$g.find('.s247-gal__add').on('click', function(e){
					e.preventDefault();
					var frame = wp.media({
						title: 'Vælg billeder',
						button: { text: 'Tilføj' },
						library: { type: 'image' },
						multiple: true
					});
					frame.on('select', function(){
						var sel = frame.state().get('selection');
						var current = getIds();
						sel.each(function(att){
							var data = att.toJSON();
							if (current.indexOf(data.id) === -1) {
								current.push(data.id);
								var url = (data.sizes && data.sizes.thumbnail) ? data.sizes.thumbnail.url : data.url;
								$list.append(thumbHtml(data.id, url));
							}
						});
						setIds(current);
					});
					frame.open();
				});
			});
		});
	})(jQuery);
	</script>
	<?php
}

/* ───────── Booking-liste i admin: kolonner ───────── */
add_filter( 'manage_booking_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['s247_date']    = __( 'Dato', 'studie247' );
			$new['s247_product'] = __( 'Produkt', 'studie247' );
			$new['s247_status']  = __( 'Tjek', 'studie247' );
		}
	}
	return $new;
} );

add_action( 'manage_booking_posts_custom_column', function ( $col, $post_id ) {
	if ( 's247_date' === $col ) {
		$d = get_post_meta( $post_id, '_s247_date', true );
		$s = get_post_meta( $post_id, '_s247_start', true );
		echo esc_html( $d . ( $s ? ' kl. ' . $s : '' ) );
	}
	if ( 's247_product' === $col ) {
		$pid = (int) get_post_meta( $post_id, '_s247_produkt_id', true );
		if ( $pid ) {
			printf( '<a href="%s">%s</a>', esc_url( get_edit_post_link( $pid ) ), esc_html( get_the_title( $pid ) ) );
		} else {
			$slug = get_post_meta( $post_id, '_s247_produkt', true );
			echo $slug ? esc_html( $slug ) : '—';
		}
	}
	if ( 's247_status' === $col ) {
		$out = get_post_meta( $post_id, '_s247_check_out_status', true );
		$in  = get_post_meta( $post_id, '_s247_check_in_status',  true );
		$badge = function ( $s ) {
			$map = array(
				'perfekt'        => array( '#0a7c2f', '✓' ),
				'let_slid'       => array( '#7d6500', '○' ),
				'mindre_skade'   => array( '#9E2B25', '!' ),
				'skal_repareres' => array( '#9E2B25', '✕' ),
				'skade'          => array( '#9E2B25', '✕' ),
				'mangler'        => array( '#9E2B25', '?' ),
			);
			if ( ! $s || ! isset( $map[ $s ] ) ) { return '<span style="color:#aaa;">—</span>'; }
			return '<span style="display:inline-block;width:18px;height:18px;line-height:18px;text-align:center;border-radius:50%;background:' . $map[ $s ][0] . ';color:#fff;font-size:11px;font-weight:700;">' . $map[ $s ][1] . '</span>';
		};
		echo 'Ud: ' . $badge( $out ) . ' &nbsp; Ind: ' . $badge( $in );
	}
}, 10, 2 );

/* ───────── Historik på udlejnings-produktet ───────── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		's247_product_state',
		__( 'Tilstands-dokumentation (intern)', 'studie247' ),
		'studie247_render_product_state',
		'udlejning_item',
		'normal',
		'high'
	);
	add_meta_box(
		's247_product_history',
		__( 'Udlejnings-historik', 'studie247' ),
		'studie247_render_product_history',
		'udlejning_item',
		'normal',
		'low'
	);
} );

function studie247_render_product_state( $post ) {
	wp_nonce_field( 's247_state_images', 's247_state_images_nonce' );
	$value = get_post_meta( $post->ID, '_s247_state_images', true );
	?>
	<p style="margin:0 0 10px;color:#666;font-size:13px;">
		<?php esc_html_e( 'Billeder af produktets tilstand som reference. Vises kun i admin — ikke på forsiden. Kan importeres via CSV eller tilføjes manuelt her.', 'studie247' ); ?>
	</p>
	<?php studie247_render_gallery_picker( 's247_state_images', $value ); ?>
	<?php
}

add_action( 'save_post_udlejning_item', function ( $post_id ) {
	if ( ! isset( $_POST['s247_state_images_nonce'] ) || ! wp_verify_nonce( $_POST['s247_state_images_nonce'], 's247_state_images' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	if ( isset( $_POST['s247_state_images'] ) ) {
		$clean = implode( ',', array_filter( array_map( 'intval', explode( ',', (string) wp_unslash( $_POST['s247_state_images'] ) ) ) ) );
		update_post_meta( $post_id, '_s247_state_images', $clean );
	}
} );

function studie247_render_product_history( $post ) {
	$bookings = get_posts( array(
		'post_type'      => 'booking',
		'post_status'    => 'publish',
		'posts_per_page' => 25,
		'meta_query'     => array(
			array( 'key' => '_s247_produkt_id', 'value' => $post->ID, 'compare' => '=' ),
		),
		'orderby'        => 'meta_value',
		'meta_key'       => '_s247_date',
		'order'          => 'DESC',
	) );

	if ( empty( $bookings ) ) {
		echo '<p style="color:#666;">' . esc_html__( 'Ingen bookinger endnu for dette produkt.', 'studie247' ) . '</p>';
		return;
	}
	?>
	<style>
		.s247-hist-row { background: #fff; }
		.s247-hist-photos { padding: 12px 14px !important; background: #fafafa !important; border-top: 1px dashed #ddd; }
		.s247-hist-photos h4 { margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
		.s247-hist-strip { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
		.s247-hist-strip a { display: block; }
		.s247-hist-strip img { width: 64px; height: 64px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; transition: transform 120ms ease; }
		.s247-hist-strip a:hover img { transform: scale(1.06); }
	</style>
	<table class="widefat striped" style="margin-top:6px;">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Dato', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'Kunde', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'Ud', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'Ind', 'studie247' ); ?></th>
				<th><?php esc_html_e( 'Fotos', 'studie247' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $bookings as $b ) :
			$date  = get_post_meta( $b->ID, '_s247_date', true );
			$name  = get_post_meta( $b->ID, '_s247_name', true );
			$out   = get_post_meta( $b->ID, '_s247_check_out_status', true );
			$in    = get_post_meta( $b->ID, '_s247_check_in_status',  true );
			$out_p = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $b->ID, '_s247_check_out_photos', true ) ) ) );
			$in_p  = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $b->ID, '_s247_check_in_photos',  true ) ) ) );
			$total = count( $out_p ) + count( $in_p );
		?>
			<tr class="s247-hist-row">
				<td><?php echo esc_html( $date ); ?></td>
				<td><?php echo esc_html( $name ); ?></td>
				<td><?php echo $out ? esc_html( $out ) : '—'; ?></td>
				<td><?php echo $in ? esc_html( $in ) : '—'; ?></td>
				<td><?php echo (int) $total; ?></td>
				<td><a class="button button-small" href="<?php echo esc_url( get_edit_post_link( $b->ID ) ); ?>"><?php esc_html_e( 'Åbn', 'studie247' ); ?></a></td>
			</tr>
			<?php if ( $total > 0 ) : ?>
				<tr>
					<td colspan="6" class="s247-hist-photos">
						<?php if ( ! empty( $out_p ) ) : ?>
							<h4><?php esc_html_e( 'Før udlejning', 'studie247' ); ?> (<?php echo count( $out_p ); ?>)</h4>
							<div class="s247-hist-strip">
								<?php foreach ( $out_p as $aid ) :
									$thumb = wp_get_attachment_image_url( $aid, 'thumbnail' );
									$full  = wp_get_attachment_image_url( $aid, 'full' );
									if ( $thumb ) : ?>
										<a href="<?php echo esc_url( $full ?: $thumb ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( $thumb ); ?>" alt=""></a>
									<?php endif;
								endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $in_p ) ) : ?>
							<h4><?php esc_html_e( 'Efter retur', 'studie247' ); ?> (<?php echo count( $in_p ); ?>)</h4>
							<div class="s247-hist-strip">
								<?php foreach ( $in_p as $aid ) :
									$thumb = wp_get_attachment_image_url( $aid, 'thumbnail' );
									$full  = wp_get_attachment_image_url( $aid, 'full' );
									if ( $thumb ) : ?>
										<a href="<?php echo esc_url( $full ?: $thumb ); ?>" target="_blank" rel="noopener"><img src="<?php echo esc_url( $thumb ); ?>" alt=""></a>
									<?php endif;
								endforeach; ?>
							</div>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}
