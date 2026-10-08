<?php
/**
 * Site footer.
 *
 * @package Taabeer
 */
$public_email = get_theme_mod( 'taabeer_public_email', '' );
$address      = get_theme_mod( 'taabeer_address', '' );
?>
<?php if ( function_exists( 'taabeer_render_elementor_layout' ) && taabeer_render_elementor_layout( 'footer' ) ) : ?>
<?php else : ?>
<footer class="site-footer">
	<div class="site-footer__lead">
		<p class="eyebrow"><?php esc_html_e( 'TAABEER', 'taabeer' ); ?></p>
		<h2><?php esc_html_e( 'Pakistani design, thoughtfully curated.', 'taabeer' ); ?></h2>
		<a class="text-link text-link--light" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Begin a conversation', 'taabeer' ); ?></a>
	</div>
	<div class="site-footer__grid">
		<div>
			<h3><?php esc_html_e( 'Explore', 'taabeer' ); ?></h3>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'site-footer__menu',
					'fallback_cb'    => false,
				)
			);
			?>
		</div>
		<div>
			<h3><?php esc_html_e( 'Studio', 'taabeer' ); ?></h3>
			<p><?php echo $address ? nl2br( esc_html( $address ) ) : esc_html__( 'London, United Kingdom', 'taabeer' ); ?></p>
			<?php if ( $public_email ) : ?>
				<p><a href="mailto:<?php echo esc_attr( antispambot( $public_email ) ); ?>"><?php echo esc_html( antispambot( $public_email ) ); ?></a></p>
			<?php endif; ?>
		</div>
		<div>
			<h3><?php esc_html_e( 'Information', 'taabeer' ); ?></h3>
			<ul class="site-footer__menu">
				<li><a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'taabeer' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'taabeer' ); ?></a></li>
				<?php if ( get_theme_mod( 'taabeer_instagram' ) ) : ?><li><a href="<?php echo esc_url( get_theme_mod( 'taabeer_instagram' ) ); ?>">Instagram</a></li><?php endif; ?>
				<?php if ( get_theme_mod( 'taabeer_pinterest' ) ) : ?><li><a href="<?php echo esc_url( get_theme_mod( 'taabeer_pinterest' ) ); ?>">Pinterest</a></li><?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.</p>
		<nav aria-label="<?php esc_attr_e( 'Legal', 'taabeer' ); ?>">
			<a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'taabeer' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookies', 'taabeer' ); ?></a>
			<button class="site-footer__cookie-button" type="button" data-cookie-settings><?php esc_html_e( 'Cookie settings', 'taabeer' ); ?></button>
		</nav>
	</div>
</footer>
<?php endif; ?>

<div class="cookie-banner" role="dialog" aria-modal="false" aria-labelledby="cookie-title" hidden data-cookie-banner>
	<div>
		<h2 id="cookie-title"><?php esc_html_e( 'Your privacy choices', 'taabeer' ); ?></h2>
		<p><?php esc_html_e( 'We use essential cookies to run the site. Optional analytics cookies will only be used with your permission.', 'taabeer' ); ?></p>
	</div>
	<div class="cookie-banner__actions">
		<button class="button button--outline" type="button" data-cookie-choice="essential"><?php esc_html_e( 'Essential only', 'taabeer' ); ?></button>
		<button class="button" type="button" data-cookie-choice="all"><?php esc_html_e( 'Accept analytics', 'taabeer' ); ?></button>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
