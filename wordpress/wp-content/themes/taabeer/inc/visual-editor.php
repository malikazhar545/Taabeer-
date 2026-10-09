<?php
/** Native Elementor conversion and layout helpers. No vendor files are modified. */
defined( 'ABSPATH' ) || exit;

function taabeer_visual_id() { return substr( wp_generate_uuid4(), 0, 8 ); }
function taabeer_visual_widget( $type, $settings, $class = '' ) {
	$settings['_css_classes'] = trim( 'tb-visual ' . $class . ' ' . ( $settings['_css_classes'] ?? '' ) );
	return array( 'id' => taabeer_visual_id(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() );
}
function taabeer_visual_container( $children, $class = '', $tag = 'div' ) {
	return array( 'id' => taabeer_visual_id(), 'elType' => 'container', 'settings' => array( 'content_width' => 'full', 'html_tag' => $tag, 'css_classes' => trim( 'tb-visual ' . $class ), '_title' => ucwords( str_replace( array( '-', '_' ), ' ', strtok( $class ?: 'Section', ' ' ) ) ) ), 'elements' => $children );
}
function taabeer_visual_heading( $text, $tag = 'h2', $class = '', $url = '' ) {
	return taabeer_visual_widget( 'heading', array( 'title' => $text, 'header_size' => $tag, 'link' => array( 'url' => $url ) ), $class );
}
function taabeer_visual_text( $html, $class = '' ) { return taabeer_visual_widget( 'text-editor', array( 'editor' => $html ), $class ); }
function taabeer_visual_image( $src, $class = '' ) { return taabeer_visual_widget( 'image', array( 'image' => array( 'url' => $src, 'id' => attachment_url_to_postid( $src ) ), 'image_size' => 'full' ), $class ); }
function taabeer_visual_button( $label, $url, $class = 'button' ) { return taabeer_visual_widget( 'button', array( 'text' => $label, 'link' => array( 'url' => $url ) ), 'tb-button ' . $class ); }
function taabeer_dom_inner( $node ) { $html = ''; foreach ( $node->childNodes as $child ) { $html .= $node->ownerDocument->saveHTML( $child ); } return $html; }

/** Convert individual text, image, heading and link elements, rather than a single HTML widget. */
function taabeer_visual_dom_node( $node ) {
	if ( XML_TEXT_NODE === $node->nodeType ) {
		return trim( $node->textContent ) ? taabeer_visual_text( '<p>' . esc_html( $node->textContent ) . '</p>' ) : null;
	}
	if ( ! $node instanceof DOMElement ) { return null; }
	$tag = strtolower( $node->tagName ); $class = $node->getAttribute( 'class' );
	if ( in_array( $tag, array( 'script', 'style' ), true ) ) { return null; }
	if ( preg_match( '/^h[1-6]$/', $tag ) ) {
		$link = $node->getElementsByTagName( 'a' )->item( 0 );
		return taabeer_visual_heading( $link ? taabeer_dom_inner( $link ) : taabeer_dom_inner( $node ), $tag, $class, $link ? $link->getAttribute( 'href' ) : '' );
	}
	if ( 'img' === $tag ) { $image=taabeer_visual_image( $node->getAttribute( 'src' ), $class ); $image['settings']['taabeer_alt']=$node->getAttribute('alt'); return $image; }
	if ( 'a' === $tag && ! $node->getElementsByTagName( 'img' )->length && ! $node->getElementsByTagName( 'h2' )->length && ! $node->getElementsByTagName( 'h3' )->length ) {
		return taabeer_visual_button( $node->textContent, $node->getAttribute( 'href' ), $class ?: 'text-link' );
	}
	if ( 'button' === $tag && $node->hasAttribute( 'data-cookie-settings' ) ) {
		return taabeer_visual_widget( 'taabeer-utility', array( 'kind' => 'cookies', 'label' => $node->textContent ), $class );
	}
	if ( in_array( $tag, array( 'p', 'ul', 'ol', 'blockquote', 'span', 'figcaption', 'dl' ), true ) ) {
		// A paragraph containing only a CTA becomes a proper Elementor Button.
		if ( 'p' === $tag && 1 === $node->childNodes->length && $node->firstChild instanceof DOMElement && 'a' === $node->firstChild->tagName ) { return taabeer_visual_dom_node( $node->firstChild ); }
		$node->removeAttribute( 'class' );
		return taabeer_visual_text( $node->ownerDocument->saveHTML( $node ), $class );
	}
	$children = array();
	if ( str_contains( $class, 'partnership-cta' ) ) { $children[] = taabeer_visual_image( taabeer_demo_image_url( 'story-studio.webp' ), 'tb-partnership-photo' ); }
	foreach ( $node->childNodes as $child ) { $result = taabeer_visual_dom_node( $child ); if ( $result ) { $children[] = $result; } }
	$result = taabeer_visual_container( $children, $class, in_array( $tag, array( 'section', 'article', 'header', 'footer', 'nav', 'aside' ), true ) ? $tag : 'div' );
	if ( $node->hasAttribute( 'id' ) ) { $result['settings']['css_id'] = $node->getAttribute( 'id' ); }
	if ( 'a' === $tag ) { $result['settings']['html_tag'] = 'a'; $result['settings']['link'] = array( 'url' => $node->getAttribute( 'href' ) ); }
	return $result;
}
function taabeer_visual_parse( $html ) {
	$doc = new DOMDocument(); $previous = libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8"><div id="tb-parser-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors(); libxml_use_internal_errors( $previous );
	$root = $doc->getElementById( 'tb-parser-root' ); $elements = array();
	if ( $root ) { foreach ( $root->childNodes as $node ) { $result = taabeer_visual_dom_node( $node ); if ( $result ) { $elements[] = $result; } } }
	return $elements;
}
function taabeer_visual_convert_tree( $elements ) {
	$out = array();
	foreach ( $elements as $element ) {
		if ( 'text-editor' === ( $element['widgetType'] ?? '' ) && preg_match( '/<(section|header|article|div)\b/', $element['settings']['editor'] ?? '' ) && empty( $element['settings']['_css_classes'] ) ) {
			$out = array_merge( $out, taabeer_visual_parse( $element['settings']['editor'] ) );
		} else {
			if ( ! empty( $element['elements'] ) ) { $element['elements'] = taabeer_visual_convert_tree( $element['elements'] ); }
			$out[] = $element;
		}
	}
	return $out;
}
function taabeer_visual_save( $id, $data ) {
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' );
	if ( 'page' === get_post_type( $id ) ) { update_post_meta( $id, '_wp_page_template', 'default' ); }
	delete_post_meta( $id, '_elementor_element_cache' ); delete_post_meta( $id, '_elementor_css' );
}
function taabeer_visual_backup( $id ) {
	add_post_meta( $id, '_taabeer_before_visual_1_1', wp_slash( array( 'content' => get_post_field( 'post_content', $id ), 'elementor' => get_post_meta( $id, '_elementor_data', true ) ) ), true );
}

/** Shared template rendering also works inside Elementor preview requests. */
function taabeer_visual_template( $location ) { return taabeer_render_elementor_layout( $location ); }
