<?php
/**
 * Enkelt service (fx "Podcast", "SoMe-videoer", "Fotoshoot", +++).
 *
 * @package Studie247
 */

get_header();
the_post();

$tagline   = get_post_meta( get_the_ID(), '_s247_tagline', true );
$icon      = get_post_meta( get_the_ID(), '_s247_icon', true );
$startpris = get_post_meta( get_the_ID(), '_s247_startpris', true );
$included  = get_post_meta( get_the_ID(), '_s247_included', true );
$included_items = array_filter( array_map( 'trim', preg_split( "/\r\n|\r|\n/", $included ?: '' ) ) );
?>

<section class="hero" style="min-height: 520px;">
	<div class="hero__media" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 's247-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
		<?php else : ?>
			<div style="width:100%;height:100%;background:linear-gradient(135deg,#282828,#3a2a26);"></div>
		<?php endif; ?>
	</div>
	<div class="wrap wrap--wide">
		<div class="hero__inner">
			<nav class="breadcrumb" style="color: rgba(244,233,221,0.7);" aria-label="<?php esc_attr_e( 'Brødkrumme', 'studie247' ); ?>">
				<ol>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:inherit;"><?php esc_html_e( 'Forside', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" style="color:inherit;"><?php esc_html_e( 'Services', 'studie247' ); ?></a></li>
					<li><?php the_title(); ?></li>
				</ol>
			</nav>
			<?php if ( $icon ) : ?>
				<div class="card__icon" style="background: rgba(158,43,37,0.2); color: var(--s247-bone);">
					<?php echo studie247_icon( $icon, 32 ); ?>
				</div>
			<?php endif; ?>
			<h1 class="hero__title" style="font-size: clamp(2.25rem,1.7rem+3vw,4rem);"><?php the_title(); ?></h1>
			<?php if ( $tagline ) : ?>
				<p class="hero__lead"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
			<div class="hero__actions">
				<?php studie247_button( __( 'Book denne service', 'studie247' ), home_url( '/book/?service=' . get_post_field( 'post_name' ) ), 'primary', 'btn--lg' ); ?>
				<?php if ( $startpris ) : ?>
					<span style="align-self:center; color: var(--color-accent); font-family: var(--font-serif); font-style: italic; font-size: var(--fs-lg);">
						<?php echo esc_html( $startpris ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap" style="display:grid;gap:var(--sp-12);grid-template-columns: 1fr;">
		<div style="display:grid; gap: var(--sp-12); grid-template-columns: minmax(0,1fr);">

			<div class="page-content"><?php the_content(); ?></div>

			<?php if ( ! empty( $included_items ) ) : ?>
				<aside class="card" style="padding: var(--sp-8);">
					<h2 class="card__title" style="margin-bottom: var(--sp-4);">
						<?php esc_html_e( 'Det er', 'studie247' ); ?> <em style="font-family:var(--font-serif);font-style:italic;color:var(--color-accent);font-weight:400;"><?php esc_html_e( 'inkluderet', 'studie247' ); ?></em>
					</h2>
					<ul style="list-style:none; padding:0; display: grid; gap: var(--sp-3);">
						<?php foreach ( $included_items as $item ) : ?>
							<li style="display:flex; align-items:flex-start; gap: var(--sp-3);">
								<span style="color: var(--color-accent); margin-top: 0.25em;"><?php echo studie247_icon( 'check', 18 ); ?></span>
								<span><?php echo esc_html( $item ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
