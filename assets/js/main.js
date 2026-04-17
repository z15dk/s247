/**
 * Studie 247 — minimal vanilla JS.
 * Sticky header state, mobile menu, smooth scroll.
 */
(function () {
	'use strict';

	// ──────────────────────────────────────────────
	// Sticky header shadow on scroll
	// ──────────────────────────────────────────────
	const header = document.querySelector('[data-site-header]');
	if (header) {
		const setScrolled = () => {
			header.classList.toggle('site-header--scrolled', window.scrollY > 8);
		};
		setScrolled();
		window.addEventListener('scroll', setScrolled, { passive: true });
	}

	// ──────────────────────────────────────────────
	// Mobile nav toggle
	// ──────────────────────────────────────────────
	const navToggle = document.querySelector('[data-nav-toggle]');
	const navClose  = document.querySelector('[data-nav-close]');
	const mobileNav = document.getElementById('mobile-nav');

	const closeNav = () => {
		if (!mobileNav) return;
		mobileNav.dataset.open = 'false';
		mobileNav.setAttribute('aria-hidden', 'true');
		navToggle?.setAttribute('aria-expanded', 'false');
		document.documentElement.style.overflow = '';
	};

	const openNav = () => {
		if (!mobileNav) return;
		mobileNav.dataset.open = 'true';
		mobileNav.setAttribute('aria-hidden', 'false');
		navToggle?.setAttribute('aria-expanded', 'true');
		document.documentElement.style.overflow = 'hidden';
	};

	navToggle?.addEventListener('click', openNav);
	navClose?.addEventListener('click', closeNav);

	// Close on Esc
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') closeNav();
	});

	// Close on link click
	mobileNav?.querySelectorAll('a').forEach((a) => {
		a.addEventListener('click', closeNav);
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
})();
