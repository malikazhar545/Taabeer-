<?php
/**
 * Main index template.
 *
 * @package Taabeer
 */
get_header();
?>
<main id="main-content" class="page-main">
	<header class="page-hero shell">
		<p class="eyebrow"><?php esc_html_e( 'TAABEER', 'taabeer' ); ?></p>
		<h1><?php echo esc_html( is_home() && get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Journal', 'taabeer' ) ); ?></h1>
	</header>
	<div class="story-grid shell section">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); get_template_part( 'template-parts/story', 'card' ); endwhile; else : ?>
			<p><?php esc_html_e( 'No stories have been published yet.', 'taabeer' ); ?></p>
		<?php endif; ?>
	</div>
	<?php the_posts_pagination(); ?>
</main>
<?php get_footer(); ?>

