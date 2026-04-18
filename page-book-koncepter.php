<?php
/**
 * Book-koncepter — visuel mockup af 10 booking-form designs.
 *
 * Besøg via /book-koncepter/ (opret en WP-side med den slug, eller
 * brug en anden side-slug "book-koncepter").
 *
 * @package Studie247
 */

get_header();
?>

<section class="kc-page">
	<div class="wrap wrap--wide">
		<header class="kc-page__head">
			<span class="eyebrow eyebrow--accent eyebrow--no-line">Koncepter</span>
			<h1 class="section-head__title">10 bud på <em>booking-formularen</em></h1>
			<p class="kc-page__lead">Alle i Studie 247's typografi og farver. Vælg ét — eller bland dem.</p>
		</header>

		<div class="kc-grid">

			<!-- 1. Wizard -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">01</span>
					<div>
						<h2>Wizard</h2>
						<p>Ét trin ad gangen. Progress-dots øverst.</p>
					</div>
				</header>
				<div class="kc-frame">
					<div class="kc1-progress">
						<span class="kc1-dot is-done"></span>
						<span class="kc1-bar is-done"></span>
						<span class="kc1-dot is-done"></span>
						<span class="kc1-bar"></span>
						<span class="kc1-dot is-active"></span>
						<span class="kc1-bar"></span>
						<span class="kc1-dot"></span>
					</div>
					<div class="kc1-step">
						<span class="kc1-num">03 —</span>
						<h3>Hvem er du?</h3>
						<div class="kc-input">Navn</div>
						<div class="kc-input">E-mail</div>
						<div class="kc-actions">
							<button class="kc-btn kc-btn--ghost">← Tilbage</button>
							<button class="kc-btn kc-btn--primary">Næste →</button>
						</div>
					</div>
				</div>
			</article>

			<!-- 2. Magazine-split -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">02</span>
					<div>
						<h2>Magazine-split</h2>
						<p>Stort billede venstre, form højre.</p>
					</div>
				</header>
				<div class="kc-frame">
					<div class="kc2-split">
						<div class="kc2-media"></div>
						<div class="kc2-form">
							<span class="kc-eyebrow">Book · 01</span>
							<h3>Reservér din tid</h3>
							<div class="kc-input">📅 Dato</div>
							<div class="kc-input">🕐 Tid</div>
							<button class="kc-btn kc-btn--primary">Fortsæt →</button>
						</div>
					</div>
				</div>
			</article>

			<!-- 3. Pill-timeline -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">03</span>
					<div>
						<h2>Pille-tidslinje</h2>
						<p>Datoer som klikbare pills i vandret strip.</p>
					</div>
				</header>
				<div class="kc-frame">
					<span class="kc-eyebrow">Vælg dag</span>
					<div class="kc3-strip">
						<span class="kc3-pill">ma 18</span>
						<span class="kc3-pill kc3-pill--on">ti 19</span>
						<span class="kc3-pill">on 20</span>
						<span class="kc3-pill">to 21</span>
						<span class="kc3-pill kc3-pill--off">fr 22</span>
						<span class="kc3-pill">lø 23</span>
					</div>
					<span class="kc-eyebrow">Start-tid</span>
					<div class="kc3-times">
						<span class="kc3-time">08</span>
						<span class="kc3-time">10</span>
						<span class="kc3-time kc3-time--on">12</span>
						<span class="kc3-time">14</span>
						<span class="kc3-time">16</span>
					</div>
					<button class="kc-btn kc-btn--primary">Book 12:00 →</button>
				</div>
			</article>

			<!-- 4. Card-accordion -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">04</span>
					<div>
						<h2>Accordion-stak</h2>
						<p>Ét kort folder næste ud når du klikker.</p>
					</div>
				</header>
				<div class="kc-frame">
					<div class="kc4-item kc4-item--done">
						<span class="kc4-label">Dato</span>
						<span class="kc4-val">ti 19. april</span>
						<span class="kc4-edit">Rediger</span>
					</div>
					<div class="kc4-item kc4-item--active">
						<span class="kc4-label">Tid</span>
						<div class="kc4-body">
							<div class="kc3-times">
								<span class="kc3-time">08</span>
								<span class="kc3-time kc3-time--on">12</span>
								<span class="kc3-time">14</span>
							</div>
						</div>
					</div>
					<div class="kc4-item">
						<span class="kc4-label">Oplysninger</span>
					</div>
				</div>
			</article>

			<!-- 5. Calendar grid -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">05</span>
					<div>
						<h2>Kalender-grid</h2>
						<p>Rigtig månedskalender med ledighed.</p>
					</div>
				</header>
				<div class="kc-frame">
					<div class="kc5-calhead">
						<span>‹</span>
						<span>April 2026</span>
						<span>›</span>
					</div>
					<div class="kc5-weeknames">
						<span>M</span><span>T</span><span>O</span><span>T</span><span>F</span><span>L</span><span>S</span>
					</div>
					<div class="kc5-days">
						<?php for ( $d = 1; $d <= 30; $d++ ) {
							$cls = 'kc5-day';
							if ( in_array( $d, array( 5, 12, 19, 26, 6, 13, 20, 27 ), true ) ) { $cls .= ' kc5-day--off'; }
							if ( 19 === $d ) { $cls .= ' kc5-day--on'; }
							if ( in_array( $d, array( 22, 28 ), true ) ) { $cls .= ' kc5-day--busy'; }
							echo '<span class="' . esc_attr( $cls ) . '">' . $d . '</span>';
						} ?>
					</div>
				</div>
			</article>

			<!-- 6. Chat-flow -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">06</span>
					<div>
						<h2>Samtale-flow</h2>
						<p>Spørgsmål i bobler, ét ad gangen.</p>
					</div>
				</header>
				<div class="kc-frame kc6-frame">
					<div class="kc6-bub kc6-bub--us">Hej! Hvornår vil du optage?</div>
					<div class="kc6-bub kc6-bub--them">Ti 19. april · 12–16</div>
					<div class="kc6-bub kc6-bub--us">Perfekt. Hvilket setup?</div>
					<div class="kc6-typing">
						<span></span><span></span><span></span>
					</div>
				</div>
			</article>

			<!-- 7. Side A / B -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">07</span>
					<div>
						<h2>Side A / Side B</h2>
						<p>Studie-booking eller udstyrs-leje.</p>
					</div>
				</header>
				<div class="kc-frame">
					<div class="kc7-split">
						<div class="kc7-side kc7-side--a">
							<span class="kc-eyebrow">Side A</span>
							<h3><em>Book studiet</em></h3>
							<span class="kc-arrow">→</span>
						</div>
						<div class="kc7-side kc7-side--b">
							<span class="kc-eyebrow">Side B</span>
							<h3><em>Lej udstyr</em></h3>
							<span class="kc-arrow">→</span>
						</div>
					</div>
				</div>
			</article>

			<!-- 8. Produkt-hylde + kurv -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">08</span>
					<div>
						<h2>Produkt-hylde</h2>
						<p>Vælg flere items, ligesom en shop-kurv.</p>
					</div>
				</header>
				<div class="kc-frame">
					<div class="kc8-grid">
						<div class="kc8-item kc8-item--on">Sony FS6 <span>+</span></div>
						<div class="kc8-item">Canon R5 <span>+</span></div>
						<div class="kc8-item kc8-item--on">Lyd-kit <span>+</span></div>
						<div class="kc8-item">Lyspakke <span>+</span></div>
					</div>
					<div class="kc8-cart">
						<span>2 varer · 2.400 kr / dag</span>
						<button class="kc-btn kc-btn--primary">Book →</button>
					</div>
				</div>
			</article>

			<!-- 9. Terminal -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">09</span>
					<div>
						<h2>Terminal</h2>
						<p>Monospace, command-line-æstetik.</p>
					</div>
				</header>
				<div class="kc-frame kc9-frame">
					<div class="kc9-dots"><span></span><span></span><span></span></div>
					<div class="kc9-line"><span class="kc9-prompt">$</span> s247 book</div>
					<div class="kc9-line kc9-muted">→ dato:     <span class="kc9-val">2026-04-19</span></div>
					<div class="kc9-line kc9-muted">→ start:    <span class="kc9-val">12:00</span></div>
					<div class="kc9-line kc9-muted">→ timer:    <span class="kc9-val">4</span></div>
					<div class="kc9-line kc9-muted">→ produkt:  <span class="kc9-val">podcast-setup</span></div>
					<div class="kc9-line"><span class="kc9-prompt">$</span> confirm_<span class="kc9-caret"></span></div>
				</div>
			</article>

			<!-- 10. Floating glass over hero -->
			<article class="kc-card">
				<header class="kc-card__head">
					<span class="kc-card__num">10</span>
					<div>
						<h2>Glas over hero-video</h2>
						<p>Form som flydende glaskort.</p>
					</div>
				</header>
				<div class="kc-frame kc10-frame">
					<div class="kc10-glass">
						<span class="kc-eyebrow">Reservér</span>
						<h3>Hvornår skal vi klar?</h3>
						<div class="kc-input kc-input--dark">📅 Vælg dato</div>
						<div class="kc-input kc-input--dark">🕐 Vælg tid</div>
						<button class="kc-btn kc-btn--accent">Book nu →</button>
					</div>
				</div>
			</article>

		</div>
	</div>
</section>

<style>
.kc-page { padding: var(--sp-16) 0; background: var(--color-bg); }
.kc-page__head { max-width: 720px; margin-bottom: var(--sp-12); }
.kc-page__lead { color: var(--color-ink-soft); font-size: var(--fs-md); line-height: 1.5; margin-top: var(--sp-3); }

.kc-grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: var(--sp-8);
}
@media (min-width: 900px) { .kc-grid { grid-template-columns: repeat(2, 1fr); } }

.kc-card {
	background: #FFF;
	border: 1px solid var(--color-border);
	border-radius: var(--radius-lg);
	overflow: hidden;
	display: flex;
	flex-direction: column;
}

.kc-card__head {
	display: flex;
	align-items: center;
	gap: var(--sp-4);
	padding: var(--sp-5) var(--sp-6);
	border-bottom: 1px solid var(--color-border);
	background: var(--color-surface);
}

.kc-card__num {
	font-family: var(--font-serif);
	font-style: italic;
	font-weight: 400;
	font-size: 1.5rem;
	color: var(--color-accent);
	min-width: 40px;
}

.kc-card__head h2 {
	font-family: var(--font-sans);
	font-size: 1.1rem;
	font-weight: 700;
	letter-spacing: -0.01em;
	margin: 0;
}

.kc-card__head p {
	font-size: var(--fs-sm);
	color: var(--color-ink-mute);
	margin: 2px 0 0;
}

.kc-frame {
	padding: var(--sp-6);
	display: flex;
	flex-direction: column;
	gap: var(--sp-4);
	min-height: 340px;
	position: relative;
}

.kc-eyebrow {
	font-family: var(--font-mono);
	font-size: 10px;
	text-transform: uppercase;
	letter-spacing: 0.18em;
	color: var(--color-accent);
}

.kc-input {
	padding: 10px 14px;
	background: var(--color-surface);
	border: 1px solid var(--color-border);
	border-radius: 8px;
	font-size: 13px;
	color: var(--color-ink-mute);
}

.kc-input--dark {
	background: rgba(244, 233, 221, 0.12);
	border-color: rgba(244, 233, 221, 0.2);
	color: var(--s247-bone-80);
}

.kc-btn {
	align-self: flex-start;
	padding: 9px 16px;
	border-radius: 8px;
	font-family: var(--font-sans);
	font-weight: 700;
	font-size: 12px;
	letter-spacing: 0.02em;
	border: 1px solid transparent;
	cursor: pointer;
}

.kc-btn--primary { background: var(--color-ink); color: var(--s247-bone); border-color: var(--color-ink); }
.kc-btn--accent  { background: var(--color-accent); color: var(--s247-bone); border-color: var(--color-accent); }
.kc-btn--ghost   { background: transparent; color: var(--color-ink); border-color: var(--color-border); }

.kc-actions { display: flex; justify-content: space-between; margin-top: var(--sp-2); }

/* 1 — Wizard */
.kc1-progress { display: flex; align-items: center; gap: 4px; margin-bottom: var(--sp-4); }
.kc1-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--color-border); }
.kc1-dot.is-done { background: var(--color-accent); }
.kc1-dot.is-active { background: var(--color-ink); box-shadow: 0 0 0 3px rgba(40,40,40,0.12); }
.kc1-bar { flex: 1; height: 2px; background: var(--color-border); }
.kc1-bar.is-done { background: var(--color-accent); }
.kc1-num { font-family: var(--font-serif); font-style: italic; color: var(--color-accent); font-size: 1.25rem; }
.kc1-step h3 { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; margin: 4px 0 var(--sp-4); }

/* 2 — Magazine-split */
.kc2-split { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-radius: 12px; overflow: hidden; border: 1px solid var(--color-border); }
.kc2-media {
	background:
		radial-gradient(ellipse at 30% 40%, rgba(158, 43, 37, 0.4) 0%, transparent 60%),
		linear-gradient(135deg, #282828 0%, #1e1e1e 100%);
	min-height: 240px;
}
.kc2-form { padding: var(--sp-5); display: flex; flex-direction: column; gap: var(--sp-3); background: var(--color-bg); }
.kc2-form h3 { font-family: var(--font-sans); font-size: 1.15rem; font-weight: 700; letter-spacing: -0.01em; margin: 0; }

/* 3 — Pille-tidslinje */
.kc3-strip { display: flex; gap: 6px; overflow: hidden; padding: 2px 0; }
.kc3-pill {
	padding: 8px 14px;
	border: 1px solid var(--color-border);
	border-radius: 999px;
	font-size: 12px;
	font-weight: 600;
	white-space: nowrap;
}
.kc3-pill--on { background: var(--color-ink); color: var(--s247-bone); border-color: var(--color-ink); }
.kc3-pill--off { opacity: 0.35; text-decoration: line-through; }

.kc3-times { display: flex; gap: 6px; flex-wrap: wrap; }
.kc3-time {
	width: 44px; height: 44px;
	display: grid; place-items: center;
	border: 1px solid var(--color-border);
	border-radius: 8px;
	font-family: var(--font-mono);
	font-size: 13px;
	font-weight: 600;
}
.kc3-time--on { background: var(--color-accent); color: var(--s247-bone); border-color: var(--color-accent); }

/* 4 — Accordion */
.kc4-item {
	padding: 14px 16px;
	border: 1px solid var(--color-border);
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: var(--sp-3);
	font-size: 13px;
}
.kc4-item--done { background: var(--color-surface); }
.kc4-item--active { border-color: var(--color-ink); flex-direction: column; align-items: stretch; gap: var(--sp-3); }
.kc4-label { font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--color-accent); }
.kc4-val { font-weight: 700; }
.kc4-edit { font-size: 11px; color: var(--color-ink-mute); text-decoration: underline; }
.kc4-body { }

/* 5 — Kalender */
.kc5-calhead {
	display: flex; justify-content: space-between; align-items: center;
	font-family: var(--font-sans); font-weight: 700; font-size: 14px;
	padding-bottom: var(--sp-3);
	border-bottom: 1px solid var(--color-border);
}
.kc5-weeknames, .kc5-days {
	display: grid;
	grid-template-columns: repeat(7, 1fr);
	gap: 4px;
	font-size: 11px;
}
.kc5-weeknames span {
	color: var(--color-ink-mute);
	text-align: center;
	padding: 6px 0;
}
.kc5-day {
	aspect-ratio: 1;
	display: grid; place-items: center;
	border-radius: 6px;
	font-family: var(--font-mono);
	font-weight: 600;
	cursor: pointer;
}
.kc5-day:hover { background: var(--color-surface); }
.kc5-day--off { color: var(--color-ink-mute); opacity: 0.4; text-decoration: line-through; cursor: not-allowed; }
.kc5-day--busy { background: rgba(40,40,40,0.06); }
.kc5-day--on { background: var(--color-accent); color: var(--s247-bone); }

/* 6 — Chat */
.kc6-frame { background: var(--color-surface); }
.kc6-bub {
	padding: 12px 16px;
	border-radius: 16px;
	font-size: 13px;
	max-width: 80%;
	line-height: 1.4;
}
.kc6-bub--us { background: var(--color-ink); color: var(--s247-bone); align-self: flex-start; border-bottom-left-radius: 4px; }
.kc6-bub--them { background: #FFF; border: 1px solid var(--color-border); align-self: flex-end; border-bottom-right-radius: 4px; }
.kc6-typing { display: flex; gap: 4px; padding: 12px 16px; background: var(--color-ink); border-radius: 16px; border-bottom-left-radius: 4px; align-self: flex-start; }
.kc6-typing span {
	width: 6px; height: 6px; background: var(--s247-bone-60); border-radius: 50%;
	animation: kc-blink 1.2s infinite;
}
.kc6-typing span:nth-child(2) { animation-delay: 0.2s; }
.kc6-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes kc-blink { 0%, 60%, 100% { opacity: 0.2; } 30% { opacity: 1; } }

/* 7 — Side A / B */
.kc7-split {
	display: grid; grid-template-columns: 1fr 1fr; gap: 2px;
	border-radius: 12px; overflow: hidden;
	min-height: 220px;
}
.kc7-side {
	padding: var(--sp-6);
	display: flex; flex-direction: column; justify-content: space-between;
	min-height: 220px;
}
.kc7-side--a { background: #FFF; color: var(--color-ink); }
.kc7-side--b { background: var(--color-ink); color: var(--s247-bone); }
.kc7-side h3 { font-size: 1.35rem; font-weight: 700; margin: var(--sp-3) 0 0; }
.kc7-side h3 em { font-family: var(--font-serif); font-style: italic; font-weight: 400; color: var(--color-accent); }
.kc-arrow { font-size: 1.5rem; color: var(--color-accent); align-self: flex-end; }

/* 8 — Produkt-hylde */
.kc8-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
.kc8-item {
	padding: 12px 14px;
	border: 1px solid var(--color-border);
	border-radius: 8px;
	display: flex; justify-content: space-between; align-items: center;
	font-size: 13px; font-weight: 600;
	cursor: pointer;
}
.kc8-item span { color: var(--color-accent); font-weight: 700; }
.kc8-item--on { background: var(--color-accent); color: var(--s247-bone); border-color: var(--color-accent); }
.kc8-item--on span { color: var(--s247-bone); }
.kc8-cart {
	display: flex; justify-content: space-between; align-items: center;
	padding: 12px 14px;
	background: var(--color-surface);
	border-radius: 8px;
	font-size: 13px; font-weight: 600;
	margin-top: var(--sp-2);
}

/* 9 — Terminal */
.kc9-frame { background: var(--s247-black-rich); color: var(--s247-bone); font-family: var(--font-mono); }
.kc9-dots { display: flex; gap: 6px; padding-bottom: var(--sp-3); border-bottom: 1px solid rgba(244,233,221,0.1); }
.kc9-dots span { width: 10px; height: 10px; border-radius: 50%; background: rgba(244,233,221,0.2); }
.kc9-dots span:first-child { background: #ff5f57; }
.kc9-dots span:nth-child(2) { background: #febc2e; }
.kc9-dots span:last-child { background: #28c840; }
.kc9-line { font-size: 12px; line-height: 1.7; }
.kc9-muted { color: rgba(244,233,221,0.7); }
.kc9-prompt { color: var(--color-accent); font-weight: 700; margin-right: 6px; }
.kc9-val { color: var(--color-accent); }
.kc9-caret { display: inline-block; width: 7px; height: 13px; background: var(--color-accent); vertical-align: -2px; animation: kc-caret 1s infinite; }
@keyframes kc-caret { 50% { opacity: 0; } }

/* 10 — Glas over video */
.kc10-frame {
	background:
		radial-gradient(ellipse at 70% 30%, rgba(158,43,37,0.5) 0%, transparent 50%),
		linear-gradient(135deg, #1e1e1e 0%, #282828 100%);
	padding: var(--sp-8);
}
.kc10-glass {
	background: rgba(244, 233, 221, 0.1);
	backdrop-filter: blur(22px);
	-webkit-backdrop-filter: blur(22px);
	border: 1px solid rgba(244, 233, 221, 0.2);
	border-radius: 14px;
	padding: var(--sp-5);
	color: var(--s247-bone);
	display: flex; flex-direction: column; gap: var(--sp-3);
}
.kc10-glass h3 { font-family: var(--font-sans); font-size: 1.1rem; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
.kc10-glass .kc-eyebrow { color: var(--s247-bone-60); }
</style>

<?php get_footer(); ?>
