<?php
/**
 * One-click Taabeer demo import and setup screen.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function taabeer_setup_menu() {
	add_theme_page( __( 'Taabeer Setup', 'taabeer' ), __( 'Taabeer Setup', 'taabeer' ), 'manage_options', 'taabeer-setup', 'taabeer_setup_screen' );
}
add_action( 'admin_menu', 'taabeer_setup_menu' );

function taabeer_setup_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$imported  = get_option( 'taabeer_demo_imported', '' );
	$elementor = did_action( 'elementor/loaded' ) || class_exists( '\\Elementor\\Plugin' );
	$commerce  = class_exists( 'WooCommerce' );
	?>
	<div class="wrap taabeer-setup-wrap">
		<h1><?php esc_html_e( 'Taabeer Website Setup', 'taabeer' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Create the complete editorial page structure, navigation, Elementor layouts, collections, draft stories and WooCommerce-ready data.', 'taabeer' ); ?></p>

		<?php if ( isset( $_GET['taabeer_import'] ) && 'success' === $_GET['taabeer_import'] ) : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'The Taabeer demo has been imported. Review every page and replace concept photography before publication.', 'taabeer' ); ?></p></div>
		<?php endif; ?>

		<div class="taabeer-setup-grid">
			<section class="taabeer-setup-card">
				<h2><?php esc_html_e( 'Required editing tools', 'taabeer' ); ?></h2>
				<p><strong>Elementor:</strong> <?php echo $elementor ? '<span class="taabeer-ok">' . esc_html__( 'Active', 'taabeer' ) . '</span>' : '<span>' . esc_html__( 'Install the free plugin before editing layouts.', 'taabeer' ) . '</span>'; ?></p>
				<?php if ( ! $elementor ) : ?><p><a class="button" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' ) ); ?>"><?php esc_html_e( 'Install Elementor', 'taabeer' ); ?></a></p><?php endif; ?>
				<p><strong>WooCommerce:</strong> <?php echo $commerce ? '<span class="taabeer-ok">' . esc_html__( 'Active', 'taabeer' ) . '</span>' : '<span>' . esc_html__( 'Optional now; required before product import and selling.', 'taabeer' ) . '</span>'; ?></p>
				<?php if ( ! $commerce ) : ?><p><a class="button" href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' ) ); ?>"><?php esc_html_e( 'Install WooCommerce', 'taabeer' ); ?></a></p><?php endif; ?>
			</section>

			<section class="taabeer-setup-card">
				<h2><?php esc_html_e( 'Import the site', 'taabeer' ); ?></h2>
				<p><?php esc_html_e( 'The importer is safe to run again: it reuses existing Taabeer pages and does not overwrite edited page content.', 'taabeer' ); ?></p>
				<?php if ( $imported ) : ?><p><strong><?php printf( esc_html__( 'Last imported: %s', 'taabeer' ), esc_html( $imported ) ); ?></strong></p><?php endif; ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="taabeer_import_demo">
					<?php wp_nonce_field( 'taabeer_import_demo' ); ?>
					<button class="button button-primary button-hero" type="submit"><?php esc_html_e( 'Import Taabeer Demo', 'taabeer' ); ?></button>
				</form>
			</section>

			<section class="taabeer-setup-card">
				<h2><?php esc_html_e( 'Launch controls', 'taabeer' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="taabeer_save_settings">
					<?php wp_nonce_field( 'taabeer_save_settings' ); ?>
					<p><label for="taabeer_ga_measurement_id"><strong><?php esc_html_e( 'Google Analytics measurement ID', 'taabeer' ); ?></strong></label><br><input class="regular-text" id="taabeer_ga_measurement_id" name="taabeer_ga_measurement_id" value="<?php echo esc_attr( get_option( 'taabeer_ga_measurement_id', '' ) ); ?>" placeholder="G-XXXXXXXXXX"></p>
					<p><label><input type="checkbox" name="taabeer_commerce_enabled" value="yes" <?php checked( get_option( 'taabeer_commerce_enabled', 'no' ), 'yes' ); ?>> <?php esc_html_e( 'Enable prices and purchasing', 'taabeer' ); ?></label></p>
					<p class="description"><?php esc_html_e( 'Keep purchasing disabled until prices, stock, shipping, returns, taxes and payment testing are complete.', 'taabeer' ); ?></p>
					<p><button class="button" type="submit"><?php esc_html_e( 'Save launch settings', 'taabeer' ); ?></button></p>
				</form>
			</section>
		</div>
	</div>
	<style>.taabeer-setup-wrap{max-width:1120px}.taabeer-setup-wrap>.description{font-size:16px;max-width:760px}.taabeer-setup-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:28px}.taabeer-setup-card{background:#fff;border:1px solid #dcdcde;padding:24px}.taabeer-setup-card h2{margin-top:0}.taabeer-ok{color:#0a6b45;font-weight:600}</style>
	<?php
}

function taabeer_handle_settings_save() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'taabeer_save_settings' ) ) {
		wp_die( esc_html__( 'You are not allowed to change these settings.', 'taabeer' ) );
	}
	$measurement = isset( $_POST['taabeer_ga_measurement_id'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['taabeer_ga_measurement_id'] ) ) ) : '';
	if ( $measurement && ! preg_match( '/^G-[A-Z0-9]+$/', $measurement ) ) {
		$measurement = '';
	}
	update_option( 'taabeer_ga_measurement_id', $measurement );
	update_option( 'taabeer_commerce_enabled', isset( $_POST['taabeer_commerce_enabled'] ) ? 'yes' : 'no' );
	wp_safe_redirect( admin_url( 'themes.php?page=taabeer-setup&settings-updated=1' ) );
	exit;
}
add_action( 'admin_post_taabeer_save_settings', 'taabeer_handle_settings_save' );

function taabeer_handle_demo_import() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'taabeer_import_demo' ) ) {
		wp_die( esc_html__( 'You are not allowed to import this demo.', 'taabeer' ) );
	}
	taabeer_run_demo_import();

	wp_safe_redirect( admin_url( 'themes.php?page=taabeer-setup&taabeer_import=success' ) );
	exit;
}
add_action( 'admin_post_taabeer_import_demo', 'taabeer_handle_demo_import' );

/**
 * Run the idempotent content import from the admin screen or WP-CLI.
 *
 * @return array Imported page IDs keyed by slug.
 */
function taabeer_run_demo_import() {

	taabeer_register_content_types();
	$pages = taabeer_import_pages();
	taabeer_import_terms();
	taabeer_import_navigation( $pages );
	taabeer_import_layouts();
	taabeer_import_draft_story();
	if ( class_exists( 'WooCommerce' ) ) {
		taabeer_import_woocommerce_data();
	}

	if ( ! empty( $pages['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['home'] );
	}
	if ( ! empty( $pages['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $pages['privacy-policy'] );
	}
	update_option( 'blogname', 'TAABEER' );
	update_option( 'blogdescription', 'Pakistani design, thoughtfully curated' );
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'taabeer_demo_imported', current_time( 'mysql' ) );
	update_option( 'taabeer_commerce_enabled', get_option( 'taabeer_commerce_enabled', 'no' ) );
	flush_rewrite_rules();

	return $pages;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'taabeer import',
		function () {
			$pages = taabeer_run_demo_import();
			WP_CLI::success( sprintf( 'Taabeer demo imported. %d page records are available.', count( $pages ) ) );
		}
	);
}

function taabeer_import_pages() {
	$pages = array(
		'home' => array(
			'Home', 'publish', 0,
			'',
			taabeer_elementor_home_data(),
		),
		'collections' => array(
			'Collections', 'publish', 0,
			'<p>Explore Pakistani design through five collections, each bringing its own perspective to what we wear, the spaces we inhabit and the art we choose to live with.</p>' . taabeer_collection_page_markup(),
			taabeer_elementor_standard_page( 'Collections', 'Explore Pakistani design through five collections, each bringing its own perspective to what we wear, the spaces we inhabit and the art we choose to live with.', taabeer_collection_page_markup() ),
		),
		'discover-pakistan' => array(
			'Discover Pakistan', 'publish', 0,
			'<p>Discover the places, people and ideas behind Pakistani design. Through regional stories, contemporary work and new creative voices, TAABEER invites you to explore a culture in the making.</p>',
			taabeer_elementor_standard_page( 'Discover Pakistan', 'Discover the places, people and ideas behind Pakistani design. Through regional stories, contemporary work and new creative voices, TAABEER invites you to explore a culture in the making.', taabeer_discover_page_markup() ),
		),
		'regions' => array(
			'Pakistan by Region', 'publish', 'discover-pakistan',
			'<p>Explore Pakistan through the places and people behind its design. Approved regional pages introduce makers, materials and creative practices alongside contemporary interpretations.</p>',
			taabeer_elementor_standard_page( 'Pakistan by Region', 'Explore Pakistan through the places and people behind its design.', taabeer_region_cards_markup() ),
		),
		'pakistan-in-the-making' => array(
			'Pakistan in the Making', 'publish', 'discover-pakistan',
			'<p>How do places, encounters and changing ways of life become part of design? Through individual pieces and the voices of their creators, TAABEER explores cultural connections and the ways designers interpret them today.</p>',
			taabeer_elementor_standard_page( 'Pakistan in the Making', 'How do places, encounters and changing ways of life become part of design?', '<p>Research-led stories will explore specific objects, places, periods and maker testimony. Articles remain in draft until the historical context, imagery and attributions are approved.</p>' ),
		),
		'contemporary-pakistan' => array(
			'Contemporary Pakistan', 'publish', 'discover-pakistan',
			'<p>Discover Pakistani design through the people creating it today, and the choices behind form, material and everyday life.</p>',
			taabeer_elementor_standard_page( 'Contemporary Pakistan', 'Discover Pakistani design through the people creating it today.', '<p>TAABEER looks at what creators carry forward, what they question and what they make their own across clothing, jewellery, furniture, interiors and art.</p>' ),
		),
		'new-voices' => array(
			'New Voices', 'publish', 'discover-pakistan',
			'<p>Meet the designers and independent studios developing their own language within Pakistani design.</p>',
			taabeer_elementor_standard_page( 'New Voices', 'Meet designers and independent studios developing their own language within Pakistani design.', '<p>Approved profiles will appear here after biographies, quotations, credits and image permissions have been confirmed.</p>' ),
		),
		'about' => array(
			'About Taabeer', 'publish', 0,
			taabeer_about_copy(),
			taabeer_elementor_standard_page( 'About TAABEER', 'TAABEER began with a desire to present Pakistani design with the attention it deserves.', taabeer_about_copy() ),
		),
		'partnerships' => array(
			'Partnerships', 'publish', 0,
			taabeer_partnership_copy(),
			taabeer_elementor_standard_page( 'Create a connection with TAABEER', 'We welcome conversations with Pakistani designers, artists, makers and brands whose work brings a distinctive perspective to our collections.', taabeer_partnership_copy() . '<p><a class="button" href="' . esc_url( home_url( '/contact/?enquiry=partnership' ) ) . '">Discuss a partnership</a></p>' ),
		),
		'contact' => array(
			'Contact', 'publish', 0,
			'<p>For questions about TAABEER, our forthcoming collections or partnership opportunities, we would be pleased to hear from you.</p>[taabeer_contact_form]',
			taabeer_elementor_contact_data(),
		),
		'privacy-policy' => array(
			'Privacy Policy', 'publish', 0,
			taabeer_privacy_policy_copy(),
			taabeer_elementor_standard_page( 'Privacy Policy', 'How TAABEER handles information shared through this website.', taabeer_privacy_policy_copy() ),
		),
		'cookie-policy' => array(
			'Cookie Policy', 'publish', 0,
			taabeer_cookie_policy_copy(),
			taabeer_elementor_standard_page( 'Cookie Policy', 'The choices available when this website uses cookies and similar technologies.', taabeer_cookie_policy_copy() ),
		),
	);

	foreach ( array( 'punjab' => 'Punjab', 'balochistan' => 'Balochistan', 'sindh' => 'Sindh', 'khyber-pakhtunkhwa' => 'Khyber Pakhtunkhwa', 'gilgit-baltistan' => 'Gilgit-Baltistan' ) as $slug => $title ) {
		$pages[ $slug ] = array(
			$title, 'draft', 'regions',
			'<p>Regional introduction, verified craft examples, maker profiles and approved imagery are required before publication.</p>',
			taabeer_elementor_standard_page( $title, 'Regional page template', '<h2>Craft and design</h2><p>Add researched, verified examples.</p><h2>Meet the makers</h2><p>Add approved artisan and designer profiles.</p><h2>The TAABEER edit</h2><p>Related confirmed pieces will appear here.</p><h2>Explore the stories</h2><p>Link approved journal features.</p>' ),
		);
	}

	$ids = array();
	foreach ( $pages as $slug => $page ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$parent = is_string( $page[2] ) && isset( $ids[ $page[2] ] ) ? $ids[ $page[2] ] : absint( $page[2] );
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $page[0],
				'post_name'    => $slug,
				'post_status'  => $page[1],
				'post_parent'  => $parent,
				'post_content' => $page[3],
			)
		);
		if ( ! is_wp_error( $id ) ) {
			$ids[ $slug ] = $id;
			update_post_meta( $id, '_taabeer_unapproved', '1' );
			taabeer_apply_elementor_data( $id, $page[4], 'wp-page' );
		}
	}
	return $ids;
}

function taabeer_apply_elementor_data( $post_id, $data, $template_type = 'wp-page' ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $template_type );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.25.0' );
	update_post_meta( $post_id, '_wp_page_template', 'elementor_full_width' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
}

function taabeer_elementor_id( $seed ) {
	return substr( md5( 'taabeer-' . $seed ), 0, 8 );
}

function taabeer_elementor_widget_section( $widget_type, $settings, $seed ) {
	return array(
		'id'       => taabeer_elementor_id( 'section-' . $seed ),
		'elType'   => 'section',
		'settings' => array( 'content_width' => 'full', 'stretch_section' => 'section-stretched', 'gap' => 'no' ),
		'elements' => array(
			array(
				'id'       => taabeer_elementor_id( 'column-' . $seed ),
				'elType'   => 'column',
				'settings' => array( '_column_size' => 100 ),
				'elements' => array(
					array( 'id' => taabeer_elementor_id( 'widget-' . $seed ), 'elType' => 'widget', 'widgetType' => $widget_type, 'settings' => $settings, 'elements' => array() ),
				),
			),
		),
	);
}

function taabeer_elementor_standard_page( $title, $intro, $html ) {
	$content = '<header class="page-hero shell"><p class="eyebrow">TAABEER</p><h1>' . esc_html( $title ) . '</h1><p class="page-hero__intro">' . esc_html( $intro ) . '</p></header><div class="entry-content shell shell--reading section">' . wp_kses_post( $html ) . '</div>';
	return array( taabeer_elementor_widget_section( 'text-editor', array( 'editor' => $content ), sanitize_title( $title ) ) );
}

function taabeer_elementor_contact_data() {
	return array(
		taabeer_elementor_widget_section( 'text-editor', array( 'editor' => '<header class="page-hero shell"><p class="eyebrow">Contact</p><h1>Get in touch</h1><p class="page-hero__intro">For questions about TAABEER, our forthcoming collections or partnership opportunities, we would be pleased to hear from you.</p></header>' ), 'contact-heading' ),
		taabeer_elementor_widget_section( 'taabeer-contact', array(), 'contact-form' ),
	);
}

function taabeer_elementor_home_data() {
	return array(
		taabeer_elementor_widget_section( 'taabeer-hero', array(), 'home-hero' ),
		taabeer_elementor_widget_section( 'text-editor', array( 'editor' => '<section class="home-intro section shell"><div class="home-intro__label"><p class="eyebrow">The TAABEER perspective</p></div><div class="home-intro__copy"><h2>A considered perspective on Pakistani design</h2><p>From textiles and pieces for the home to art and adornment, our focus is on work with a distinctive point of view. We look at the detail, the material and the story behind each piece, creating a selection that connects cultural expression with the vision of its designers today.</p></div></section>' ), 'home-intro' ),
		taabeer_elementor_widget_section( 'taabeer-collections', array(), 'home-collections' ),
		taabeer_elementor_widget_section( 'taabeer-split-feature', array( 'tone' => 'green', 'eyebrow' => 'Discover Pakistan', 'title' => 'A culture in the making', 'body' => '<p>Explore the regions, creative voices and evolving ideas behind the TAABEER selection. From heritage practices to contemporary design, discover the stories that bring each piece into focus.</p>', 'link_text' => 'Discover Pakistan', 'link' => array( 'url' => home_url( '/discover-pakistan/' ) ), 'image' => array( 'url' => taabeer_demo_image_url( 'discover-pakistan.webp' ) ) ), 'home-discover' ),
		taabeer_elementor_widget_section( 'taabeer-split-feature', array( 'tone' => 'ivory', 'eyebrow' => 'About TAABEER', 'title' => 'Heritage and contemporary design, side by side', 'body' => '<p>Based in London, TAABEER is building a curated selection that presents Pakistani design through the work itself, the people behind it and the place it can hold in our lives today.</p>', 'link_text' => 'Read our story', 'link' => array( 'url' => home_url( '/about/' ) ), 'image' => array( 'url' => taabeer_demo_image_url( 'about-material.webp' ) ) ), 'home-about' ),
		taabeer_elementor_widget_section( 'taabeer-story-grid', array( 'title' => 'Behind the pieces', 'count' => 3 ), 'home-stories' ),
		taabeer_elementor_widget_section( 'text-editor', array( 'editor' => '<section class="newsletter-section section section--ivory-dark"><div class="shell newsletter-section__inner"><div><p class="eyebrow">Stay close to TAABEER</p><h2>New collections, stories and upcoming releases</h2></div><p>Mailing-list registration will open once the subscription service and privacy wording are approved.</p></div></section><section class="partnership-cta section shell"><p class="eyebrow">Partnerships</p><h2>Create a connection with TAABEER</h2><p>We welcome conversations with Pakistani designers, artists, makers and brands whose work brings a distinctive perspective to our collections.</p><p><a class="button" href="' . esc_url( home_url( '/contact/?enquiry=partnership' ) ) . '">Discuss a partnership</a></p></section>' ), 'home-closing' ),
	);
}

function taabeer_import_layouts() {
	$layouts = array(
		'header' => array( 'TAABEER Header', 'taabeer-header' ),
		'footer' => array( 'TAABEER Footer', 'taabeer-footer' ),
	);
	foreach ( $layouts as $location => $layout ) {
		$found = get_posts( array( 'post_type' => 'taabeer_layout', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_taabeer_layout_location', 'meta_value' => $location ) );
		if ( $found ) { continue; }
		$id = wp_insert_post( array( 'post_type' => 'taabeer_layout', 'post_title' => $layout[0], 'post_status' => 'publish' ) );
		if ( ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_taabeer_layout_location', $location );
			taabeer_apply_elementor_data( $id, array( taabeer_elementor_widget_section( $layout[1], array(), 'layout-' . $location ) ), 'wp-post' );
		}
	}
}

function taabeer_import_terms() {
	$collections = array(
		'The Art of Wear'       => 'Textiles and clothing chosen for their character, detail and ease of wear.',
		'The Art of Adornment'  => 'Jewellery and decorative accessories that bring a distinctive finishing touch.',
		'The Art of Living'     => 'Furniture, decorative pieces and useful designs that bring character to everyday spaces.',
		'The Art of Expression' => 'Art and collectible work selected for its visual language and point of view.',
		'The Art of Leather'    => 'Bags and leather accessories chosen for their shape, finish and everyday purpose.',
	);
	foreach ( $collections as $name => $description ) {
		if ( ! term_exists( $name, 'taabeer_collection' ) ) { wp_insert_term( $name, 'taabeer_collection', array( 'description' => $description ) ); }
	}
	foreach ( array( 'Punjab', 'Balochistan', 'Sindh', 'Khyber Pakhtunkhwa', 'Gilgit-Baltistan' ) as $region ) {
		if ( ! term_exists( $region, 'taabeer_region' ) ) { wp_insert_term( $region, 'taabeer_region' ); }
	}
}

function taabeer_import_navigation( $pages ) {
	$menu_name = 'TAABEER Primary';
	$menu = wp_get_nav_menu_object( $menu_name );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
	if ( is_wp_error( $menu_id ) ) { return; }
	if ( ! wp_get_nav_menu_items( $menu_id ) ) {
		foreach ( array( 'collections' => 'Collections', 'discover-pakistan' => 'Discover Pakistan', 'about' => 'About', 'partnerships' => 'Partnerships', 'contact' => 'Contact' ) as $slug => $label ) {
			if ( isset( $pages[ $slug ] ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $label, 'menu-item-object-id' => $pages[ $slug ], 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ); }
		}
		wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Journal', 'menu-item-url' => get_post_type_archive_link( 'heritage_story' ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish' ) );
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	$locations['footer']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

function taabeer_import_draft_story() {
	if ( get_page_by_path( 'a-considered-place-for-pakistani-design', OBJECT, 'heritage_story' ) ) { return; }
	$id = wp_insert_post( array( 'post_type' => 'heritage_story', 'post_status' => 'draft', 'post_title' => 'A considered place for Pakistani design', 'post_name' => 'a-considered-place-for-pakistani-design', 'post_excerpt' => 'An introduction to the thinking behind the TAABEER selection.', 'post_content' => taabeer_first_story_copy() ) );
	if ( ! is_wp_error( $id ) ) { update_post_meta( $id, '_taabeer_unapproved', '1' ); }
}

function taabeer_import_woocommerce_data() {
	foreach ( array( 'The Art of Wear', 'The Art of Adornment', 'The Art of Living', 'The Art of Expression', 'The Art of Leather' ) as $category ) {
		if ( ! term_exists( $category, 'product_cat' ) ) { wp_insert_term( $category, 'product_cat' ); }
	}
	if ( get_page_by_path( 'ajrak-stole-working-title', OBJECT, 'product' ) ) { return; }
	$product = new WC_Product_Simple();
	$product->set_name( 'Ajrak Stole — working title' );
	$product->set_slug( 'ajrak-stole-working-title' );
	$product->set_status( 'draft' );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_short_description( 'An Ajrak stole selected for the TAABEER textile edit. Final details remain to be supplied.' );
	$product->set_description( 'Layout sample only. Final pattern, fabric, dimensions, maker, care instructions, imagery, price, stock and fulfilment information must be approved before publication.' );
	$product->set_sold_individually( true );
	$wear_category = get_term_by( 'name', 'The Art of Wear', 'product_cat' );
	if ( $wear_category ) {
		$product->set_category_ids( array( (int) $wear_category->term_id ) );
	}
	$product->save();
	update_post_meta( $product->get_id(), '_taabeer_unapproved', '1' );
}

function taabeer_collection_page_markup() {
	return '<div class="collection-list"><h2><a href="' . esc_url( home_url( '/collection/the-art-of-wear/' ) ) . '">The Art of Wear</a></h2><p>Textiles and clothing chosen for their character, detail and ease of wear.</p><h2><a href="' . esc_url( home_url( '/collection/the-art-of-adornment/' ) ) . '">The Art of Adornment</a></h2><p>Jewellery and decorative accessories that bring a distinctive finishing touch.</p><h2><a href="' . esc_url( home_url( '/collection/the-art-of-living/' ) ) . '">The Art of Living</a></h2><p>Furniture, decorative pieces and useful designs that bring character to everyday spaces.</p><h2><a href="' . esc_url( home_url( '/collection/the-art-of-expression/' ) ) . '">The Art of Expression</a></h2><p>Art and collectible work selected for its visual language and point of view.</p><h2><a href="' . esc_url( home_url( '/collection/the-art-of-leather/' ) ) . '">The Art of Leather</a></h2><p>Bags and leather accessories chosen for shape, finish and everyday purpose.</p></div>';
}

function taabeer_discover_page_markup() {
	return '<div class="editorial-index"><h2><a href="' . esc_url( home_url( '/discover-pakistan/regions/' ) ) . '">Pakistan by Region</a></h2><p>Punjab, Balochistan, Sindh, Khyber Pakhtunkhwa and Gilgit-Baltistan.</p><h2><a href="' . esc_url( home_url( '/discover-pakistan/pakistan-in-the-making/' ) ) . '">Pakistan in the Making</a></h2><p>Cultural encounters, historical connections and the changing context of design.</p><h2><a href="' . esc_url( home_url( '/discover-pakistan/contemporary-pakistan/' ) ) . '">Contemporary Pakistan</a></h2><p>Design through the perspectives of people working today.</p><h2><a href="' . esc_url( home_url( '/discover-pakistan/new-voices/' ) ) . '">New Voices</a></h2><p>Emerging designers and independent studios.</p></div>';
}

function taabeer_region_cards_markup() {
	return '<div class="region-list"><p><a href="' . esc_url( home_url( '/region/punjab/' ) ) . '">Punjab</a></p><p><a href="' . esc_url( home_url( '/region/balochistan/' ) ) . '">Balochistan</a></p><p><a href="' . esc_url( home_url( '/region/sindh/' ) ) . '">Sindh</a></p><p><a href="' . esc_url( home_url( '/region/khyber-pakhtunkhwa/' ) ) . '">Khyber Pakhtunkhwa</a></p><p><a href="' . esc_url( home_url( '/region/gilgit-baltistan/' ) ) . '">Gilgit-Baltistan</a></p></div>';
}

function taabeer_privacy_policy_copy() {
	return '<h2>Information you share</h2><p>When you contact TAABEER, we may receive your name, email address, telephone number and the details included in your enquiry. If newsletter registration is introduced, we will also record your subscription choice.</p><h2>How information is used</h2><p>We use this information to reply to enquiries, discuss partnerships, provide requested updates and operate the website. We only keep it for as long as it is needed for those purposes or to meet applicable record-keeping requirements.</p><h2>Website services</h2><p>Essential technical data may be processed to keep the website secure and working. Optional analytics will only run after consent. If ecommerce is enabled later, this policy will be updated to identify the services used for orders, payments, delivery and customer accounts.</p><h2>Sharing and access</h2><p>Information may be handled by trusted website, hosting or email providers where needed to provide the service. You can ask about the information TAABEER holds about you, request a correction or raise a privacy question through the contact page.</p><p><a class="button button--outline" href="' . esc_url( home_url( '/contact/' ) ) . '">Contact TAABEER</a></p>';
}

function taabeer_cookie_policy_copy() {
	return '<h2>What cookies do</h2><p>Cookies are small files stored by a browser. TAABEER uses essential storage to remember privacy choices and support the secure operation of this website.</p><h2>Optional analytics</h2><p>Analytics will only be used after you select “Accept analytics”. It helps us understand how visitors use the website so we can improve its content and performance. The final service and cookie inventory will be recorded here when analytics is connected.</p><h2>Your choices</h2><p>You can accept optional analytics or continue with essential cookies only. Use “Cookie settings” in the footer at any time to choose again. You can also remove stored cookies through your browser settings.</p><h2>Future services</h2><p>If newsletter, ecommerce, payment or embedded media services introduce additional cookies, this page and the consent controls will be updated before those services are enabled.</p>';
}

function taabeer_about_copy() {
	return '<p>TAABEER began with a desire to present Pakistani design with the attention it deserves: through the work itself, the people behind it and the place it can hold in our lives today.</p><p>Based in London, we are building a curated selection that brings heritage and contemporary design side by side. A textile can carry a familiar pattern into a modern wardrobe. A carefully chosen piece of pottery can bring colour and character to a home. Art can offer a new way of seeing a place we know, or introduce us to one we have yet to discover.</p><p>Our approach is personal and selective. We consider how a piece is made, how it feels and what makes its design distinctive. We aim to give each maker a clear identity within the collection, with descriptions that help customers understand the work they are choosing.</p><p>For those with a connection to Pakistan, TAABEER offers another way to stay close to its creative expression. For those discovering it for the first time, we offer a considered introduction.</p>';
}

function taabeer_partnership_copy() {
	return '<p>Our approach begins with a considered selection. We are interested in the design of each piece, the quality of its making and the story its creator wants to share. We explore opportunities to introduce selected work to a UK audience through thoughtful presentation and clear attribution.</p><p>If you would like to discuss stocking your work or developing a collection together, please share a short introduction, images of your work, materials, pricing, production lead times and contact details.</p>';
}

function taabeer_first_story_copy() {
	return '<p>When we choose something to wear or bring into our home, we are choosing more than an appearance. We are choosing a texture we will return to, a colour we want to live with, or a detail that feels familiar in a new way.</p><p>This is the starting point for TAABEER. We want to create a place where Pakistani design can be encountered through individual pieces, with enough space to understand what makes each one worth a closer look.</p><p>Heritage and contemporary design will sit alongside one another in our selection. We are interested in how a recognisable pattern changes when paired with a simpler silhouette, how a decorative piece feels in a modern room, and how an artist gives a familiar reference a different meaning.</p><p>The way we present the work matters too. A photograph should help you see the surface and proportions. A description should tell you what the piece is made from and how it can be used. Where a maker shares the story behind their work, we want to give that voice room.</p><p>Our collection will develop gradually. Through this journal, we will share the thinking behind those choices and introduce the people whose work helps shape them.</p><p>TAABEER is an invitation to look more closely at Pakistani design, whether it forms part of your own heritage or is something you are discovering for the first time.</p>';
}
