<?php
/**
 * WooCommerce integration.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 640,
			'single_image_width'    => 1200,
			'product_grid'          => array( 'default_rows' => 3, 'min_rows' => 1, 'max_rows' => 6, 'default_columns' => 3, 'min_columns' => 1, 'max_columns' => 4 ),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'taabeer_woocommerce_setup' );

/**
 * Keep the editorial product templates in control across WooCommerce versions.
 */
function taabeer_woocommerce_template_include( $template ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $template;
	}
	if ( is_singular( 'product' ) ) {
		return TAABEER_DIR . '/single-product.php';
	}
	if ( is_shop() || is_product_taxonomy() ) {
		return TAABEER_DIR . '/archive-product.php';
	}
	return $template;
}
add_filter( 'template_include', 'taabeer_woocommerce_template_include', 99 );

function taabeer_woocommerce_hooks() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_before_main_content', 'taabeer_wc_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'taabeer_wc_wrapper_end', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	add_filter( 'woocommerce_show_page_title', '__return_false' );
}
add_action( 'wp', 'taabeer_woocommerce_hooks' );

function taabeer_wc_wrapper_start() {
	echo '<main id="main-content" class="woocommerce-main shell section">';
	if ( is_shop() || is_product_taxonomy() ) {
		echo '<header class="woocommerce-archive-header"><p class="eyebrow">' . esc_html__( 'The TAABEER edit', 'taabeer' ) . '</p><h1>' . esc_html( woocommerce_page_title( false ) ) . '</h1></header>';
	}
}

function taabeer_wc_wrapper_end() {
	echo '</main>';
}

function taabeer_product_editorial_fields() {
	echo '<div class="options_group">';
	woocommerce_wp_text_input( array( 'id' => '_taabeer_maker', 'label' => __( 'Maker or brand', 'taabeer' ), 'desc_tip' => true, 'description' => __( 'Use the approved public credit.', 'taabeer' ) ) );
	woocommerce_wp_text_input( array( 'id' => '_taabeer_origin', 'label' => __( 'Production location', 'taabeer' ), 'desc_tip' => true, 'description' => __( 'Keep this separate from design inspiration or motif history.', 'taabeer' ) ) );
	woocommerce_wp_textarea_input( array( 'id' => '_taabeer_inspiration', 'label' => __( 'Design story', 'taabeer' ), 'description' => __( 'Verified context about the piece, its design and its maker.', 'taabeer' ) ) );
	woocommerce_wp_text_input( array( 'id' => '_taabeer_image_credit', 'label' => __( 'Photography credit', 'taabeer' ) ) );
	woocommerce_wp_text_input( array( 'id' => '_taabeer_dispatch_lead', 'label' => __( 'Dispatch lead time', 'taabeer' ) ) );
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'taabeer_product_editorial_fields' );

function taabeer_save_product_editorial_fields( $product ) {
	foreach ( array( 'maker', 'origin', 'inspiration', 'image_credit', 'dispatch_lead' ) as $field ) {
		$key = '_taabeer_' . $field;
		if ( isset( $_POST[ $key ] ) ) {
			$value = 'inspiration' === $field ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			$product->update_meta_data( $key, $value );
		}
	}
}
add_action( 'woocommerce_admin_process_product_object', 'taabeer_save_product_editorial_fields' );

function taabeer_product_story_tab( $tabs ) {
	global $product;
	if ( $product && ( $product->get_meta( '_taabeer_inspiration' ) || $product->get_meta( '_taabeer_maker' ) ) ) {
		$tabs['taabeer_story'] = array(
			'title'    => __( 'Story and provenance', 'taabeer' ),
			'priority' => 15,
			'callback' => 'taabeer_product_story_tab_content',
		);
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'taabeer_product_story_tab' );

function taabeer_product_story_tab_content() {
	global $product;
	$maker       = $product->get_meta( '_taabeer_maker' );
	$origin      = $product->get_meta( '_taabeer_origin' );
	$inspiration = $product->get_meta( '_taabeer_inspiration' );
	if ( $inspiration ) {
		echo '<p>' . nl2br( esc_html( $inspiration ) ) . '</p>';
	}
	if ( $maker || $origin ) {
		echo '<dl class="product-provenance">';
		if ( $maker ) { echo '<div><dt>' . esc_html__( 'Maker or brand', 'taabeer' ) . '</dt><dd>' . esc_html( $maker ) . '</dd></div>'; }
		if ( $origin ) { echo '<div><dt>' . esc_html__( 'Production location', 'taabeer' ) . '</dt><dd>' . esc_html( $origin ) . '</dd></div>'; }
		echo '</dl>';
	}
}

function taabeer_commerce_is_live() {
	return 'yes' === get_option( 'taabeer_commerce_enabled', 'no' );
}

function taabeer_hold_purchasing_until_launch() {
	if ( ! class_exists( 'WooCommerce' ) || taabeer_commerce_is_live() ) {
		return;
	}
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	add_action( 'woocommerce_single_product_summary', 'taabeer_product_launch_note', 25 );
}
add_action( 'wp', 'taabeer_hold_purchasing_until_launch', 30 );

function taabeer_product_launch_note() {
	echo '<div class="product-launch-note"><p class="eyebrow">' . esc_html__( 'Collection preview', 'taabeer' ) . '</p><p>' . esc_html__( 'Ordering will open after specifications, availability, delivery and returns information have been approved.', 'taabeer' ) . '</p></div>';
}

function taabeer_disable_purchasing( $purchasable ) {
	return taabeer_commerce_is_live() ? $purchasable : false;
}
add_filter( 'woocommerce_is_purchasable', 'taabeer_disable_purchasing' );
add_filter( 'woocommerce_variation_is_purchasable', 'taabeer_disable_purchasing' );
