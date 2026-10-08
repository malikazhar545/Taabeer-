<?php
/**
 * Story card.
 *
 * @package Taabeer
 */
?>
<article <?php post_class( 'story-card reveal-on-scroll' ); ?>>
	<a class="story-card__media" href="<?php the_permalink(); ?>">
		<?php taabeer_render_image( 'taabeer-story', 'story-card__image', 'story-textile.webp', get_the_title() ); ?>
	</a>
	<div class="story-card__content">
		<p class="eyebrow"><?php echo esc_html( sprintf( _n( '%s minute read', '%s minute read', taabeer_reading_time(), 'taabeer' ), taabeer_reading_time() ) ); ?></p>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php the_excerpt(); ?>
		<a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read story', 'taabeer' ); ?></a>
	</div>
</article>
