<?php
/**
 * Additive code migrations for repository deployments.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_run_theme_migrations() {
	$installed = (string) get_option( 'taabeer_schema_version', '0.0.0' );
	if ( ! version_compare( TAABEER_VERSION, $installed, '>' ) ) {
		return;
	}

	if ( function_exists( 'taabeer_register_content_types' ) ) {
		taabeer_register_content_types();
	}

	do_action( 'taabeer_theme_migrate', $installed, TAABEER_VERSION );
	flush_rewrite_rules( false );
	update_option( 'taabeer_schema_version', TAABEER_VERSION );
	delete_option( 'taabeer_pending_code_update' );
}
add_action( 'admin_init', 'taabeer_run_theme_migrations', 20 );

