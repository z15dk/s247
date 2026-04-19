<?php
/**
 * Studiet-side — hero-video + setup-switcher + reels + book + guide.
 *
 * @package Studie247
 */

get_header();

$hero_video   = get_theme_mod( 's247_studiet_video' );
$hero_poster  = get_theme_mod( 's247_studiet_poster' );
$eyebrow      = get_theme_mod( 's247_studiet_eyebrow', 'Studiet' );
$title_a      = get_theme_mod( 's247_studiet_title_a', 'Hvor dit' );
$title_b      = get_theme_mod( 's247_studiet_title_b', 'indhold skabes' );
$lead         = get_theme_mod( 's247_studiet_lead', '200 m² produktionsrum. Kamera-rig, lys, lyd, cyklorama — alt klart fra dag 1.' );
$cta_text     = get_theme_mod( 's247_studiet_cta_text', 'Book studiet' );
$cta_url      = get_theme_mod( 's247_studiet_cta_url', home_url( '/booking-studie/' ) );

$book_title_a = get_theme_mod( 's247_studiet_book_title_a', 'Book vores studie' );
$book_title_b = get_theme_mod( 's247_studiet_book_title_b', 'online' );
$book_lead    = get_theme_mod( 's247_studiet_book_lead' );
$book_cta     = get_theme_mod( 's247_studiet_book_cta', 'Book nu' );
$book_url     = get_theme_mod( 's247_studiet_book_url', home_url( '/booking-studie/' ) );
$book_psst    = get_theme_mod( 's247_studiet_book_psst' );
$book_psst_cta= get_theme_mod( 's247_studiet_book_psst_cta', 'Se udlejning' );
$book_psst_url= get_theme_mod( 's247_studiet_book_psst_url', home_url( '/udlejning/' ) );

$guide_eyebrow = get_theme_mod( 's247_studiet_guide_eyebrow', 'Guide' );
$guide_title_a = get_theme_mod( 's247_studiet_guide_title_a', 'Sådan booker du' );
$guide_title_b = get_theme_mod( 's247_studiet_guide_title_b', 'uden at græde' );

/* Standard setups (bruges hvis customizer er tom).
   'use_type' mapper til formål-dropdown på /booking-studie/. */
$setup_defaults = array(
	1 => array( 'label' => 'Podcast',         'desc' => '2-4 personer, 3 kameravinkler, rig til lyd.',            'use_type' => 'podcast' ),
	2 => array( 'label' => 'Video-interview', 'desc' => 'Cinematisk setup med prompter og dedikeret lys.',        'use_type' => 'kursusvideo' ),
	3 => array( 'label' => 'Talking-head',    'desc' => 'Ren simpel baggrund, én person, hurtigt i gang.',        'use_type' => 'undervisningsvideo' ),
	4 => array( 'label' => 'Produkt / foto',  'desc' => 'Cyklorama, softboxe, klar til still og bevægelse.',      'use_type' => 'some-content' ),
	5 => array( 'label' => '',                'desc' => '',                                                       'use_type' => '' ),
	6 => array( 'label' => '',                'desc' => '',                                                       'use_type' => '' ),
);

/* Samle setups */
$setups = array();
for ( $i = 1; $i <= 6; $i++ ) {
	$label    = get_theme_mod( "s247_studiet_setup{$i}_label", $setup_defaults[ $i ]['label'] );
	$image    = get_theme_mod( "s247_studiet_setup{$i}_image" );
	$desc     = get_theme_mod( "s247_studiet_setup{$i}_desc",  $setup_defaults[ $i ]['desc'] );
	$use_type = get_theme_mod( "s247_studiet_setup{$i}_use_type", $setup_defaults[ $i ]['use_type'] );
	if ( $label ) {
		$setups[] = array( 'label' => $label, 'desc' => $desc, 'image' => $image, 'use_type' => $use_type );
	}
}

/* Samle reels */
$reels = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$video  = get_theme_mod( "s247_studiet_reel{$i}_video" );
	$poster = get_theme_mod( "s247_studiet_reel{$i}_poster" );
	$title  = get_theme_mod( "s247_studiet_reel{$i}_title" );
	$label  = get_theme_mod( "s247_studiet_reel{$i}_label" );
	if ( $video || $poster || $title ) {
		$reels[] = array( 'video' => $video, 'poster' => $poster, 'title' => $title, 'label' => $label );
	}
}

/* Standard guide-steps */
$step_defaults = array(
	1 => array(
		'title' => 'Find en ledig tid',
		'text'  => 'Åbn kalenderen og vælg dag + start/slut-tidspunkt. Ingen kode ord, ingen formular der varer 40 minutter.',
		'joke'  => 'Pro-tip: undgå fredag kl. 14 — det er når alle andre også vil.',
	),
	2 => array(
		'title' => 'Vælg dit setup',
		'text'  => 'Podcast, interview, talking-head, foto? Klik det du skal have. Vi rigger klar.',
		'joke'  => 'Ja, du må godt skifte mening 3 gange. Vi sletter ikke din booking.',
	),
	3 => array(
		'title' => 'Betal online',
		'text'  => 'Kort eller faktura. Du får en kvittering på mail plus en ICS-fil til din kalender.',
		'joke'  => 'Ingen skjulte gebyrer. Vi lover.',
	),
	4 => array(
		'title' => 'Mød op — vi er klar',
		'text'  => 'Cyklorama hvidt, lys tændt, kaffe brygget. Du ringer på, vi åbner.',
		'joke'  => 'Medbring snacks. Vi har ingen følelser om din valg af slikketype.',
	),
	5 => array(
		'title' => 'Optag, slap af, ud',
		'text'  => 'Når du er færdig, smider du bare døren til. Vi rydder op.',
		'joke'  => 'Du efterlader jer med godt indhold, vi efterlader os med ren gulvvask.',
	),
);

/* Samle guide-steps */
$steps = array();
for ( $i = 1; $i <= 5; $i++ ) {
	$title = get_theme_mod( "s247_studiet_step{$i}_title", $step_defaults[ $i ]['title'] );
	$text  = get_theme_mod( "s247_studiet_step{$i}_text",  $step_defaults[ $i ]['text'] );
	$joke  = get_theme_mod( "s247_studiet_step{$i}_joke",  $step_defaults[ $i ]['joke'] );
	if ( $title || $text ) {
		$steps[] = array( 'title' => $title, 'text' => $text, 'joke' => $joke );
	}
}
?>

<!-- 1) HERO VIDEO -->
<section class="studiet-hero">
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
		<span class="eyebrow eyebrow--on-dark"><?php echo esc_html( $eyebrow ); ?></span>
		<h1 class="studiet-hero__title">
			<?php echo esc_html( $title_a ); ?> <em><?php echo esc_html( $title_b ); ?></em>
		</h1>
		<p class="studiet-hero__lead"><?php echo wp_kses_post( $lead ); ?></p>
		<?php if ( $cta_text && $cta_url ) : ?>
			<a class="btn btn--primary btn--lg" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
		<?php endif; ?>
	</div>
</section>

<!-- 1b) STATS STRIP — wow-tal med ét blik (redigeres i Customizer → Studiet-side) -->
<?php
$stats = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$num   = get_theme_mod( "s247_studiet_stat{$i}_num", '' );
	$unit  = get_theme_mod( "s247_studiet_stat{$i}_unit", '' );
	$label = get_theme_mod( "s247_studiet_stat{$i}_label", '' );
	if ( $num || $label ) {
		$stats[] = array( 'num' => $num, 'unit' => $unit, 'label' => $label );
	}
}
?>
<?php if ( ! empty( $stats ) ) : ?>
<section class="studiet-stats">
	<div class="wrap wrap--wide">
		<div class="studiet-stats__grid">
			<?php foreach ( $stats as $s ) : ?>
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

<!-- 2) SETUP SWITCHER -->
<?php if ( ! empty( $setups ) ) : ?>
<section class="section studiet-setups">
	<div class="wrap wrap--wide">
		<div class="studiet-setups__head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Setups', 'studie247' ); ?></span>
			<h2 class="section-head__title">
				<?php esc_html_e( 'Find dit', 'studie247' ); ?> <em><?php esc_html_e( 'format', 'studie247' ); ?></em>
			</h2>
		</div>
		<div class="studiet-setups__panel" data-studiet-setups>
			<div class="studiet-setups__stage">
				<?php foreach ( $setups as $idx => $setup ) : ?>
					<div
						class="studiet-setups__image<?php echo 0 === $idx ? ' is-active' : ''; ?>"
						data-setup-panel="<?php echo esc_attr( $idx ); ?>"
						<?php echo 0 !== $idx ? 'hidden' : ''; ?>
					>
						<?php if ( $setup['image'] ) : ?>
							<img src="<?php echo esc_url( $setup['image'] ); ?>" alt="<?php echo esc_attr( $setup['label'] ); ?>" loading="lazy">
						<?php else : ?>
							<div class="studiet-setups__placeholder"><?php esc_html_e( 'Upload billede i Customizer', 'studie247' ); ?></div>
						<?php endif; ?>
						<?php
						$setup_book_url = $setup['use_type']
							? add_query_arg( 'use_type', $setup['use_type'], home_url( '/booking-studie/' ) )
							: home_url( '/booking-studie/' );
						?>
						<a class="studiet-setups__book btn btn--primary" href="<?php echo esc_url( $setup_book_url ); ?>">
							<?php printf( esc_html__( 'Book til %s', 'studie247' ), esc_html( $setup['label'] ) ); ?>
							<?php echo studie247_icon( 'arrow-right', 16 ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="studiet-setups__tabs" role="tablist" aria-orientation="vertical">
				<?php foreach ( $setups as $idx => $setup ) : ?>
					<button
						type="button"
						class="studiet-setups__tab<?php echo 0 === $idx ? ' is-active' : ''; ?>"
						data-setup-target="<?php echo esc_attr( $idx ); ?>"
						aria-selected="<?php echo 0 === $idx ? 'true' : 'false'; ?>"
					>
						<span class="studiet-setups__tab-num"><?php printf( '%02d', $idx + 1 ); ?></span>
						<span class="studiet-setups__tab-body">
							<span class="studiet-setups__tab-label"><?php echo esc_html( $setup['label'] ); ?></span>
							<?php if ( $setup['desc'] ) : ?>
								<span class="studiet-setups__tab-desc"><?php echo esc_html( $setup['desc'] ); ?></span>
							<?php endif; ?>
						</span>
						<?php echo studie247_icon( 'arrow-right', 16 ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- 2b) MID-PAGE CTA BANNER — stor og direkte -->
<section class="studiet-banner">
	<div class="wrap wrap--wide">
		<div class="studiet-banner__inner">
			<span class="eyebrow eyebrow--on-dark eyebrow--no-line"><?php esc_html_e( 'Klar til at optage?', 'studie247' ); ?></span>
			<h2 class="studiet-banner__title">
				<?php esc_html_e( 'Vælg en dag.', 'studie247' ); ?>
				<em><?php esc_html_e( 'Vi står klar.', 'studie247' ); ?></em>
			</h2>
			<p class="studiet-banner__lead">
				<?php esc_html_e( 'Fra 6 timer til hele dage. Kalender altid opdateret — vælg slot, betal, mød op.', 'studie247' ); ?>
			</p>
			<div class="studiet-banner__ctas">
				<a class="btn btn--primary btn--xl" href="<?php echo esc_url( home_url( '/booking-studie/' ) ); ?>">
					<?php esc_html_e( 'Book studiet nu', 'studie247' ); ?>
					<?php echo studie247_icon( 'arrow-right', 18 ); ?>
				</a>
				<a class="studiet-banner__link" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">
					<?php esc_html_e( 'eller stil et spørgsmål', 'studie247' ); ?>
					<?php echo studie247_icon( 'arrow-right', 14 ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<!-- 3) REELS — 3 portrait videos -->
<?php if ( ! empty( $reels ) ) : ?>
<section class="section studiet-reels">
	<div class="wrap wrap--wide">
		<header class="section-head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php esc_html_e( 'Fra studiet', 'studie247' ); ?></span>
			<h2 class="section-head__title">
				<?php esc_html_e( 'Eksempler, ikke', 'studie247' ); ?> <em><?php esc_html_e( 'eksperimenter', 'studie247' ); ?></em>
			</h2>
		</header>
		<div class="studiet-reels__grid">
			<?php foreach ( $reels as $reel ) : ?>
				<figure class="reel">
					<div class="reel__media" data-reel>
						<?php if ( $reel['video'] ) : ?>
							<video
								muted
								loop
								playsinline
								preload="metadata"
								<?php echo $reel['poster'] ? 'poster="' . esc_url( $reel['poster'] ) . '"' : ''; ?>
								data-reel-video
							>
								<source src="<?php echo esc_url( $reel['video'] ); ?>" type="video/mp4">
							</video>
							<button type="button" class="reel__play" aria-label="<?php esc_attr_e( 'Afspil video', 'studie247' ); ?>" data-reel-play>
								<?php echo studie247_icon( 'play', 28 ); ?>
							</button>
						<?php elseif ( $reel['poster'] ) : ?>
							<img src="<?php echo esc_url( $reel['poster'] ); ?>" alt="<?php echo esc_attr( $reel['title'] ); ?>" loading="lazy">
						<?php else : ?>
							<div class="reel__placeholder"><?php echo studie247_icon( 'play', 36 ); ?></div>
						<?php endif; ?>
					</div>
					<?php if ( $reel['title'] || $reel['label'] ) : ?>
						<figcaption class="reel__caption">
							<?php if ( $reel['label'] ) : ?>
								<span class="reel__label"><?php echo esc_html( $reel['label'] ); ?></span>
							<?php endif; ?>
							<?php if ( $reel['title'] ) : ?>
								<span class="reel__title"><?php echo esc_html( $reel['title'] ); ?></span>
							<?php endif; ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- 4) BOOK + rent -->
<section class="section studiet-book">
	<div class="wrap wrap--wide">
		<div class="studiet-book__card">
			<div class="studiet-book__main">
				<h2 class="section-head__title">
					<?php echo esc_html( $book_title_a ); ?> <em><?php echo esc_html( $book_title_b ); ?></em>
				</h2>
				<?php if ( $book_lead ) : ?>
					<p class="studiet-book__lead"><?php echo wp_kses_post( $book_lead ); ?></p>
				<?php endif; ?>
				<?php if ( $book_cta && $book_url ) : ?>
					<a class="btn btn--primary btn--xl" href="<?php echo esc_url( $book_url ); ?>"><?php echo esc_html( $book_cta ); ?></a>
				<?php endif; ?>
			</div>
			<?php
			// Hent op til 4 udstyrs-items med billede til thumb-grid
			$rent_preview = get_posts( array(
				'post_type'      => 'udlejning_item',
				'posts_per_page' => 4,
				'orderby'        => 'rand',
				'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ),
			) );
			// Total antal varer til tælleren (viser samme uanset hvor mange der har billede)
			$rent_total = (int) wp_count_posts( 'udlejning_item' )->publish;
			?>
			<aside class="studiet-book__aside">
				<span class="studiet-book__aside-eyebrow">
					<span class="studiet-book__pulse" aria-hidden="true"></span>
					<?php esc_html_e( 'Psst — du kan også leje udstyr', 'studie247' ); ?>
				</span>

				<?php if ( ! empty( $rent_preview ) ) : ?>
					<div class="studiet-book__thumbs">
						<?php foreach ( $rent_preview as $item ) :
							$pris_dag = get_post_meta( $item->ID, '_s247_pris_dag', true );
						?>
							<a class="studiet-book__thumb" href="<?php echo esc_url( get_permalink( $item ) ); ?>" title="<?php echo esc_attr( $item->post_title ); ?>">
								<?php echo get_the_post_thumbnail( $item, 's247-square', array( 'loading' => 'lazy', 'alt' => $item->post_title ) ); ?>
								<?php if ( $pris_dag ) : ?>
									<span class="studiet-book__price"><?php echo esc_html( $pris_dag ); ?></span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
					<p class="studiet-book__thumbs-lead">
						<?php if ( $rent_total > 0 ) : ?>
							<strong class="studiet-book__count">+<?php echo (int) $rent_total; ?></strong>
							<?php esc_html_e( 'varer klar', 'studie247' ); ?> —
						<?php endif; ?>
						<?php esc_html_e( 'kameraer, lys, lyd og grip fra dag til dag.', 'studie247' ); ?>
					</p>
				<?php elseif ( $book_psst ) : ?>
					<p class="studiet-book__psst"><?php echo wp_kses_post( $book_psst ); ?></p>
				<?php endif; ?>

				<?php if ( $book_psst_cta && $book_psst_url ) : ?>
					<a class="studiet-book__link" href="<?php echo esc_url( $book_psst_url ); ?>">
						<?php echo esc_html( $book_psst_cta ); ?>
						<?php echo studie247_icon( 'arrow-right', 14 ); ?>
					</a>
				<?php endif; ?>
			</aside>
		</div>
	</div>
</section>

<!-- 5) BOOKING-GUIDE -->
<?php if ( ! empty( $steps ) ) : ?>
<section class="section studiet-guide">
	<div class="wrap wrap--wide">
		<header class="section-head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line"><?php echo esc_html( $guide_eyebrow ); ?></span>
			<h2 class="section-head__title">
				<?php echo esc_html( $guide_title_a ); ?> <em><?php echo esc_html( $guide_title_b ); ?></em>
			</h2>
		</header>
		<ol class="studiet-guide__list">
			<?php foreach ( $steps as $idx => $step ) : ?>
				<li class="studiet-guide__step">
					<span class="studiet-guide__num"><?php printf( '%02d', $idx + 1 ); ?></span>
					<div class="studiet-guide__body">
						<h3 class="studiet-guide__step-title"><?php echo esc_html( $step['title'] ); ?></h3>
						<?php if ( $step['text'] ) : ?>
							<p class="studiet-guide__text"><?php echo wp_kses_post( $step['text'] ); ?></p>
						<?php endif; ?>
						<?php if ( $step['joke'] ) : ?>
							<p class="studiet-guide__joke"><em>&mdash;</em> <?php echo esc_html( $step['joke'] ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php
		$gc_badge      = get_theme_mod( 's247_studiet_guide_cta_badge', '60' );
		$gc_badge_unit = get_theme_mod( 's247_studiet_guide_cta_badge_unit', 'sek' );
		$gc_title_a    = get_theme_mod( 's247_studiet_guide_cta_title_a', 'Så kort tager det.' );
		$gc_title_b    = get_theme_mod( 's247_studiet_guide_cta_title_b', 'Gå i gang.' );
		$gc_lead       = get_theme_mod( 's247_studiet_guide_cta_lead', 'Vil du se rummet først? Kig forbi til en gratis rundvisning — 15 min, ingen forpligtelser.' );
		$gc_button     = get_theme_mod( 's247_studiet_guide_cta_button', 'Book studiet' );
		$gc_button_url = get_theme_mod( 's247_studiet_guide_cta_button_url', home_url( '/booking-studie/' ) );
		$gc_link       = get_theme_mod( 's247_studiet_guide_cta_link', 'eller tag en rundvisning først' );
		$gc_link_url   = get_theme_mod( 's247_studiet_guide_cta_link_url', home_url( '/kontakt-os/?emne=rundvisning' ) );
		$gc_video      = get_theme_mod( 's247_studiet_guide_cta_video', '' );
		$gc_poster     = get_theme_mod( 's247_studiet_guide_cta_poster', '' );
		$gc_has_media  = $gc_video || $gc_poster;
		?>
		<div class="studiet-guide__cta<?php echo $gc_has_media ? ' has-media' : ''; ?>">
			<div class="studiet-guide__cta-left">
				<?php if ( $gc_badge || $gc_badge_unit ) : ?>
					<span class="studiet-guide__timer" aria-hidden="true">
						<?php if ( $gc_badge ) : ?>
							<span class="studiet-guide__timer-num"><?php echo esc_html( $gc_badge ); ?></span>
						<?php endif; ?>
						<?php if ( $gc_badge_unit ) : ?>
							<span class="studiet-guide__timer-unit"><?php echo esc_html( $gc_badge_unit ); ?></span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
				<?php if ( $gc_title_a || $gc_title_b ) : ?>
					<h3 class="studiet-guide__cta-title">
						<?php echo esc_html( $gc_title_a ); ?>
						<?php if ( $gc_title_b ) : ?><em><?php echo esc_html( $gc_title_b ); ?></em><?php endif; ?>
					</h3>
				<?php endif; ?>
				<?php if ( $gc_lead ) : ?>
					<p class="studiet-guide__cta-lead"><?php echo wp_kses_post( $gc_lead ); ?></p>
				<?php endif; ?>
			</div>

			<div class="studiet-guide__cta-right">
				<?php if ( $gc_has_media ) : ?>
					<div class="studiet-guide__cta-media" data-reel>
						<?php if ( $gc_video ) : ?>
							<video
								muted
								loop
								playsinline
								preload="metadata"
								<?php echo $gc_poster ? 'poster="' . esc_url( $gc_poster ) . '"' : ''; ?>
								data-reel-video
							>
								<source src="<?php echo esc_url( $gc_video ); ?>" type="video/mp4">
							</video>
							<button type="button" class="reel__play" aria-label="<?php esc_attr_e( 'Afspil rundvisning', 'studie247' ); ?>" data-reel-play>
								<?php echo studie247_icon( 'play', 28 ); ?>
							</button>
						<?php elseif ( $gc_poster ) : ?>
							<img src="<?php echo esc_url( $gc_poster ); ?>" alt="<?php esc_attr_e( 'Rundvisning', 'studie247' ); ?>" loading="lazy">
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="studiet-guide__cta-actions">
					<?php if ( $gc_button && $gc_button_url ) : ?>
						<a class="btn btn--primary btn--xl" href="<?php echo esc_url( $gc_button_url ); ?>">
							<?php echo esc_html( $gc_button ); ?>
							<?php echo studie247_icon( 'arrow-right', 18 ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $gc_link && $gc_link_url ) : ?>
						<a class="studiet-guide__cta-link" href="<?php echo esc_url( $gc_link_url ); ?>">
							<?php echo esc_html( $gc_link ); ?>
							<?php echo studie247_icon( 'arrow-right', 14 ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/section', 'cta' ); ?>

<?php get_footer();
