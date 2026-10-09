<?php
/** Run against a LOCAL imported WordPress site: pipe into docker compose exec -T wordpress php. */
require '/var/www/html/wp-load.php';
if (!str_contains(home_url(), 'localhost')) { throw new RuntimeException('Local validation only.'); }
function verify_tb($condition,$label) { if(!$condition){throw new RuntimeException($label);} echo "PASS $label\n"; }
$before=[];foreach(get_posts(['post_type'=>['page','elementor-hf'],'post_status'=>['publish','draft'],'numberposts'=>-1]) as $post){$before[$post->ID]=get_post_meta($post->ID,'_elementor_data',true);}
$count=count($before);taabeer_import_pages();
$after=get_posts(['post_type'=>['page','elementor-hf'],'post_status'=>['publish','draft'],'numberposts'=>-1]);
verify_tb(count($after)===$count,'reimport creates no duplicate pages');
foreach($before as $id=>$data){verify_tb(get_post_meta($id,'_elementor_data',true)===$data,"reimport preserves Elementor content $id");}
taabeer_complete_visual_foundation();
foreach($before as $id=>$data){verify_tb(get_post_meta($id,'_elementor_data',true)===$data,"completed migration preserves content $id");}
$tree=taabeer_visual_parse('<section><h2>Client heading</h2><p>Client paragraph <a href="/contact/">contact us</a>.</p><img src="/image.webp" alt="Woven red textile"><a class="button" href="/collections/">Explore</a></section>');
$json=wp_json_encode($tree);
foreach(['Client heading','Client paragraph','contact us','Woven red textile','Explore'] as $text){verify_tb(str_contains($json,$text),'native conversion preserves '.$text);}
verify_tb(str_contains($json,'"widgetType":"image"') && str_contains($json,'"widgetType":"heading"'),'conversion creates native editable widgets');
$custom=[['widgetType'=>'client-widget','settings'=>['title'=>'Client custom setting'],'elements'=>[]]];
verify_tb(taabeer_visual_convert_tree($custom)===$custom,'unrecognised client widgets retained');
verify_tb(!taabeer_commerce_is_live(),'purchasing remains disabled');
verify_tb(!apply_filters('woocommerce_variation_is_purchasable',true),'variations cannot bypass launch flag');
foreach(['cart','checkout','myaccount','shop'] as $key){verify_tb('draft'===get_post_status(get_option('woocommerce_'.$key.'_page_id')),$key.' stays draft');}
verify_tb(wp_next_scheduled('updraft_backup') && wp_next_scheduled('updraft_backup_database'),'file and database backups scheduled');
$html=taabeer_contact_form_shortcode(['recipient_email'=>'inbox@example.com']);
verify_tb(str_contains($html,hash_hmac('sha256','inbox@example.com',wp_salt('auth'))),'form recipient is signed against tampering');
foreach(taabeer_required_plugins() as $file){verify_tb(in_array($file,get_option('active_plugins'),true),$file.' active');}
echo "FOUNDATION VALIDATION PASSED\n";
