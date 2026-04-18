<?php
/**
 * Template Name: Om
 *
 * @package Studie247
 */

get_header();

$intro = get_theme_mod( 's247_om_intro' );

$team = array();
for ( $i = 1; $i <= 8; $i++ ) {
	$name = get_theme_mod( "s247_team{$i}_name" );
	if ( ! $name ) {
		continue;
	}
	$team[] = array(
		'name'        => $name,
		'role'        => get_theme_mod( "s247_team{$i}_role" ),
		'image'       => get_theme_mod( "s247_team{$i}_image" ),
		'modal_image' => get_theme_mod( "s247_team{$i}_modal_image" ),
		'modal_text'  => get_theme_mod( "s247_team{$i}_modal_text" ),
	);
}
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

<?php if ( ! empty( $team ) ) : ?>
<section class="section team-section">
	<div class="wrap wrap--wide">
		<ul class="team-grid">
			<?php foreach ( $team as $idx => $member ) : ?>
				<li>
					<button
						type="button"
						class="team-card"
						data-team-open="<?php echo esc_attr( $idx ); ?>"
						aria-haspopup="dialog"
					>
						<span class="team-card__media">
							<?php if ( $member['image'] ) : ?>
								<img src="<?php echo esc_url( $member['image'] ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy" decoding="async">
							<?php else : ?>
								<span class="team-card__placeholder" aria-hidden="true"><?php echo esc_html( mb_substr( $member['name'], 0, 1 ) ); ?></span>
							<?php endif; ?>
						</span>
						<span class="team-card__meta">
							<span class="team-card__name"><?php echo esc_html( $member['name'] ); ?></span>
							<?php if ( $member['role'] ) : ?>
								<span class="team-card__role"><?php echo esc_html( $member['role'] ); ?></span>
							<?php endif; ?>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php foreach ( $team as $idx => $member ) : ?>
	<div class="team-modal" data-team-modal="<?php echo esc_attr( $idx ); ?>" role="dialog" aria-modal="true" aria-labelledby="team-modal-<?php echo esc_attr( $idx ); ?>-title" hidden>
		<div class="team-modal__backdrop" data-team-close></div>
		<div class="team-modal__dialog" role="document">
			<button type="button" class="team-modal__close" data-team-close aria-label="<?php esc_attr_e( 'Luk', 'studie247' ); ?>">
				<?php echo studie247_icon( 'close', 24 ); ?>
			</button>
			<div class="team-modal__media">
				<?php $modal_img = $member['modal_image'] ? $member['modal_image'] : $member['image']; ?>
				<?php if ( $modal_img ) : ?>
					<img src="<?php echo esc_url( $modal_img ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy" decoding="async">
				<?php endif; ?>
			</div>
			<div class="team-modal__body">
				<h2 class="team-modal__name" id="team-modal-<?php echo esc_attr( $idx ); ?>-title"><?php echo esc_html( $member['name'] ); ?></h2>
				<?php if ( $member['role'] ) : ?>
					<p class="team-modal__role"><?php echo esc_html( $member['role'] ); ?></p>
				<?php endif; ?>
				<?php if ( $member['modal_text'] ) : ?>
					<div class="team-modal__text"><?php echo wp_kses_post( wpautop( $member['modal_text'] ) ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>
<?php endif; ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
