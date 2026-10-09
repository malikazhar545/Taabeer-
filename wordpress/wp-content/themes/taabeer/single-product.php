<?php
/**
 * Editorial WooCommerce product page.
 *
 * @package Taabeer
 */

defined( 'ABSPATH' ) || exit;

get_header();
if ( get_option( 'taabeer_visual_foundation' ) ) {
	while ( have_posts() ) { the_post(); $GLOBALS['product'] = wc_get_product( get_the_ID() ); echo '<main id="main-content" class="woocommerce">'; taabeer_visual_template( 'product' ); echo '</main>'; }
	get_footer(); return;
}
?>
<main id="main-content" class="woocommerce-main shell section">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php wc_get_template_part( 'content', 'single-product' ); ?>
	<?php endwhile; ?>
</main>
<?php
get_footer();

