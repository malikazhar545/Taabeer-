<?php
/**
 * Editorial WooCommerce product page.
 *
 * @package Taabeer
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="woocommerce-main shell section">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<?php wc_get_template_part( 'content', 'single-product' ); ?>
	<?php endwhile; ?>
</main>
<?php
get_footer();

