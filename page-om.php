<?php
/**
 * Om-side — bruges automatisk når side-slug er "om".
 *
 * @package Studie247
 */

get_header();

$intro = get_theme_mod( 's247_om_intro' );

$hero_video   = get_theme_mod( 's247_om_hero_video', '' );
$hero_poster  = get_theme_mod( 's247_om_hero_poster', '' );
$hero_eyebrow = get_theme_mod( 's247_om_hero_eyebrow', 'Om Studie 247' );
$hero_title_a = get_theme_mod( 's247_om_hero_title_a', 'Bag kameraet er' );
$hero_title_b = get_theme_mod( 's247_om_hero_title_b', 'rigtige mennesker' );
$hero_lead    = get_theme_mod( 's247_om_hero_lead',    'Vi er et lille hold med store ambitioner — og en fælles drøm om at gøre dit indhold bedre.' );
?>

<section class="studiet-hero om-hero">
	<div class="studiet-hero__media" aria-hidden="true">
		<?php if ( $hero_video ) : ?>
			<video autoplay muted loop playsinline poster="<?php echo esc_url( $hero_poster ); ?>">
				<source src="<?php echo esc_url( $hero_video ); ?>" type="video/mp4">
			</video>
		<?php elseif ( $hero_poster ) : ?>
			<img src="<?php echo esc_url( $hero_poster ); ?>" alt="" loading="eager" fetchpriority="high">
		<?php else : ?>
			<div class="studiet-hero__placeholder"></div>
		<?php endif; ?>
		<div class="studiet-hero__overlay"></div>
	</div>
	<div class="wrap wrap--wide studiet-hero__content">
		<?php if ( $hero_eyebrow ) : ?>
			<span class="eyebrow eyebrow--on-dark"><?php echo esc_html( $hero_eyebrow ); ?></span>
		<?php endif; ?>
		<h1 class="studiet-hero__title">
			<?php echo esc_html( $hero_title_a ); ?> <em><?php echo esc_html( $hero_title_b ); ?></em>
		</h1>
		<?php if ( $hero_lead ) : ?>
			<p class="studiet-hero__lead"><?php echo wp_kses_post( $hero_lead ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="section">
	<div class="wrap wrap--tight">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Om Studie 247', 'studie247' ); ?></span>
			<h2 class="section-head__title">
				<?php esc_html_e( 'Simpelt.', 'studie247' ); ?> <em><?php esc_html_e( 'Professionelt.', 'studie247' ); ?></em> <?php esc_html_e( 'Menneskeligt.', 'studie247' ); ?>
			</h2>
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
// Stort editorial-statement (mørkt fuld-breddebanner).
$st_eyebrow = get_theme_mod( 's247_om_statement_eyebrow', 'Det vi tror på' );
$st_a       = get_theme_mod( 's247_om_statement_text_a',  'Vi bliver der lidt længere.' );
$st_em      = get_theme_mod( 's247_om_statement_text_em', 'Vi tager en ekstra take.' );
$st_b       = get_theme_mod( 's247_om_statement_text_b',  'Det er ikke en service — det er en arbejdsmoral.' );
?>
<?php if ( $st_a || $st_em || $st_b ) : ?>
<section class="om-statement">
	<div class="wrap wrap--wide">
		<div class="om-statement__inner">
			<?php if ( $st_eyebrow ) : ?>
				<span class="eyebrow eyebrow--on-dark eyebrow--no-line"><?php echo esc_html( $st_eyebrow ); ?></span>
			<?php endif; ?>
			<p class="om-statement__text">
				<?php if ( $st_a )  : ?><span class="om-statement__line"><?php echo wp_kses_post( $st_a ); ?></span><?php endif; ?>
				<?php if ( $st_em ) : ?><span class="om-statement__line om-statement__line--em"><em><?php echo wp_kses_post( $st_em ); ?></em></span><?php endif; ?>
				<?php if ( $st_b )  : ?><span class="om-statement__line"><?php echo wp_kses_post( $st_b ); ?></span><?php endif; ?>
			</p>
		</div>
	</div>
</section>
<?php endif; ?>

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
// Foto-collage — 5 billeder i asymmetrisk grid.
$collage_eyebrow = get_theme_mod( 's247_om_collage_eyebrow', 'Fra studiet' );
$collage = array_filter( array(
	get_theme_mod( 's247_om_collage_1', '' ),
	get_theme_mod( 's247_om_collage_2', '' ),
	get_theme_mod( 's247_om_collage_3', '' ),
	get_theme_mod( 's247_om_collage_4', '' ),
	get_theme_mod( 's247_om_collage_5', '' ),
) );
?>
<?php if ( count( $collage ) >= 3 ) : ?>
<section class="om-collage">
	<div class="wrap wrap--wide">
		<?php if ( $collage_eyebrow ) : ?>
			<span class="eyebrow eyebrow--accent eyebrow--no-line om-collage__eyebrow"><?php echo esc_html( $collage_eyebrow ); ?></span>
		<?php endif; ?>
		<div class="om-collage__grid">
			<?php foreach ( $collage as $idx => $url ) : ?>
				<figure class="om-collage__item om-collage__item--<?php echo (int) ( $idx + 1 ); ?>">
					<img src="<?php echo esc_url( $url ); ?>" alt="" loading="lazy">
				</figure>
			<?php endforeach; ?>
		</div>
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
