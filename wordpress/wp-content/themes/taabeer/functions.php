<?php
/**
 * Taabeer theme bootstrap.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TAABEER_VERSION', '1.0.4' );
define( 'TAABEER_DIR', get_template_directory() );
define( 'TAABEER_URI', get_template_directory_uri() );

$taabeer_includes = array(
	'/inc/setup.php',
	'/inc/content-model.php',
	'/inc/collections.php',
	'/inc/migrations.php',
	'/inc/elementor.php',
	'/inc/contact.php',
	'/inc/seo.php',
	'/inc/woocommerce.php',
	'/inc/demo-import.php',
);

foreach ( $taabeer_includes as $taabeer_include ) {
	require_once TAABEER_DIR . $taabeer_include;
}
