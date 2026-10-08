<?php
/** Collection card upgrade, retaining the client's editable HTML. @package Taabeer */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Only upgrade the recognised legacy list; unfamiliar layouts are left intact. */
function taabeer_collection_cards_from_legacy( $html ) {
	return preg_replace_callback( '~<div class="collection-list">(.*?)</div>~s', function ( $list ) {
		$pattern = '~\s*(<h2\b[^>]*>(.*?)</h2>)\s*(<p\b[^>]*>.*?</p>)\s*~s';
		preg_match_all( $pattern, $list[1], $pairs, PREG_SET_ORDER );
		if ( ! $pairs || '' !== trim( preg_replace( $pattern, '', $list[1] ) ) ) {
			return $list[0];
		}
		$images = array(
			'the-art-of-wear' => 'collection-wear.webp',
			'the-art-of-adornment' => 'collection-adornment.webp',
			'the-art-of-living' => 'collection-living.webp',
			'the-art-of-expression' => 'collection-expression.webp',
			'the-art-of-leather' => 'collection-leather.webp',
		);
		$cards = '';
		foreach ( $pairs as $index => $pair ) {
			$slug = sanitize_title( wp_strip_all_tags( $pair[2] ) );
			$heading = $pair[1];
			if ( preg_match( '~href=["\']([^"\']+)["\']~', $pair[2], $link ) ) {
				$path = wp_parse_url( html_entity_decode( $link[1], ENT_QUOTES, 'UTF-8' ), PHP_URL_PATH );
				$link_slug = basename( rtrim( (string) $path, '/' ) );
				if ( isset( $images[ $link_slug ] ) ) {
					$slug = $link_slug;
				}
			} elseif ( isset( $images[ $slug ] ) ) {
				$heading = str_replace( $pair[2], '<a href="' . esc_url( home_url( '/collection/' . $slug . '/' ) ) . '">' . $pair[2] . '</a>', $heading );
			}
			if ( ! isset( $images[ $slug ] ) ) {
				return $list[0];
			}
			$cards .= '<article class="collection-tile"><div class="collection-tile__image"><img src="' . esc_url( taabeer_demo_image_url( $images[ $slug ] ) ) . '" alt="" width="900" height="1100" loading="lazy"></div><div class="collection-tile__body"><span class="collection-tile__number" aria-hidden="true">' . esc_html( sprintf( '%02d', $index + 1 ) ) . '</span>' . $heading . $pair[3] . '<span class="collection-tile__cta" aria-hidden="true">' . esc_html__( 'Explore collection', 'taabeer' ) . '<span>&#8599;</span></span></div></article>';
		}
		return '<div class="collection-card-grid">' . $cards . '</div>';
	}, $html );
}

/** Recursively alter only the legacy list inside text widgets, preserving every other setting. */
function taabeer_collection_cards_elementor( $elements ) {
	foreach ( $elements as &$element ) {
		if ( 'text-editor' === ( $element['widgetType'] ?? '' ) && isset( $element['settings']['editor'] ) ) {
			$element['settings']['editor'] = taabeer_collection_cards_from_legacy( $element['settings']['editor'] );
		}
		if ( ! empty( $element['elements'] ) ) {
			$element['elements'] = taabeer_collection_cards_elementor( $element['elements'] );
		}
	}
	return $elements;
}

function taabeer_migrate_collection_cards_1_0_4( $installed ) {
	if ( version_compare( $installed, '1.0.4', '>=' ) ) {
		return;
	}
	$page = get_page_by_path( 'collections', OBJECT, 'page' );
	if ( ! $page ) {
		return;
	}
	$raw = get_post_meta( $page->ID, '_elementor_data', true );
	$data = is_string( $raw ) ? json_decode( $raw, true ) : null;
	$updated = is_array( $data ) ? taabeer_collection_cards_elementor( $data ) : $data;
	$content = taabeer_collection_cards_from_legacy( $page->post_content );
	if ( $data === $updated && $content === $page->post_content ) {
		return;
	}
	// Keep a local recovery copy in addition to the deployment manager's backup.
	add_post_meta( $page->ID, '_taabeer_collections_before_1_0_4', wp_slash( array( 'post_content' => $page->post_content, 'elementor_data' => $raw ) ), true );
	if ( $content !== $page->post_content ) {
		wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ) );
	}
	if ( $data !== $updated ) {
		update_post_meta( $page->ID, '_elementor_data', wp_slash( wp_json_encode( $updated ) ) );
		delete_post_meta( $page->ID, '_elementor_element_cache' );
		delete_post_meta( $page->ID, '_elementor_css' );
	}
}
add_action( 'taabeer_theme_migrate', 'taabeer_migrate_collection_cards_1_0_4' );
