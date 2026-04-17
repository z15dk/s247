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
		<div class="site-footer__grid">
			<div class="site-footer__col">
				<?php studie247_logo(); ?>
				<p class="site-footer__tagline"><?php esc_html_e( 'Optag. Skab. Udgiv 247.', 'studie247' ); ?></p>
				<p style="max-width: 32ch; color: rgba(244,233,221,0.7); font-size: var(--fs-sm); margin-top: var(--sp-4);">
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
					<li><?php esc_html_e( 'Adresse kommer', 'studie247' ); ?></li>
					<li><?php esc_html_e( 'CVR: 00000000', 'studie247' ); ?></li>
				</ul>
			</div>

			<div class="site-footer__col">
				<h3><?php esc_html_e( 'Book', 'studie247' ); ?></h3>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/book/' ) ); ?>"><?php esc_html_e( 'Book studie', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/priser/' ) ); ?>"><?php esc_html_e( 'Se priser', 'studie247' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>"><?php esc_html_e( 'Få et tilbud', 'studie247' ); ?></a></li>
				</ul>
			</div>
		</div>

		<div class="site-footer__bottom">
			<span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Studie 247</span>
			<ul style="list-style:none;padding:0;margin:0;display:flex;gap:var(--sp-4);">
				<li><a href="<?php echo esc_url( home_url( '/privatlivspolitik/' ) ); ?>"><?php esc_html_e( 'Privatliv', 'studie247' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/cookies/' ) ); ?>"><?php esc_html_e( 'Cookies', 'studie247' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/handelsbetingelser/' ) ); ?>"><?php esc_html_e( 'Handelsbetingelser', 'studie247' ); ?></a></li>
			</ul>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
