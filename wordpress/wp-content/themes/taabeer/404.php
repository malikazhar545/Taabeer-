<?php
/** 404 page. @package Taabeer */
get_header();
?>
<main id="main-content" class="error-page section shell">
	<p class="eyebrow">404</p>
	<h1><?php esc_html_e( 'This page has moved beyond view.', 'taabeer' ); ?></h1>
	<p><?php esc_html_e( 'Return to the TAABEER home page or continue exploring our collections and stories.', 'taabeer' ); ?></p>
	<div class="button-row"><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return home', 'taabeer' ); ?></a><a class="button button--outline" href="<?php echo esc_url( home_url( '/collections/' ) ); ?>"><?php esc_html_e( 'Explore collections', 'taabeer' ); ?></a></div>
</main>
<?php get_footer(); ?>
