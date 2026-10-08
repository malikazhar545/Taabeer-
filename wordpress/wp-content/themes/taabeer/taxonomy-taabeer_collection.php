<?php
/**
 * Collection landing template.
 *
 * @package Taabeer
 */
get_header();
$term = get_queried_object();
?>
<main id="main-content" class="page-main">
	<header class="collection-archive-hero shell">
		<p class="eyebrow"><?php esc_html_e( 'Collection', 'taabeer' ); ?></p>
		<h1><?php single_term_title(); ?></h1>
		<?php if ( term_description() ) : ?><div class="collection-archive-hero__description"><?php echo wp_kses_post( term_description() ); ?></div><?php endif; ?>
	</header>
	<section class="section shell">
		<?php if ( have_posts() ) : ?>
			<div class="story-grid">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/story', 'card' ); endwhile; ?>
			</div>
		<?php else : ?>
			<div class="empty-state"><p><?php esc_html_e( 'This collection is prepared for future pieces and stories. Public items will appear after content and availability are approved.', 'taabeer' ); ?></p></div>
		<?php endif; ?>
	</section>
</main>
<?php get_footer(); ?>

