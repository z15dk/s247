<?php
/**
 * Template Name: Studiet
 *
 * @package Studie247
 */

get_header();
?>

<section class="hero" style="min-height: 520px;">
	<div class="hero__media" aria-hidden="true">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 's247-hero', array( 'loading' => 'eager' ) ); ?>
		<?php else : ?>
			<div style="width:100%;height:100%;background:linear-gradient(135deg,#282828,#3a2a26);"></div>
		<?php endif; ?>
	</div>
	<div class="wrap wrap--wide">
		<div class="hero__inner">
			<span class="eyebrow hero__eyebrow"><?php esc_html_e( 'Studiet', 'studie247' ); ?></span>
			<h1 class="hero__title">
				<?php esc_html_e( 'Ét rum.', 'studie247' ); ?> <em><?php esc_html_e( 'Mange muligheder.', 'studie247' ); ?></em>
			</h1>
			<p class="hero__lead"><?php esc_html_e( '200 m² fuldt udstyret produktionsstudie i hjertet af byen. Klar når du er.', 'studie247' ); ?></p>
		</div>
	</div>
</section>

<?php while ( have_posts() ) : the_post(); ?>
	<section class="section">
		<div class="wrap wrap--tight page-content"><?php the_content(); ?></div>
	</section>
<?php endwhile; ?>

<section class="section" id="raalej" style="background: var(--color-surface);">
	<div class="wrap">
		<header class="section-head">
			<span class="eyebrow"><?php esc_html_e( 'Råleje', 'studie247' ); ?></span>
			<h2 class="section-head__title">
				<?php esc_html_e( 'Lej studiet', 'studie247' ); ?> <em><?php esc_html_e( 'råt', 'studie247' ); ?></em>
			</h2>
			<p class="section-head__lead">
				<?php esc_html_e( 'Har du dit eget team og udstyr? Lej rummet med de fastmonterede goodies — cyklorama, lys, lyd og strøm klar.', 'studie247' ); ?>
			</p>
		</header>
		<div class="grid grid--3">
			<div class="card" style="padding: var(--sp-8);">
				<h3 class="card__title"><?php esc_html_e( 'Hvad er med', 'studie247' ); ?></h3>
				<ul style="list-style:none;padding:0;display:grid;gap:var(--sp-2);">
					<li><?php echo studie247_icon( 'check', 16 ); ?> <?php esc_html_e( 'Cyklorama (hvid)', 'studie247' ); ?></li>
					<li><?php echo studie247_icon( 'check', 16 ); ?> <?php esc_html_e( 'Grundlys', 'studie247' ); ?></li>
					<li><?php echo studie247_icon( 'check', 16 ); ?> <?php esc_html_e( '3-faset strøm', 'studie247' ); ?></li>
					<li><?php echo studie247_icon( 'check', 16 ); ?> <?php esc_html_e( 'Make-up room', 'studie247' ); ?></li>
					<li><?php echo studie247_icon( 'check', 16 ); ?> <?php esc_html_e( 'Køkken / lounge', 'studie247' ); ?></li>
					<li><?php echo studie247_icon( 'check', 16 ); ?> <?php esc_html_e( 'Wi-Fi (1 Gbit)', 'studie247' ); ?></li>
				</ul>
			</div>
			<div class="card" style="padding: var(--sp-8);">
				<h3 class="card__title"><?php esc_html_e( 'Tillæg', 'studie247' ); ?></h3>
				<p class="card__body"><?php esc_html_e( 'Kamera, mikrofoner, grip, storage, make-up artist og producer kan tilføjes. Se udlejningen for detaljer.', 'studie247' ); ?></p>
				<a class="card__cta" href="<?php echo esc_url( home_url( '/udlejning/' ) ); ?>">
					<?php esc_html_e( 'Udlejning', 'studie247' ); ?>
					<?php echo studie247_icon( 'arrow-right', 16 ); ?>
				</a>
			</div>
			<div class="card card--dark" style="padding: var(--sp-8);">
				<h3 class="card__title" style="color:var(--s247-bone);"><?php esc_html_e( 'Pris', 'studie247' ); ?></h3>
				<span class="card__price"><?php esc_html_e( 'fra 1.500 kr / time', 'studie247' ); ?></span>
				<p class="card__body"><?php esc_html_e( 'Minimum 3 timer. Weekend og aften tillægges 20%.', 'studie247' ); ?></p>
				<?php studie247_button( __( 'Book råleje', 'studie247' ), home_url( '/book/?type=raa' ), 'secondary', 'btn--on-dark' ); ?>
			</div>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
