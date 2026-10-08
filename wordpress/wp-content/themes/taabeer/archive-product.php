<?php
/**
 * Editorial WooCommerce product archive.
 *
 * @package Taabeer
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="woocommerce-main shell section">
	<header class="woocommerce-archive-header">
		<p class="eyebrow"><?php esc_html_e( 'The TAABEER edit', 'taabeer' ); ?></p>
		<h1><?php woocommerce_page_title(); ?></h1>
		<?php do_action( 'woocommerce_archive_description' ); ?>
	</header>

	<?php if ( woocommerce_product_loop() ) : ?>
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
		<?php woocommerce_product_loop_start(); ?>
		<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<?php wc_get_template_part( 'content', 'product' ); ?>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php woocommerce_product_loop_end(); ?>
		<?php do_action( 'woocommerce_after_shop_loop' ); ?>
	<?php else : ?>
		<div class="woocommerce-editorial-empty">
			<p class="eyebrow"><?php esc_html_e( 'Coming soon', 'taabeer' ); ?></p>
			<h2><?php esc_html_e( 'The online collection is being prepared.', 'taabeer' ); ?></h2>
			<p><?php esc_html_e( 'Explore our editorial collections while product details, availability and delivery are finalised.', 'taabeer' ); ?></p>
			<a class="button button--outline" href="<?php echo esc_url( home_url( '/collections/' ) ); ?>"><?php esc_html_e( 'Explore collections', 'taabeer' ); ?></a>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();

