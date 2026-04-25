/**
 * Studie 247 — vanilla JS.
 * Sticky header, mobile menu, smooth scroll, scroll reveals.
 */
(function () {
	'use strict';

	// ──────────────────────────────────────────────
	// Header: glider ind ved første scroll og bliver synlig (one-way).
	// ──────────────────────────────────────────────
	const header = document.querySelector('[data-site-header]');
	if (header) {
		const showAt = 20;
		let shown = false;
		const onScroll = () => {
			if (!shown && window.scrollY > showAt) {
				header.classList.add('site-header--visible');
				shown = true;
				window.removeEventListener('scroll', onScroll);
			}
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	// ──────────────────────────────────────────────
	// Mobile nav toggle
	// ──────────────────────────────────────────────
	const navToggles = document.querySelectorAll('[data-nav-toggle]');
	const navCloses  = document.querySelectorAll('[data-nav-close]');
	const mobileNav  = document.getElementById('mobile-nav');

	const closeNav = () => {
		if (!mobileNav) return;
		mobileNav.dataset.open = 'false';
		mobileNav.setAttribute('aria-hidden', 'true');
		navToggles.forEach((btn) => btn.setAttribute('aria-expanded', 'false'));
		document.documentElement.style.overflow = '';
	};

	const openNav = () => {
		if (!mobileNav) return;
		mobileNav.dataset.open = 'true';
		mobileNav.setAttribute('aria-hidden', 'false');
		navToggles.forEach((btn) => btn.setAttribute('aria-expanded', 'true'));
		document.documentElement.style.overflow = 'hidden';
	};

	navToggles.forEach((btn) => btn.addEventListener('click', openNav));
	navCloses.forEach((btn) => btn.addEventListener('click', closeNav));

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') closeNav();
	});

	mobileNav?.querySelectorAll('a').forEach((a) => {
		a.addEventListener('click', closeNav);
	});

	// ──────────────────────────────────────────────
	// Studio moods — stillbillede-slider med glide-transition
	// ──────────────────────────────────────────────
	document.querySelectorAll('[data-moods]').forEach((root) => {
		const track = root.querySelector('[data-moods-track]');
		const tabs  = root.querySelectorAll('[data-mood-target]');
		const prev  = root.querySelector('[data-moods-prev]');
		const next  = root.querySelector('[data-moods-next]');
		const count = parseInt(root.dataset.moodsCount || '0', 10);
		if (!track || count < 1) return;

		let current = 0;
		let autoTimer = null;
		const autoDelay = 5000;

		// Full-variant = fuld-bredde med peek: slide 92vw + 2vw gap = 94vw step.
		// Default-variant = track fylder stage, 100% step.
		const isFull = root.classList.contains('moods__panel--full');
		const step   = isFull ? '94vw' : '100%';

		const goto = (i) => {
			current = (i + count) % count;
			track.style.transform = 'translateX(calc(-' + current + ' * ' + step + '))';
			tabs.forEach((t) => {
				const active = parseInt(t.dataset.moodTarget, 10) === current;
				t.classList.toggle('is-active', active);
				if (t.hasAttribute('role')) t.setAttribute('aria-selected', active ? 'true' : 'false');
			});
		};

		const resetAuto = () => {
			if (autoTimer) clearInterval(autoTimer);
			autoTimer = setInterval(() => goto(current + 1), autoDelay);
		};

		tabs.forEach((t) => t.addEventListener('click', () => {
			goto(parseInt(t.dataset.moodTarget, 10));
			resetAuto();
		}));
		if (prev) prev.addEventListener('click', () => { goto(current - 1); resetAuto(); });
		if (next) next.addEventListener('click', () => { goto(current + 1); resetAuto(); });

		// Pause auto når bruger hover'er, genoptag når de forlader
		root.addEventListener('mouseenter', () => { if (autoTimer) clearInterval(autoTimer); });
		root.addEventListener('mouseleave', resetAuto);

		// Tastatur-nav (pile-taster når slideren har fokus)
		root.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowLeft')  { goto(current - 1); resetAuto(); }
			if (e.key === 'ArrowRight') { goto(current + 1); resetAuto(); }
		});

		goto(0);
		resetAuto();
	});

	// ──────────────────────────────────────────────
	// Team modal (Om-side)
	// ──────────────────────────────────────────────
	const teamOpeners = document.querySelectorAll('[data-team-open]');
	const teamModals  = document.querySelectorAll('[data-team-modal]');

	const closeTeamModal = () => {
		teamModals.forEach((m) => m.setAttribute('hidden', ''));
		document.body.classList.remove('team-modal-open');
	};

	teamOpeners.forEach((btn) => {
		btn.addEventListener('click', () => {
			const id = btn.dataset.teamOpen;
			teamModals.forEach((m) => {
				if (m.dataset.teamModal === id) {
					m.removeAttribute('hidden');
				} else {
					m.setAttribute('hidden', '');
				}
			});
			document.body.classList.add('team-modal-open');
			const dialog = document.querySelector('[data-team-modal="' + id + '"] .team-modal__close');
			dialog && dialog.focus();
		});
	});

	document.querySelectorAll('[data-team-close]').forEach((el) => {
		el.addEventListener('click', closeTeamModal);
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') closeTeamModal();
	});

	// ──────────────────────────────────────────────
	// Kontakt — Vælg side (radiogruppe)
	// ──────────────────────────────────────────────
	const sideButtons = document.querySelectorAll('.side[data-side]');
	const sideInput   = document.querySelector('[data-side-input]');
	sideButtons.forEach((btn) => {
		btn.addEventListener('click', () => {
			sideButtons.forEach((b) => {
				const on = b === btn;
				b.classList.toggle('is-selected', on);
				b.setAttribute('aria-checked', on ? 'true' : 'false');
			});
			if (sideInput) sideInput.value = btn.dataset.side;
		});
	});

	// ──────────────────────────────────────────────
	// Kontakt os — multi-step flow
	// ──────────────────────────────────────────────
	const koFlow = document.querySelector('[data-ko-flow]');
	if (koFlow) {
		const steps = koFlow.querySelectorAll('[data-ko-step]');
		const dots  = koFlow.querySelectorAll('[data-ko-dot]');
		const topicInput = koFlow.querySelector('[data-ko-topic-input]');

		const show = (n) => {
			steps.forEach((s) => {
				const active = s.dataset.koStep === String(n);
				s.classList.toggle('is-active', active);
				if (active) { s.removeAttribute('hidden'); } else { s.setAttribute('hidden', ''); }
			});
			dots.forEach((d) => d.classList.toggle('is-active', d.dataset.koDot === String(n)));
		};

		koFlow.querySelectorAll('[data-ko-next]').forEach((b) => {
			b.addEventListener('click', () => {
				const target = b.dataset.koNext;
				const currentStep = b.closest('[data-ko-step]');
				const required = currentStep.querySelectorAll('[required]');
				for (const el of required) {
					if (!el.checkValidity()) { el.reportValidity(); return; }
				}
				show(target);
			});
		});

		koFlow.querySelectorAll('[data-ko-prev]').forEach((b) => {
			b.addEventListener('click', () => show(b.dataset.koPrev));
		});

		koFlow.querySelectorAll('[data-ko-topic]').forEach((t) => {
			t.addEventListener('click', () => {
				koFlow.querySelectorAll('[data-ko-topic]').forEach((x) => {
					x.classList.remove('is-selected');
					x.setAttribute('aria-checked', 'false');
				});
				t.classList.add('is-selected');
				t.setAttribute('aria-checked', 'true');
				if (topicInput) topicInput.value = t.dataset.koTopic;
			});
		});
	}

	// ──────────────────────────────────────────────
	// Book — kalender-grid step flow
	// ──────────────────────────────────────────────
	const bookPicker = document.querySelector('[data-book-picker]');
	if (bookPicker) {
		const slots    = bookPicker.querySelector('[data-book-slots]');
		const picked   = bookPicker.querySelector('[data-book-picked]');
		const form     = document.querySelector('[data-book-form]');
		const dateIn   = form?.querySelector('[data-field-date]');
		const timeIn   = form?.querySelector('[data-field-time]');
		const durIn    = form?.querySelector('[data-field-duration]');
		const sumDate  = document.querySelector('[data-sum-date]');
		const sumTime  = document.querySelector('[data-sum-time]');
		const sumDur   = document.querySelector('[data-sum-duration]');
		const submit   = form?.querySelector('[data-book-submit]');
		const hint     = form?.querySelector('[data-book-hint]');

		let bookedMap = {};
		try { bookedMap = JSON.parse(bookPicker.dataset.booked || '{}'); } catch (e) {}

		const applyBookedFor = (iso) => {
			const blocked = bookedMap[iso] || [];
			bookPicker.querySelectorAll('.book2__time').forEach((t) => {
				const h = parseInt(t.dataset.time, 10);
				const isBlocked = blocked.includes(h);
				t.classList.toggle('is-disabled', isBlocked);
				if (isBlocked && t.classList.contains('is-selected')) {
					t.classList.remove('is-selected');
					if (timeIn) timeIn.value = '';
					if (sumTime) { sumTime.textContent = '—'; delete sumTime.dataset.filled; }
				}
			});
		};

		const fmtDate = (iso) => {
			const d = new Date(iso + 'T00:00:00');
			const days = ['søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag'];
			const months = ['januar','februar','marts','april','maj','juni','juli','august','september','oktober','november','december'];
			return `${days[d.getDay()]} ${d.getDate()}. ${months[d.getMonth()]}`;
		};

		const isProductMode = bookPicker.dataset.bookMode === 'product';
		const priceDay      = parseInt(bookPicker.dataset.priceDay  || '0', 10);
		const priceWeek     = parseInt(bookPicker.dataset.priceWeek || '0', 10);
		const sumPrice      = document.querySelector('[data-sum-price]');

		let studioPrices = {};
		let studioHours  = {};
		try { studioPrices = JSON.parse(bookPicker.dataset.studioPrices || '{}'); } catch (e) {}
		try { studioHours  = JSON.parse(bookPicker.dataset.studioHours  || '{}'); } catch (e) {}

		// Multiplikator-tabel matcher PHP (page-booking-studie.php).
		const rentalTable = {
			'1 dag':  { base: 'dag', mult: 1   },
			'2 dage': { base: 'dag', mult: 2   },
			'3 dage': { base: 'dag', mult: 3   },
			'4 dage': { base: 'dag', mult: 3.5 },
			'1 uge':  { base: 'uge', mult: 1   },
			'2 uger': { base: 'uge', mult: 1.5 },
		};

		const formatDKK = (n) => Math.round(n).toLocaleString('da-DK') + ' kr';

		// Aftenpris-tillæg: 200 kr pr. påbegyndt time i tidsrummet 20:00–08:00.
		const AFTER_HOURS_RATE = 200;
		const AFTER_HOURS_EVENING = 20;
		const AFTER_HOURS_MORNING = 8;
		const studioPriceHours = { '6 timer': 6, '12 timer': 12 };
		const sumLateEl  = document.querySelector('[data-sum-late]');
		const sumLateRow = document.querySelector('[data-sum-late-row]');

		const countAfterHours = (startH, durH) => {
			let c = 0;
			for (let i = 0; i < durH; i++) {
				const hod = (startH + i) % 24;
				if (hod >= AFTER_HOURS_EVENING || hod < AFTER_HOURS_MORNING) c++;
			}
			return c;
		};

		const updatePrice = () => {
			if (!sumPrice) return;
			let base = 0;
			let lateFee = 0;
			let lateHours = 0;
			if (isProductMode) {
				const row = rentalTable[durIn.value];
				if (row) {
					const basePrice = row.base === 'uge' ? priceWeek : priceDay;
					if (basePrice) base = basePrice * row.mult;
				}
			} else {
				if (durIn.value && studioPrices[durIn.value]) {
					base = studioPrices[durIn.value];
				}
				const startH = timeIn && timeIn.value ? parseInt(timeIn.value.slice(0, 2), 10) : null;
				const durH   = studioPriceHours[durIn.value] || 0;
				if (startH !== null && durH > 0) {
					lateHours = countAfterHours(startH, durH);
					lateFee   = lateHours * AFTER_HOURS_RATE;
				}
			}
			const total = base + lateFee;
			if (!total) { sumPrice.textContent = '—'; delete sumPrice.dataset.filled; }
			else { sumPrice.textContent = formatDKK(total); sumPrice.dataset.filled = '1'; }

			if (sumLateEl && sumLateRow) {
				if (lateHours > 0) {
					sumLateEl.textContent = `${lateHours} t × ${AFTER_HOURS_RATE} kr`;
					sumLateRow.removeAttribute('hidden');
				} else {
					sumLateEl.textContent = '—';
					sumLateRow.setAttribute('hidden', '');
				}
			}
		};

		/**
		 * Disabler varigheds-knapper der ville overlappe med eksisterende
		 * bookinger på den valgte dato (kun studie-mode).
		 */
		const addDays = (iso, n) => {
			const d = new Date(iso + 'T00:00:00');
			d.setDate(d.getDate() + n);
			return d.toISOString().slice(0, 10);
		};

		const applyDurationConstraints = () => {
			if (isProductMode) return;
			const iso = dateIn.value;
			const startH = timeIn.value ? parseInt(timeIn.value.slice(0, 2), 10) : null;
			bookPicker.querySelectorAll('.book2__dur').forEach((btn) => {
				const dur  = btn.dataset.duration;
				const need = studioHours[dur] || 0;
				let wouldOverlap = false;
				if (startH !== null && need > 0 && iso) {
					// Tjek hver påtænkt time-slot mod den korrekte dato (wrapper midnat).
					for (let i = 0; i < need; i++) {
						const abs = startH + i;
						const dayOff = Math.floor(abs / 24);
						const hod    = abs % 24;
						const target = dayOff === 0 ? iso : addDays(iso, dayOff);
						const blocked = bookedMap[target] || [];
						if (blocked.includes(hod)) { wouldOverlap = true; break; }
					}
				}
				btn.classList.toggle('is-disabled', wouldOverlap);
				btn.disabled = wouldOverlap;
				if (wouldOverlap && btn.classList.contains('is-selected')) {
					btn.classList.remove('is-selected');
					if (durIn) durIn.value = '';
					if (sumDur) { sumDur.textContent = '—'; delete sumDur.dataset.filled; }
					updatePrice();
				}
			});
		};

		// ─── Studie-mode: formål-dropdown styrer betinget synlige felter ───
		const useType        = form?.querySelector('[data-use-type]');
		const useOptions     = form?.querySelector('[data-use-options]');
		const editTypeInputs = form?.querySelectorAll('[data-edit-type]') || [];
		const panels = {
			podcast:           form?.querySelector('[data-panel="podcast"]'),
			podcastEditing:    form?.querySelector('[data-panel="podcast-editing"]'),
			videoEditing:      form?.querySelector('[data-panel="video-editing"]'),
			someFormat:        form?.querySelector('[data-panel="some-format"]'),
		};
		const videoEditingTypes = ['kursusvideo', 'undervisningsvideo', 'some-content', 'annonce-video'];

		const currentEdit = () => form?.querySelector('[data-edit-type]:checked')?.value || '';

		const updatePanels = () => {
			const t    = useType?.value || '';
			const edit = currentEdit();
			if (useOptions) useOptions.hidden = !t;
			// Podcast-type (Lyd/Video) vises KUN ved Podcast + Redigering.
			if (panels.podcast)        panels.podcast.hidden        = !(t === 'podcast' && edit === 'redigering');
			if (panels.podcastEditing) panels.podcastEditing.hidden = !(t === 'podcast' && edit === 'redigering');
			if (panels.videoEditing)   panels.videoEditing.hidden   = !(videoEditingTypes.includes(t) && edit === 'redigering');
			if (panels.someFormat)     panels.someFormat.hidden     = !(t === 'some-content' && edit === 'redigering');
		};

		const videoCount     = form?.querySelector('[data-video-count]');
		const videoCountOut  = form?.querySelector('[data-video-count-out]');
		const videoDur       = form?.querySelector('[data-video-duration]');
		const videoDurOut    = form?.querySelector('[data-video-duration-out]');
		if (videoCount && videoCountOut) {
			videoCount.addEventListener('input', () => { videoCountOut.textContent = videoCount.value; });
		}
		if (videoDur && videoDurOut) {
			videoDur.addEventListener('input', () => { videoDurOut.textContent = videoDur.value + ' min'; });
		}

		const checkReady = () => {
			let ready = dateIn.value && timeIn.value && durIn.value;
			if (!isProductMode && useType) ready = ready && !!useType.value;
			if (submit) submit.disabled = !ready;
			if (hint) hint.textContent = ready
				? 'Klar — tjek opsummeringen og udfyld dine oplysninger.'
				: (isProductMode
					? 'Vælg start-dato og varighed for at fortsætte.'
					: 'Vælg formål, dato, tid og varighed for at fortsætte.');
		};

		if (useType) {
			useType.addEventListener('change', () => { updatePanels(); checkReady(); });
			// Hvis use_type er prefilled via ?use_type= (fra /studiet/), åbn panels med det samme.
			if (useType.value) { updatePanels(); }
		}
		editTypeInputs.forEach((i) => i.addEventListener('change', updatePanels));

		bookPicker.querySelectorAll('.book2__day[data-date]').forEach((d) => {
			d.addEventListener('click', () => {
				bookPicker.querySelectorAll('.book2__day.is-selected').forEach((x) => x.classList.remove('is-selected'));
				d.classList.add('is-selected');
				const iso = d.dataset.date;
				if (dateIn) dateIn.value = iso;
				if (sumDate) { sumDate.textContent = fmtDate(iso); sumDate.dataset.filled = '1'; }
				if (picked) picked.textContent = 'Valgt: ' + fmtDate(iso);
				if (slots) slots.hidden = false;
				applyBookedFor(iso);
				applyDurationConstraints();
				checkReady();
			});
		});

		bookPicker.querySelectorAll('.book2__time').forEach((t) => {
			t.addEventListener('click', () => {
				if (t.disabled || t.classList.contains('is-disabled')) return;
				bookPicker.querySelectorAll('.book2__time.is-selected').forEach((x) => x.classList.remove('is-selected'));
				t.classList.add('is-selected');
				if (timeIn) timeIn.value = t.dataset.time;
				if (sumTime) { sumTime.textContent = t.dataset.time; sumTime.dataset.filled = '1'; }
				applyDurationConstraints();
				updatePrice();
				checkReady();
			});
		});

		bookPicker.querySelectorAll('.book2__dur').forEach((d) => {
			d.addEventListener('click', () => {
				if (d.disabled || d.classList.contains('is-disabled')) return;
				bookPicker.querySelectorAll('.book2__dur.is-selected').forEach((x) => x.classList.remove('is-selected'));
				d.classList.add('is-selected');
				if (durIn) durIn.value = d.dataset.duration;
				if (sumDur) { sumDur.textContent = d.dataset.duration; sumDur.dataset.filled = '1'; }
				updatePrice();
				checkReady();
			});
		});
	}

	// ──────────────────────────────────────────────
	// Studiet — reels click-to-play
	// ──────────────────────────────────────────────
	document.querySelectorAll('[data-reel]').forEach((reel) => {
		const video = reel.querySelector('[data-reel-video]');
		const btn   = reel.querySelector('[data-reel-play]');
		if (!video || !btn) return;

		const start = () => {
			video.muted = false;
			video.controls = true;
			video.play().catch(() => {
				// If unmuted play is blocked, fall back to muted.
				video.muted = true;
				video.play();
			});
			reel.classList.add('is-playing');
		};

		btn.addEventListener('click', start);
		video.addEventListener('click', () => {
			if (!reel.classList.contains('is-playing')) start();
		});
	});

	// ──────────────────────────────────────────────
	// Studiet — setup switcher
	// ──────────────────────────────────────────────
	document.querySelectorAll('[data-studiet-setups]').forEach((root) => {
		const tabs   = root.querySelectorAll('[data-setup-target]');
		const panels = root.querySelectorAll('[data-setup-panel]');
		tabs.forEach((tab) => {
			tab.addEventListener('click', () => {
				const target = tab.dataset.setupTarget;
				tabs.forEach((t) => {
					const active = t.dataset.setupTarget === target;
					t.classList.toggle('is-active', active);
					t.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				panels.forEach((p) => {
					const active = p.dataset.setupPanel === target;
					p.classList.toggle('is-active', active);
					if (active) { p.removeAttribute('hidden'); } else { p.setAttribute('hidden', ''); }
				});
			});
		});
	});

	// ──────────────────────────────────────────────
	// Smooth scroll for on-page anchor links
	// ──────────────────────────────────────────────
	document.querySelectorAll('a[href^="#"]').forEach((a) => {
		a.addEventListener('click', (e) => {
			const id = a.getAttribute('href');
			if (!id || id === '#') return;
			const target = document.querySelector(id);
			if (target) {
				e.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		});
	});

	// ──────────────────────────────────────────────
	// Scroll reveal — [data-reveal]
	// ──────────────────────────────────────────────
	const reveals = document.querySelectorAll('[data-reveal]');
	if (reveals.length && 'IntersectionObserver' in window) {
		const io = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
		reveals.forEach((el) => io.observe(el));
	} else {
		// Fallback: show everything immediately
		reveals.forEach((el) => el.classList.add('is-visible'));
	}
	// ──────────────────────────────────────────────
	// Count-up animation på stats — tik fra 0 til slut-værdi
	// når sektionen ruller ind i viewporten. Fungerer for tal som
	// "200", "4K", "340+", "4.500+", "96%", "24/7".
	// ──────────────────────────────────────────────
	const numEls = document.querySelectorAll('.studiet-stats__num');
	if (numEls.length && 'IntersectionObserver' in window) {
		const parseTarget = (raw) => {
			// Slash-tal (24/7) animeres ikke — returnér null = skip.
			if (raw.includes('/')) return null;
			// Saml alle cifre fra begyndelsen indtil første ikke-tal-tegn.
			// Respektér dansk tusind-separator "." (fx 4.500).
			const match = raw.match(/^([\d.,]+)(.*)$/);
			if (!match) return null;
			const numStr = match[1].replace(/\./g, '').replace(',', '.');
			const num    = parseFloat(numStr);
			if (isNaN(num)) return null;
			const suffix = match[2] || '';
			return { num, suffix, raw };
		};

		const formatDKK = (n) => Math.round(n).toLocaleString('da-DK');

		const animate = (el) => {
			const target = parseTarget(el.textContent.trim());
			if (!target) return;
			el.dataset.finalText = target.raw;
			const duration = 1400;
			const start = performance.now();
			const tick = (now) => {
				const t = Math.min(1, (now - start) / duration);
				// ease-out-cubic
				const eased = 1 - Math.pow(1 - t, 3);
				const val = Math.round(target.num * eased);
				el.textContent = (target.num >= 1000 ? formatDKK(val) : String(val)) + target.suffix;
				if (t < 1) requestAnimationFrame(tick);
				else el.textContent = target.raw; // Lås præcist på slut-værdien.
			};
			requestAnimationFrame(tick);
		};

		const countIO = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					animate(entry.target);
					countIO.unobserve(entry.target);
				}
			});
		}, { threshold: 0.4 });

		numEls.forEach((el) => {
			// Sæt startværdi til 0 inden IO trigger så brugeren ikke ser
			// endelige tal et kort sekund før animation.
			const target = parseTarget(el.textContent.trim());
			if (target) {
				el.dataset.finalText = target.raw;
				el.textContent = '0' + target.suffix;
			}
			countIO.observe(el);
		});
	}

	// ─── Rotator (udstyr i blog-sidebar) ──────────────────────
	document.querySelectorAll('[data-rotator]').forEach((root) => {
		const slides = Array.from(root.querySelectorAll('[data-rotator-slide]'));
		const dots   = Array.from(root.querySelectorAll('[data-rotator-dot]'));
		if (slides.length < 2) return;

		const interval = Math.max(1500, parseInt(root.dataset.rotatorInterval || '5000', 10));
		let idx = slides.findIndex((s) => s.classList.contains('is-active'));
		if (idx < 0) idx = 0;

		const show = (next) => {
			slides[idx].classList.remove('is-active');
			slides[idx].setAttribute('aria-hidden', 'true');
			if (dots[idx]) dots[idx].classList.remove('is-active');
			idx = (next + slides.length) % slides.length;
			slides[idx].classList.add('is-active');
			slides[idx].setAttribute('aria-hidden', 'false');
			if (dots[idx]) dots[idx].classList.add('is-active');
		};

		let timer = setInterval(() => show(idx + 1), interval);
		const stop  = () => { if (timer) { clearInterval(timer); timer = null; } };
		const start = () => { if (!timer) timer = setInterval(() => show(idx + 1), interval); };

		root.addEventListener('mouseenter', stop);
		root.addEventListener('mouseleave', start);
		document.addEventListener('visibilitychange', () => {
			if (document.hidden) stop(); else start();
		});

		dots.forEach((dot, i) => {
			dot.addEventListener('click', () => {
				show(i);
				stop(); start();
			});
		});
	});

})();
