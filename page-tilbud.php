<?php
/**
 * Tilbud — sjov og inspirerende quiz-flow der ender i en
 * prisforespørgsel. 5 trin, hver med et "kvittering"-feedback
 * der gør det legesygt at udfylde.
 *
 * Slug: /tilbud/
 *
 * @package Studie247
 */

$errors    = array();
$submitted = isset( $_GET['sendt'] ) && '1' === $_GET['sendt'];

$form_project = '';  // Type projekt
$form_mood    = '';  // Vibe / stemning
$form_budget  = '';  // Budget-bracket
$form_when    = '';  // Tidshorisont
$form_name    = '';
$form_email   = '';
$form_phone   = '';
$form_details = '';

if ( ! empty( $_POST['s247_tilbud_nonce'] ) && wp_verify_nonce( $_POST['s247_tilbud_nonce'], 's247_tilbud' ) ) {
	$form_project = sanitize_text_field( wp_unslash( $_POST['project']  ?? '' ) );
	$form_mood    = sanitize_text_field( wp_unslash( $_POST['mood']     ?? '' ) );
	$form_budget  = sanitize_text_field( wp_unslash( $_POST['budget']   ?? '' ) );
	$form_when    = sanitize_text_field( wp_unslash( $_POST['when']     ?? '' ) );
	$form_name    = sanitize_text_field( wp_unslash( $_POST['name']     ?? '' ) );
	$form_email   = sanitize_email(      wp_unslash( $_POST['email']    ?? '' ) );
	$form_phone   = sanitize_text_field( wp_unslash( $_POST['phone']    ?? '' ) );
	$form_details = sanitize_textarea_field( wp_unslash( $_POST['details'] ?? '' ) );

	if ( ! $form_project ) { $errors[] = __( 'Vælg hvilken type projekt.', 'studie247' ); }
	if ( ! $form_name )    { $errors[] = __( 'Udfyld dit navn.', 'studie247' ); }
	if ( ! is_email( $form_email ) ) { $errors[] = __( 'Indtast en gyldig email.', 'studie247' ); }
	if ( empty( $_POST['s247_consent'] ) ) {
		$errors[] = __( 'Du skal acceptere privatlivspolitikken for at sende forespørgslen.', 'studie247' );
	}

	if ( empty( $errors ) ) {
		$topic = sprintf( '%s · %s · %s', $form_project, $form_budget ?: 'uoplyst', $form_when ?: 'når som helst' );
		$message = sprintf(
			"Projekt: %s\nStemning: %s\nBudget: %s\nTidshorisont: %s\n\n%s",
			$form_project,
			$form_mood    ?: '—',
			$form_budget  ?: '—',
			$form_when    ?: '—',
			$form_details ?: ''
		);

		if ( function_exists( 'studie247_save_kontakt_besked' ) ) {
			studie247_save_kontakt_besked( array(
				'name'    => $form_name,
				'email'   => $form_email,
				'phone'   => $form_phone,
				'topic'   => 'Tilbud: ' . $topic,
				'message' => $message,
				'source'  => 'tilbud',
			) );
		}

		$admin_to      = get_theme_mod( 's247_email', 'info@s247.dk' );
		$admin_subject = sprintf( '[Studie 247] Tilbud ønsket: %s', $form_project );
		$admin_body    = "TILBUDSFORESPØRGSEL\n\nNavn: {$form_name}\nEmail: {$form_email}\nTelefon: {$form_phone}\n\n{$message}";
		@wp_mail( $admin_to, $admin_subject, $admin_body, array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . $form_name . ' <' . $form_email . '>',
		) );

		if ( function_exists( 'studie247_render_mail_template' ) ) {
			$mail = studie247_render_mail_template( 'contact', array(
				'navn'   => $form_name,
				'email'  => $form_email,
				'side'   => 'Tilbud',
				'besked' => nl2br( $message ),
			) );
			$is_html = studie247_mail_template_is_html( 'contact' );
			@wp_mail( $form_email, $mail['subject'], $mail['body'], array(
				'Content-Type: ' . ( $is_html ? 'text/html' : 'text/plain' ) . '; charset=UTF-8',
				'From: Studie 247 <' . $admin_to . '>',
				'Reply-To: ' . $admin_to,
			) );
		}

		wp_safe_redirect( home_url( '/tilbud/?sendt=1' ) );
		exit;
	}
}

get_header();
?>

<section class="tb-section">
	<div class="tb-decor" aria-hidden="true">
		<span class="tb-decor__blob tb-decor__blob--a"></span>
		<span class="tb-decor__blob tb-decor__blob--b"></span>
		<span class="tb-decor__blob tb-decor__blob--c"></span>
	</div>
	<div class="wrap wrap--tight">

		<?php if ( $submitted ) : ?>
			<div class="tb-success">
				<div class="tb-success__confetti" aria-hidden="true">🎉</div>
				<span class="eyebrow eyebrow--accent eyebrow--no-line">Modtaget</span>
				<h1 class="tb-head"><em>Perfekt</em> — vi regner på det.</h1>
				<p>Du har post i indbakken indenfor 24 timer med et skræddersyet tilbud. Har du en deadline i vanvid? Ring os — vi finder altid en vej.</p>
				<div class="tb-success__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Tilbage til forsiden <?php echo studie247_icon( 'arrow-right', 16 ); ?></a>
					<?php
					$tel_raw = get_theme_mod( 's247_phone', '+45 00 00 00 00' );
					$tel_link = '+' . preg_replace( '/\D/', '', $tel_raw );
					?>
					<a class="btn btn--ghost" href="tel:<?php echo esc_attr( $tel_link ); ?>"><?php echo studie247_icon( 'phone', 16 ); ?> <?php echo esc_html( $tel_raw ); ?></a>
				</div>
			</div>
		<?php else : ?>

			<header class="tb-headwrap">
				<span class="tb-eyebrow">
					<span class="tb-eyebrow__icon" aria-hidden="true"><?php echo studie247_icon( 'sparkle', 14 ); ?></span>
					<span class="tb-eyebrow__text"><?php esc_html_e( 'Få et tilbud', 'studie247' ); ?></span>
				</span>
				<h1 class="tb-head">Hvad skal vi <em>skabe</em> sammen?</h1>
				<p class="tb-lead">Fem hurtige spørgsmål — og du får et bud på pris og timing inden i morgen. Ingen forpligtelser, ingen robotter.</p>
			</header>

			<?php if ( $errors ) : ?>
				<div class="kontakt-errors" role="alert">
					<span class="kontakt-errors__label">Lige en ting —</span>
					<ul><?php foreach ( $errors as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?></ul>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( home_url( '/tilbud/' ) ); ?>" class="tb-flow" data-tb-flow novalidate>
				<?php wp_nonce_field( 's247_tilbud', 's247_tilbud_nonce' ); ?>

				<div class="tb-steps">

					<!-- 1: Type projekt -->
					<section class="tb-step is-active" data-tb-step="1">
						<span class="tb-counter">01 / 05</span>
						<h2 class="tb-q"><em>Hvad</em> vil du skabe?</h2>
						<p class="tb-hint">Vælg det der passer bedst — vi kan altid finjustere senere.</p>
						<div class="tb-cards" role="radiogroup" aria-label="Projekt">
							<?php
							$projects = array(
								array( 'icon' => 'mic',          'label' => 'Podcast',          'sub' => 'Lyd der holder' ),
								array( 'icon' => 'square-video', 'label' => 'SoMe-video',       'sub' => 'Kort og skarp' ),
								array( 'icon' => 'academic',     'label' => 'Online kursus',    'sub' => 'Fra e-learning til keynote' ),
								array( 'icon' => 'camera',       'label' => 'Fotoshoot',        'sub' => 'Billeder der sælger' ),
								array( 'icon' => 'film',         'label' => 'Reklamefilm',      'sub' => 'Stort format' ),
								array( 'icon' => 'handshake',    'label' => 'Noget helt andet', 'sub' => 'Fortæl os det' ),
							);
							foreach ( $projects as $p ) : ?>
								<button type="button" class="tb-card<?php echo $form_project === $p['label'] ? ' is-selected' : ''; ?>" data-tb-pick="project" data-tb-value="<?php echo esc_attr( $p['label'] ); ?>" role="radio" aria-checked="<?php echo $form_project === $p['label'] ? 'true' : 'false'; ?>">
									<span class="tb-card__icon" aria-hidden="true"><?php echo studie247_icon( $p['icon'], 32 ); ?></span>
									<span class="tb-card__label"><?php echo esc_html( $p['label'] ); ?></span>
									<span class="tb-card__sub"><?php echo esc_html( $p['sub'] ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
						<input type="hidden" name="project" value="<?php echo esc_attr( $form_project ); ?>" data-tb-input="project">
						<div class="tb-actions"><span></span><button type="button" class="btn btn--primary" data-tb-next="2" data-tb-requires="project">Videre <?php echo studie247_icon( 'arrow-right', 16 ); ?></button></div>
					</section>

					<!-- 2: Stemning -->
					<section class="tb-step" data-tb-step="2" hidden>
						<span class="tb-counter">02 / 05</span>
						<h2 class="tb-q"><em>Hvordan</em> skal det føles?</h2>
						<p class="tb-hint">Tag den der ligger tættest på. Eller bare gæt — vi zoomer ind sammen.</p>
						<div class="tb-cards tb-cards--sm" role="radiogroup" aria-label="Stemning">
							<?php
							$moods = array(
								array( 'icon' => 'feather',     'label' => 'Elegant & rolig' ),
								array( 'icon' => 'zap',         'label' => 'Hurtig & energisk' ),
								array( 'icon' => 'circle-dot',  'label' => 'Minimal & clean' ),
								array( 'icon' => 'flame',       'label' => 'Rå & autentisk' ),
								array( 'icon' => 'palette',     'label' => 'Farverig & legesyg' ),
								array( 'icon' => 'book',        'label' => 'Fortællende & dyb' ),
							);
							foreach ( $moods as $m ) : ?>
								<button type="button" class="tb-card tb-card--compact<?php echo $form_mood === $m['label'] ? ' is-selected' : ''; ?>" data-tb-pick="mood" data-tb-value="<?php echo esc_attr( $m['label'] ); ?>" role="radio" aria-checked="<?php echo $form_mood === $m['label'] ? 'true' : 'false'; ?>">
									<span class="tb-card__icon" aria-hidden="true"><?php echo studie247_icon( $m['icon'], 26 ); ?></span>
									<span class="tb-card__label"><?php echo esc_html( $m['label'] ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
						<input type="hidden" name="mood" value="<?php echo esc_attr( $form_mood ); ?>" data-tb-input="mood">
						<div class="tb-actions">
							<button type="button" class="btn btn--ghost" data-tb-prev="1">← Tilbage</button>
							<button type="button" class="btn btn--primary" data-tb-next="3">Videre <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>

					<!-- 3: Budget -->
					<section class="tb-step" data-tb-step="3" hidden>
						<span class="tb-counter">03 / 05</span>
						<h2 class="tb-q">Hvad <em>regner</em> du med?</h2>
						<p class="tb-hint">En retning hjælper os. Tallet er bare et startpunkt.</p>
						<div class="tb-cards tb-cards--budget" role="radiogroup" aria-label="Budget">
							<?php
							$budgets = array(
								array( 'label' => 'Under 10.000', 'sub' => 'Let & hurtigt' ),
								array( 'label' => '10–25.000',   'sub' => 'Solid produktion' ),
								array( 'label' => '25–50.000',   'sub' => 'Med hele pakken' ),
								array( 'label' => '50–100.000',  'sub' => 'Flagskib' ),
								array( 'label' => '100.000+',    'sub' => 'Langt samarbejde' ),
								array( 'label' => 'Vejled mig',  'sub' => 'Jeg er åben' ),
							);
							foreach ( $budgets as $b ) : ?>
								<button type="button" class="tb-card tb-card--compact<?php echo $form_budget === $b['label'] ? ' is-selected' : ''; ?>" data-tb-pick="budget" data-tb-value="<?php echo esc_attr( $b['label'] ); ?>" role="radio" aria-checked="<?php echo $form_budget === $b['label'] ? 'true' : 'false'; ?>">
									<span class="tb-card__label"><?php echo esc_html( $b['label'] ); ?></span>
									<span class="tb-card__sub"><?php echo esc_html( $b['sub'] ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
						<input type="hidden" name="budget" value="<?php echo esc_attr( $form_budget ); ?>" data-tb-input="budget">
						<div class="tb-actions">
							<button type="button" class="btn btn--ghost" data-tb-prev="2">← Tilbage</button>
							<button type="button" class="btn btn--primary" data-tb-next="4">Videre <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>

					<!-- 4: Hvornår -->
					<section class="tb-step" data-tb-step="4" hidden>
						<span class="tb-counter">04 / 05</span>
						<h2 class="tb-q">Hvornår skal det <em>ud</em>?</h2>
						<p class="tb-hint">Vi prioriterer ud fra din deadline — ingen magi, men tæt på.</p>
						<div class="tb-cards tb-cards--sm" role="radiogroup" aria-label="Tidshorisont">
							<?php
							$whens = array(
								array( 'icon' => 'rocket',         'label' => 'I går helst' ),
								array( 'icon' => 'calendar',       'label' => 'Indenfor 2 uger' ),
								array( 'icon' => 'calendar-clock', 'label' => 'Indenfor en måned' ),
								array( 'icon' => 'sunrise',        'label' => '1–3 måneder' ),
								array( 'icon' => 'infinity',       'label' => 'Løbende samarbejde' ),
								array( 'icon' => 'help',           'label' => 'Vi ved ikke' ),
							);
							foreach ( $whens as $w ) : ?>
								<button type="button" class="tb-card tb-card--compact<?php echo $form_when === $w['label'] ? ' is-selected' : ''; ?>" data-tb-pick="when" data-tb-value="<?php echo esc_attr( $w['label'] ); ?>" role="radio" aria-checked="<?php echo $form_when === $w['label'] ? 'true' : 'false'; ?>">
									<span class="tb-card__icon" aria-hidden="true"><?php echo studie247_icon( $w['icon'], 26 ); ?></span>
									<span class="tb-card__label"><?php echo esc_html( $w['label'] ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
						<input type="hidden" name="when" value="<?php echo esc_attr( $form_when ); ?>" data-tb-input="when">
						<div class="tb-actions">
							<button type="button" class="btn btn--ghost" data-tb-prev="3">← Tilbage</button>
							<button type="button" class="btn btn--primary" data-tb-next="5">Næsten der <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>

					<!-- 5: Kontaktinfo -->
					<section class="tb-step" data-tb-step="5" hidden>
						<span class="tb-counter">05 / 05</span>
						<h2 class="tb-q">Hvem <em>skriver</em> vi til?</h2>
						<p class="tb-hint">Vi vender tilbage på den måde du foretrækker.</p>
						<div class="tb-summary">
							<span><strong data-tb-sum="project">—</strong> projekt</span>
							<span>·</span>
							<span><strong data-tb-sum="mood">—</strong> stemning</span>
							<span>·</span>
							<span><strong data-tb-sum="budget">—</strong> budget</span>
							<span>·</span>
							<span><strong data-tb-sum="when">—</strong> tid</span>
						</div>
						<div class="tb-grid">
							<label class="tb-field">
								<span class="tb-field-label">Dit navn</span>
								<input type="text" name="name" value="<?php echo esc_attr( $form_name ); ?>" required>
							</label>
							<label class="tb-field">
								<span class="tb-field-label">E-mail</span>
								<input type="email" name="email" value="<?php echo esc_attr( $form_email ); ?>" required>
							</label>
							<label class="tb-field">
								<span class="tb-field-label">Telefon (valgfrit)</span>
								<input type="tel" name="phone" value="<?php echo esc_attr( $form_phone ); ?>" placeholder="+45 …">
							</label>
						</div>
						<label class="tb-field">
							<span class="tb-field-label">Fortæl os mere (valgfrit)</span>
							<textarea name="details" rows="4" placeholder="Ideer, links, referencer, deadlines — smid det bare af …"><?php echo esc_textarea( $form_details ); ?></textarea>
						</label>
						<?php if ( function_exists( 'studie247_consent_field' ) ) { studie247_consent_field(); } ?>
						<div class="tb-actions">
							<button type="button" class="btn btn--ghost" data-tb-prev="4">← Tilbage</button>
							<button type="submit" class="btn btn--primary btn--lg">Send forespørgsel <?php echo studie247_icon( 'arrow-right', 16 ); ?></button>
						</div>
					</section>

				</div>

				<div class="tb-progress" aria-hidden="true">
					<span class="tb-progress__bar" data-tb-bar></span>
					<?php for ( $n = 1; $n <= 5; $n++ ) : ?>
						<span class="tb-progress__dot<?php echo 1 === $n ? ' is-active' : ''; ?>" data-tb-dot="<?php echo $n; ?>"></span>
					<?php endfor; ?>
				</div>
			</form>

		<?php endif; ?>
	</div>
</section>

<script>
(function(){
	var form = document.querySelector('[data-tb-flow]');
	if (!form) return;
	var state = { project: '', mood: '', budget: '', when: '' };

	function show(n) {
		form.querySelectorAll('[data-tb-step]').forEach(function(s){
			var is = parseInt(s.dataset.tbStep, 10) === n;
			s.hidden = !is;
			if (is) { s.classList.add('is-active'); } else { s.classList.remove('is-active'); }
		});
		form.querySelectorAll('[data-tb-dot]').forEach(function(d){
			d.classList.toggle('is-active', parseInt(d.dataset.tbDot, 10) <= n);
		});
		var bar = form.querySelector('[data-tb-bar]');
		if (bar) bar.style.setProperty('--tb-progress', ((n - 1) / 4 * 100) + '%');
		// Opdater summary i step 5
		form.querySelectorAll('[data-tb-sum]').forEach(function(el){
			el.textContent = state[el.dataset.tbSum] || '—';
		});
		// Scroll top på mobile
		if (window.innerWidth < 720) {
			form.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}

	form.addEventListener('click', function(e){
		var pickBtn = e.target.closest('[data-tb-pick]');
		if (pickBtn) {
			var key = pickBtn.dataset.tbPick;
			var val = pickBtn.dataset.tbValue;
			state[key] = val;
			var hidden = form.querySelector('[data-tb-input="' + key + '"]');
			if (hidden) hidden.value = val;
			// Unselect siblings
			pickBtn.closest('.tb-cards').querySelectorAll('.tb-card').forEach(function(c){
				c.classList.remove('is-selected');
				c.setAttribute('aria-checked', 'false');
			});
			pickBtn.classList.add('is-selected');
			pickBtn.setAttribute('aria-checked', 'true');
			// Auto-advance fra cards
			var step = pickBtn.closest('[data-tb-step]');
			var nextBtn = step.querySelector('[data-tb-next]');
			if (nextBtn) setTimeout(function(){ nextBtn.click(); }, 280);
			return;
		}

		var next = e.target.closest('[data-tb-next]');
		if (next) {
			var required = next.dataset.tbRequires;
			if (required && !state[required]) {
				var step = next.closest('[data-tb-step]');
				step.classList.add('tb-shake');
				setTimeout(function(){ step.classList.remove('tb-shake'); }, 500);
				return;
			}
			show(parseInt(next.dataset.tbNext, 10));
			return;
		}
		var prev = e.target.closest('[data-tb-prev]');
		if (prev) { show(parseInt(prev.dataset.tbPrev, 10)); return; }
	});
})();
</script>

<?php get_footer(); ?>
