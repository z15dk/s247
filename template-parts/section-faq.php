<?php
/**
 * FAQ — editorial two-col layout + FAQPage schema.
 */
$faqs = array(
	array(
		'q' => __( 'Hvad er inkluderet i en booking?', 'studie247' ),
		'a' => __( 'Adgang til studiet med alt fast udstyr, rigelig opsætningstid og en producer der hjælper dig i gang. Specifikt udstyr kan tillægges.', 'studie247' ),
	),
	array(
		'q' => __( 'Hvor hurtigt får jeg det færdige materiale?', 'studie247' ),
		'a' => __( 'Typisk inden for 5-10 arbejdsdage, afhængigt af omfang. Hastelevering er muligt.', 'studie247' ),
	),
	array(
		'q' => __( 'Kan jeg leje studiet uden produktion?', 'studie247' ),
		'a' => __( 'Ja. Vi udlejer også studiet råt til erfarne produktionsteams. Se siden Studiet for detaljer.', 'studie247' ),
	),
	array(
		'q' => __( 'Kan I hjælpe med manuskript og idéudvikling?', 'studie247' ),
		'a' => __( 'Ja. Vi har producere og tekstforfattere, som kan hjælpe fra idé til færdigt script.', 'studie247' ),
	),
	array(
		'q' => __( 'Hvor ligger studiet?', 'studie247' ),
		'a' => __( 'Aarhus — præcis adresse får du med booking-bekræftelsen. Der er parkering og god offentlig transport.', 'studie247' ),
	),
);
?>
<section class="section" aria-labelledby="faq-title">
	<div class="wrap">
		<div class="faq-wrap">
			<header class="section-head faq__head" data-reveal>
				<div class="section-head__meta">
					<span class="section-num">06</span>
					<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'FAQ', 'studie247' ); ?></span>
				</div>
				<h2 id="faq-title" class="section-head__title">
					<?php esc_html_e( 'Ofte stillede', 'studie247' ); ?> <em><?php esc_html_e( 'spørgsmål', 'studie247' ); ?></em>
				</h2>
				<p class="section-head__lead">
					<?php echo wp_kses_post( __( 'Stilles ofte nok til at vi samlede dem her. Mangler du svar? <em>Skriv til os.</em>', 'studie247' ) ); ?>
				</p>
			</header>

			<div class="accordion" data-reveal style="--reveal-delay: 120ms;">
				<?php foreach ( $faqs as $faq ) : ?>
					<details class="accordion__item">
						<summary class="accordion__trigger"><?php echo esc_html( $faq['q'] ); ?></summary>
						<div class="accordion__panel"><?php echo esc_html( $faq['a'] ); ?></div>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<?php
$faq_schema = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array_map( function ( $f ) {
		return array(
			'@type'          => 'Question',
			'name'           => $f['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $f['a'],
			),
		);
	}, $faqs ),
);
?>
<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
