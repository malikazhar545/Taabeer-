<?php
/** Local regression checks for analysis persistence and stylesheet dependencies. */
require '/var/www/html/wp-load.php';
if(!str_contains(home_url(),'localhost')){exit(1);}
function tb_seo_check($ok,$message){if(!$ok){throw new RuntimeException($message);}echo "PASS $message\n";}
wp_set_current_user(1);
$id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Local analysis integration fixture','post_content'=>'<p>Local test content.</p>'));
try {
	update_post_meta($id,'_yoast_wpseo_focuskw','local test');$hash=taabeer_seo_analysis_hash($id);
	tb_seo_check(is_wp_error(taabeer_seo_analysis_save($id,'stale',73,90)),'stale content cannot receive an old score');
	tb_seo_check(is_wp_error(taabeer_seo_analysis_save($id,$hash,101,90)),'out-of-range scores rejected');
	wp_set_current_user(0);tb_seo_check(is_wp_error(taabeer_seo_analysis_save($id,$hash,73,90)),'anonymous users cannot write SEO scores');wp_set_current_user(1);
	tb_seo_check(true===taabeer_seo_analysis_save($id,$hash,46,60),'valid fixture analysis can be persisted');
	tb_seo_check(46===(int)get_post_meta($id,'_yoast_wpseo_linkdex',true) && 60===(int)get_post_meta($id,'_yoast_wpseo_content_score',true),'native Yoast fields retain supplied results without forcing green');
	update_post_meta($id,'_yoast_wpseo_focuskw','updated phrase');tb_seo_check(taabeer_seo_analysis_hash($id)!==$hash,'SEO edits invalidate earlier analyses');
	wp_update_post(array('ID'=>$id,'post_status'=>'draft'));tb_seo_check(!taabeer_seo_analysis_allowed($id),'draft content excluded from bulk analysis');
} finally {wp_trash_post($id);}
taabeer_assets();tb_seo_check(in_array('elementor-frontend',wp_styles()->registered['taabeer-style']->deps,true),'Elementor base CSS is a dependency of theme defaults');
$home=(int)get_option('page_on_front');$payload=taabeer_seo_analysis_payload($home);
tb_seo_check(!str_contains($payload['html'],'newsletter_nonce') && !str_contains($payload['html'],'form_signature'),'analysis payload excludes form transport tokens');
tb_seo_check(str_contains($payload['html'],'<h1'),'analysis sees rendered Elementor content');
$contact=get_page_by_path('contact');$before=get_post_meta($contact->ID,'_elementor_data',true);taabeer_seo_content_1_2_1();
tb_seo_check($before===get_post_meta($contact->ID,'_elementor_data',true),'completed content migration preserves client edits');
tb_seo_check(str_contains($before,'"widgetType":"accordion"'),'contact FAQ uses an editable native Elementor accordion');
echo "SEO REFRESH CHECKS PASSED\n";
