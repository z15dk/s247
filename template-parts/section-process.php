<?php
/**
 * Process — fra idé til udgivelse.
 */
?>
<section class="section" aria-labelledby="process-title">
	<div class="wrap">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Sådan foregår det', 'studie247' ); ?></span>
			<h2 id="process-title" class="section-head__title">
				<?php esc_html_e( 'Fra', 'studie247' ); ?> <em><?php esc_html_e( 'idé', 'studie247' ); ?></em>
				<?php esc_html_e( 'til', 'studie247' ); ?> <em><?php esc_html_e( 'udgivelse', 'studie247' ); ?></em>
			</h2>
		</header>

		<div class="process">
			<?php
			$steps = array(
				array(
					'title' => __( 'Vi tager en snak', 'studie247' ),
					'body'  => __( 'Du fortæller om projektet, og vi hjælper med at ramme det rette format, længde og look.', 'studie247' ),
				),
				array(
					'title' => __( 'Vi producerer sammen', 'studie247' ),
					'body'  => __( 'Du møder op — vi sørger for udstyr, opsætning og instruktion, så du kan fokusere på dit budskab.', 'studie247' ),
				),
				array(
					'title' => __( 'Vi klipper og finish', 'studie247' ),
					'body'  => __( 'Klip, lyd, farveretouche og grafik. Vi leverer færdigt, fleksibelt og til tiden.', 'studie247' ),
				),
				array(
					'title' => __( 'Klar til udgivelse', 'studie247' ),
					'body'  => __( 'Du får filer i alle relevante formater og er klar til at udgive — 247.', 'studie247' ),
				),
			);
			foreach ( $steps as $step ) : ?>
				<div class="process__step">
					<h3 class="process__title"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="process__body"><?php echo esc_html( $step['body'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
