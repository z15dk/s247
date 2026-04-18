<?php
/**
 * Site footer.
 *
 * @package Studie247
 */
?>
</main><!-- #main -->

<footer class="site-footer" role="contentinfo">
	<div class="wrap wrap--wide">
		<div class="site-footer__top" data-reveal>
			<p class="site-footer__big">
				<?php esc_html_e( 'Optag.', 'studie247' ); ?>
				<em><?php esc_html_e( 'Skab.', 'studie247' ); ?></em>
				<?php esc_html_e( 'Udgiv', 'studie247' ); ?>
				<em style="font-family:var(--font-serif);font-style:italic;color:var(--color-accent);">247.</em>
			</p>
		</div>

		<div class="site-footer__grid">
			<div class="site-footer__col">
				<div class="logo logo--on-dark">
					<span class="logo__word">STUDIE</span>
					<span class="logo__num">247</span>
				</div>
				<p style="max-width: 34ch; color: var(--s247-bone-60); font-size: var(--fs-sm); margin-top: var(--sp-5); line-height: var(--lh-base);">
					<?php esc_html_e( 'Dit studie — døgnet rundt. Simpelt. Professionelt. Menneskeligt.', 'studie247' ); ?>
				</p>
			</div>

			<div class="site-footer__col">
				<h3><?php esc_html_e( 'Navigation', 'studie247' ); ?></h3>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Services', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/studiet/' ) ); ?>"><?php esc_html_e( 'Studiet', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/priser/' ) ); ?>"><?php esc_html_e( 'Priser', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/udlejning/' ) ); ?>"><?php esc_html_e( 'Udlejning', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/om/' ) ); ?>"><?php esc_html_e( 'Om', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>"><?php esc_html_e( 'Kontakt', 'studie247' ); ?></a></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<h3><?php esc_html_e( 'Kontakt', 'studie247' ); ?></h3>
				<ul>
					<li><a href="mailto:hej@studie247.dk">hej@studie247.dk</a></li>
					<li><a href="tel:+4500000000">+45 00 00 00 00</a></li>
					<li style="color:var(--s247-bone-60);"><?php esc_html_e( 'Aarhus', 'studie247' ); ?></li>
					<li style="color:var(--s247-bone-60);"><?php esc_html_e( 'CVR: 00000000', 'studie247' ); ?></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<h3><?php esc_html_e( 'Book', 'studie247' ); ?></h3>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/booking-studie/' ) ); ?>"><?php esc_html_e( 'Book studie', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/priser/' ) ); ?>"><?php esc_html_e( 'Se priser', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>"><?php esc_html_e( 'Få et tilbud', 'studie247' ); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="site-footer__bottom">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Studie 247</span>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/privatlivspolitik/' ) ); ?>"><?php esc_html_e( 'Privatliv', 'studie247' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/cookies/' ) ); ?>"><?php esc_html_e( 'Cookies', 'studie247' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/handelsbetingelser/' ) ); ?>"><?php esc_html_e( 'Vilkår', 'studie247' ); ?></a></li>
			</ul>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
