<?php
/**
 * Additive code migrations for repository deployments.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish the two imported legal placeholders with useful baseline content.
 * Existing client-edited pages are never replaced.
 */
function taabeer_migrate_1_0_2( $installed ) {
	if ( version_compare( $installed, '1.0.2', '>=' ) ) {
		return;
	}

	$policies = array(
		'privacy-policy' => array(
			'title'       => 'Privacy Policy',
			'intro'       => 'How TAABEER handles information shared through this website.',
			'old_content' => '<h2>Policy content pending approval</h2><p>This page must be completed after the website services, processors, retention decisions, analytics, newsletter and commerce features are confirmed.</p>',
			'content'     => function_exists( 'taabeer_privacy_policy_copy' ) ? taabeer_privacy_policy_copy() : '',
		),
		'cookie-policy'  => array(
			'title'       => 'Cookie Policy',
			'intro'       => 'The choices available when this website uses cookies and similar technologies.',
			'old_content' => '<h2>Cookie inventory pending</h2><p>Document the cookies and similar technologies used by the finished build, including providers, purposes and durations.</p>',
			'content'     => function_exists( 'taabeer_cookie_policy_copy' ) ? taabeer_cookie_policy_copy() : '',
		),
	);

	foreach ( $policies as $slug => $policy ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( ! $page ) {
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_title'   => $policy['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_content' => $policy['content'],
				)
			);
			if ( ! is_wp_error( $page_id ) && function_exists( 'taabeer_apply_elementor_data' ) ) {
				update_post_meta( $page_id, '_taabeer_unapproved', '1' );
				taabeer_apply_elementor_data( $page_id, taabeer_elementor_standard_page( $policy['title'], $policy['intro'], $policy['content'] ), 'wp-page' );
			}
		} elseif (
			'draft' === $page->post_status
			&& (
				( '1' === get_post_meta( $page->ID, '_taabeer_unapproved', true ) && trim( $page->post_content ) === $policy['old_content'] )
				|| (
					'privacy-policy' === $slug
					&& '' === get_post_meta( $page->ID, '_elementor_edit_mode', true )
					&& false !== strpos( $page->post_content, 'Suggested text:' )
					&& false !== strpos( $page->post_content, 'Our website address is:' )
				)
			)
		) {
			wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'publish', 'post_content' => $policy['content'] ) );
			update_post_meta( $page->ID, '_taabeer_unapproved', '1' );
			if ( function_exists( 'taabeer_apply_elementor_data' ) ) {
				taabeer_apply_elementor_data( $page->ID, taabeer_elementor_standard_page( $policy['title'], $policy['intro'], $policy['content'] ), 'wp-page' );
			}
			$page_id = $page->ID;
		} else {
			$page_id = $page->ID;
		}

		if ( 'privacy-policy' === $slug && ! empty( $page_id ) ) {
			update_option( 'wp_page_for_privacy_policy', (int) $page_id );
		}
	}

	/* Add real destinations to the imported editorial indexes when they are still untouched demo content. */
	$collections = get_page_by_path( 'collections', OBJECT, 'page' );
	$collections_intro = 'Explore Pakistani design through five collections, each bringing its own perspective to what we wear, the spaces we inhabit and the art we choose to live with.';
	$old_collection_markup = '<div class="collection-list"><h2>The Art of Wear</h2><p>Textiles and clothing chosen for their character, detail and ease of wear.</p><h2>The Art of Adornment</h2><p>Jewellery and decorative accessories that bring a distinctive finishing touch.</p><h2>The Art of Living</h2><p>Furniture, decorative pieces and useful designs that bring character to everyday spaces.</p><h2>The Art of Expression</h2><p>Art and collectible work selected for its visual language and point of view.</p><h2>The Art of Leather</h2><p>Bags and leather accessories chosen for shape, finish and everyday purpose.</p></div>';
	if ( $collections && '1' === get_post_meta( $collections->ID, '_taabeer_unapproved', true ) && trim( $collections->post_content ) === '<p>' . $collections_intro . '</p>' . $old_collection_markup && function_exists( 'taabeer_collection_page_markup' ) ) {
		$new_markup = taabeer_collection_page_markup();
		wp_update_post( array( 'ID' => $collections->ID, 'post_content' => '<p>' . $collections_intro . '</p>' . $new_markup ) );
		if ( function_exists( 'taabeer_apply_elementor_data' ) ) {
			taabeer_apply_elementor_data( $collections->ID, taabeer_elementor_standard_page( 'Collections', $collections_intro, $new_markup ), 'wp-page' );
		}
	}

	$regions = get_page_by_path( 'discover-pakistan/regions', OBJECT, 'page' );
	$regions_content = '<p>Explore Pakistan through the places and people behind its design. Approved regional pages introduce makers, materials and creative practices alongside contemporary interpretations.</p>';
	if ( $regions && '1' === get_post_meta( $regions->ID, '_taabeer_unapproved', true ) && trim( $regions->post_content ) === $regions_content && function_exists( 'taabeer_region_cards_markup' ) && function_exists( 'taabeer_apply_elementor_data' ) ) {
		taabeer_apply_elementor_data( $regions->ID, taabeer_elementor_standard_page( 'Pakistan by Region', 'Explore Pakistan through the places and people behind its design.', taabeer_region_cards_markup() ), 'wp-page' );
	}

	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'Hello world!' === $hello->post_title && false !== strpos( wp_strip_all_tags( $hello->post_content ), 'Welcome to WordPress.' ) ) {
		wp_trash_post( $hello->ID );
	}
}
add_action( 'taabeer_theme_migrate', 'taabeer_migrate_1_0_2' );

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
