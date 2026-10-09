<?php
/**
 * Single creative profile.
 *
 * @package Taabeer
 */
get_header();
if(get_option('taabeer_visual_foundation')) { echo '<main id="main-content" class="page-main">'; if(taabeer_visual_template('profile')) {echo '</main>';get_footer();return;} echo '</main>'; }
while ( have_posts() ) : the_post();
?>
<main id="main-content" class="profile-single">
	<header class="profile-single__header shell">
		<div class="profile-single__portrait"><?php taabeer_render_image( 'taabeer-portrait', '', 'about-material.webp', get_the_title() ); ?></div>
		<div>
			<p class="eyebrow"><?php esc_html_e( 'Creative Profile', 'taabeer' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<dl class="profile-details">
				<?php foreach ( array( 'studio' => 'Studio', 'location' => 'Location', 'discipline' => 'Discipline', 'materials' => 'Materials and techniques' ) as $key => $label ) : $value = get_post_meta( get_the_ID(), '_taabeer_' . $key, true ); if ( $value ) : ?>
					<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
				<?php endif; endforeach; ?>
			</dl>
		</div>
	</header>
	<article class="entry-content shell shell--reading section"><?php the_content(); ?></article>
</main>
<?php endwhile; get_footer(); ?>

