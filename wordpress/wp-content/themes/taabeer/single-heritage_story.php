<?php
/**
 * Single journal story.
 *
 * @package Taabeer
 */
get_header();
while ( have_posts() ) : the_post();
?>
<main id="main-content" class="story-single">
	<header class="story-single__header shell shell--reading">
		<p class="eyebrow"><?php esc_html_e( 'TAABEER Journal', 'taabeer' ); ?> · <?php echo esc_html( sprintf( _n( '%s minute read', '%s minute read', taabeer_reading_time(), 'taabeer' ), taabeer_reading_time() ) ); ?></p>
		<h1><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?><p class="story-single__dek"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
	</header>
	<figure class="story-single__hero shell shell--wide">
		<?php taabeer_render_image( 'full', '', 'story-textile.webp', get_the_title() ); ?>
		<?php if ( get_post_meta( get_the_ID(), '_taabeer_image_credit', true ) ) : ?>
			<figcaption><?php echo esc_html( get_post_meta( get_the_ID(), '_taabeer_image_credit', true ) ); ?></figcaption>
		<?php endif; ?>
	</figure>
	<article class="entry-content story-single__content shell shell--reading">
		<?php the_content(); ?>
	</article>
	<?php
	$terms = get_the_terms( get_the_ID(), 'taabeer_collection' );
	if ( $terms && ! is_wp_error( $terms ) ) :
		?>
		<footer class="story-single__related shell shell--reading">
			<p class="eyebrow"><?php esc_html_e( 'Explore related collections', 'taabeer' ); ?></p>
			<?php foreach ( $terms as $term ) : ?><a class="text-link" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a><?php endforeach; ?>
		</footer>
	<?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>

