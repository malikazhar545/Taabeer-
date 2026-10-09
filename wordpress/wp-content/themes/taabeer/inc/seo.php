<?php
/**
 * Lightweight SEO and structured data. Defers to dedicated SEO plugins.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

function taabeer_meta_description() {
	if ( is_singular() ) {
		$description = get_post_meta( get_queried_object_id(), '_taabeer_meta_description', true );
		if ( ! $description ) {
			$description = has_excerpt( get_queried_object_id() ) ? get_the_excerpt( get_queried_object_id() ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 28 );
		}
		return $description;
	}
	if ( is_post_type_archive( 'heritage_story' ) ) {
		return __( 'The materials, ideas and people behind Pakistani design, explored through the TAABEER journal.', 'taabeer' );
	}
	return get_bloginfo( 'description' );
}

function taabeer_seo_head() {
	if ( taabeer_has_seo_plugin() ) {
		return;
	}
	$description = taabeer_meta_description();
	$canonical   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
	$title       = wp_get_document_title();
	$image       = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( get_queried_object_id(), 'full' ) : taabeer_demo_image_url( 'hero-weaver.webp' );
	?>
	<meta name="description" content="<?php echo esc_attr( $description ); ?>">
	<link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
	<meta property="og:type" content="<?php echo esc_attr( is_singular( 'heritage_story' ) ? 'article' : 'website' ); ?>">
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
	<meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<meta name="twitter:card" content="summary_large_image">
	<?php
}
add_action( 'wp_head', 'taabeer_seo_head', 3 );

function taabeer_schema() {
	if ( taabeer_has_seo_plugin() ) {
		return;
	}
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'description' => get_bloginfo( 'description' ),
		'address'  => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'London',
			'addressCountry'  => 'GB',
		),
	);
	if ( is_singular( 'heritage_story' ) ) {
		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'Article',
			'headline'      => get_the_title(),
			'description'   => taabeer_meta_description(),
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'mainEntityOfPage' => get_permalink(),
			'publisher'      => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
		);
		if ( has_post_thumbnail() ) {
			$schema['image'] = get_the_post_thumbnail_url( get_the_ID(), 'full' );
		}
	}
	printf( '<script type="application/ld+json">%s</script>', wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}
add_action( 'wp_footer', 'taabeer_schema', 20 );

function taabeer_robots_noindex_unapproved( $robots ) {
	if ( is_singular() && '1' === get_post_meta( get_queried_object_id(), '_taabeer_unapproved', true ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'taabeer_robots_noindex_unapproved' );
add_filter('wpseo_robots_array',function($robots){if(is_singular() && '1'===get_post_meta(get_queried_object_id(),'_taabeer_unapproved',true)){$robots['index']='noindex';}return $robots;});
add_filter('wpseo_metadesc',function($description){
	if($description){return $description;}
	if(is_post_type_archive('heritage_story')){return 'Explore the TAABEER journal: Pakistani design, materials, creative voices and the heritage stories behind our considered collections.';}
	if(is_post_type_archive('creative_profile')){return 'Meet the creative voices behind Pakistani design. Explore the artists, designers and makers whose ideas inform the TAABEER selection.';}
	if(is_tax()){$term=get_queried_object();$copy=wp_strip_all_tags(term_description());return $copy?:sprintf('Explore %s through the TAABEER selection, with stories, materials and contemporary perspectives on Pakistani craft and design.',$term->name);}
	return $description;
});

