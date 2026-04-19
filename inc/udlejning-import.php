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
	$result      = null;
	$batch_start = null;

	if ( ! empty( $_POST['s247_udlejning_csv_nonce'] )
		&& wp_verify_nonce( $_POST['s247_udlejning_csv_nonce'], 's247_udlejning_csv' )
		&& ! empty( $_FILES['s247_csv']['tmp_name'] )
	) {
		$tmp  = $_FILES['s247_csv']['tmp_name'];
		$name = isset( $_FILES['s247_csv']['name'] ) ? strtolower( $_FILES['s247_csv']['name'] ) : '';
		if ( ! defined( 'S247_IMPORT_WEBP' ) ) {
			define( 'S247_IMPORT_WEBP', ! empty( $_POST['s247_webp'] ) );
		}
		$download_images = ! empty( $_POST['s247_download_images'] );

		// Forbered batch: parse CSV til array og gem i transient.
		$prepared = studie247_prepare_import( $tmp, $name, $download_images );
		if ( ! empty( $prepared['error'] ) ) {
			$result = array( 'error' => $prepared['error'], 'created' => 0, 'updated' => 0, 'skipped' => 0, 'messages' => array() );
		} else {
			$batch_start = $prepared['id'];
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Importér udstyr fra CSV', 'studie247' ); ?></h1>

		<?php if ( $batch_start ) : ?>
			<div id="s247-import-progress" data-import-id="<?php echo esc_attr( $batch_start ); ?>" style="background:#fff;border:1px solid #ccd0d4;padding:20px;max-width:720px;margin-bottom:20px;">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Importerer …', 'studie247' ); ?></h2>
				<p class="s247-import-progress__status"><?php esc_html_e( 'Klargør batch …', 'studie247' ); ?></p>
				<div style="background:#f0f0f1;border-radius:6px;height:14px;overflow:hidden;margin:12px 0;">
					<div class="s247-import-progress__bar" style="background:#9E2B25;height:100%;width:0%;transition:width 300ms ease;"></div>
				</div>
				<p class="s247-import-progress__count" style="font-family:monospace;font-size:13px;color:#666;">0 / ?</p>
				<details class="s247-import-progress__log" style="margin-top:12px;" open>
					<summary><?php esc_html_e( 'Log', 'studie247' ); ?></summary>
					<ul style="margin:8px 0 0 20px;font-size:12px;font-family:monospace;max-height:260px;overflow:auto;"></ul>
				</details>
			</div>
			<script>
			(function(){
				var el   = document.getElementById('s247-import-progress');
				var id   = el.dataset.importId;
				var bar  = el.querySelector('.s247-import-progress__bar');
				var cnt  = el.querySelector('.s247-import-progress__count');
				var stat = el.querySelector('.s247-import-progress__status');
				var log  = el.querySelector('.s247-import-progress__log ul');
				var ajaxUrl = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';
				var nonce   = '<?php echo esc_js( wp_create_nonce( 's247_import_batch' ) ); ?>';
				var offset = 0;
				var totals = { created: 0, updated: 0, skipped: 0 };
				var retries = 0;
				var MAX_RETRIES = 3;

				function logLine(text){
					var li = document.createElement('li');
					li.textContent = text;
					log.appendChild(li);
				}

				function fail(message, bodySnippet){
					stat.innerHTML = '<strong style="color:#b32d2e;">✗ ' + message + '</strong>';
					if (bodySnippet) {
						logLine('Server-svar: ' + bodySnippet);
					}
				}

				function step(){
					var data = new FormData();
					data.append('action', 's247_import_batch');
					data.append('nonce', nonce);
					data.append('id', id);
					data.append('offset', offset);

					fetch(ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' })
						.then(function(r){
							return r.text().then(function(body){ return { status: r.status, ok: r.ok, body: body }; });
						})
						.then(function(r){
							var res;
							try {
								res = JSON.parse(r.body);
							} catch (e) {
								// Serveren svarede ikke med JSON — vis den rå body så man kan se hvad der gik galt
								// (typisk: PHP fatal, HTML fejlside fra nginx/cloudflare, eller en PHP-notice der lækker).
								var snippet = (r.body || '').replace(/\s+/g, ' ').slice(0, 400);
								fail('Serveren svarede ikke med JSON (HTTP ' + r.status + ')', snippet);
								return;
							}
							if (!r.ok || !res.success) {
								fail('Fejl: ' + ((res && res.data && res.data.message) || ('HTTP ' + r.status)));
								return;
							}
							retries = 0;
							var d = res.data;
							offset = d.next_offset;
							totals.created += d.created;
							totals.updated += d.updated;
							totals.skipped += d.skipped;
							var pct = d.total > 0 ? Math.round((offset / d.total) * 100) : 100;
							bar.style.width = pct + '%';
							cnt.textContent = offset + ' / ' + d.total + ' — ' +
								totals.created + ' oprettet · ' + totals.updated + ' opdateret · ' + totals.skipped + ' sprunget over';
							(d.messages || []).forEach(logLine);
							if (d.done) {
								stat.innerHTML = '<strong style="color:#0a7c2f;">✓ Import færdig</strong>';
							} else {
								stat.textContent = 'Behandler rækker ' + (offset - d.batch_size + 1) + '–' + offset + ' …';
								setTimeout(step, 400);
							}
						})
						.catch(function(err){
							// Ægte netværksfejl (request aldrig nået frem eller forbindelsen blev lukket).
							retries++;
							if (retries > MAX_RETRIES) {
								fail('Netværksfejl efter ' + MAX_RETRIES + ' forsøg — ' + (err && err.message ? err.message : 'ukendt'));
								return;
							}
							var wait = Math.pow(2, retries) * 1000; // 2s, 4s, 8s
							stat.textContent = 'Netværksfejl — prøver igen om ' + (wait / 1000) + ' sek (' + retries + '/' + MAX_RETRIES + ') …';
							setTimeout(step, wait);
						});
				}
				step();
			})();
			</script>
		<?php endif; ?>

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
			<?php esc_html_e( 'Upload enten en CSV-fil eller en ZIP. Kolonner: ', 'studie247' ); ?>
			<code>title, excerpt, content, pris_dag, pris_uge, deposit, sku, in_stock, kategori, image_url, image_file, state_image_1..4, state_url_1..4</code>.
			<?php esc_html_e( 'Kun "title" er påkrævet. Eksisterende produkter matches via SKU → title og opdateres i stedet for at duplikere.', 'studie247' ); ?>
		</p>
		<p style="max-width:720px;background:#fff;border-left:3px solid #9E2B25;padding:10px 14px;">
			<strong><?php esc_html_e( 'ZIP-smart-import:', 'studie247' ); ?></strong>
			<?php esc_html_e( 'Pak CSV + en billed-mappe sammen (fx "produkter.csv" og "images/sony-fs6.jpg"). I CSV\'en skriver du bare filnavnet i "image_file" (fx sony-fs6.jpg). Systemet finder billedet i ZIP\'en og uploader det automatisk.', 'studie247' ); ?>
		</p>
		<p style="max-width:720px;background:#fff;border-left:3px solid #0a7c2f;padding:10px 14px;">
			<strong><?php esc_html_e( 'Tilstands-dokumentation:', 'studie247' ); ?></strong>
			<?php esc_html_e( 'Tilføj op til 4 billeder pr. produkt til intern tilstands-dokumentation via kolonnerne state_image_1 til state_image_4 (filnavne i ZIP) eller state_url_1 til state_url_4 (URL\'er). Billederne gemmes på produktet og vises kun i admin — ikke på forsiden.', 'studie247' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data" style="background:#fff;padding:20px;border:1px solid #ccd0d4;max-width:720px;">
			<?php wp_nonce_field( 's247_udlejning_csv', 's247_udlejning_csv_nonce' ); ?>
			<p>
				<label for="s247_csv"><strong><?php esc_html_e( 'Vælg CSV eller ZIP', 'studie247' ); ?></strong></label><br>
				<input type="file" name="s247_csv" id="s247_csv" accept=".csv,.zip,text/csv,application/zip" required>
			</p>
			<p>
				<label>
					<input type="checkbox" name="s247_download_images" value="1" checked>
					<?php esc_html_e( 'Hent billeder (fra image_url eller fra ZIP\'ens images/-mappe)', 'studie247' ); ?>
				</label>
			</p>
			<p>
				<label>
					<input type="checkbox" name="s247_webp" value="1">
					<?php esc_html_e( 'Konvertér til WebP (kræver Imagick eller GD med webp-support — prøv uden hvis importen fejler)', 'studie247' ); ?>
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
	$cols = array( 'title','excerpt','content','pris_dag','pris_uge','deposit','sku','in_stock','kategori','image_url','image_file','state_image_1','state_image_2','state_image_3','state_image_4','state_url_1','state_url_2','state_url_3','state_url_4' );
	echo implode( ',', $cols ) . "\n";
	$rows = array(
		array( 'Sony FS6',           'Fuld-frame cinema-kamera',      'Cinematisk farvegengivelse, S-Cinetone, klar til plug-and-play.',            '1.499 kr', '5.999 kr', '10.000 kr', 'CAM-FS6',    '1', 'Kamera',       '', 'sony-fs6.jpg',    'sony-fs6-state-1.jpg','sony-fs6-state-2.jpg','sony-fs6-state-3.jpg','sony-fs6-state-4.jpg','','','','' ),
		array( 'Canon R5 C',         'Hybrid still + 8K video',       '8K RAW, fuld-frame sensor, inkl. 2 batterier og CFexpress-kort.',            '1.299 kr', '4.999 kr', '8.000 kr',  'CAM-R5C',    '1', 'Kamera',       '', 'canon-r5c.jpg',   'canon-r5c-state-1.jpg','canon-r5c-state-2.jpg','','','','','','' ),
		array( 'GoPro Hero 10',      'Action-kamera',                 'Vandtæt, stabiliseret, kommer med 3 batterier og ladestation.',              '299 kr',   '999 kr',   '',          'CAM-GH10',   '1', 'Kamera',       '', 'gopro-hero10.jpg','','','','','','','','' ),
		array( 'Aputure 600D Pro',   'Daylight COB-lys 600W',         'Bowens-mount, kører på bi-color. Inkluderer lantern + softbox.',             '599 kr',   '2.299 kr', '3.000 kr',  'LYS-A600',   '1', 'Lys',          '', 'aputure-600d.jpg','','','','','','','','' ),
		array( 'Pixel Lyspakke',     '3 lamper + stativer',           'Softbox hovedlys, 2 spots og 3 C-stands. Perfekt til talking-head.',         '449 kr',   '1.499 kr', '2.000 kr',  'LYS-PIXEL',  '1', 'Lys',          '', 'pixel-pakke.jpg', '','','','','','','','' ),
		array( 'Sennheiser MKH-416', 'Shotgun-mikrofon',              'Broadcast-standard. Kommer med blimp, pistol-grip og XLR-kabel.',            '349 kr',   '1.199 kr', '2.500 kr',  'LYD-MKH',    '1', 'Lyd',          '', 'mkh416.jpg',      '','','','','','','','' ),
		array( 'Zoom F6 Field Recorder', '32-bit floating recorder',  '6 kanaler, timecode, SD-kort inkl. Ingen gain at sætte.',                    '299 kr',   '999 kr',   '',          'LYD-ZF6',    '1', 'Lyd',          '', 'zoom-f6.jpg',     '','','','','','','','' ),
		array( 'DJI Ronin 4D',       'Gimbal med indbygget kamera',   '4-axis stabilisering, LiDAR autofocus. Til bevægelses-shots.',               '999 kr',   '3.499 kr', '6.000 kr',  'GRIP-R4D',   '1', 'Grip|Kamera',  '', 'ronin-4d.jpg',    '','','','','','','','' ),
		array( 'Manfrotto Slider',   '100 cm rail-slider',            'Motoriseret. Strøm via V-mount eller net.',                                  '199 kr',   '699 kr',   '',          'GRIP-SLD',  '1', 'Grip',         '', 'slider.jpg',      '','','','','','','','' ),
		array( 'Teleprompter 15"',   'iPad-baseret prompter',         'Inkl. app. Passer på alle kameraer med 15mm rods.',                          '299 kr',   '999 kr',   '',          'GRIP-PROM', '0', 'Grip',         '', 'prompter.jpg',    '','','','','','','','' ),
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
function studie247_udlejning_import_csv( $path, $download_images = true, $image_dir = '' ) {
	$result = array(
		'created'  => 0,
		'updated'  => 0,
		'skipped'  => 0,
		'messages' => array(),
		'error'    => '',
	);

	// Giv importen luft til at hente mange billeder.
	@set_time_limit( 0 );
	@ini_set( 'memory_limit', '512M' );

	$handle = @fopen( $path, 'r' );
	if ( ! $handle ) {
		$result['error'] = __( 'Kunne ikke åbne filen.', 'studie247' );
		return $result;
	}

	// Gæt separator: komma, semikolon eller tab (vælg den der giver flest kolonner).
	$first = fgets( $handle );
	$counts = array(
		','  => substr_count( $first, ',' ),
		';'  => substr_count( $first, ';' ),
		"\t" => substr_count( $first, "\t" ),
	);
	arsort( $counts );
	reset( $counts );
	$sep = key( $counts );
	if ( ! $sep || 0 === $counts[ $sep ] ) { $sep = ','; }
	rewind( $handle );

	$alias_map = array(
		'tittel'                    => 'title',
		'titel'                     => 'title',
		'navn'                      => 'title',
		'kort beskrivelse'          => 'excerpt',
		'beskrivelse'               => 'excerpt',
		'lang beskrivelse'          => 'content',
		'dags pris'                 => 'pris_dag',
		'dagspris'                  => 'pris_dag',
		'pris pr dag'               => 'pris_dag',
		'pris pr. dag'              => 'pris_dag',
		'uge pris'                  => 'pris_uge',
		'ugepris'                   => 'pris_uge',
		'pris pr uge'               => 'pris_uge',
		'evt depositum'             => 'deposit',
		'depositum'                 => 'deposit',
		'tags'                      => 'sku',
		'tag'                       => 'sku',
		'vare-nr'                   => 'sku',
		'varenr'                    => 'sku',
		'antal'                     => 'antal',
		'stk'                       => 'antal',
		'quantity'                  => 'antal',
		'ejer'                      => 'ejer',
		'evt serienummer'           => 'serienummer',
		'serienummer'               => 'serienummer',
		'serie'                     => 'serienummer',
		'dokumentation af stand 1'  => 'state_url_1',
		'dokumentation af stand 2'  => 'state_url_2',
		'dokumentation af stand 3'  => 'state_url_3',
		'dokumentation af stand 4'  => 'state_url_4',
		'stand 1'                   => 'state_url_1',
		'stand 2'                   => 'state_url_2',
		'stand 3'                   => 'state_url_3',
		'stand 4'                   => 'state_url_4',
	);

	$normalize = function ( $h ) use ( $alias_map ) {
		// Fjern BOM fra første celle.
		$key = str_replace( "\xEF\xBB\xBF", '', (string) $h );
		$key = strtolower( trim( $key ) );
		$key = preg_replace( '/\s+/', ' ', $key );
		return isset( $alias_map[ $key ] ) ? $alias_map[ $key ] : $key;
	};

	// Prøv op til 3 rækker — spring over "meta-description"-rækker der ikke indeholder 'title'.
	$header        = null;
	$pre_header    = 0;
	for ( $i = 0; $i < 3; $i++ ) {
		$candidate = fgetcsv( $handle, 0, $sep );
		if ( false === $candidate ) { break; }
		$mapped = array_map( $normalize, $candidate );
		if ( in_array( 'title', $mapped, true ) ) {
			$header = $mapped;
			break;
		}
		$pre_header++;
	}

	if ( ! $header ) {
		fclose( $handle );
		$result['error'] = __( 'Kunne ikke finde en header-række med kolonnen "Tittel" / "title". Tjek at den findes i de første 3 rækker.', 'studie247' );
		return $result;
	}

	if ( $pre_header > 0 ) {
		$result['messages'][] = sprintf( 'Sprang over %d info-række(r) før header.', $pre_header );
	}

	$row_num = 1;
	while ( ( $row = fgetcsv( $handle, 0, $sep ) ) !== false ) {
		$row_num++;
		if ( count( $row ) === 1 && '' === trim( $row[0] ) ) { continue; } // tom linje

		// Reset PHP's timeout for hver række så vi overlever store imports.
		@set_time_limit( 60 );
		try {

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
			$q = get_posts( array(
				'post_type'      => 'udlejning_item',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );
			if ( ! empty( $q ) ) { $existing_id = (int) $q[0]; }
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
		foreach ( array( 'pris_dag', 'pris_uge', 'deposit', 'sku', 'ejer', 'serienummer' ) as $m ) {
			if ( isset( $data[ $m ] ) && $data[ $m ] !== '' ) {
				update_post_meta( $post_id, '_s247_' . $m, sanitize_text_field( $data[ $m ] ) );
			}
		}
		// Antal (styrer også in_stock hvis sat).
		if ( isset( $data['antal'] ) && $data['antal'] !== '' ) {
			$qty = max( 0, (int) $data['antal'] );
			update_post_meta( $post_id, '_s247_antal', $qty );
			update_post_meta( $post_id, '_s247_in_stock', $qty > 0 ? '1' : '0' );
		} else {
			$in_stock = isset( $data['in_stock'] ) ? strtolower( trim( $data['in_stock'] ) ) : '1';
			$in_stock = in_array( $in_stock, array( '1', 'true', 'ja', 'yes', 'y', 'på lager' ), true ) ? '1' : '0';
			update_post_meta( $post_id, '_s247_in_stock', $in_stock );
		}

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

		// Billede — prioritet: image_file (lokal fra ZIP) → image_url (remote).
		if ( $download_images ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attach_id = 0;
			$alt_text  = $title;

			if ( ! empty( $data['image_file'] ) && $image_dir ) {
				$local = studie247_find_image_in_dir( $image_dir, $data['image_file'] );
				if ( $local ) {
					$attach_id = studie247_sideload_local( $local, $post_id, $alt_text );
				}
			}

			if ( ! $attach_id && ! empty( $data['image_url'] ) && filter_var( $data['image_url'], FILTER_VALIDATE_URL ) ) {
				$attach_id = studie247_sideload_url( $data['image_url'], $post_id, $alt_text );
			}

			if ( $attach_id && ! is_wp_error( $attach_id ) ) {
				set_post_thumbnail( $post_id, $attach_id );
				$result['messages'][] = sprintf( 'Række %d (%s): hoved-billede importeret (ID %d).', $row_num, $title, $attach_id );
			} elseif ( is_wp_error( $attach_id ) ) {
				$result['messages'][] = sprintf( 'Række %d (%s): billede-fejl — %s', $row_num, $title, $attach_id->get_error_message() );
			} elseif ( ! empty( $data['image_url'] ) || ! empty( $data['image_file'] ) ) {
				$result['messages'][] = sprintf( 'Række %d (%s): billedet blev ikke importeret (ukendt årsag).', $row_num, $title );
			}

			// Tilstands-billeder (intern doku) — state_image_1..4 og state_url_1..4.
			$state_ids = array();
			for ( $si = 1; $si <= 4; $si++ ) {
				$state_aid = 0;
				$state_alt = sprintf( '%s — tilstand %d', $title, $si );
				$file_key  = 'state_image_' . $si;
				$url_key   = 'state_url_'   . $si;
				$has_file  = ! empty( $data[ $file_key ] );
				$has_url   = ! empty( $data[ $url_key ] );
				if ( ! $has_file && ! $has_url ) { continue; }

				try {
					if ( $has_file && $image_dir ) {
						$local = studie247_find_image_in_dir( $image_dir, $data[ $file_key ] );
						if ( $local ) { $state_aid = studie247_sideload_local( $local, $post_id, $state_alt ); }
					}
					if ( ! $state_aid && $has_url && filter_var( $data[ $url_key ], FILTER_VALIDATE_URL ) ) {
						$state_aid = studie247_sideload_url( $data[ $url_key ], $post_id, $state_alt );
					}
				} catch ( Throwable $e ) {
					$result['messages'][] = sprintf( 'Række %d: tilstands-billede %d kastede en exception — %s', $row_num, $si, $e->getMessage() );
					continue;
				}

				if ( $state_aid && ! is_wp_error( $state_aid ) ) {
					$state_ids[] = (int) $state_aid;
					$result['messages'][] = sprintf( 'Række %d (%s): tilstands-billede %d importeret (ID %d).', $row_num, $title, $si, $state_aid );
				} elseif ( is_wp_error( $state_aid ) ) {
					$result['messages'][] = sprintf( 'Række %d (%s): tilstands-billede %d fejlede — %s', $row_num, $title, $si, $state_aid->get_error_message() );
				} else {
					$result['messages'][] = sprintf( 'Række %d (%s): tilstands-billede %d blev ikke importeret.', $row_num, $title, $si );
				}
			}
			if ( ! empty( $state_ids ) ) {
				$existing_state = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $post_id, '_s247_state_images', true ) ) ) );
				$merged         = array_values( array_unique( array_merge( $existing_state, $state_ids ) ) );
				update_post_meta( $post_id, '_s247_state_images', implode( ',', $merged ) );
			}
		}

		if ( $existing_id ) {
			$result['updated']++;
		} else {
			$result['created']++;
		}
		} catch ( Throwable $e ) {
			$result['skipped']++;
			$result['messages'][] = sprintf( 'Række %d crashede — %s', $row_num, $e->getMessage() );
			continue;
		}
	}
	fclose( $handle );

	return $result;
}

/**
 * ZIP-import: pak CSV + images/ mappe ud og kør CSV-importen.
 */
function studie247_udlejning_import_zip( $zip_path, $download_images = true ) {
	$result = array(
		'created'  => 0,
		'updated'  => 0,
		'skipped'  => 0,
		'messages' => array(),
		'error'    => '',
	);

	if ( ! class_exists( 'ZipArchive' ) ) {
		$result['error'] = __( 'PHP mangler ZipArchive-udvidelsen. Upload CSV alene, eller bed serveren om at aktivere php-zip.', 'studie247' );
		return $result;
	}

	$zip = new ZipArchive();
	if ( true !== $zip->open( $zip_path ) ) {
		$result['error'] = __( 'Kunne ikke åbne ZIP-filen.', 'studie247' );
		return $result;
	}

	$upload_dir = wp_upload_dir();
	$tmp_base   = trailingslashit( $upload_dir['basedir'] ) . 's247-import-' . wp_generate_password( 8, false );
	if ( ! wp_mkdir_p( $tmp_base ) ) {
		$zip->close();
		$result['error'] = __( 'Kunne ikke oprette midlertidig mappe til udpakning.', 'studie247' );
		return $result;
	}

	$zip->extractTo( $tmp_base );
	$zip->close();

	// Find den første CSV-fil (rekursivt).
	$csv_path = '';
	$rii      = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $tmp_base, RecursiveDirectoryIterator::SKIP_DOTS ) );
	foreach ( $rii as $file ) {
		if ( $file->isFile() && strtolower( $file->getExtension() ) === 'csv' ) {
			$csv_path = $file->getPathname();
			break;
		}
	}

	if ( ! $csv_path ) {
		studie247_rrmdir( $tmp_base );
		$result['error'] = __( 'Der blev ikke fundet en CSV-fil i ZIP\'en.', 'studie247' );
		return $result;
	}

	// Brug hele udpaknings-mappen som billede-rod; helper søger rekursivt.
	$result = studie247_udlejning_import_csv( $csv_path, $download_images, $tmp_base );

	// Ryd op.
	studie247_rrmdir( $tmp_base );

	return $result;
}

/**
 * Find en fil (ved navn) rekursivt i en mappe. Match er case-insensitive.
 */
function studie247_find_image_in_dir( $dir, $filename ) {
	$filename = strtolower( basename( $filename ) );
	if ( ! is_dir( $dir ) ) { return ''; }
	$rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ) );
	foreach ( $rii as $file ) {
		if ( $file->isFile() && strtolower( $file->getFilename() ) === $filename ) {
			return $file->getPathname();
		}
	}
	return '';
}

/**
 * Kopier en lokal fil ind i mediebiblioteket og returnér attachment-ID.
 * Konverterer automatisk til WebP hvis muligt. Sætter alt-tekst.
 */
function studie247_sideload_local( $local_path, $post_id, $alt_text = '' ) {
	$mime_ok = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' );
	$ext     = strtolower( pathinfo( $local_path, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, $mime_ok, true ) ) {
		return new WP_Error( 'bad_ext', 'Ikke-understøttet billedformat: ' . $ext );
	}

	// Kopi ind i uploads-mappen.
	$filename = wp_unique_filename( wp_upload_dir()['path'], basename( $local_path ) );
	$dest     = trailingslashit( wp_upload_dir()['path'] ) . $filename;
	if ( ! @copy( $local_path, $dest ) ) {
		return new WP_Error( 'copy_failed', 'Kunne ikke kopiere billedet ind i mediebiblioteket.' );
	}

	// Konvertér til WebP (kun hvis slået til).
	if ( defined( 'S247_IMPORT_WEBP' ) && S247_IMPORT_WEBP && ! in_array( $ext, array( 'webp', 'svg' ), true ) ) {
		$webp = studie247_to_webp( $dest );
		if ( $webp ) {
			@unlink( $dest );
			$dest = $webp;
		}
	}

	$filetype = wp_check_filetype( basename( $dest ), null );
	$attach   = array(
		'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
		'post_title'     => $alt_text ?: preg_replace( '/\.[^.]+$/', '', basename( $dest ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id = wp_insert_attachment( $attach, $dest, $post_id );
	if ( is_wp_error( $attach_id ) ) { return $attach_id; }

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$meta = wp_generate_attachment_metadata( $attach_id, $dest );
	wp_update_attachment_metadata( $attach_id, $meta );

	if ( $alt_text ) {
		update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt_text );
	}

	return $attach_id;
}

/**
 * Rekursiv mappe-sletning.
 */
function studie247_rrmdir( $dir ) {
	if ( ! is_dir( $dir ) ) { return; }
	$items = @scandir( $dir );
	if ( ! $items ) { return; }
	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) { continue; }
		$path = trailingslashit( $dir ) . $item;
		if ( is_dir( $path ) ) {
			studie247_rrmdir( $path );
		} else {
			@unlink( $path );
		}
	}
	@rmdir( $dir );
}

/**
 * Robust URL-til-attachment sideloader.
 * Håndterer URLs uden fil-endelse, konverterer til WebP og sætter alt-tekst.
 */
function studie247_sideload_url( $url, $post_id, $alt_text = '' ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url, 15 );
	if ( is_wp_error( $tmp ) ) {
		return new WP_Error( 'download_failed', sprintf( 'Download fejl for %s: %s', esc_url( $url ), $tmp->get_error_message() ) );
	}
	if ( ! is_readable( $tmp ) || filesize( $tmp ) < 100 ) {
		@unlink( $tmp );
		return new WP_Error( 'empty_download', 'Billedet blev downloadet men filen er tom/for lille: ' . esc_url( $url ) );
	}

	$path = parse_url( $url, PHP_URL_PATH );
	$name = $path ? basename( $path ) : '';

	// Bestem fil-endelse fra MIME hvis URL ikke har en.
	if ( ! preg_match( '/\.(jpe?g|png|gif|webp|svg)$/i', $name ) ) {
		$mime = '';
		if ( function_exists( 'mime_content_type' ) ) {
			$mime = mime_content_type( $tmp );
		}
		if ( ! $mime && function_exists( 'finfo_open' ) ) {
			$f = finfo_open( FILEINFO_MIME_TYPE );
			$mime = $f ? finfo_file( $f, $tmp ) : '';
			if ( $f ) { finfo_close( $f ); }
		}
		$mime_map = array(
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/webp' => 'webp',
			'image/svg+xml' => 'svg',
		);
		$ext = isset( $mime_map[ $mime ] ) ? $mime_map[ $mime ] : 'jpg';

		$base = $name ?: 'image-' . wp_generate_password( 6, false );
		$base = preg_replace( '/\.[^.]+$/', '', $base );
		$name = $base . '.' . $ext;
	}

	$name = sanitize_file_name( $name );
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

	// Konvertér til WebP (kun hvis slået til).
	if ( defined( 'S247_IMPORT_WEBP' ) && S247_IMPORT_WEBP && ! in_array( $ext, array( 'webp', 'svg' ), true ) ) {
		$webp = studie247_to_webp( $tmp );
		if ( $webp ) {
			@unlink( $tmp );
			$tmp  = $webp;
			$name = preg_replace( '/\.[^.]+$/', '.webp', $name );
		}
	}

	$file_array = array(
		'name'     => $name,
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file_array, $post_id, $alt_text );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		return $id;
	}

	if ( $alt_text ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt_text );
		wp_update_post( array( 'ID' => $id, 'post_title' => $alt_text ) );
	}

	return $id;
}

/**
 * Konvertér en billedfil til WebP. Returnerer ny sti eller false.
 */
function studie247_to_webp( $src_path, $quality = 82 ) {
	if ( ! is_readable( $src_path ) ) { return false; }

	// Prøv Imagick først (bedre kvalitet), derefter GD.
	if ( class_exists( 'Imagick' ) ) {
		try {
			$im = new Imagick( $src_path );
			$im->setImageFormat( 'webp' );
			$im->setImageCompressionQuality( $quality );
			$im->setOption( 'webp:method', '6' );
			$out = preg_replace( '/\.[^.]+$/', '', $src_path ) . '.webp';
			$im->writeImage( $out );
			$im->clear();
			return is_readable( $out ) ? $out : false;
		} catch ( Exception $e ) { /* fallback til GD */ }
	}

	if ( ! function_exists( 'imagewebp' ) ) { return false; }

	$info = @getimagesize( $src_path );
	if ( ! $info ) { return false; }
	$img = null;
	switch ( $info[2] ) {
		case IMAGETYPE_JPEG: $img = @imagecreatefromjpeg( $src_path ); break;
		case IMAGETYPE_PNG:
			$img = @imagecreatefrompng( $src_path );
			if ( $img ) {
				imagepalettetotruecolor( $img );
				imagealphablending( $img, true );
				imagesavealpha( $img, true );
			}
			break;
		case IMAGETYPE_GIF:  $img = @imagecreatefromgif( $src_path ); break;
		default: return false;
	}
	if ( ! $img ) { return false; }

	$out = preg_replace( '/\.[^.]+$/', '', $src_path ) . '.webp';
	$ok  = @imagewebp( $img, $out, $quality );
	imagedestroy( $img );
	return ( $ok && is_readable( $out ) ) ? $out : false;
}

/**
 * Forbered import: parse CSV/ZIP, gem rækker i transient, returnér batch-ID.
 */
function studie247_prepare_import( $upload_tmp, $upload_name, $download_images = true ) {
	$image_dir = '';
	$csv_path  = $upload_tmp;
	$name      = strtolower( (string) $upload_name );

	if ( substr( $name, -4 ) === '.zip' ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return array( 'error' => 'PHP mangler ZipArchive — upload CSV alene.' );
		}
		$zip = new ZipArchive();
		if ( true !== $zip->open( $upload_tmp ) ) {
			return array( 'error' => 'Kunne ikke åbne ZIP-filen.' );
		}
		$upload_dir = wp_upload_dir();
		$image_dir  = trailingslashit( $upload_dir['basedir'] ) . 's247-import-' . wp_generate_password( 8, false );
		if ( ! wp_mkdir_p( $image_dir ) ) {
			$zip->close();
			return array( 'error' => 'Kunne ikke oprette midlertidig mappe.' );
		}
		$zip->extractTo( $image_dir );
		$zip->close();

		$rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $image_dir, RecursiveDirectoryIterator::SKIP_DOTS ) );
		foreach ( $rii as $file ) {
			if ( $file->isFile() && strtolower( $file->getExtension() ) === 'csv' ) {
				$csv_path = $file->getPathname();
				break;
			}
		}
		if ( $csv_path === $upload_tmp ) {
			return array( 'error' => 'Ingen CSV fundet i ZIP.' );
		}
	}

	$rows = studie247_parse_csv_to_rows( $csv_path );
	if ( ! empty( $rows['error'] ) ) {
		return $rows;
	}

	$id = 's247_import_' . wp_generate_password( 12, false );
	set_transient( $id, array(
		'rows'            => $rows['rows'],
		'image_dir'       => $image_dir,
		'download_images' => $download_images,
		'webp'            => defined( 'S247_IMPORT_WEBP' ) && S247_IMPORT_WEBP,
	), 2 * HOUR_IN_SECONDS );

	return array( 'id' => $id, 'total' => count( $rows['rows'] ) );
}

/**
 * Parse CSV (uden at lave posts endnu) — returnér bare rækker som map.
 */
function studie247_parse_csv_to_rows( $path ) {
	$handle = @fopen( $path, 'r' );
	if ( ! $handle ) { return array( 'error' => 'Kunne ikke åbne CSV.' ); }

	$first  = fgets( $handle );
	$counts = array( ',' => substr_count( $first, ',' ), ';' => substr_count( $first, ';' ), "\t" => substr_count( $first, "\t" ) );
	arsort( $counts );
	reset( $counts );
	$sep = key( $counts );
	if ( ! $sep || 0 === $counts[ $sep ] ) { $sep = ','; }
	rewind( $handle );

	$alias_map = array(
		'tittel'=>'title','titel'=>'title','navn'=>'title',
		'kort beskrivelse'=>'excerpt','beskrivelse'=>'excerpt',
		'lang beskrivelse'=>'content',
		'dags pris'=>'pris_dag','dagspris'=>'pris_dag','pris pr dag'=>'pris_dag','pris pr. dag'=>'pris_dag',
		'uge pris'=>'pris_uge','ugepris'=>'pris_uge','pris pr uge'=>'pris_uge',
		'evt depositum'=>'deposit','depositum'=>'deposit',
		'tags'=>'sku','tag'=>'sku','vare-nr'=>'sku','varenr'=>'sku',
		'antal'=>'antal','stk'=>'antal','quantity'=>'antal',
		'ejer'=>'ejer',
		'evt serienummer'=>'serienummer','serienummer'=>'serienummer','serie'=>'serienummer',
		'dokumentation af stand 1'=>'state_url_1','dokumentation af stand 2'=>'state_url_2',
		'dokumentation af stand 3'=>'state_url_3','dokumentation af stand 4'=>'state_url_4',
		'stand 1'=>'state_url_1','stand 2'=>'state_url_2','stand 3'=>'state_url_3','stand 4'=>'state_url_4',
	);
	$normalize = function ( $h ) use ( $alias_map ) {
		$key = str_replace( "\xEF\xBB\xBF", '', (string) $h );
		$key = strtolower( trim( $key ) );
		$key = preg_replace( '/\s+/', ' ', $key );
		return isset( $alias_map[ $key ] ) ? $alias_map[ $key ] : $key;
	};

	$header = null;
	for ( $i = 0; $i < 3; $i++ ) {
		$c = fgetcsv( $handle, 0, $sep );
		if ( false === $c ) { break; }
		$m = array_map( $normalize, $c );
		if ( in_array( 'title', $m, true ) ) { $header = $m; break; }
	}
	if ( ! $header ) {
		fclose( $handle );
		return array( 'error' => 'Ingen title-kolonne fundet i de første 3 rækker.' );
	}

	$rows = array();
	while ( ( $row = fgetcsv( $handle, 0, $sep ) ) !== false ) {
		if ( count( $row ) === 1 && '' === trim( $row[0] ) ) { continue; }
		$data = array();
		foreach ( $header as $i => $col ) {
			$data[ $col ] = isset( $row[ $i ] ) ? trim( $row[ $i ] ) : '';
		}
		if ( empty( $data['title'] ) ) { continue; }
		$rows[] = $data;
	}
	fclose( $handle );
	return array( 'rows' => $rows );
}

/**
 * AJAX: behandl en batch af rækker.
 */
add_action( 'wp_ajax_s247_import_batch', function () {
	// Sluk for at PHP-notices/warnings/deprecations lækker ud i JSON-body og
	// korrumperer response'en (fetch -> r.json() kaster så, og frontend tror
	// det er en netværksfejl). Vi fanger alt output og smider det væk før send.
	ob_start();

	check_ajax_referer( 's247_import_batch', 'nonce' );
	if ( ! current_user_can( 'edit_posts' ) ) {
		if ( ob_get_length() ) { ob_end_clean(); }
		wp_send_json_error( array( 'message' => 'Manglende rettigheder.' ) );
	}

	$id     = sanitize_text_field( wp_unslash( $_POST['id']     ?? '' ) );
	$offset = (int) ( $_POST['offset'] ?? 0 );

	$state = get_transient( $id );
	if ( ! $state || empty( $state['rows'] ) ) {
		if ( ob_get_length() ) { ob_end_clean(); }
		wp_send_json_error( array( 'message' => 'Import-data udløb. Upload CSV igen.' ) );
	}

	@set_time_limit( 120 );
	@ini_set( 'memory_limit', '512M' );

	$rows            = $state['rows'];
	$image_dir       = $state['image_dir'];
	$download_images = $state['download_images'];
	if ( ! empty( $state['webp'] ) && ! defined( 'S247_IMPORT_WEBP' ) ) {
		define( 'S247_IMPORT_WEBP', true );
	}

	// Mindre batch når vi henter billeder — hver række kan trigge op til 5
	// downloads (hoved + 4 tilstands-billeder) à 15 sek timeout. 1 række pr.
	// request holder os trygt under nginx/proxy-timeout (typisk 60 sek).
	$batch = $download_images ? 1 : 5;

	$total   = count( $rows );
	$end     = min( $offset + $batch, $total );
	$chunk   = array_slice( $rows, $offset, $batch, true );

	$counts  = array( 'created' => 0, 'updated' => 0, 'skipped' => 0 );
	$msgs    = array();

	foreach ( $chunk as $idx => $data ) {
		$row_num = $idx + 2; // +2 fordi vi hoppede over header
		try {
			$outcome = studie247_import_single_row( $data, $download_images, $image_dir );
			$counts[ $outcome['status'] ]++;
			foreach ( (array) $outcome['messages'] as $m ) {
				$msgs[] = 'R' . $row_num . ' · ' . $m;
			}
		} catch ( Throwable $e ) {
			$counts['skipped']++;
			$msgs[] = 'R' . $row_num . ' crashede — ' . $e->getMessage();
		}
	}

	$next_offset = $end;
	$done        = $next_offset >= $total;
	if ( $done && ! empty( $image_dir ) && is_dir( $image_dir ) ) {
		studie247_rrmdir( $image_dir );
		delete_transient( $id );
	}

	// Smid stille-output (notices, whitespace, BOM) væk før vi skriver JSON.
	if ( ob_get_length() ) { ob_end_clean(); }

	wp_send_json_success( array(
		'next_offset' => $next_offset,
		'total'       => $total,
		'batch_size'  => count( $chunk ),
		'done'        => $done,
		'created'     => $counts['created'],
		'updated'     => $counts['updated'],
		'skipped'     => $counts['skipped'],
		'messages'    => $msgs,
	) );
} );

/**
 * Behandl én række — genbruger logikken fra den oprindelige importer.
 */
function studie247_import_single_row( $data, $download_images, $image_dir ) {
	$messages = array();
	$status   = 'skipped';

	$title = isset( $data['title'] ) ? trim( $data['title'] ) : '';
	if ( ! $title ) {
		return array( 'status' => 'skipped', 'messages' => array( 'Ingen title.' ) );
	}

	// Find eksisterende: SKU → title.
	$existing_id = 0;
	if ( ! empty( $data['sku'] ) ) {
		$q = get_posts( array(
			'post_type'      => 'udlejning_item',
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'posts_per_page' => 1,
			'meta_query'     => array( array( 'key' => '_s247_sku', 'value' => $data['sku'], 'compare' => '=' ) ),
			'fields'         => 'ids',
		) );
		if ( ! empty( $q ) ) { $existing_id = (int) $q[0]; }
	}
	if ( ! $existing_id ) {
		$q = get_posts( array(
			'post_type'      => 'udlejning_item',
			'post_status'    => array( 'publish', 'draft', 'pending' ),
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( ! empty( $q ) ) { $existing_id = (int) $q[0]; }
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
		return array( 'status' => 'skipped', 'messages' => array( 'DB-fejl: ' . $post_id->get_error_message() ) );
	}

	// Auto-generér unik S247-ID hvis ny post.
	studie247_ensure_product_uid( $post_id );

	// Meta.
	foreach ( array( 'pris_dag', 'pris_uge', 'deposit', 'sku', 'ejer', 'serienummer' ) as $m ) {
		if ( isset( $data[ $m ] ) && $data[ $m ] !== '' ) {
			update_post_meta( $post_id, '_s247_' . $m, sanitize_text_field( $data[ $m ] ) );
		}
	}
	if ( isset( $data['antal'] ) && $data['antal'] !== '' ) {
		$qty = max( 0, (int) $data['antal'] );
		update_post_meta( $post_id, '_s247_antal', $qty );
		update_post_meta( $post_id, '_s247_in_stock', $qty > 0 ? '1' : '0' );
	} elseif ( isset( $data['in_stock'] ) ) {
		$val = strtolower( trim( $data['in_stock'] ) );
		$in  = in_array( $val, array( '1', 'true', 'ja', 'yes', 'y', 'på lager' ), true ) ? '1' : '0';
		update_post_meta( $post_id, '_s247_in_stock', $in );
	}

	// Kategori.
	if ( ! empty( $data['kategori'] ) ) {
		$terms = array_map( 'trim', explode( '|', $data['kategori'] ) );
		$ids   = array();
		foreach ( $terms as $tn ) {
			if ( '' === $tn ) { continue; }
			$t = term_exists( $tn, 'udlejning_kategori' );
			if ( ! $t ) { $t = wp_insert_term( $tn, 'udlejning_kategori' ); }
			if ( ! is_wp_error( $t ) ) {
				$ids[] = (int) ( is_array( $t ) ? $t['term_id'] : $t );
			}
		}
		if ( ! empty( $ids ) ) { wp_set_object_terms( $post_id, $ids, 'udlejning_kategori', false ); }
	}

	// Billeder.
	if ( $download_images ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attach_id = 0;
		if ( ! empty( $data['image_file'] ) && $image_dir ) {
			$local = studie247_find_image_in_dir( $image_dir, $data['image_file'] );
			if ( $local ) { $attach_id = studie247_sideload_local( $local, $post_id, $title ); }
		}
		if ( ! $attach_id && ! empty( $data['image_url'] ) && filter_var( $data['image_url'], FILTER_VALIDATE_URL ) ) {
			$attach_id = studie247_sideload_url( $data['image_url'], $post_id, $title );
		}
		if ( $attach_id && ! is_wp_error( $attach_id ) ) {
			set_post_thumbnail( $post_id, $attach_id );
			$messages[] = 'hoved-billede OK';
		} elseif ( is_wp_error( $attach_id ) ) {
			$messages[] = 'hoved-billede fejlede: ' . $attach_id->get_error_message();
		}

		$state_ids = array();
		for ( $si = 1; $si <= 4; $si++ ) {
			$state_aid = 0;
			$alt = $title . ' — tilstand ' . $si;
			$fk = 'state_image_' . $si;
			$uk = 'state_url_' . $si;
			if ( ! empty( $data[ $fk ] ) && $image_dir ) {
				$local = studie247_find_image_in_dir( $image_dir, $data[ $fk ] );
				if ( $local ) { $state_aid = studie247_sideload_local( $local, $post_id, $alt ); }
			}
			if ( ! $state_aid && ! empty( $data[ $uk ] ) && filter_var( $data[ $uk ], FILTER_VALIDATE_URL ) ) {
				$state_aid = studie247_sideload_url( $data[ $uk ], $post_id, $alt );
			}
			if ( $state_aid && ! is_wp_error( $state_aid ) ) {
				$state_ids[] = (int) $state_aid;
			} elseif ( is_wp_error( $state_aid ) ) {
				$messages[] = 'stand ' . $si . ' fejlede: ' . $state_aid->get_error_message();
			}
		}
		if ( ! empty( $state_ids ) ) {
			$existing_state = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $post_id, '_s247_state_images', true ) ) ) );
			$merged         = array_values( array_unique( array_merge( $existing_state, $state_ids ) ) );
			update_post_meta( $post_id, '_s247_state_images', implode( ',', $merged ) );
			$messages[] = count( $state_ids ) . ' tilstands-billeder importeret';
		}
	}

	$status = $existing_id ? 'updated' : 'created';
	array_unshift( $messages, '"' . $title . '" ' . ( 'updated' === $status ? 'opdateret' : 'oprettet' ) );
	return array( 'status' => $status, 'messages' => $messages );
}
