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
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php echo esc_html( get_theme_mod( 's247_process_eyebrow', __( 'Proces', 'studie247' ) ) ); ?></span>
			</div>
			<h2 id="process-title" class="section-head__title">
				<?php echo esc_html( get_theme_mod( 's247_process_title_a', __( 'Fra', 'studie247' ) ) ); ?> <em><?php echo esc_html( get_theme_mod( 's247_process_title_b', __( 'idé', 'studie247' ) ) ); ?></em>
				<?php echo esc_html( get_theme_mod( 's247_process_title_c', __( 'til', 'studie247' ) ) ); ?> <em><?php echo esc_html( get_theme_mod( 's247_process_title_d', __( 'udgivelse', 'studie247' ) ) ); ?></em>
			</h2>
			<p class="section-head__lead">
				<?php echo wp_kses_post( get_theme_mod( 's247_process_lead', __( 'En rolig og rutineret proces, hvor vi tager hånd om detaljerne — <em>så du kan fokusere på budskabet.</em>', 'studie247' ) ) ); ?>
			</p>
		</header>

		<div class="process">
			<?php
			$steps = array();
			for ( $n = 1; $n <= 4; $n++ ) {
				$steps[] = array(
					'title' => get_theme_mod( "s247_process_s{$n}_title", '' ),
					'desc'  => get_theme_mod( "s247_process_s{$n}_desc", '' ),
				);
			}
			$i = 0;
			foreach ( $steps as $step ) :
				$i++;
				if ( ! $step['title'] && ! $step['desc'] ) { continue; }
				?>
				<div class="process__step" data-reveal style="--reveal-delay: <?php echo esc_attr( 60 * $i ); ?>ms;">
					<span class="process__num"><?php printf( '%02d', $i ); ?></span>
					<div class="process__body">
						<h3 class="process__title"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="process__desc"><?php echo wp_kses_post( $step['desc'] ); ?></p>
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
