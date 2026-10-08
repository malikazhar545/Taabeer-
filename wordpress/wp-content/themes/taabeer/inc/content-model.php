<?php
/**
 * Editorial content types and structured fields.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_register_content_types() {
	register_post_type(
		'taabeer_layout',
		array(
			'labels' => array(
				'name'          => __( 'Taabeer Layouts', 'taabeer' ),
				'singular_name' => __( 'Taabeer Layout', 'taabeer' ),
				'add_new_item'  => __( 'Add Taabeer Layout', 'taabeer' ),
				'edit_item'     => __( 'Edit Taabeer Layout', 'taabeer' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => 'themes.php',
			'show_in_rest'        => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-layout',
			'supports'            => array( 'title', 'editor', 'revisions' ),
		)
	);

	register_post_type(
		'heritage_story',
		array(
			'labels' => array(
				'name'          => __( 'Journal', 'taabeer' ),
				'singular_name' => __( 'Story', 'taabeer' ),
				'add_new_item'  => __( 'Add Story', 'taabeer' ),
				'edit_item'     => __( 'Edit Story', 'taabeer' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => 'journal',
			'rewrite'      => array( 'slug' => 'journal' ),
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
		)
	);

	register_post_type(
		'creative_profile',
		array(
			'labels' => array(
				'name'          => __( 'Creative Profiles', 'taabeer' ),
				'singular_name' => __( 'Creative Profile', 'taabeer' ),
				'add_new_item'  => __( 'Add Creative Profile', 'taabeer' ),
				'edit_item'     => __( 'Edit Creative Profile', 'taabeer' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => 'creative-voices',
			'rewrite'      => array( 'slug' => 'creative-voices' ),
			'menu_icon'    => 'dashicons-admin-users',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
		)
	);

	$collection_objects = array( 'heritage_story', 'creative_profile' );
	if ( post_type_exists( 'product' ) ) {
		$collection_objects[] = 'product';
	}

	register_taxonomy(
		'taabeer_collection',
		$collection_objects,
		array(
			'labels'       => array(
				'name'          => __( 'Taabeer Collections', 'taabeer' ),
				'singular_name' => __( 'Taabeer Collection', 'taabeer' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => 'collection' ),
		)
	);

	register_taxonomy(
		'taabeer_region',
		array( 'heritage_story', 'creative_profile' ),
		array(
			'labels'       => array( 'name' => __( 'Regions', 'taabeer' ), 'singular_name' => __( 'Region', 'taabeer' ) ),
			'public'       => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => 'region' ),
		)
	);

	register_taxonomy(
		'taabeer_theme',
		array( 'heritage_story', 'creative_profile' ),
		array(
			'labels'       => array( 'name' => __( 'Editorial Themes', 'taabeer' ), 'singular_name' => __( 'Editorial Theme', 'taabeer' ) ),
			'public'       => true,
			'show_in_rest' => true,
			'hierarchical' => false,
			'rewrite'      => array( 'slug' => 'theme' ),
		)
	);
}
add_action( 'init', 'taabeer_register_content_types', 8 );

function taabeer_add_meta_boxes() {
	add_meta_box( 'taabeer_story_details', __( 'Story Attribution', 'taabeer' ), 'taabeer_story_meta_box', 'heritage_story', 'normal', 'high' );
	add_meta_box( 'taabeer_profile_details', __( 'Creative Profile Details', 'taabeer' ), 'taabeer_profile_meta_box', 'creative_profile', 'normal', 'high' );
	add_meta_box( 'taabeer_layout_location', __( 'Layout Location', 'taabeer' ), 'taabeer_layout_meta_box', 'taabeer_layout', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'taabeer_add_meta_boxes' );

function taabeer_story_meta_box( $post ) {
	wp_nonce_field( 'taabeer_save_editorial_meta', 'taabeer_editorial_nonce' );
	$fields = array(
		'location'    => __( 'Location', 'taabeer' ),
		'maker_name'  => __( 'Maker or designer credit', 'taabeer' ),
		'technique'   => __( 'Verified technique', 'taabeer' ),
		'materials'   => __( 'Verified materials', 'taabeer' ),
		'image_credit'=> __( 'Image credit and permission note', 'taabeer' ),
	);
	taabeer_render_meta_fields( $post, $fields );
}

function taabeer_profile_meta_box( $post ) {
	wp_nonce_field( 'taabeer_save_editorial_meta', 'taabeer_editorial_nonce' );
	$fields = array(
		'studio'       => __( 'Studio', 'taabeer' ),
		'location'     => __( 'Location', 'taabeer' ),
		'discipline'   => __( 'Discipline', 'taabeer' ),
		'perspective'  => __( 'Design perspective', 'taabeer' ),
		'materials'    => __( 'Materials and techniques', 'taabeer' ),
		'website'      => __( 'Approved website or social URL', 'taabeer' ),
		'image_credit' => __( 'Portrait and studio image credits', 'taabeer' ),
	);
	taabeer_render_meta_fields( $post, $fields );
}

function taabeer_layout_meta_box( $post ) {
	wp_nonce_field( 'taabeer_save_layout_meta', 'taabeer_layout_nonce' );
	$current = get_post_meta( $post->ID, '_taabeer_layout_location', true );
	?>
	<p><label for="taabeer_layout_location"><?php esc_html_e( 'Use this Elementor layout as:', 'taabeer' ); ?></label></p>
	<select class="widefat" id="taabeer_layout_location" name="taabeer_layout_location">
		<option value=""><?php esc_html_e( 'Unassigned', 'taabeer' ); ?></option>
		<option value="header" <?php selected( $current, 'header' ); ?>><?php esc_html_e( 'Site header', 'taabeer' ); ?></option>
		<option value="footer" <?php selected( $current, 'footer' ); ?>><?php esc_html_e( 'Site footer', 'taabeer' ); ?></option>
	</select>
	<?php
}

function taabeer_render_meta_fields( $post, $fields ) {
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, '_taabeer_' . $key, true );
		printf(
			'<p><label for="taabeer_%1$s"><strong>%2$s</strong></label><br><input class="widefat" id="taabeer_%1$s" name="taabeer_%1$s" value="%3$s"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $value )
		);
	}
}

function taabeer_save_editorial_meta( $post_id ) {
	if ( ! isset( $_POST['taabeer_editorial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['taabeer_editorial_nonce'] ) ), 'taabeer_save_editorial_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = array( 'location', 'maker_name', 'technique', 'materials', 'image_credit', 'studio', 'discipline', 'perspective', 'website' );
	foreach ( $fields as $key ) {
		$form_key = 'taabeer_' . $key;
		if ( isset( $_POST[ $form_key ] ) ) {
			$value = 'website' === $key ? esc_url_raw( wp_unslash( $_POST[ $form_key ] ) ) : sanitize_text_field( wp_unslash( $_POST[ $form_key ] ) );
			update_post_meta( $post_id, '_taabeer_' . $key, $value );
		}
	}
}
add_action( 'save_post', 'taabeer_save_editorial_meta' );

function taabeer_save_layout_meta( $post_id ) {
	if ( 'taabeer_layout' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['taabeer_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['taabeer_layout_nonce'] ) ), 'taabeer_save_layout_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$location = isset( $_POST['taabeer_layout_location'] ) ? sanitize_key( wp_unslash( $_POST['taabeer_layout_location'] ) ) : '';
	if ( ! in_array( $location, array( '', 'header', 'footer' ), true ) ) {
		$location = '';
	}
	update_post_meta( $post_id, '_taabeer_layout_location', $location );
}
add_action( 'save_post_taabeer_layout', 'taabeer_save_layout_meta' );
