/**
 * Studie 247 — vanilla JS.
 * Sticky header, mobile menu, smooth scroll, scroll reveals.
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

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') closeNav();
	});

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
