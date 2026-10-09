<?php
/**
 * Creative profiles archive.
 *
 * @package Taabeer
 */
get_header();
if ( function_exists( 'taabeer_visual_template' ) && get_option( 'taabeer_visual_foundation' ) ) {
	echo '<main id="main-content" class="page-main">';
	if ( taabeer_visual_template( 'profiles' ) ) { echo '</main>'; get_footer(); return; }
	echo '</main>';
}

?>
<main id="main-content" class="page-main">
	<header class="archive-hero shell">
		<div><p class="eyebrow"><?php esc_html_e( 'New Voices', 'taabeer' ); ?></p><h1><?php esc_html_e( 'People developing their own design language', 'taabeer' ); ?></h1></div>
		<p><?php esc_html_e( 'Profiles are published only after the creator’s biography, quotations, links and image permissions have been approved.', 'taabeer' ); ?></p>
	</header>
	<div class="profile-grid shell section">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article class="profile-card">
				<a href="<?php the_permalink(); ?>"><?php taabeer_render_image( 'taabeer-portrait', '', 'about-material.webp', get_the_title() ); ?><h2><?php the_title(); ?></h2></a>
				<p><?php echo esc_html( get_post_meta( get_the_ID(), '_taabeer_discipline', true ) ); ?></p>
			</article>
		<?php endwhile; else : ?><p><?php esc_html_e( 'Approved creative profiles will appear here.', 'taabeer' ); ?></p><?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>

