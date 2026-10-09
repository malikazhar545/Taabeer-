<?php
/** Client-owned Elementor, SEO and dormant commerce setup. */
defined( 'ABSPATH' ) || exit;

function taabeer_required_plugins() {
	return array('elementor'=>'elementor/elementor.php','header-footer-elementor'=>'header-footer-elementor/header-footer-elementor.php','wordpress-seo'=>'wordpress-seo/wp-seo.php','woocommerce'=>'woocommerce/woocommerce.php','updraftplus'=>'updraftplus/updraftplus.php','limit-login-attempts-reloaded'=>'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php');
}
function taabeer_install_foundation_plugins() {
	require_once ABSPATH.'wp-admin/includes/plugin.php';
	require_once ABSPATH.'wp-admin/includes/plugin-install.php';
	require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
	foreach(taabeer_required_plugins() as $slug=>$file) {
		if(!file_exists(WP_PLUGIN_DIR.'/'.$file)) {
			$api=plugins_api('plugin_information',array('slug'=>$slug,'fields'=>array('sections'=>false)));
			if(is_wp_error($api)) { return $api; }
			$upgrader=new Plugin_Upgrader(new Automatic_Upgrader_Skin());
			$result=$upgrader->install($api->download_link);
			if(is_wp_error($result) || !$result) { return is_wp_error($result)?$result:new WP_Error('install_failed','Could not install '.$slug.'. Check filesystem permissions.'); }
		}
		if(!is_plugin_active($file)) { $result=activate_plugin($file,'',false,true); if(is_wp_error($result)) { return $result; } }
	}
	return true;
}
function taabeer_visual_menu_items() {
	$locations=get_nav_menu_locations();$items=wp_get_nav_menu_items($locations['primary']??0);$out=array();
	foreach((array)$items as $item) { $out[]=array('_id'=>taabeer_visual_id(),'text'=>$item->title,'link'=>array('url'=>$item->url)); }
	return $out;
}
function taabeer_visual_find_widgets($tree,$name) {
	$out=array(); foreach($tree as $el) { if(($el['widgetType']??'')===$name) {$out[]=$el;} if(!empty($el['elements'])) {$out=array_merge($out,taabeer_visual_find_widgets($el['elements'],$name));} } return $out;
}
function taabeer_visual_header_data($old) {
	$widgets=taabeer_visual_find_widgets($old,'taabeer-header');$s=$widgets[0]['settings']??array();
	return array(
		taabeer_visual_text('<p>'.esc_html($s['announcement']??'A London-based curated house of Pakistani design').'</p>','taabeer-announcement'),
		taabeer_visual_container(array(taabeer_visual_container(array(
			taabeer_visual_container(array(taabeer_visual_heading($s['brand']??'TAABEER','div','site-brand__english',home_url('/')),taabeer_visual_heading($s['urdu']??'تعبیر','div','site-brand__urdu',home_url('/'))),'site-brand'),
			taabeer_visual_widget('taabeer-navigation',array('items'=>taabeer_visual_menu_items()),'tb-nav'),
			taabeer_visual_container(array(taabeer_visual_widget('taabeer-utility',array('kind'=>'search','label'=>'Search')),taabeer_visual_widget('taabeer-utility',array('kind'=>'cart','label'=>'Shopping bag'))),'site-header__actions')
		),'site-header__inner')),'site-header','header')
	);
}
function taabeer_visual_footer_data($id) {
	$html=\Elementor\Plugin::instance()->frontend->get_builder_content_for_display($id);
	$doc=new DOMDocument();$prev=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html);libxml_clear_errors();libxml_use_internal_errors($prev);
	$footer=$doc->getElementsByTagName('footer')->item(0);
	return $footer?array(taabeer_visual_dom_node($footer),taabeer_visual_widget('taabeer-cookie-notice',array())):array();
}
function taabeer_visual_shared_templates() {
	foreach(array('header','footer') as $location) {
		$existing=get_posts(array('post_type'=>'elementor-hf','posts_per_page'=>1,'meta_key'=>'_taabeer_shared','meta_value'=>$location)); if($existing) {continue;}
		$old=get_posts(array('post_type'=>'taabeer_layout','posts_per_page'=>1,'meta_key'=>'_taabeer_layout_location','meta_value'=>$location)); if(!$old) {continue;}
		$data=json_decode(get_post_meta($old[0]->ID,'_elementor_data',true),true)?:array();
		$data='header'===$location?taabeer_visual_header_data($data):taabeer_visual_footer_data($old[0]->ID);
		if(!$data) {continue;}
		$id=wp_insert_post(array('post_type'=>'elementor-hf','post_status'=>'publish','post_title'=>'TAABEER — Site-wide '.ucfirst($location)));
		if(is_wp_error($id)||!$id){continue;}
		taabeer_visual_save($id,$data); update_post_meta($id,'_taabeer_shared',$location); update_post_meta($id,'ehf_template_type','type_'.$location);
		update_post_meta($id,'ehf_target_include_locations',array('rule'=>array('basic-global'),'specific'=>array()));
		update_post_meta($id,'_wp_page_template','elementor_canvas');
	}
}
function taabeer_visual_editor_html($tree) {
	$out='';foreach(taabeer_visual_find_widgets($tree,'text-editor') as $el){$out.=$el['settings']['editor']??'';}return $out;
}
function taabeer_visual_contact_details() {
	$items=array(taabeer_visual_heading('Let’s start a conversation','h2'),taabeer_visual_text('<p>For collections, collaborations and everything in between.</p>'));
	foreach(array(array('Location',get_theme_mod('taabeer_address','London, United Kingdom'),'map-marker-alt'),array('Email','hello@example.com','envelope'),array('Telephone','+44 (0)0000 000000','phone')) as $detail) {
		$items[]=taabeer_visual_widget('icon-box',array('title_text'=>$detail[0],'description_text'=>$detail[1],'selected_icon'=>array('value'=>'fas fa-'.$detail[2],'library'=>'fa-solid'),'position'=>'left','title_size'=>'h3'),'tb-contact-detail');
	}
	$items[]=taabeer_visual_text('<p>Email and telephone are placeholders. Confirmed contact details will follow.</p>','tb-contact-placeholder');
	return taabeer_visual_container($items,'tb-contact-aside');
}
function taabeer_visual_backup_setup() {
	global $updraftplus;
	if(!get_option('taabeer_backup_foundation') && is_object($updraftplus)) {
		foreach(array('updraft_interval'=>'schedule_backup','updraft_interval_database'=>'schedule_backup_database') as $option=>$method) {
			$current=get_option($option); if(!$current || 'manual'===$current) {update_option($option,$updraftplus->$method('daily'));}
		}
		add_option('updraft_retain',7);add_option('updraft_retain_db',14);update_option('taabeer_backup_foundation',1);
	}
}
function taabeer_visual_design_system() {
	$id=taabeer_ensure_elementor_kit();if(!$id){return;}
	$settings=get_post_meta($id,'_elementor_page_settings',true);$settings=is_array($settings)?$settings:array();
	if(get_post_meta($id,'_taabeer_kit_needs_brand_defaults',true) || empty($settings['system_colors'])) {$settings['system_colors']=array(array('_id'=>'primary','title'=>'Primary','color'=>'#173F35'),array('_id'=>'secondary','title'=>'Secondary','color'=>'#0D2C25'),array('_id'=>'text','title'=>'Text','color'=>'#252722'),array('_id'=>'accent','title'=>'Accent','color'=>'#173F35'));}
	if(get_post_meta($id,'_taabeer_kit_needs_brand_defaults',true) || empty($settings['system_typography'])) {$settings['system_typography']=array();foreach(array('primary'=>array('Baskerville','400'),'secondary'=>array('Baskerville','400'),'text'=>array('Helvetica','400'),'accent'=>array('Helvetica','600')) as $key=>$font){$settings['system_typography'][]=array('_id'=>$key,'title'=>ucfirst($key),'typography_typography'=>'custom','typography_font_family'=>$font[0],'typography_font_weight'=>$font[1]);}}
	update_post_meta($id,'_elementor_page_settings',$settings);delete_post_meta($id,'_elementor_css');delete_post_meta($id,'_taabeer_kit_needs_brand_defaults');
}
/** Use Elementor's own document API, leaving vendor code and existing valid kits untouched. */
function taabeer_ensure_elementor_kit() {
	if(!did_action('elementor/init') || !isset(\Elementor\Plugin::instance()->kits_manager)){return 0;}
	$manager=\Elementor\Plugin::instance()->kits_manager;
	$id=(int)get_option('elementor_active_kit');
	if($id && $manager->is_kit($id) && 'trash'!==get_post_status($id)){return $id;}
	$available=get_posts(array('post_type'=>'elementor_library','post_status'=>'publish','posts_per_page'=>1,'meta_key'=>'_elementor_template_type','meta_value'=>'kit'));
	$id=$available?(int)$available[0]->ID:$manager->create_default();
	if(!$id || is_wp_error($id)){return 0;}
	if(!$available){update_post_meta($id,'_taabeer_kit_needs_brand_defaults',1);}
	update_option('elementor_active_kit',$id);
	\Elementor\Plugin::instance()->files_manager->clear_cache();
	return $id;
}
function taabeer_visual_redesign_page($post,$tree) {
	$slug=$post->post_name;$html=taabeer_visual_editor_html($tree);
	if(!$html){return taabeer_visual_convert_tree($tree);}
	$doc=new DOMDocument();$prev=libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html);libxml_clear_errors();libxml_use_internal_errors($prev);
	$xpath=new DOMXPath($doc);$hero=$xpath->query('//header[contains(@class,"page-hero")]')->item(0);$body=$xpath->query('//div[contains(@class,"entry-content")]')->item(0);
	if(!$hero || !in_array($slug,array('about','partnerships','contact','regions','pakistan-in-the-making','contemporary-pakistan','new-voices','privacy-policy','cookie-policy'),true)){return taabeer_visual_convert_tree($tree);}
	$all_widgets = array(); $scan = function($nodes) use (&$scan,&$all_widgets) { foreach($nodes as $node) { if(isset($node['widgetType'])){$all_widgets[]=$node['widgetType'];} if(!empty($node['elements'])){$scan($node['elements']);} } }; $scan($tree);
	if(array_diff($all_widgets,array('text-editor','taabeer-contact'))) { return taabeer_visual_convert_tree($tree); }
	$heading=taabeer_visual_dom_node($hero);
	$body_elements=$body?taabeer_visual_parse(taabeer_dom_inner($body)):array();
	// Copy remains intact; only the composition changes. Any custom widgets are retained below.
	$images=array('about'=>'story-object.webp','partnerships'=>'story-studio.webp','regions'=>'story-textile.webp','pakistan-in-the-making'=>'story-object.webp','contemporary-pakistan'=>'collection-expression.webp','new-voices'=>'discover-pakistan.webp');
	if('contact'===$slug){
		$forms=taabeer_visual_find_widgets($tree,'taabeer-contact');if(!$forms){$forms[]=taabeer_visual_widget('taabeer-contact',array());}
		$aside=taabeer_visual_contact_details();
		return array($heading,taabeer_visual_container(array($aside,taabeer_visual_container($forms,'tb-contact-fields')),'tb-contact-grid shell'));
	}
	if(in_array($slug,array('privacy-policy','cookie-policy'),true)){return array($heading,taabeer_visual_container($body_elements,'tb-policy-body shell'));}
	$image=taabeer_visual_image(taabeer_demo_image_url($images[$slug]),'tb-editorial-image');
	$class='about'===$slug?'tb-about-body':('partnerships'===$slug?'tb-partnership-body':'tb-editorial-body');
	return array($heading,taabeer_visual_container(array($image,taabeer_visual_container($body_elements,'tb-editorial-copy')),$class.' shell'));
}
function taabeer_visual_create_template($location,$title,$data) {
	$existing=get_posts(array('post_type'=>'taabeer_layout','post_status'=>array('publish','draft'),'posts_per_page'=>1,'meta_key'=>'_taabeer_layout_location','meta_value'=>$location));if($existing){return $existing[0]->ID;}
	$id=wp_insert_post(array('post_type'=>'taabeer_layout','post_status'=>'publish','post_title'=>$title));if(is_wp_error($id)){return 0;}
	update_post_meta($id,'_taabeer_layout_location',$location);taabeer_visual_save($id,$data);return $id;
}
function taabeer_visual_dynamic($kind,$empty='',$class='') {return taabeer_visual_widget('taabeer-dynamic',array('kind'=>$kind,'empty'=>$empty),$class);}
function taabeer_visual_archive_templates() {
	$journal=taabeer_visual_parse('<header class="page-hero shell"><p class="eyebrow">Journal &amp; Heritage Stories</p><h1>The stories behind the selection</h1><p class="page-hero__intro">A closer look at the materials, ideas and people behind Pakistani design. Through conversations, studio visits and collection notes, the TAABEER journal explores how heritage and contemporary expression meet.</p></header>');
	$journal[]=taabeer_visual_container(array(taabeer_visual_image(taabeer_demo_image_url('story-textile.webp')),taabeer_visual_dynamic('stories','Our first stories are coming soon.')),'tb-journal-body shell');
	taabeer_visual_create_template('journal','Journal — Listing',$journal);
	foreach(array('collection'=>'Collection','region'=>'Region','profiles'=>'Creative Voices') as $key=>$label){
		taabeer_visual_create_template($key,$label.' — Archive',array(taabeer_visual_container(array(taabeer_visual_heading($label,'p','eyebrow'),taabeer_visual_dynamic('title'),taabeer_visual_dynamic('description')),'tb-archive-heading shell'),taabeer_visual_container(array(taabeer_visual_dynamic('profiles'===$key?'profiles':'stories','New pieces and stories are being prepared.')),'shell section')));
	}
	taabeer_visual_create_template('shop','Shop — Collection Preview',array(taabeer_visual_container(array(taabeer_visual_heading('The TAABEER Edit','p','eyebrow'),taabeer_visual_heading('Considered pieces. Lasting stories.','h1'),taabeer_visual_text('<p>Explore the next chapter of TAABEER. Online collections will open when the pieces are ready.</p>')),'tb-archive-heading shell'),taabeer_visual_container(array(taabeer_visual_dynamic('products','Our online collection is being prepared.')),'shell section')));
	taabeer_visual_create_template('product','Product — Editorial Template',array(taabeer_visual_container(array(taabeer_visual_dynamic('product-gallery'),taabeer_visual_dynamic('product-summary')),'tb-product-grid shell'),taabeer_visual_container(array(taabeer_visual_dynamic('product-tabs')),'shell section')));
	taabeer_visual_create_template('404','404 — Page not found',taabeer_visual_parse('<section class="tb-error shell"><p class="eyebrow">404 / A different path</p><h1>This page has moved beyond view.</h1><p>Return to TAABEER or continue exploring our collections and stories.</p><div class="button-row"><a class="button" href="'.esc_url(home_url('/')).'">Return home</a><a class="button button--outline" href="'.esc_url(home_url('/collections/')).'">Explore collections</a></div></section>'));
	taabeer_visual_create_template('search','Search — Results',array(taabeer_visual_container(array(taabeer_visual_dynamic('title'),taabeer_visual_dynamic('search','No matching stories or pieces yet. Try another search.')),'tb-search-page shell')));
	taabeer_visual_create_template('profile','Creative Profile — Story',array(taabeer_visual_container(array(taabeer_visual_dynamic('image'),taabeer_visual_container(array(taabeer_visual_heading('Creative Voice','p','eyebrow'),taabeer_visual_dynamic('title'),taabeer_visual_dynamic('description'),taabeer_visual_dynamic('content')),'tb-editorial-copy')),'tb-editorial-body shell')));
}
function taabeer_visual_commerce_setup() {
	if(!class_exists('WooCommerce')){return;}
	add_option('taabeer_commerce_enabled','no');
	if(!get_option('taabeer_commerce_foundation')) {
		taabeer_import_woocommerce_data();
		foreach(array('size'=>'Size','colour'=>'Colour','material'=>'Material') as $slug=>$name){if(!taxonomy_exists('pa_'.$slug) && !wc_attribute_taxonomy_id_by_name($slug)){wc_create_attribute(array('name'=>$name,'slug'=>$slug,'type'=>'select','order_by'=>'menu_order','has_archives'=>false));}}
		$zones=WC_Shipping_Zones::get_zones();if(!$zones){$uk=new WC_Shipping_Zone();$uk->set_zone_name('United Kingdom — rates pending');$uk->set_zone_order(1);$uk->add_location('GB','country');$uk->save();$international=new WC_Shipping_Zone();$international->set_zone_name('International — destinations and rates pending');$international->set_zone_order(2);$international->save();}
		foreach(array('shop'=>'Shop','cart'=>'Basket','checkout'=>'Checkout','myaccount'=>'My account') as $key=>$title){
			$option='woocommerce_'.$key.'_page_id';$id=get_option($option);if(!$id){$id=wp_insert_post(array('post_type'=>'page','post_status'=>'draft','post_title'=>$title,'post_name'=>sanitize_title($title)));update_option($option,$id);}
			if($id && !get_post_meta($id,'_elementor_data',true)){$shortcodes=array('cart'=>'[woocommerce_cart]','checkout'=>'[woocommerce_checkout]','myaccount'=>'[woocommerce_my_account]');$els=array(taabeer_visual_container(array(taabeer_visual_heading($title,'h1')),'tb-archive-heading shell'));if(isset($shortcodes[$key])){$els[]=taabeer_visual_container(array(taabeer_visual_widget('shortcode',array('shortcode'=>$shortcodes[$key]))),'shell section');}taabeer_visual_save($id,$els);}
		}
		update_option('taabeer_commerce_foundation',1);
	}
	if(!get_option('taabeer_commerce_draft_ready') && !taabeer_commerce_is_live() && !wc_get_orders(array('limit'=>1,'return'=>'ids'))) {
		if('USD'===get_option('woocommerce_currency') && 'US:CA'===get_option('woocommerce_default_country')) {update_option('woocommerce_currency','GBP');update_option('woocommerce_default_country','GB');}
		foreach(array('shop','cart','checkout','myaccount') as $key) {$id=get_option('woocommerce_'.$key.'_page_id');if($id){wp_update_post(array('ID'=>$id,'post_status'=>'draft'));update_post_meta($id,'_taabeer_preview_only',1);}}
		update_option('woocommerce_coming_soon','yes');update_option('woocommerce_store_pages_only','yes');update_option('taabeer_commerce_draft_ready',1);
	}
}
function taabeer_visual_seo_setup() {
	$seo=array(
		'home'=>array('Pakistani Design & Contemporary Heritage | TAABEER','Explore TAABEER, a London-based curated house of Pakistani design. Discover considered collections, contemporary craft and the stories behind each selection.','Pakistani design'),
		'collections'=>array('Curated Pakistani Design Collections | TAABEER','Explore TAABEER collections across textiles, homeware, leather, jewellery and art. Discover contemporary Pakistani design through materials and thoughtful making.','Pakistani design collections'),
		'discover-pakistan'=>array('Discover Pakistan: Craft, Culture & Design | TAABEER','Discover Pakistan through its craft, culture and contemporary design. Explore regional influences, creative voices and the stories that inform TAABEER.','discover Pakistan'),
		'about'=>array('About TAABEER | Pakistani Design, Thoughtfully Curated','Meet TAABEER, a London-based house of Pakistani design. Learn about our philosophy, considered curation and approach to heritage and contemporary expression.','about TAABEER'),
		'partnerships'=>array('Design & Retail Partnerships | TAABEER','Connect with TAABEER about design collaborations, retail partnerships and considered collections. We welcome introductions from designers, artists and makers.','TAABEER partnerships'),
		'contact'=>array('Contact TAABEER | Collections & Partnership Enquiries','Get in touch with TAABEER in London for collection enquiries, creative collaborations and partnerships. Share your message with our team through the contact form.','contact TAABEER'),
		'privacy-policy'=>array('Privacy Policy | TAABEER','Read how TAABEER handles information shared through enquiries and website services, including privacy choices and ways to contact us about your information.','TAABEER privacy policy'),
		'cookie-policy'=>array('Cookie Policy & Privacy Choices | TAABEER','Learn how TAABEER uses essential cookies and optional analytics. Review your cookie choices and manage your preferences when exploring the website.','TAABEER cookie policy')
	);
	$posts=get_posts(array('post_type'=>array('page','heritage_story','product'),'post_status'=>array('publish','draft'),'numberposts'=>-1));
	foreach($posts as $post){
		if(isset($seo[$post->post_name])) {
			// The public brand pages use the supplied handover copy; policies remain pending review.
			if('publish'===$post->post_status && !in_array($post->post_name,array('privacy-policy','cookie-policy'),true)){delete_post_meta($post->ID,'_taabeer_unapproved');}
			$entry=$seo[$post->post_name];$old_title=get_post_meta($post->ID,'_yoast_wpseo_title',true);
			if(!$old_title || $old_title===$post->post_title.' %%sep%% TAABEER') {update_post_meta($post->ID,'_yoast_wpseo_title',$entry[0]);}
			if(!metadata_exists('post',$post->ID,'_yoast_wpseo_metadesc')) {update_post_meta($post->ID,'_yoast_wpseo_metadesc',$entry[1]);}
			add_post_meta($post->ID,'_yoast_wpseo_focuskw',$entry[2],true);
		}
		if(!metadata_exists('post',$post->ID,'_yoast_wpseo_title')){update_post_meta($post->ID,'_yoast_wpseo_title',$post->post_title.' %%sep%% TAABEER');}
		if(!metadata_exists('post',$post->ID,'_yoast_wpseo_metadesc')){
			$text=get_post_meta($post->ID,'_taabeer_meta_description',true);if(!$text){$html=taabeer_visual_editor_html(json_decode(get_post_meta($post->ID,'_elementor_data',true),true)?:array());$text=wp_trim_words(wp_strip_all_tags($post->post_excerpt?:($post->post_content?:$html)),25,'');}if($text){update_post_meta($post->ID,'_yoast_wpseo_metadesc',$text);}
		}
	}
}
function taabeer_complete_visual_foundation() {
	if(get_option('taabeer_visual_foundation')==='1.1.0'){return;}
	if(!did_action('elementor/loaded') || !function_exists('hfe_render_header') || !post_type_exists('elementor-hf') || !defined('WPSEO_VERSION') || !class_exists('WooCommerce')) {return;}
	$posts=get_posts(array('post_type'=>array('page','heritage_story'),'post_status'=>array('publish','draft'),'numberposts'=>-1));
	foreach($posts as $post){
		if(get_post_meta($post->ID,'_taabeer_visual_ready',true)){continue;}
		$old=json_decode(get_post_meta($post->ID,'_elementor_data',true),true);
		if(!$old){if(!$post->post_content || in_array($post->ID,array_map('intval',array(get_option('woocommerce_cart_page_id'),get_option('woocommerce_checkout_page_id'),get_option('woocommerce_myaccount_page_id'),get_option('woocommerce_shop_page_id'))),true)){continue;}$old=taabeer_visual_parse('<header class="tb-story-heading shell"><h1>'.esc_html($post->post_title).'</h1></header><article class="tb-story-copy shell">'.wpautop($post->post_content).'</article>');}
		taabeer_visual_backup($post->ID);
		if('discover-pakistan'===$post->post_name){$old=taabeer_discover_features_elementor($old);}
		$new=taabeer_visual_redesign_page($post,$old);taabeer_visual_save($post->ID,$new);update_post_meta($post->ID,'_taabeer_visual_ready','1.1.0');
	}
	taabeer_visual_design_system();taabeer_visual_shared_templates();taabeer_visual_archive_templates();taabeer_visual_commerce_setup();taabeer_visual_seo_setup();taabeer_visual_backup_setup();taabeer_elementor_cpt_support();
	foreach(array('header','footer') as $location) { if(!get_posts(array('post_type'=>'elementor-hf','posts_per_page'=>1,'meta_key'=>'_taabeer_shared','meta_value'=>$location))) {update_option('taabeer_foundation_error','Shared '.$location.' is missing. Import the Taabeer demo and reload setup.');return;} }
	if(class_exists('Elementor\\Plugin')){\Elementor\Plugin::instance()->files_manager->clear_cache();}
	update_option('taabeer_visual_foundation','1.1.0');flush_rewrite_rules(false);
}

/** Runs after a repository update on an authorised administrator request; retry failures are visible. */
function taabeer_foundation_admin_bootstrap() {
	if(version_compare(TAABEER_VERSION,'1.1.0','<') || get_option('taabeer_visual_foundation')==='1.1.0' || !current_user_can('install_plugins') || wp_doing_ajax()){return;}
	if(get_transient('taabeer_foundation_lock')){return;}set_transient('taabeer_foundation_lock',1,5*MINUTE_IN_SECONDS);
	$result=taabeer_install_foundation_plugins();
	if(is_wp_error($result)){update_option('taabeer_foundation_error',$result->get_error_message());}else{delete_option('taabeer_foundation_error');taabeer_complete_visual_foundation();}
	delete_transient('taabeer_foundation_lock');
}
add_action('admin_init','taabeer_foundation_admin_bootstrap',40);
add_action('admin_notices',function(){if(current_user_can('manage_options') && get_option('taabeer_foundation_error')){echo '<div class="notice notice-error"><p>TAABEER setup: '.esc_html(get_option('taabeer_foundation_error')).' Reload this page after resolving the issue to retry.</p></div>';}});

function taabeer_visual_setup_panel() {
	echo '<h2>Elementor editing &amp; required plugins</h2><p>Edit pages individually. The shared header and footer apply to the entire site. Stock, prices, shipping and orders use WooCommerce; page titles and descriptions use Yoast SEO.</p><ul>';
	foreach(taabeer_required_plugins() as $slug=>$file){echo '<li><strong>'.esc_html($slug).'</strong>: '.(is_plugin_active($file)?'Active':'Installation pending').'</li>';}
	echo '</ul><table class="widefat striped"><thead><tr><th>Page or shared template</th><th>Content</th><th>SEO / permalink</th></tr></thead><tbody>';
	$posts=get_posts(array('post_type'=>array('page','taabeer_layout','elementor-hf'),'post_status'=>array('publish','draft'),'numberposts'=>-1,'orderby'=>'post_type title','order'=>'ASC'));
	foreach($posts as $post){if(!get_post_meta($post->ID,'_elementor_data',true)){continue;}if('taabeer_layout'===$post->post_type && in_array(get_post_meta($post->ID,'_taabeer_layout_location',true),array('header','footer'),true)){continue;}
		echo '<tr><td>'.esc_html($post->post_title).' <small>('.esc_html($post->post_status).')</small></td><td><a href="'.esc_url(admin_url('post.php?post='.$post->ID.'&action=elementor')).'">Edit with Elementor</a></td><td><a href="'.esc_url(get_edit_post_link($post->ID)).'">Edit settings</a></td></tr>';
	}
	echo '</tbody></table><p><strong>Before selling:</strong> supply approved product data, payment-provider credentials, delivery destinations/rates, tax instructions and returns wording; test orders before enabling purchasing.</p>';
}
