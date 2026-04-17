<?php
/**
 * Process — nummereret, editorial, hairline-list.
 */
?>
<section class="section section--dark" aria-labelledby="process-title">
	<div class="wrap">
		<header class="section-head" data-reveal>
			<div class="section-head__meta">
				<span class="section-num">03</span>
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Proces', 'studie247' ); ?></span>
			</div>
			<h2 id="process-title" class="section-head__title">
				<?php esc_html_e( 'Fra', 'studie247' ); ?> <em><?php esc_html_e( 'idé', 'studie247' ); ?></em>
				<?php esc_html_e( 'til', 'studie247' ); ?> <em><?php esc_html_e( 'udgivelse', 'studie247' ); ?></em>
			</h2>
			<p class="section-head__lead">
				<?php echo wp_kses_post( __( 'En rolig og rutineret proces, hvor vi tager hånd om detaljerne — <em>så du kan fokusere på budskabet.</em>', 'studie247' ) ); ?>
			</p>
		</header>

		<div class="process">
			<?php
			$steps = array(
				array(
					'title' => __( 'Vi tager en snak', 'studie247' ),
					'desc'  => __( 'Du fortæller om projektet — vi hjælper med at ramme det rette format, længde og look.', 'studie247' ),
				),
				array(
					'title' => __( 'Vi producerer sammen', 'studie247' ),
					'desc'  => __( 'Du møder op, og vi sørger for udstyr, opsætning og instruktion. Fokus på dit budskab.', 'studie247' ),
				),
				array(
					'title' => __( 'Vi klipper og finisher', 'studie247' ),
					'desc'  => __( 'Klip, lyd, farveretouche og grafik — leveret fleksibelt og til tiden.', 'studie247' ),
				),
				array(
					'title' => __( 'Klar til udgivelse', 'studie247' ),
					'desc'  => __( 'Du får filer i alle relevante formater og er klar til at udgive — 247.', 'studie247' ),
				),
			);
			$i = 0;
			foreach ( $steps as $step ) :
				$i++;
				?>
				<div class="process__step" data-reveal style="--reveal-delay: <?php echo esc_attr( 60 * $i ); ?>ms;">
					<span class="process__num"><?php printf( '%02d', $i ); ?></span>
					<div class="process__body">
						<h3 class="process__title"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="process__desc"><?php echo esc_html( $step['desc'] ); ?></p>
					</div>
					<span class="process__arrow"><?php echo studie247_icon( 'arrow-right', 24 ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<style>
.section--dark .process__step { border-color: var(--s247-bone-20); }
.section--dark .process__step:first-child { border-top-color: var(--s247-bone-20); }
.section--dark .process__desc { color: var(--s247-bone-60); }
.section--dark .process__title { color: var(--color-ink-on-dark); }
.section--dark .process__step:hover .process__title { color: var(--color-accent); }
</style>
