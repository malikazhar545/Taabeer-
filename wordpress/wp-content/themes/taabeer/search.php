<?php
/** Search results. @package Taabeer */
get_header();
?>
<main id="main-content" class="page-main">
	<header class="page-hero shell"><p class="eyebrow"><?php esc_html_e( 'Search', 'taabeer' ); ?></p><h1><?php printf( esc_html__( 'Results for “%s”', 'taabeer' ), esc_html( get_search_query() ) ); ?></h1></header>
	<section class="search-results section shell shell--reading">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article class="search-result"><p class="eyebrow"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? '' ); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
		<?php endwhile; the_posts_pagination(); else : ?><p><?php esc_html_e( 'No matching pages or stories were found.', 'taabeer' ); ?></p><?php endif; ?>
	</section>
</main>
<?php get_footer(); ?>

