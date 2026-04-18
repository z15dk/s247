<?php
/**
 * Udlejning CSV-import.
 *
 * Tilføjer en admin-side under Udlejning-menuen hvor man kan uploade
 * en CSV med kolonner: title, excerpt, content, pris_dag, pris_uge,
 * deposit, sku, in_stock, kategori, image_url.
 *
 * @package Studie247
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=udlejning_item',
		__( 'Importér CSV', 'studie247' ),
		__( 'Importér CSV', 'studie247' ),
		'edit_posts',
		's247-udlejning-import',
		'studie247_udlejning_import_page'
	);
} );

function studie247_udlejning_import_page() {
	$result = null;
	if ( ! empty( $_POST['s247_udlejning_csv_nonce'] )
		&& wp_verify_nonce( $_POST['s247_udlejning_csv_nonce'], 's247_udlejning_csv' )
		&& ! empty( $_FILES['s247_csv']['tmp_name'] )
	) {
		$result = studie247_udlejning_import_csv( $_FILES['s247_csv']['tmp_name'], ! empty( $_POST['s247_download_images'] ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Importér udstyr fra CSV', 'studie247' ); ?></h1>

		<?php if ( $result ) : ?>
			<?php if ( ! empty( $result['error'] ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $result['error'] ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-success">
					<p>
						<?php
						printf(
							esc_html__( 'Import færdig: %1$d oprettet, %2$d opdateret, %3$d sprunget over.', 'studie247' ),
							(int) $result['created'],
							(int) $result['updated'],
							(int) $result['skipped']
						);
						?>
					</p>
				</div>
				<?php if ( ! empty( $result['messages'] ) ) : ?>
					<details style="margin-top:10px;">
						<summary><?php esc_html_e( 'Detaljer', 'studie247' ); ?></summary>
						<ul style="margin: 8px 0 0 20px;">
							<?php foreach ( $result['messages'] as $msg ) : ?>
								<li><?php echo esc_html( $msg ); ?></li>
							<?php endforeach; ?>
						</ul>
					</details>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>

		<p style="max-width:720px;">
			<?php esc_html_e( 'Upload en CSV-fil med kolonner: ', 'studie247' ); ?>
			<code>title, excerpt, content, pris_dag, pris_uge, deposit, sku, in_stock, kategori, image_url</code>.
			<?php esc_html_e( 'Kun "title" er påkrævet. Hvis "sku" eller "title" matcher et eksisterende produkt, opdateres det.', 'studie247' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data" style="background:#fff;padding:20px;border:1px solid #ccd0d4;max-width:720px;">
			<?php wp_nonce_field( 's247_udlejning_csv', 's247_udlejning_csv_nonce' ); ?>
			<p>
				<label for="s247_csv"><strong><?php esc_html_e( 'Vælg CSV-fil', 'studie247' ); ?></strong></label><br>
				<input type="file" name="s247_csv" id="s247_csv" accept=".csv,text/csv" required>
			</p>
			<p>
				<label>
					<input type="checkbox" name="s247_download_images" value="1" checked>
					<?php esc_html_e( 'Hent billeder fra "image_url" og tilføj dem til mediebiblioteket', 'studie247' ); ?>
				</label>
			</p>
			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Importér', 'studie247' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=s247-udlejning-import&template=1' ) ); ?>" class="button">
					<?php esc_html_e( 'Hent skabelon', 'studie247' ); ?>
				</a>
			</p>
		</form>

		<h2 style="margin-top:30px;"><?php esc_html_e( 'CSV-eksempel', 'studie247' ); ?></h2>
		<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;overflow:auto;max-width:100%;font-size:12px;">title,excerpt,content,pris_dag,pris_uge,deposit,sku,in_stock,kategori,image_url
Sony FS6,Fuld-frame cinema kamera,Cinematisk farver. Klar til leje.,"1.499 kr","5.999 kr","5.000 kr",FS6-01,1,Kamera,https://example.com/fs6.jpg
GoPro Hero 10,Action-kamera til lyd og lys,,299 kr,999 kr,,GH10-02,1,Kamera,
Pixel Lyspakke,3 lamper plus stativer,Softbox + 2 spots,449 kr,1.499 kr,2.000 kr,LIGHT-01,1,Lys,</pre>
	</div>
	<?php
}

/**
 * Håndter template-download.
 */
add_action( 'admin_init', function () {
	if ( empty( $_GET['page'] ) || 's247-udlejning-import' !== $_GET['page'] ) { return; }
	if ( empty( $_GET['template'] ) ) { return; }
	if ( ! current_user_can( 'edit_posts' ) ) { return; }

	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="studie247-udlejning-skabelon.csv"' );
	// UTF-8 BOM så Excel åbner Ã¦/Ã¸/Ã¥ korrekt.
	echo "\xEF\xBB\xBF";
	echo "title,excerpt,content,pris_dag,pris_uge,deposit,sku,in_stock,kategori,image_url\n";
	$rows = array(
		array( 'Sony FS6',           'Fuld-frame cinema-kamera',      'Cinematisk farvegengivelse, S-Cinetone, klar til plug-and-play.',            '1.499 kr', '5.999 kr', '10.000 kr', 'CAM-FS6',    '1', 'Kamera',       '' ),
		array( 'Canon R5 C',         'Hybrid still + 8K video',       '8K RAW, fuld-frame sensor, inkl. 2 batterier og CFexpress-kort.',            '1.299 kr', '4.999 kr', '8.000 kr',  'CAM-R5C',    '1', 'Kamera',       '' ),
		array( 'GoPro Hero 10',      'Action-kamera',                 'Vandtæt, stabiliseret, kommer med 3 batterier og ladestation.',              '299 kr',   '999 kr',   '',          'CAM-GH10',   '1', 'Kamera',       '' ),
		array( 'Aputure 600D Pro',   'Daylight COB-lys 600W',         'Bowens-mount, kører på bi-color. Inkluderer lantern + softbox.',             '599 kr',   '2.299 kr', '3.000 kr',  'LYS-A600',   '1', 'Lys',          '' ),
		array( 'Pixel Lyspakke',     '3 lamper + stativer',           'Softbox hovedlys, 2 spots og 3 C-stands. Perfekt til talking-head.',         '449 kr',   '1.499 kr', '2.000 kr',  'LYS-PIXEL',  '1', 'Lys',          '' ),
		array( 'Sennheiser MKH-416', 'Shotgun-mikrofon',              'Broadcast-standard. Kommer med blimp, pistol-grip og XLR-kabel.',            '349 kr',   '1.199 kr', '2.500 kr',  'LYD-MKH',    '1', 'Lyd',          '' ),
		array( 'Zoom F6 Field Recorder', '32-bit floating recorder',  '6 kanaler, timecode, SD-kort inkl. Ingen gain at sætte.',                    '299 kr',   '999 kr',   '',          'LYD-ZF6',    '1', 'Lyd',          '' ),
		array( 'DJI Ronin 4D',       'Gimbal med indbygget kamera',   '4-axis stabilisering, LiDAR autofocus. Til bevægelses-shots.',               '999 kr',   '3.499 kr', '6.000 kr',  'GRIP-R4D',   '1', 'Grip|Kamera',  '' ),
		array( 'Manfrotto Slider',   '100 cm rail-slider',            'Motoriseret. Strøm via V-mount eller net.',                                  '199 kr',   '699 kr',   '',          'GRIP-SLD',  '1', 'Grip',         '' ),
		array( 'Teleprompter 15"',   'iPad-baseret prompter',         'Inkl. app. Passer på alle kameraer med 15mm rods.',                          '299 kr',   '999 kr',   '',          'GRIP-PROM', '0', 'Grip',         '' ),
	);
	foreach ( $rows as $r ) {
		echo '"' . implode( '","', array_map( function ( $v ) { return str_replace( '"', '""', $v ); }, $r ) ) . '"' . "\n";
	}
	exit;
} );

/**
 * Parse CSV og opret/opdatér udlejning_item posts.
 *
 * @return array { 'created' => int, 'updated' => int, 'skipped' => int, 'messages' => array, 'error' => string }
 */
function studie247_udlejning_import_csv( $path, $download_images = true ) {
	$result = array(
		'created'  => 0,
		'updated'  => 0,
		'skipped'  => 0,
		'messages' => array(),
		'error'    => '',
	);

	$handle = @fopen( $path, 'r' );
	if ( ! $handle ) {
		$result['error'] = __( 'Kunne ikke åbne filen.', 'studie247' );
		return $result;
	}

	// Gæt separator: komma eller semikolon.
	$first = fgets( $handle );
	$sep   = ( substr_count( $first, ';' ) > substr_count( $first, ',' ) ) ? ';' : ',';
	rewind( $handle );

	$header = fgetcsv( $handle, 0, $sep );
	if ( ! $header ) {
		fclose( $handle );
		$result['error'] = __( 'CSV-filen mangler en header-række.', 'studie247' );
		return $result;
	}
	$header = array_map( function ( $h ) {
		return strtolower( trim( $h ) );
	}, $header );

	$row_num = 1;
	while ( ( $row = fgetcsv( $handle, 0, $sep ) ) !== false ) {
		$row_num++;
		if ( count( $row ) === 1 && '' === trim( $row[0] ) ) { continue; } // tom linje

		$data = array();
		foreach ( $header as $i => $col ) {
			$data[ $col ] = isset( $row[ $i ] ) ? trim( $row[ $i ] ) : '';
		}

		$title = isset( $data['title'] ) ? $data['title'] : '';
		if ( ! $title ) {
			$result['skipped']++;
			$result['messages'][] = sprintf( 'Række %d: sprang over (intet title).', $row_num );
			continue;
		}

		// Find eksisterende post: først via SKU, så via title.
		$existing_id = 0;
		if ( ! empty( $data['sku'] ) ) {
			$q = get_posts( array(
				'post_type'      => 'udlejning_item',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'posts_per_page' => 1,
				'meta_query'     => array(
					array( 'key' => '_s247_sku', 'value' => $data['sku'], 'compare' => '=' ),
				),
				'fields'         => 'ids',
			) );
			if ( ! empty( $q ) ) { $existing_id = (int) $q[0]; }
		}
		if ( ! $existing_id ) {
			$existing = get_page_by_title( $title, OBJECT, 'udlejning_item' );
			if ( $existing ) { $existing_id = $existing->ID; }
		}

		$postarr = array(
			'post_type'    => 'udlejning_item',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $data['content'] ?? '',
			'post_excerpt' => $data['excerpt'] ?? '',
		);
		if ( $existing_id ) { $postarr['ID'] = $existing_id; }

		$post_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			$result['skipped']++;
			$result['messages'][] = sprintf( 'Række %d: fejl — %s', $row_num, $post_id->get_error_message() );
			continue;
		}

		// Meta
		foreach ( array( 'pris_dag', 'pris_uge', 'deposit', 'sku' ) as $m ) {
			if ( isset( $data[ $m ] ) && $data[ $m ] !== '' ) {
				update_post_meta( $post_id, '_s247_' . $m, sanitize_text_field( $data[ $m ] ) );
			}
		}
		$in_stock = isset( $data['in_stock'] ) ? strtolower( trim( $data['in_stock'] ) ) : '1';
		$in_stock = in_array( $in_stock, array( '1', 'true', 'ja', 'yes', 'y', 'på lager' ), true ) ? '1' : '0';
		update_post_meta( $post_id, '_s247_in_stock', $in_stock );

		// Kategori
		if ( ! empty( $data['kategori'] ) ) {
			$terms = array_map( 'trim', explode( '|', $data['kategori'] ) );
			$ids   = array();
			foreach ( $terms as $term_name ) {
				if ( '' === $term_name ) { continue; }
				$term = term_exists( $term_name, 'udlejning_kategori' );
				if ( ! $term ) {
					$term = wp_insert_term( $term_name, 'udlejning_kategori' );
				}
				if ( ! is_wp_error( $term ) ) {
					$ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
				}
			}
			if ( ! empty( $ids ) ) {
				wp_set_object_terms( $post_id, $ids, 'udlejning_kategori', false );
			}
		}

		// Billede
		if ( $download_images && ! empty( $data['image_url'] ) && filter_var( $data['image_url'], FILTER_VALIDATE_URL ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$attach_id = media_sideload_image( $data['image_url'], $post_id, null, 'id' );
			if ( ! is_wp_error( $attach_id ) ) {
				set_post_thumbnail( $post_id, $attach_id );
			} else {
				$result['messages'][] = sprintf( 'Række %d: kunne ikke hente billede — %s', $row_num, $attach_id->get_error_message() );
			}
		}

		if ( $existing_id ) {
			$result['updated']++;
		} else {
			$result['created']++;
		}
	}
	fclose( $handle );

	return $result;
}
