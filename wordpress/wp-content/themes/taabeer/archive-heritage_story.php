<?php
/**
 * Journal archive.
 *
 * @package Taabeer
 */
get_header();
?>
<main id="main-content" class="page-main">
	<header class="archive-hero shell">
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Journal and Heritage Stories', 'taabeer' ); ?></p>
			<h1><?php esc_html_e( 'The stories behind the selection', 'taabeer' ); ?></h1>
		</div>
		<p><?php esc_html_e( 'A closer look at the materials, ideas and people behind Pakistani design. Through conversations, studio visits and collection notes, the TAABEER journal explores how heritage and contemporary expression meet.', 'taabeer' ); ?></p>
	</header>
	<section class="section shell">
		<?php if ( have_posts() ) : ?>
			<div class="story-grid">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/story', 'card' ); endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="empty-state">
				<p class="eyebrow"><?php esc_html_e( 'Our first stories are coming soon.', 'taabeer' ); ?></p>
				<p><?php esc_html_e( 'We will publish only when each story, image and maker credit has been approved.', 'taabeer' ); ?></p>
			</div>
		<?php endif; ?>
	</section>
</main>
<?php get_footer(); ?>

