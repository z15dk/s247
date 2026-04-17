<?php
/**
 * FAQ — statisk seed; kan senere drives af CPT eller customizer.
 * SEO-goldmine: tilføj FAQPage schema når indhold er på plads.
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
		'a' => __( 'Ja. Vi udlejer også studiet "råt" til erfarne produktionsteams. Se siden Studiet for detaljer.', 'studie247' ),
	),
	array(
		'q' => __( 'Kan I hjælpe med manuskript og idéudvikling?', 'studie247' ),
		'a' => __( 'Ja. Vi har producere og tekstforfattere, som kan hjælpe fra idé til færdigt script.', 'studie247' ),
	),
);
?>
<section class="section" aria-labelledby="faq-title">
	<div class="wrap faq">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'FAQ', 'studie247' ); ?></span>
			<h2 id="faq-title" class="section-head__title">
				<?php esc_html_e( 'Ofte stillede', 'studie247' ); ?> <em><?php esc_html_e( 'spørgsmål', 'studie247' ); ?></em>
			</h2>
		</header>

		<div class="accordion">
			<?php foreach ( $faqs as $faq ) : ?>
				<details class="accordion__item">
					<summary class="accordion__trigger"><?php echo esc_html( $faq['q'] ); ?></summary>
					<div class="accordion__panel"><?php echo esc_html( $faq['a'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
// FAQPage schema.
$faq_schema = array(
	'@context'    => 'https://schema.org',
	'@type'       => 'FAQPage',
	'mainEntity'  => array_map( function ( $f ) {
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
