<?php
/**
 * Theme setup, assets and global presentation settings.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_setup() {
	load_theme_textdomain( 'taabeer', TAABEER_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 140,
			'width'       => 560,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'taabeer-portrait', 960, 1200, true );
	add_image_size( 'taabeer-landscape', 1600, 1067, true );
	add_image_size( 'taabeer-story', 1200, 800, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'taabeer' ),
			'footer'  => __( 'Footer Navigation', 'taabeer' ),
		)
	);
}
add_action( 'after_setup_theme', 'taabeer_setup' );

function taabeer_assets() {
	wp_enqueue_style( 'taabeer-style', TAABEER_URI . '/assets/css/taabeer.css', array(), TAABEER_VERSION );
	if ( is_front_page() ) {
		wp_enqueue_style( 'taabeer-homepage', TAABEER_URI . '/assets/css/homepage.css', array( 'taabeer-style' ), TAABEER_VERSION );
	}
	if ( is_page( 'collections' ) ) {
		wp_enqueue_style( 'taabeer-collections-page', TAABEER_URI . '/assets/css/collections.css', array( 'taabeer-style' ), TAABEER_VERSION );
	}
	wp_enqueue_script( 'taabeer-site', TAABEER_URI . '/assets/js/taabeer.js', array(), TAABEER_VERSION, true );
	wp_localize_script(
		'taabeer-site',
		'TaabeerTheme',
		array(
			'menuOpen'   => __( 'Open navigation', 'taabeer' ),
			'menuClose'  => __( 'Close navigation', 'taabeer' ),
			'cookieName' => 'taabeer_cookie_choice',
			'gaId'       => get_option( 'taabeer_ga_measurement_id', '' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'taabeer_assets' );

function taabeer_editor_assets() {
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'admin_init', 'taabeer_editor_assets' );

function taabeer_body_classes( $classes ) {
	$classes[] = 'taabeer-site';
	if ( is_page( 'collections' ) ) {
		$classes[] = 'taabeer-collections-page';
	}
	if ( is_singular( 'heritage_story' ) ) {
		$classes[] = 'taabeer-longform';
	}
	if ( class_exists( 'WooCommerce' ) ) {
		$classes[] = 'taabeer-commerce-ready';
	}
	return $classes;
}
add_filter( 'body_class', 'taabeer_body_classes' );

function taabeer_excerpt_length() {
	return 28;
}
add_filter( 'excerpt_length', 'taabeer_excerpt_length', 99 );

function taabeer_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'taabeer_excerpt_more' );

function taabeer_reading_time( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 210 ) );
}

function taabeer_is_built_with_elementor( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	return class_exists( '\\Elementor\\Plugin' ) && 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true );
}

function taabeer_demo_image_url( $filename ) {
	return TAABEER_URI . '/assets/images/demo/' . ltrim( $filename, '/' );
}

/**
 * Provide a useful menu before the demo navigation has been imported.
 *
 * Kept in setup.php so Elementor header previews can call it outside the
 * normal header template request.
 */
function taabeer_fallback_menu() {
	$links = array(
		__( 'Collections', 'taabeer' )       => home_url( '/collections/' ),
		__( 'Discover Pakistan', 'taabeer' ) => home_url( '/discover-pakistan/' ),
		__( 'Journal', 'taabeer' )           => get_post_type_archive_link( 'heritage_story' ) ?: home_url( '/journal/' ),
		__( 'About', 'taabeer' )             => home_url( '/about/' ),
		__( 'Contact', 'taabeer' )           => home_url( '/contact/' ),
	);

	echo '<ul class="primary-navigation__list">';
	foreach ( $links as $label => $url ) {
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

function taabeer_render_image( $size = 'large', $class = '', $fallback = 'about-material.webp', $alt = '' ) {
	if ( has_post_thumbnail() ) {
		the_post_thumbnail(
			$size,
			array(
				'class'   => $class,
				'loading' => is_singular() ? 'eager' : 'lazy',
			)
		);
		return;
	}
	printf(
		'<img class="%1$s" src="%2$s" alt="%3$s" loading="lazy" width="1200" height="1500">',
		esc_attr( $class ),
		esc_url( taabeer_demo_image_url( $fallback ) ),
		esc_attr( $alt )
	);
}

function taabeer_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'taabeer_business',
		array(
			'title'       => __( 'Taabeer Business Details', 'taabeer' ),
			'description' => __( 'Only approved values should be published. Empty optional fields stay hidden.', 'taabeer' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'taabeer_public_email' => array( 'Public email', 'email' ),
		'taabeer_phone'        => array( 'Telephone', 'text' ),
		'taabeer_whatsapp'     => array( 'WhatsApp URL', 'url' ),
		'taabeer_instagram'    => array( 'Instagram URL', 'url' ),
		'taabeer_pinterest'    => array( 'Pinterest URL', 'url' ),
		'taabeer_address'      => array( 'Published address', 'textarea' ),
		'taabeer_company_no'   => array( 'Company number', 'text' ),
	);

	foreach ( $fields as $setting => $field ) {
		$wp_customize->add_setting(
			$setting,
			array(
				'default'           => '',
				'sanitize_callback' => 'email' === $field[1] ? 'sanitize_email' : ( 'url' === $field[1] ? 'esc_url_raw' : 'sanitize_textarea_field' ),
			)
		);
		$wp_customize->add_control(
			$setting,
			array(
				'label'   => __( $field[0], 'taabeer' ),
				'section' => 'taabeer_business',
				'type'    => $field[1],
			)
		);
	}
}
add_action( 'customize_register', 'taabeer_customize_register' );
