<?php
/**
 * FAQ — editorial two-col layout + FAQPage schema.
 * Tekster styres via Customizer → FAQ-sektion (forside).
 */
$faqs = array();
for ( $n = 1; $n <= 8; $n++ ) {
	$q = trim( (string) get_theme_mod( "s247_faq_q{$n}", '' ) );
	$a = trim( (string) get_theme_mod( "s247_faq_a{$n}", '' ) );
	if ( $q && $a ) {
		$faqs[] = array( 'q' => $q, 'a' => $a );
	}
}
?>
<section class="section" aria-labelledby="faq-title">
	<div class="wrap">
		<div class="faq-wrap">
			<header class="section-head faq__head" data-reveal>
				<div class="section-head__meta">
					<span class="section-num">06</span>
					<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php echo esc_html( get_theme_mod( 's247_faq_eyebrow', __( 'FAQ', 'studie247' ) ) ); ?></span>
				</div>
				<h2 id="faq-title" class="section-head__title">
					<?php echo esc_html( get_theme_mod( 's247_faq_title_a', __( 'Ofte stillede', 'studie247' ) ) ); ?> <em><?php echo esc_html( get_theme_mod( 's247_faq_title_b', __( 'spørgsmål', 'studie247' ) ) ); ?></em>
				</h2>
				<p class="section-head__lead">
					<?php echo wp_kses_post( get_theme_mod( 's247_faq_lead', __( 'Stilles ofte nok til at vi samlede dem her. Mangler du svar? <em>Skriv til os.</em>', 'studie247' ) ) ); ?>
				</p>
			</header>

			<div class="accordion" data-reveal style="--reveal-delay: 120ms;">
				<?php foreach ( $faqs as $faq ) : ?>
					<details class="accordion__item">
						<summary class="accordion__trigger"><?php echo esc_html( $faq['q'] ); ?></summary>
						<div class="accordion__panel"><?php echo wp_kses_post( $faq['a'] ); ?></div>
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
