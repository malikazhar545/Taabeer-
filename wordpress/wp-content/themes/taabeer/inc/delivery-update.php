<?php
/** Final editorial integrations. Existing page text stays in Elementor's database. */
defined('ABSPATH') || exit;

function taabeer_privacy_sections() {
	return array(
		'Privacy at TAABEER'=>'This Privacy Policy explains how TAABEER uses personal information when you browse this website, contact us or subscribe to email updates. TAABEER is responsible for the information collected through these services. For a privacy question or request, use the contact form and explain what you would like us to help with.',
		'Enquiries'=>'The contact form asks for your name, email address, enquiry type and message. Your message is sent to the website’s configured business inbox so we can respond and keep track of the conversation. Please do not include sensitive information that is unnecessary for your enquiry. We use this information to respond to requests and, where relevant, take steps towards a potential business relationship.',
		'Newsletter subscriptions'=>'If you choose to join the mailing list, we store your email address, subscription status, signup and confirmation dates, the consent wording shown to you and the page where you signed up. We email a confirmation link before activating your subscription. Confirmed subscribers may receive TAABEER collection news, stories and brand updates. This use is based on your consent; you can withdraw it using the unsubscribe link in the confirmation welcome email or by contacting us. Each future marketing message must also offer an unsubscribe option.',
		'Website security and analytics'=>'Our website and hosting services process technical information needed to deliver pages and protect forms against abuse. The forms use security checks and temporary rate limits. Optional analytics, if configured, loads only after you choose to accept analytics. You can change that choice through Cookie settings in the footer. The Cookie Policy explains the storage used by this website.',
		'Service providers and access'=>'Website administrators and authorised people handling enquiries or subscriptions can access the information needed for their work. Hosting, backup and email-delivery providers process information to operate these services. Subscriber records are stored in the WordPress database; the signup workflow does not require an external mailing-list platform. Enquiry emails, subscriber confirmations and administrator notifications also pass through the configured email service. Provider arrangements should be reviewed before adding new services or transferring data internationally.',
		'How long information is kept'=>'Unconfirmed newsletter requests are removed after 30 days by the website’s scheduled cleanup. Confirmed subscription records are kept while the subscription is active. Unsubscribed records are retained as a suppression record so that the address is not inadvertently treated as subscribed. You can ask us to erase information that is no longer needed. Enquiry records are kept for the time needed to handle the conversation and any relevant business or record-keeping obligations. Copies in backups and email systems follow the retention arrangements for those services.',
		'Your choices and rights'=>'You may ask to access or correct your personal information, request erasure or restriction where applicable, object to certain uses, or request a portable copy where that right applies. Newsletter consent can be withdrawn at any time without affecting use that was lawful before withdrawal. We may need to verify a request before releasing or changing personal information. If you are dissatisfied with how a privacy concern is handled, you can contact the UK Information Commissioner’s Office.',
		'Future services and policy changes'=>'Online purchasing is not currently enabled. Payment, delivery and customer-account processing will be described before those features launch. We will also update this policy when newsletter, analytics or other website services change. Please contact us if you have a question about this policy or the services used on this website.'
	);
}
function taabeer_cookie_sections() {
	return array(
		'Cookies and your choices'=>'This Cookie Policy explains the cookies and browser storage used by TAABEER. Essential storage supports website functions and security. Optional analytics is controlled by your choice. You can use the website and submit an enquiry or newsletter request without accepting analytics.',
		'Privacy preference storage'=>'The first-party taabeer_cookie_choice cookie records whether you chose essential storage only or accepted analytics. It lasts for up to 180 days. The same preference is stored in local storage with an expiry date, so that your choice can be remembered between visits. This storage does not contain your newsletter email or enquiry message.',
		'WordPress and administrator sessions'=>'WordPress may use cookies to support administrator login and security. Logged-in users may see wordpress_logged_in_*, wordpress_sec_* and wp-settings-* cookies. These are used for authentication or saved user preferences; their lifetime depends on the login and preference settings. An ordinary visitor does not need an administrator account to browse the site.',
		'Optional analytics'=>'Google Analytics is supported but will load only when a measurement ID is configured and you accept analytics. When enabled, Google Analytics may set _ga and _ga_* cookies to distinguish visits and measure usage. The actual lifetime and processing depend on the Analytics property configuration and should be checked when that service is connected. Selecting Essential only stops this site’s analytics loading and removes accessible Google Analytics cookies.',
		'Forms and newsletter signup'=>'The forms use WordPress security tokens, a hidden spam field and temporary server-side rate limits. They do not require an advertising cookie. Newsletter confirmation and unsubscribe links use random security tokens. Subscriber details are stored on the website’s server and are not put into browser preference storage. Read the Privacy Policy for the information collected and how subscriptions are managed.',
		'Changing your preference'=>'Choose Cookie settings in the footer to reopen the preference controls. You can also remove cookies or local storage in your browser. Removing the stored preference makes the banner appear again. Browser restrictions may affect login and other essential functions.',
		'Future shop and embedded services'=>'The shop, checkout and customer accounts remain unavailable while ecommerce is in preparation. Additional commerce, payment or embedded-media storage will be reviewed and this policy updated before those features are enabled.'
	);
}
function taabeer_policy_tree($title,$sections) {
	$parts=array();foreach($sections as $heading=>$copy){$parts[]=taabeer_visual_heading($heading,'h2');$parts[]=taabeer_visual_text('<p>'.esc_html($copy).'</p>');}
	$parts[]=taabeer_visual_text('<p><a href="'.esc_url(home_url('/contact/')).'">Contact TAABEER</a> · <a href="'.esc_url(home_url('/privacy-policy/')).'">Privacy Policy</a> · <a href="'.esc_url(home_url('/cookie-policy/')).'">Cookie Policy</a></p>');
	if('Privacy Policy'===$title){$parts[]=taabeer_visual_text('<p>Privacy information and complaints: <a href="https://ico.org.uk/make-a-complaint/">Information Commissioner’s Office</a>.</p>');}
	return array(taabeer_visual_container(array(taabeer_visual_heading('TAABEER','p','eyebrow'),taabeer_visual_heading($title,'h1')),'tb-story-heading shell'),taabeer_visual_container($parts,'tb-story-copy tb-policy-copy shell'));
}
function taabeer_add_newsletter_to_tree($nodes) {
	foreach($nodes as &$node) {
		if(!empty($node['elements'])){$node['elements']=taabeer_add_newsletter_to_tree($node['elements']);}
		$classes=explode(' ',$node['settings']['css_classes']??'');
		if(!in_array('newsletter-section__inner',$classes,true)){continue;}
		$found=false;foreach($node['elements'] as $child){if('taabeer-newsletter'===($child['widgetType']??'')){$found=true;}}
		if(!$found){
			$node['elements']=array_values(array_filter($node['elements'],function($child){return 'Mailing-list registration will open once the subscription service and privacy wording are approved.'!==trim(wp_strip_all_tags($child['settings']['editor']??''));}));
			$node['elements'][]=taabeer_visual_widget('taabeer-newsletter',array());
		}
	}
	return $nodes;
}
function taabeer_delivery_update() {
	if(get_option('taabeer_delivery_1_2_0') || !did_action('elementor/init')){return;}
	// A fresh installation may load wp-admin before the demo has been imported.
	$home=(int)get_option('page_on_front');
	if(!$home || !get_post_meta($home,'_taabeer_visual_ready',true) || !get_post_meta($home,'_elementor_data',true)){return;}
	taabeer_visual_design_system();taabeer_elementor_cpt_support();taabeer_newsletter_install();
	$raw=get_post_meta($home,'_elementor_data',true);$tree=json_decode($raw,true);
	if($tree){add_post_meta($home,'_taabeer_before_delivery_1_2_0',wp_slash($raw),true);taabeer_visual_save($home,taabeer_add_newsletter_to_tree($tree));}
	foreach(array('privacy-policy'=>array('Privacy Policy',taabeer_privacy_sections()),'cookie-policy'=>array('Cookie Policy',taabeer_cookie_sections())) as $slug=>$definition){
		$post=get_page_by_path($slug);if(!$post){continue;}
		add_post_meta($post->ID,'_taabeer_before_delivery_1_2_0',wp_slash(array('content'=>$post->post_content,'elementor'=>get_post_meta($post->ID,'_elementor_data',true))),true);
		taabeer_visual_save($post->ID,taabeer_policy_tree($definition[0],$definition[1]));
		$content='';foreach($definition[1] as $heading=>$copy){$content.='<h2>'.esc_html($heading).'</h2><p>'.esc_html($copy).'</p>';}
		wp_update_post(wp_slash(array('ID'=>$post->ID,'post_content'=>$content)));
	}
	// The owner requested indexing of all published brand/legal pages. Draft products remain private.
	foreach(get_posts(array('post_type'=>'page','post_status'=>'publish','numberposts'=>-1)) as $post){delete_post_meta($post->ID,'_taabeer_unapproved');update_post_meta($post->ID,'_yoast_wpseo_meta-robots-noindex','2');delete_post_meta($post->ID,'_yoast_wpseo_meta-robots-nofollow');}
	foreach(get_posts(array('post_type'=>'heritage_story','post_status'=>array('draft','publish'),'numberposts'=>-1)) as $post){
		if(!get_post_meta($post->ID,'_yoast_wpseo_title',true)){update_post_meta($post->ID,'_yoast_wpseo_title',$post->post_title.' | TAABEER Journal');}
		if(!get_post_meta($post->ID,'_yoast_wpseo_metadesc',true)){update_post_meta($post->ID,'_yoast_wpseo_metadesc',wp_trim_words(wp_strip_all_tags($post->post_excerpt?:$post->post_content),24,''));}
		if(!get_post_meta($post->ID,'_yoast_wpseo_focuskw',true)){update_post_meta($post->ID,'_yoast_wpseo_focuskw','Pakistani design');}
	}
	\Elementor\Plugin::instance()->files_manager->clear_cache();update_option('taabeer_delivery_1_2_0',1);
}
add_action('admin_init',function(){if(current_user_can('manage_options')){taabeer_delivery_update();}},50);

function taabeer_delivery_seo_update() {
	if(get_option('taabeer_seo_delivery_1_2_0') || !get_option('taabeer_delivery_1_2_0') || !did_action('elementor/init')){return;}
	$meta=array(
		'home'=>array('Pakistani Design & Contemporary Heritage | TAABEER','Explore Pakistani design with TAABEER, a London-based curated house. Discover collections, contemporary craft and the stories behind our selection.','Pakistani design'),
		'collections'=>array('Pakistani Design Collections | TAABEER','Explore Pakistani design collections from TAABEER: wear, adornment, living, expression and leather, selected for their materials and individual character.','Pakistani design collections'),
		'discover-pakistan'=>array('Discover Pakistan: Culture, Craft & Design | TAABEER','Discover Pakistan through regional perspectives, contemporary design and new creative voices. Explore the places and ideas behind the TAABEER selection.','discover Pakistan'),
		'regions'=>array('Pakistan by Region | Design & Heritage | TAABEER','Explore Pakistan by region with TAABEER. Discover Punjab, Sindh, Balochistan, Khyber Pakhtunkhwa and Gilgit-Baltistan through our regional notebooks.','Pakistan by region'),
		'about'=>array('About TAABEER | A Curated House of Pakistani Design','Learn about TAABEER, a London-based house of Pakistani design, bringing heritage and contemporary expression together through considered collections.','about TAABEER'),
		'partnerships'=>array('TAABEER Partnerships | Designers, Artists & Makers','Explore TAABEER partnerships for Pakistani designers, artists, makers and brands. Share your work and creative approach with our London-based team.','TAABEER partnerships'),
		'contact'=>array('Contact TAABEER | Collections & Partnership Enquiries','Contact TAABEER for collection questions, creative collaborations and partnership enquiries. Send your message to our team using the website contact form.','contact TAABEER'),
		'privacy-policy'=>array('Privacy Policy | TAABEER','Read the TAABEER Privacy Policy for enquiries, newsletter subscriptions, website services, data retention and your choices about personal information.','privacy policy'),
		'cookie-policy'=>array('Cookie Policy & Your Preferences | TAABEER','Read the TAABEER Cookie Policy to understand essential storage, optional analytics and how to change your cookie preferences while using the website.','cookie policy')
	);
	foreach(taabeer_editorial_page_definitions() as $slug=>$row){$meta[$slug]=array($row[0].' | Design & Stories | TAABEER','Explore '.$row[0].' with TAABEER through creative perspectives, materials and thoughtful introductions to Pakistani design and heritage.',$row[0]);}
	foreach(taabeer_region_definitions() as $slug=>$row){$meta[$slug]=array($row[0].' | Discover Pakistan | TAABEER','Explore '.$row[0].' with TAABEER through creative perspectives, materials and thoughtful introductions to Pakistani design and heritage.',$row[0]);}
	$posts=get_posts(array('post_type'=>array('page','heritage_story'),'post_status'=>array('publish','draft'),'numberposts'=>-1));
	foreach($posts as $post){
		if(get_post_meta($post->ID,'_taabeer_preview_only',true)){continue;}
		if(isset($meta[$post->post_name])){foreach(array('title','metadesc','focuskw') as $i=>$key){update_post_meta($post->ID,'_yoast_wpseo_'.$key,$meta[$post->post_name][$i]);}}
		$tree=json_decode(get_post_meta($post->ID,'_elementor_data',true),true);
		if($tree){
			add_post_meta($post->ID,'_taabeer_before_seo_content_1_2_0',wp_slash($post->post_content),true);
			// Elementor's own serializer supplies the content used by WordPress search and Yoast.
			$content=\Elementor\Plugin::instance()->db->get_plain_text_from_data($tree);
			if(trim($content)){wp_update_post(wp_slash(array('ID'=>$post->ID,'post_content'=>$content)));}
		}
		if(function_exists('YoastSEO')){YoastSEO()->classes->get('Yoast\\WP\\SEO\\Builders\\Indexable_Builder')->build_for_id_and_type($post->ID,'post');}
	}
	update_option('taabeer_seo_delivery_1_2_0',1);
}
add_action('admin_init',function(){if(current_user_can('manage_options')){taabeer_delivery_seo_update();}},55);

function taabeer_semantic_eyebrows($html) {
	return preg_replace('#<p(\s+class="[^"]*\beyebrow\b[^"]*"[^>]*)>(.*?)</p>#s','<div$1>$2</div>',$html);
}
add_filter('elementor/widget/render_content',function($html,$widget){return str_starts_with($widget->get_name(),'taabeer-')?taabeer_semantic_eyebrows($html):$html;},20,2);
function taabeer_seo_semantic_tree($nodes) {
	$copy=array(
		'Explore Pakistani design through five collections, each bringing its own perspective to what we wear, the spaces we inhabit and the art we choose to live with.'=>'Explore Pakistani design collections through five perspectives on what we wear, the spaces we inhabit and the art we choose to live with.',
		'Discover the places, people and ideas behind Pakistani design. Through regional stories, contemporary work and new creative voices, TAABEER invites you to explore a culture in the making.'=>'Discover Pakistan through the places, people and ideas behind its design. Explore regional stories, contemporary work and new creative voices with TAABEER.',
		'We welcome conversations with Pakistani designers, artists, makers and brands whose work brings a distinctive perspective to our collections.'=>'TAABEER partnerships begin with a conversation. We welcome Pakistani designers, artists, makers and brands whose work brings a distinctive perspective to our collections.',
		'For questions about TAABEER, our forthcoming collections or partnership opportunities, we would be pleased to hear from you.'=>'Contact TAABEER for questions about our forthcoming collections, creative collaborations or partnership opportunities. We would be pleased to hear from you.',
		'Five regional notebooks. Different starting points for exploring the people, objects and ideas behind Pakistani design.'=>'Explore Pakistan by region through five notebooks: different starting points for discovering the people, objects and ideas behind Pakistani design.'
	);
	foreach($nodes as &$node){
		$type=$node['widgetType']??'';$classes=explode(' ',$node['settings']['_css_classes']??'');
		if(in_array('eyebrow',$classes,true)){
			if('heading'===$type && 'p'===($node['settings']['header_size']??'')){$node['settings']['header_size']='div';}
			if('text-editor'===$type){$node['settings']['editor']=preg_replace('#<(/?)p\b#','<$1div',$node['settings']['editor']);}
		}
		if('text-editor'===$type){$text=trim(wp_strip_all_tags($node['settings']['editor']));if(isset($copy[$text])){$node['settings']['editor']='<p>'.esc_html($copy[$text]).'</p>';}}
		if(!empty($node['elements'])){$node['elements']=taabeer_seo_semantic_tree($node['elements']);}
	}
	return $nodes;
}
function taabeer_delivery_semantics_update() {
	if(!current_user_can('manage_options') || !did_action('elementor/init') || !get_option('taabeer_seo_delivery_1_2_0') || get_option('taabeer_seo_semantics_1_2_0')){return;}
	foreach(get_posts(array('post_type'=>'page','post_status'=>'publish','numberposts'=>-1)) as $post){
		$raw=get_post_meta($post->ID,'_elementor_data',true);$tree=json_decode($raw,true);if(!$tree){continue;}
		add_post_meta($post->ID,'_taabeer_before_seo_semantics_1_2_0',wp_slash($raw),true);$tree=taabeer_seo_semantic_tree($tree);
		if('about'===$post->post_name){$tree[]=taabeer_visual_container(array(taabeer_visual_button('Explore collections',home_url('/collections/')),taabeer_visual_button('Discover Pakistan',home_url('/discover-pakistan/'),'button button--outline')),'button-row tb-about-next shell');}
		taabeer_visual_save($post->ID,$tree);
		wp_update_post(wp_slash(array('ID'=>$post->ID,'post_content'=>\Elementor\Plugin::instance()->db->get_plain_text_from_data($tree))));
	}
	\Elementor\Plugin::instance()->files_manager->clear_cache();update_option('taabeer_seo_semantics_1_2_0',1);
}
add_action('admin_init','taabeer_delivery_semantics_update',56);

/** Configure the native Yoast Journal settings once; later client edits are retained. */
function taabeer_delivery_journal_update() {
	if(!current_user_can('manage_options') || !did_action('elementor/init') || !get_option('taabeer_delivery_1_2_0') || !class_exists('WPSEO_Options') || get_option('taabeer_journal_delivery_1_2_0')){return;}
	if(class_exists('WPSEO_Options')){
		$title=WPSEO_Options::get('title-ptarchive-heritage_story');
		if(!$title || '%%pt_plural%% Archive %%page%% %%sep%% %%sitename%%'===$title){WPSEO_Options::set('title-ptarchive-heritage_story','Journal & Heritage Stories %%page%% | TAABEER');}
		if(!WPSEO_Options::get('metadesc-ptarchive-heritage_story')){WPSEO_Options::set('metadesc-ptarchive-heritage_story','Explore the TAABEER Journal: Pakistani design, materials, creative voices and the heritage stories behind our considered collections.');}
		WPSEO_Options::set('noindex-ptarchive-heritage_story',false);
	}
	foreach(get_posts(array('post_type'=>'heritage_story','post_status'=>array('draft','publish'),'numberposts'=>-1)) as $post){
		$description=get_post_meta($post->ID,'_yoast_wpseo_metadesc',true);
		if('An introduction to the thinking behind the TAABEER selection.'===$description){update_post_meta($post->ID,'_yoast_wpseo_metadesc','Discover the thinking behind TAABEER: a considered place for Pakistani design, with attention to materials, individual pieces and the voices of their makers.');}
		if('publish'===$post->post_status){delete_post_meta($post->ID,'_taabeer_unapproved');update_post_meta($post->ID,'_yoast_wpseo_meta-robots-noindex','2');}
		if(function_exists('YoastSEO')){YoastSEO()->classes->get('Yoast\\WP\\SEO\\Builders\\Indexable_Builder')->build_for_id_and_type($post->ID,'post');}
	}
	update_option('taabeer_journal_delivery_1_2_0',1);
}
add_action('admin_init','taabeer_delivery_journal_update',57);
// Recover a missing kit before opening an editor, including after later template changes.
add_action('admin_init',function(){
	if(current_user_can('edit_pages') && did_action('elementor/init')){
		$id=(int)get_option('elementor_active_kit');
		if(!$id || !\Elementor\Plugin::instance()->kits_manager->is_kit($id) || 'publish'!==get_post_status($id)){taabeer_visual_design_system();}
	}
},20);
