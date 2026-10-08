<?php
/**
 * Free Elementor integration and reusable theme layouts.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_elementor_support() {
	add_theme_support( 'elementor' );
}
add_action( 'after_setup_theme', 'taabeer_elementor_support' );

// The theme uses local system font stacks and does not need third-party font requests.
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

function taabeer_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'taabeer_elementor_locations' );

function taabeer_register_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'taabeer',
		array(
			'title' => __( 'TAABEER', 'taabeer' ),
			'icon'  => 'fa fa-leaf',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'taabeer_register_elementor_category' );

function taabeer_register_elementor_widgets( $widgets_manager ) {
	require_once TAABEER_DIR . '/inc/elementor-widgets.php';
	$widgets_manager->register( new \Taabeer_Elementor_Header_Widget() );
	$widgets_manager->register( new \Taabeer_Elementor_Footer_Widget() );
	$widgets_manager->register( new \Taabeer_Elementor_Hero_Widget() );
	$widgets_manager->register( new \Taabeer_Elementor_Collections_Widget() );
	$widgets_manager->register( new \Taabeer_Elementor_Split_Feature_Widget() );
	$widgets_manager->register( new \Taabeer_Elementor_Story_Grid_Widget() );
	$widgets_manager->register( new \Taabeer_Elementor_Contact_Widget() );
}
add_action( 'elementor/widgets/register', 'taabeer_register_elementor_widgets' );

function taabeer_render_elementor_layout( $location ) {
	if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
		return false;
	}
	$layouts = get_posts(
		array(
			'post_type'      => 'taabeer_layout',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_taabeer_layout_location',
			'meta_value'     => sanitize_key( $location ),
			'orderby'        => 'modified',
			'order'          => 'DESC',
		)
	);
	if ( ! $layouts ) {
		return false;
	}
	$content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $layouts[0]->ID );
	if ( ! $content ) {
		return false;
	}
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output.
	return true;
}

function taabeer_elementor_cpt_support() {
	$supported = get_option( 'elementor_cpt_support', array( 'post', 'page' ) );
	$supported = array_unique( array_merge( (array) $supported, array( 'post', 'page', 'heritage_story', 'creative_profile', 'taabeer_layout', 'product' ) ) );
	update_option( 'elementor_cpt_support', $supported );
}
add_action( 'after_switch_theme', 'taabeer_elementor_cpt_support' );
