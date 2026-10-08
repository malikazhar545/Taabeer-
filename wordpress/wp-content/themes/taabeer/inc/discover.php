<?php
/** Discover editorial upgrade, retaining the client's editable HTML. @package Taabeer */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Only upgrade the recognised legacy list; unfamiliar layouts are left intact. */
function taabeer_discover_features_from_legacy( $html ) {
	return preg_replace_callback( '~<div class="editorial-index">(.*?)</div>~s', function ( $list ) {
		$pattern = '~\s*(<h2\b[^>]*>(.*?)</h2>)\s*(<p\b[^>]*>.*?</p>)\s*~s';
		preg_match_all( $pattern, $list[1], $pairs, PREG_SET_ORDER );
		if ( ! $pairs || '' !== trim( preg_replace( $pattern, '', $list[1] ) ) ) {
			return $list[0];
		}
		$images = array(
			'regions' => 'story-object.webp',
			'pakistan-in-the-making' => 'story-studio.webp',
			'contemporary-pakistan' => 'collection-expression.webp',
			'new-voices' => 'discover-pakistan.webp',
		);
		$cards = '';
		foreach ( $pairs as $index => $pair ) {
			$slug = sanitize_title( wp_strip_all_tags( $pair[2] ) );
			if ( 'pakistan-by-region' === $slug ) {
				$slug = 'regions';
			}
			$heading = $pair[1];
			if ( preg_match( '~href=["\']([^"\']+)["\']~', $pair[2], $link ) ) {
				$path = wp_parse_url( html_entity_decode( $link[1], ENT_QUOTES, 'UTF-8' ), PHP_URL_PATH );
				$link_slug = basename( rtrim( (string) $path, '/' ) );
				if ( isset( $images[ $link_slug ] ) ) {
					$slug = $link_slug;
				}
			} elseif ( isset( $images[ $slug ] ) ) {
				$heading = str_replace( $pair[2], '<a href="' . esc_url( home_url( '/discover-pakistan/' . $slug . '/' ) ) . '">' . $pair[2] . '</a>', $heading );
			}
			if ( ! isset( $images[ $slug ] ) ) {
				return $list[0];
			}
			$cards .= '<article class="discover-feature-link"><div class="discover-feature-link__image"><img src="' . esc_url( taabeer_demo_image_url( $images[ $slug ] ) ) . '" alt="" width="900" height="600" loading="' . ( 0 === $index ? 'eager' : 'lazy' ) . '"></div><div class="discover-feature-link__body"><span class="discover-feature-link__number" aria-hidden="true">' . esc_html( sprintf( '%02d', $index + 1 ) ) . '</span>' . $heading . $pair[3] . '<span class="discover-feature-link__cta" aria-hidden="true">' . ( 'regions' === $slug ? esc_html__( 'Explore the regions', 'taabeer' ) : esc_html__( 'Discover more', 'taabeer' ) ) . '<span>&#8599;</span></span></div></article>';
		}
		return '<div class="discover-editorial">' . $cards . '</div>';
	}, $html );
}

/** Recursively alter only the legacy list inside text widgets, preserving every other setting. */
function taabeer_discover_features_elementor( $elements ) {
	foreach ( $elements as &$element ) {
		if ( 'text-editor' === ( $element['widgetType'] ?? '' ) && isset( $element['settings']['editor'] ) ) {
			$element['settings']['editor'] = taabeer_discover_features_from_legacy( $element['settings']['editor'] );
		}
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = taabeer_discover_features_elementor( $element['elements'] );
		}
	}
	return $elements;
}

function taabeer_migrate_discover_features_1_0_5( $installed ) {
	if ( version_compare( $installed, '1.0.5', '>=' ) ) {
		return;
	}
	$page = get_page_by_path( 'discover-pakistan', OBJECT, 'page' );
	if ( ! $page ) {
		return;
	}
	$raw = get_post_meta( $page->ID, '_elementor_data', true );
	$data = is_string( $raw ) ? json_decode( $raw, true ) : null;
	$updated = is_array( $data ) ? taabeer_discover_features_elementor( $data ) : $data;
	$content = taabeer_discover_features_from_legacy( $page->post_content );
	if ( $data === $updated && $content === $page->post_content ) {
		return;
	}
	// Keep a local recovery copy in addition to the deployment manager's backup.
	add_post_meta( $page->ID, '_taabeer_discover_before_1_0_5', wp_slash( array( 'post_content' => $page->post_content, 'elementor_data' => $raw ) ), true );
	if ( $content !== $page->post_content ) {
		wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ) );
	}
	if ( $data !== $updated ) {
		update_post_meta( $page->ID, '_elementor_data', wp_slash( wp_json_encode( $updated ) ) );
		delete_post_meta( $page->ID, '_elementor_element_cache' );
		delete_post_meta( $page->ID, '_elementor_css' );
	}
}
add_action( 'taabeer_theme_migrate', 'taabeer_migrate_discover_features_1_0_5' );
