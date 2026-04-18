/**
 * Studie 247 — vanilla JS.
 * Sticky header, mobile menu, smooth scroll, scroll reveals.
 */
(function () {
	'use strict';

	// ──────────────────────────────────────────────
	// Floating pill header — hidden at top, slides in on scroll
	// ──────────────────────────────────────────────
	const header = document.querySelector('[data-site-header]');
	if (header) {
		const showThreshold = 320;   // px scrolled before pill slides in
		let lastShown = false;
		const update = () => {
			const shouldShow = window.scrollY > showThreshold;
			if (shouldShow !== lastShown) {
				header.classList.toggle('site-header--visible', shouldShow);
				lastShown = shouldShow;
			}
		};
		update();
		window.addEventListener('scroll', update, { passive: true });
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
	// Studio moods — tab switcher
	// ──────────────────────────────────────────────
	document.querySelectorAll('[data-moods]').forEach((root) => {
		const tabs   = root.querySelectorAll('[data-mood-target]');
		const panels = root.querySelectorAll('[data-mood-panel]');
		tabs.forEach((tab) => {
			tab.addEventListener('click', () => {
				const target = tab.dataset.moodTarget;
				tabs.forEach((t) => {
					const active = t.dataset.moodTarget === target;
					t.classList.toggle('is-active', active);
					t.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				panels.forEach((p) => {
					const active = p.dataset.moodPanel === target;
					p.classList.toggle('is-active', active);
					if (active) { p.removeAttribute('hidden'); } else { p.setAttribute('hidden', ''); }
				});
			});
		});
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

		const fmtDate = (iso) => {
			const d = new Date(iso + 'T00:00:00');
			const days = ['søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag'];
			const months = ['januar','februar','marts','april','maj','juni','juli','august','september','oktober','november','december'];
			return `${days[d.getDay()]} ${d.getDate()}. ${months[d.getMonth()]}`;
		};

		const checkReady = () => {
			const ready = dateIn.value && timeIn.value && durIn.value;
			if (submit) submit.disabled = !ready;
			if (hint) hint.textContent = ready
				? 'Klar — tjek opsummeringen og udfyld dine oplysninger.'
				: 'Vælg dato, tid og varighed for at fortsætte.';
		};

		bookPicker.querySelectorAll('.book2__day[data-date]').forEach((d) => {
			d.addEventListener('click', () => {
				bookPicker.querySelectorAll('.book2__day.is-selected').forEach((x) => x.classList.remove('is-selected'));
				d.classList.add('is-selected');
				const iso = d.dataset.date;
				if (dateIn) dateIn.value = iso;
				if (sumDate) { sumDate.textContent = fmtDate(iso); sumDate.dataset.filled = '1'; }
				if (picked) picked.textContent = 'Valgt: ' + fmtDate(iso);
				if (slots) slots.hidden = false;
				checkReady();
			});
		});

		bookPicker.querySelectorAll('.book2__time').forEach((t) => {
			t.addEventListener('click', () => {
				bookPicker.querySelectorAll('.book2__time.is-selected').forEach((x) => x.classList.remove('is-selected'));
				t.classList.add('is-selected');
				if (timeIn) timeIn.value = t.dataset.time;
				if (sumTime) { sumTime.textContent = t.dataset.time; sumTime.dataset.filled = '1'; }
				checkReady();
			});
		});

		bookPicker.querySelectorAll('.book2__dur').forEach((d) => {
			d.addEventListener('click', () => {
				bookPicker.querySelectorAll('.book2__dur.is-selected').forEach((x) => x.classList.remove('is-selected'));
				d.classList.add('is-selected');
				if (durIn) durIn.value = d.dataset.duration;
				if (sumDur) { sumDur.textContent = d.dataset.duration; sumDur.dataset.filled = '1'; }
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
})();
