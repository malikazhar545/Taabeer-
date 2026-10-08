<?php
/**
 * Default page template.
 *
 * @package Taabeer
 */
get_header();
?>
<main id="main-content" class="page-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php if ( taabeer_is_built_with_elementor() ) : ?>
			<?php the_content(); ?>
		<?php else : ?>
			<header class="page-hero shell">
				<p class="eyebrow"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="entry-content shell shell--reading">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>

