<?php
/**
 * Om-side — bruges automatisk når side-slug er "om".
 *
 * @package Studie247
 */

get_header();

$intro = get_theme_mod( 's247_om_intro' );
?>

<section class="section">
	<div class="wrap wrap--tight">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Om Studie 247', 'studie247' ); ?></span>
			<h1 class="section-head__title">
				<?php esc_html_e( 'Simpelt.', 'studie247' ); ?> <em><?php esc_html_e( 'Professionelt.', 'studie247' ); ?></em> <?php esc_html_e( 'Menneskeligt.', 'studie247' ); ?>
			</h1>
			<?php if ( $intro ) : ?>
				<p class="section-head__lead"><?php echo wp_kses_post( $intro ); ?></p>
			<?php else : ?>
				<p class="section-head__lead">
					<?php esc_html_e( 'Vi holder det enkelt — fordi klarhed skaber tillid. Vores udtryk er skarpt, roligt og varmt på samme tid. Målet er, at man med det samme føler sig i trygge hænder.', 'studie247' ); ?>
				</p>
			<?php endif; ?>
		</header>

		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="page-content"><?php the_content(); ?></div>
			<?php endif; ?>
		<?php endwhile; ?>
	</div>
</section>

<?php
// Manifest-block — 3 principper i stor italic-serif.
$mani_eyebrow = get_theme_mod( 's247_om_manifest_eyebrow', 'Vores principper' );
$mani_title   = get_theme_mod( 's247_om_manifest_title',   'Det vi holder fast i' );
$principles   = array_filter( array(
	get_theme_mod( 's247_om_manifest_1', 'Vi siger nej, når vi mener nej.' ),
	get_theme_mod( 's247_om_manifest_2', 'Et minut for meget er et minut for dårligt.' ),
	get_theme_mod( 's247_om_manifest_3', 'Hvis det ikke er sjovt, så er det forkert.' ),
) );
?>
<?php if ( ! empty( $principles ) ) : ?>
<section class="section om-manifest">
	<div class="wrap wrap--wide">
		<header class="om-manifest__head">
			<?php if ( $mani_eyebrow ) : ?>
				<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php echo esc_html( $mani_eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( $mani_title ) : ?>
				<h2 class="om-manifest__title"><?php echo esc_html( $mani_title ); ?></h2>
			<?php endif; ?>
		</header>
		<ol class="om-manifest__list">
			<?php foreach ( $principles as $idx => $p ) : ?>
				<li class="om-manifest__item">
					<span class="om-manifest__num"><?php printf( '%02d', $idx + 1 ); ?></span>
					<p class="om-manifest__text"><?php echo wp_kses_post( $p ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<?php endif; ?>

<?php
// Stats-strip — 4 nøgletal.
$om_stats = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$num   = get_theme_mod( "s247_om_stat{$i}_num", '' );
	$unit  = get_theme_mod( "s247_om_stat{$i}_unit", '' );
	$label = get_theme_mod( "s247_om_stat{$i}_label", '' );
	if ( $num || $label ) {
		$om_stats[] = array( 'num' => $num, 'unit' => $unit, 'label' => $label );
	}
}
?>
<?php if ( ! empty( $om_stats ) ) : ?>
<section class="studiet-stats om-stats">
	<div class="wrap wrap--wide">
		<div class="studiet-stats__grid">
			<?php foreach ( $om_stats as $s ) : ?>
				<div class="studiet-stats__item">
					<?php if ( $s['num'] ) : ?>
						<span class="studiet-stats__num"><?php echo esc_html( $s['num'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['unit'] ) : ?>
						<span class="studiet-stats__unit"><?php echo esc_html( $s['unit'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['label'] ) : ?>
						<span class="studiet-stats__label"><?php echo esc_html( $s['label'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/section', 'team' ); ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
