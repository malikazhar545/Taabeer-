<?php
/** Refresh real Yoast analyses after imported/programmatic Elementor changes. */
defined('ABSPATH') || exit;

function taabeer_seo_label_tree($nodes,$label,&$done) {
	foreach($nodes as &$node){
		if(!$done && str_contains($node['settings']['_css_classes']??'','eyebrow')){
			$key='heading'===($node['widgetType']??'')?'title':'editor';
			$old=trim(wp_strip_all_tags($node['settings'][$key]??''));
			if(in_array($old,array('TAABEER','Contact','Discover Pakistan'),true)){$node['settings'][$key]='title'===$key?$label:'<div>'.esc_html($label).'</div>';}
			$done=true;
		}
		if(!$done && !empty($node['elements'])){$node['elements']=taabeer_seo_label_tree($node['elements'],$label,$done);}
	}return $nodes;
}
function taabeer_seo_content_1_2_1() {
	if(!current_user_can('manage_options') || !did_action('elementor/init') || !get_option('taabeer_seo_semantics_1_2_0') || get_option('taabeer_seo_content_1_2_1')){return;}
	$labels=array('collections'=>'Pakistani design collections','discover-pakistan'=>'Discover Pakistan','discover-pakistan/regions'=>'Pakistan by region','partnerships'=>'TAABEER partnerships','contact'=>'Contact TAABEER');
	$notes=array(
		'discover-pakistan'=>'There is more than one way to explore a country. Begin with a regional notebook to explore a sense of place, follow a contemporary perspective, or meet the ideas behind a new creative voice. These introductions are starting points for a closer look, rather than a complete account of a country or a craft. As the journal develops, individual stories will connect places with the materials, objects and people in our selection. Move between the notebooks and collections to see how familiar references can take on a different expression in everyday life.',
		'discover-pakistan/regions'=>'Exploring different regions offers varied starting points for understanding design. Each notebook invites a closer look at place, materials and individual creative perspectives. A regional label does not tell the whole story of an object or the person who made it. Techniques, influences and ideas can travel between places and across generations. Use these introductions alongside our collections and journal, where a specific piece can be considered in its own context. As verified stories become available, the notebooks can grow through the experiences and words of the people behind the work.'
	);
	foreach($labels as $path=>$label){
		$post=get_page_by_path($path);if(!$post){continue;}$raw=get_post_meta($post->ID,'_elementor_data',true);$tree=json_decode($raw,true);if(!$tree){continue;}
		add_post_meta($post->ID,'_taabeer_before_seo_1_2_1',wp_slash(array('elementor'=>$raw,'content'=>$post->post_content)),true);
		$done=false;$tree=taabeer_seo_label_tree($tree,$label,$done);
		if(isset($notes[$path])){$tree[]=taabeer_visual_container(array(taabeer_visual_heading('A closer look','h2'),taabeer_visual_text('<p>'.esc_html($notes[$path]).'</p>')),'shell tb-seo-note');}
		if('contact'===$path){
			$faq=array(
				array('tab_title'=>'Collections and availability','tab_content'=>'<p>Contact TAABEER if you would like to know more about a collection or a piece shown on the website. Include the collection name or page link, along with the question you would like us to answer. You can ask about the design, materials or the story behind a piece. Our online shop is still in preparation, so the enquiry form does not place an order or reserve stock. Browse the <a href="'.esc_url(home_url('/collections/')).'">collections</a> for an introduction to the selection, and join the mailing list for future collection updates.</p>'),
				array('tab_title'=>'Creative and partnership enquiries','tab_content'=>'<p>If you are a designer, artist, maker or brand, tell us a little about your work and the collaboration you have in mind. A website or portfolio link can help introduce your practice. Please include the materials you work with and any context you would like us to understand. For retail, interiors or cultural projects, describe the setting and the kind of conversation you hope to begin. Read the <a href="'.esc_url(home_url('/partnerships/')).'">partnerships page</a> for more about our approach before sending your enquiry.</p>'),
				array('tab_title'=>'Newsletter and privacy questions','tab_content'=>'<p>Newsletter signup is separate from the contact form. Sending an enquiry does not subscribe you to marketing emails. To join, use the homepage signup form and confirm the link sent to your inbox. If you need help with a subscription or wish to make a privacy request, explain this in your message. Please share only the information needed to handle your question. Our <a href="'.esc_url(home_url('/privacy-policy/')).'">Privacy Policy</a> explains how enquiry and subscription information is used.</p>')
			);
			$tree[]=taabeer_visual_container(array(taabeer_visual_heading('Contact TAABEER: useful information','h2'),taabeer_visual_widget('accordion',array('tabs'=>$faq,'title_color'=>'#173f35','tab_active_color'=>'#173f35','title_typography_typography'=>'custom','title_typography_font_size'=>array('unit'=>'px','size'=>17),'title_typography_font_weight'=>'500','content_typography_typography'=>'custom','content_typography_font_size'=>array('unit'=>'px','size'=>16),'content_typography_line_height'=>array('unit'=>'em','size'=>1.8)))),'shell tb-seo-note');
		}
		taabeer_visual_save($post->ID,$tree);
		wp_update_post(wp_slash(array('ID'=>$post->ID,'post_content'=>Elementor\Plugin::instance()->db->get_plain_text_from_data($tree))));
	}
	Elementor\Plugin::instance()->files_manager->clear_cache();update_option('taabeer_seo_content_1_2_1',1);
}
add_action('admin_init','taabeer_seo_content_1_2_1',58);

function taabeer_seo_analysis_hash($id) {
	return hash('sha256',wp_json_encode(array(get_post_field('post_content',$id),get_post_field('post_modified_gmt',$id),get_post_meta($id,'_elementor_data',true),get_post_meta($id,'_yoast_wpseo_title',true),get_post_meta($id,'_yoast_wpseo_metadesc',true),get_post_meta($id,'_yoast_wpseo_focuskw',true),get_post_meta($id,'_yoast_wpseo_is_cornerstone',true),get_permalink($id),get_locale(),defined('WPSEO_VERSION')?WPSEO_VERSION:'')));
}
function taabeer_seo_analysis_allowed($id) {
	return defined('WPSEO_VERSION') && current_user_can('manage_options') && current_user_can('edit_post',$id) && 'publish'===get_post_status($id) && in_array(get_post_type($id),array('page','heritage_story'),true) && !get_post_meta($id,'_taabeer_preview_only',true);
}
add_action('admin_enqueue_scripts',function($hook){
	if('edit.php'!==$hook || !current_user_can('manage_options') || !class_exists('WPSEO_Admin_Asset_Manager')){return;}
	$screen=get_current_screen();if(!$screen || !in_array($screen->post_type,array('page','heritage_story'),true)){return;}
	$all=array();$pending=array();
	foreach(get_posts(array('post_type'=>$screen->post_type,'post_status'=>'publish','numberposts'=>-1)) as $post){
		if(!taabeer_seo_analysis_allowed($post->ID) || !get_post_meta($post->ID,'_yoast_wpseo_focuskw',true)){continue;}
		$all[]=$post->ID;
		if(get_post_meta($post->ID,'_taabeer_yoast_analysis_hash',true)!==taabeer_seo_analysis_hash($post->ID)){$pending[]=$post->ID;}
	}
	if(!$all){return;}
	$assets=new WPSEO_Admin_Asset_Manager();$assets->register_assets();
	$language=YoastSEO()->helpers->language->get_researcher_language();$language_handle='yoast-seo-'.$language.'-language';
	if(!wp_script_is($language_handle,'registered')){return;}
	wp_enqueue_script('taabeer-seo-analysis',TAABEER_URI.'/assets/js/seo-analysis.js',array('yoast-seo-analysis-package',$language_handle),TAABEER_VERSION,true);
	wp_localize_script('taabeer-seo-analysis','TaabeerSeoAnalysis',array('url'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('taabeer_seo_analysis'),'all'=>$all,'pending'=>$pending));
	add_action('admin_notices',function(){echo '<div class="notice notice-info" id="taabeer-seo-analysis"><p><strong>Yoast analysis</strong> <span role="status" aria-live="polite" data-analysis-status>Analysing current published content with the installed Yoast engine.</span> <button type="button" class="button" data-analysis-refresh>Recalculate analyses</button></p><p>Scores reflect the page content and may remain orange or red where improvements are needed. Draft commerce pages are excluded. Open a page in Yoast for its detailed recommendations.</p></div>';});
},30);

function taabeer_seo_analysis_payload($id) {
	$post=get_post($id);$html=$post->post_content;
	if(did_action('elementor/init') && get_post_meta($id,'_elementor_data',true)){
		$previous=$GLOBALS['post']??null;$GLOBALS['post']=$post;setup_postdata($post);
		try{$rendered=Elementor\Plugin::instance()->frontend->get_builder_content_for_display($id);if($rendered){$html=$rendered;}}
		finally{$GLOBALS['post']=$previous;if($previous){setup_postdata($previous);}}
	}
	$html=preg_replace('#<(script|style)[^>]*>.*?</\1>#is','',$html);
	// Remove input transport data; visible labels, copy, headings, links and image alts remain.
	$html=preg_replace('#<input\b[^>]*>#i','',$html);
	return array('id'=>$id,'hash'=>taabeer_seo_analysis_hash($id),'html'=>$html,'options'=>array(
		'keyword'=>(string)get_post_meta($id,'_yoast_wpseo_focuskw',true),
		'title'=>wpseo_replace_vars(get_post_meta($id,'_yoast_wpseo_title',true)?:$post->post_title,$post),
		'description'=>wpseo_replace_vars(get_post_meta($id,'_yoast_wpseo_metadesc',true),$post),
		'slug'=>$post->post_name,'permalink'=>get_permalink($id),'locale'=>get_locale(),
		'textTitle'=>$post->post_title,'isFrontPage'=>$id===(int)get_option('page_on_front'),
	),'cornerstone'=>'1'===get_post_meta($id,'_yoast_wpseo_is_cornerstone',true));
}
function taabeer_seo_analysis_save($id,$hash,$seo,$readability) {
	if(!taabeer_seo_analysis_allowed($id)){return new WP_Error('forbidden','You cannot analyse this content.');}
	if(!hash_equals(taabeer_seo_analysis_hash($id),$hash)){return new WP_Error('changed','The page changed during analysis. Refresh and try again.');}
	foreach(array($seo,$readability) as $score){if(!is_numeric($score) || !is_finite((float)$score) || $score<0 || $score>100){return new WP_Error('invalid','The analysis did not return a valid score.');}}
	// These are actual results from the installed Yoast analyser, never target/placeholder scores.
	update_post_meta($id,'_yoast_wpseo_linkdex',(string)(int)round($seo));
	update_post_meta($id,'_yoast_wpseo_content_score',(string)(int)round($readability));
	if((int)WPSEO_Meta::get_value('linkdex',$id)!==(int)round($seo) || (int)WPSEO_Meta::get_value('content_score',$id)!==(int)round($readability)){return new WP_Error('save_failed','Yoast did not retain the analysis. Please retry in the editor.');}
	update_post_meta($id,'_taabeer_yoast_analysis_hash',$hash);
	if(function_exists('YoastSEO')){YoastSEO()->classes->get('Yoast\\WP\\SEO\\Builders\\Indexable_Builder')->build_for_id_and_type($id,'post');}
	return true;
}
add_action('wp_ajax_taabeer_seo_analysis',function(){
	check_ajax_referer('taabeer_seo_analysis','nonce');$id=absint($_POST['id']??0);
	if(!taabeer_seo_analysis_allowed($id)){wp_send_json_error(array('message'=>'Not permitted.'),403);}
	if('save'===($_POST['operation']??'')){
		$result=taabeer_seo_analysis_save($id,sanitize_text_field(wp_unslash($_POST['hash']??'')),$_POST['seo']??null,$_POST['readability']??null);
		if(is_wp_error($result)){wp_send_json_error(array('message'=>$result->get_error_message()),409);}wp_send_json_success();
	}
	wp_send_json_success(taabeer_seo_analysis_payload($id));
});
