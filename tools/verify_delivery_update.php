<?php
/** Local-only integration checks; intercept all mail, never send test messages. */
require '/var/www/html/wp-load.php';
if(!str_contains(home_url(),'localhost')){throw new RuntimeException('Local only');}
function tb_delivery_assert($ok,$message){if(!$ok){throw new RuntimeException($message);}echo "PASS $message\n";}
wp_set_current_user(1);taabeer_delivery_update();
$mail=array();$capture=function($result,$atts) use (&$mail){$mail[]=$atts;return true;};add_filter('pre_wp_mail',$capture,10,2);
global $wpdb;$table=taabeer_newsletter_table();$email='delivery-'.wp_generate_password(8,false,false).'@example.test';$ids=array();
try {
	$input=array('newsletter_nonce'=>wp_create_nonce('taabeer_newsletter'),'email'=>$email,'recipient'=>'notifications@example.test','consent'=>'1','consent_text'=>taabeer_newsletter_defaults()['consent_label']);
	$input['form_signature']=taabeer_newsletter_signature($input['recipient'],$input['consent_text']);
	$ip=wp_generate_uuid4();
	tb_delivery_assert('error'===taabeer_newsletter_subscribe(array_merge($input,array('consent'=>'')),$ip),'consent required');
	tb_delivery_assert('error'===taabeer_newsletter_subscribe(array_merge($input,array('recipient'=>'attacker@example.test')),$ip),'recipient tampering rejected');
	tb_delivery_assert('error'===taabeer_newsletter_subscribe(array_merge($input,array('newsletter_nonce'=>'bad')),$ip),'invalid nonce rejected');
	tb_delivery_assert('pending'===taabeer_newsletter_subscribe(array_merge($input,array('website'=>'spam')),$ip) && !$mail,'honeypot sends no mail');
	tb_delivery_assert('pending'===taabeer_newsletter_subscribe($input,$ip,home_url('/')),'subscription creates pending record');
	$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email=%s",strtolower($email)));$ids[]=$row->id;
	tb_delivery_assert('pending'===$row->status && 1===count($mail),'confirmation required before activation');
	tb_delivery_assert('pending'===taabeer_newsletter_subscribe($input,$ip) && 1===count($mail),'duplicate requests do not resend immediately');
	preg_match('/https?:\/\/\S+token=([^\s]+)/',$mail[0]['message'],$match);$token=urldecode($match[1]);
	tb_delivery_assert(false===taabeer_newsletter_confirm($row->id,'wrong-token'),'wrong confirmation token rejected');
	$wpdb->update($table,array('confirm_expires'=>gmdate('Y-m-d H:i:s',time()-60)),array('id'=>$row->id));
	tb_delivery_assert(false===taabeer_newsletter_confirm($row->id,$token),'expired token rejected');
	$wpdb->update($table,array('confirm_expires'=>gmdate('Y-m-d H:i:s',time()+600)),array('id'=>$row->id));
	tb_delivery_assert(taabeer_newsletter_confirm($row->id,$token),'valid token confirms subscription');
	tb_delivery_assert(3===count($mail) && 'notifications@example.test'===$mail[2]['to'],'welcome and configured inbox notification prepared');
	tb_delivery_assert(false===taabeer_newsletter_confirm($row->id,$token) && 3===count($mail),'confirmation cannot replay');
	preg_match('/https?:\/\/\S+token=([^\s]+)/',$mail[1]['message'],$match);$unsubscribe=urldecode($match[1]);
	tb_delivery_assert(taabeer_newsletter_unsubscribe($row->id,$unsubscribe),'unsubscribe link works');
	tb_delivery_assert('unsubscribed'===$wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id=%d",$row->id)),'unsubscribed address removed from active list');
	tb_delivery_assert(count(taabeer_newsletter_export($email)['data'])===1,'native WordPress privacy export includes subscriber');
	for($n=0;$n<4;$n++){taabeer_newsletter_subscribe($input,$ip);}
	tb_delivery_assert('limited'===taabeer_newsletter_subscribe($input,$ip),'rate limit blocks excessive requests');
	tb_delivery_assert(taabeer_newsletter_erase($email)['items_removed'],'native WordPress privacy erasure removes subscriber');
} finally {
	foreach($ids as $id){$wpdb->delete($table,array('id'=>$id));}
	remove_filter('pre_wp_mail',$capture,10);
}
foreach(array('taabeer-hero'=>'tb_hero_height_mobile','taabeer-contact'=>'tb_field_padding_mobile','taabeer-newsletter'=>'field_padding_mobile','taabeer-story-grid'=>'tb_grid_columns_mobile') as $name=>$control){
	$widget=Elementor\Plugin::instance()->elements_manager->create_element_instance(taabeer_visual_widget($name,array()));
	$stack=$widget->get_stack();$key=preg_replace('/_mobile$/','',$control);$setting=$stack['style_controls'][$key]??$stack['controls'][$key]??array();
	tb_delivery_assert(!empty($setting['is_responsive']) || isset($setting['responsive']),$name.' exposes responsive Style control '.$control);
}
$page=get_page_by_path('about');$data=json_decode(get_post_meta($page->ID,'_elementor_data',true),true);
$heading=taabeer_visual_heading('Mobile control QA','h2');$heading['settings']+=array('typography_typography'=>'custom','typography_font_size_mobile'=>array('unit'=>'px','size'=>37),'title_color'=>'#314C40');
$test=wp_insert_post(array('post_type'=>'page','post_status'=>'draft','post_title'=>'Local responsive control test'));
try {
	taabeer_visual_save($test,array(taabeer_visual_container(array($heading),'shell')));
	$tree=json_decode(get_post_meta($test,'_elementor_data',true),true);
	$tree[0]['settings']['padding_mobile']=array('unit'=>'px','top'=>19,'right'=>17,'bottom'=>23,'left'=>17,'isLinked'=>false);
	$tree[0]['settings']['width_mobile']=array('unit'=>'%','size'=>92);
	taabeer_visual_save($test,$tree);
	$css=new Elementor\Core\Files\CSS\Post($test);$css->update();$rendered=$css->get_content();
	tb_delivery_assert(str_contains($rendered,'37px') && str_contains($rendered,'767px'),'native mobile font control generates breakpoint CSS');
	tb_delivery_assert(str_contains(strtoupper($rendered),'#314C40'),'native heading colour survives save');
	tb_delivery_assert((bool)preg_match('/\.tb-visual\s*\{[^}]*padding:\s*19px 17px 23px 17px/',$rendered),'native mobile container padding overrides theme defaults');
	tb_delivery_assert(str_contains($rendered,'width:92%'),'native mobile container width is editable');
} finally {wp_trash_post($test);}

$failure_email='delivery-failure-'.wp_generate_password(8,false,false).'@example.test';
$fail=function(){return false;};add_filter('pre_wp_mail',$fail);
try {
	$input['email']=$failure_email;
	tb_delivery_assert('mail-error'===taabeer_newsletter_subscribe($input,wp_generate_uuid4()),'mail transport failure is shown instead of false success');
	tb_delivery_assert('pending'===$wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE email=%s",strtolower($failure_email))),'failed confirmation delivery never activates a subscription');
} finally {remove_filter('pre_wp_mail',$fail);$wpdb->delete($table,array('email'=>strtolower($failure_email)));}
wp_set_current_user(0);ob_start();taabeer_newsletter_admin();$private_output=ob_get_clean();
tb_delivery_assert(''===$private_output,'subscriber panel does not expose records to anonymous users');
wp_set_current_user(1);
taabeer_delivery_journal_update();
tb_delivery_assert(in_array('heritage_story',(array)get_option('elementor_cpt_support'),true),'Journal stories are enabled for Elementor');
$story=get_posts(array('post_type'=>'heritage_story','post_status'=>array('draft','publish'),'numberposts'=>1))[0]??null;
if($story){
	$story_tree=json_decode(get_post_meta($story->ID,'_elementor_data',true),true);
	tb_delivery_assert(!empty($story_tree),'existing Journal story contains editable Elementor data');
	tb_delivery_assert((bool)get_post_meta($story->ID,'_yoast_wpseo_metadesc',true) && (bool)get_post_meta($story->ID,'_yoast_wpseo_focuskw',true),'Journal story has native Yoast description and focus phrase');
}
$before=array();
foreach(get_posts(array('post_type'=>array('page','heritage_story','elementor-hf'),'post_status'=>array('publish','draft'),'numberposts'=>-1)) as $post){$before[$post->ID]=get_post_meta($post->ID);}
taabeer_delivery_update();taabeer_delivery_seo_update();taabeer_delivery_semantics_update();taabeer_delivery_journal_update();
foreach($before as $id=>$meta){tb_delivery_assert($meta===get_post_meta($id),'completed delivery migrations preserve saved content and metadata '.$id);}
echo "DELIVERY INTEGRATION CHECKS PASSED; NO EMAILS SENT\n";
