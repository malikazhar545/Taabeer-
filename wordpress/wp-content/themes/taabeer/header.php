<?php
/**
 * Site header.
 *
 * @package Taabeer
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'taabeer' ); ?></a>

<?php if ( function_exists( 'taabeer_render_elementor_layout' ) && taabeer_render_elementor_layout( 'header' ) ) : ?>
<?php else : ?>
<div class="taabeer-announcement" role="note">
	<span><?php esc_html_e( 'A London-based curated house of Pakistani design', 'taabeer' ); ?></span>
</div>

<header class="site-header" data-site-header>
	<div class="site-header__inner">
		<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
			<span class="site-header__toggle-lines" aria-hidden="true"><span></span><span></span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Open navigation', 'taabeer' ); ?></span>
		</button>

		<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'Taabeer home', 'taabeer' ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="site-brand__english">TAABEER</span>
				<span class="site-brand__urdu" lang="ur" dir="rtl">تعبیر</span>
			<?php endif; ?>
		</a>

		<nav id="primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'taabeer' ); ?>" data-primary-nav>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'primary-navigation__list',
					'fallback_cb'    => 'taabeer_fallback_menu',
				)
			);
			?>
		</nav>

		<div class="site-header__actions">
			<a class="site-header__action" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'taabeer' ); ?>">
				<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
			</a>
			<?php if ( class_exists( 'WooCommerce' ) && function_exists( 'taabeer_commerce_is_live' ) && taabeer_commerce_is_live() ) : ?>
				<a class="site-header__action" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Shopping bag', 'taabeer' ); ?>">
					<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><path d="M5 8h14l-1 13H6L5 8Z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M9 9V6a3 3 0 0 1 6 0v3" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>
<?php endif; ?>
