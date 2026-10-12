<?php
/** First-party newsletter subscriptions. Independent of the GitHub deployment plugin. */
defined('ABSPATH') || exit;

function taabeer_newsletter_table() { global $wpdb; return $wpdb->prefix.'taabeer_subscribers'; }
function taabeer_newsletter_install() {
	if(get_option('taabeer_newsletter_schema')==='1'){return;}
	global $wpdb; require_once ABSPATH.'wp-admin/includes/upgrade.php';
	$table=taabeer_newsletter_table();
	dbDelta("CREATE TABLE $table (
	 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	 email varchar(190) NOT NULL,
	 status varchar(20) NOT NULL DEFAULT 'pending',
	 created_at datetime NOT NULL,
	 updated_at datetime NOT NULL,
	 confirmed_at datetime DEFAULT NULL,
	 consent_text text NOT NULL,
	 source_url text NOT NULL,
	 notify_email varchar(190) NOT NULL DEFAULT '',
	 confirm_hash varchar(64) NOT NULL DEFAULT '',
	 confirm_expires datetime DEFAULT NULL,
	 unsubscribe_hash varchar(64) NOT NULL DEFAULT '',
	 PRIMARY KEY  (id),
	 UNIQUE KEY email (email),
	 KEY status (status)
	) {$wpdb->get_charset_collate()};");
	if($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)))===$table){update_option('taabeer_newsletter_schema','1');}
	if(!wp_next_scheduled('taabeer_newsletter_cleanup')){wp_schedule_event(time()+DAY_IN_SECONDS,'daily','taabeer_newsletter_cleanup');}
}
add_action('admin_init','taabeer_newsletter_install');
add_action('taabeer_newsletter_cleanup',function(){global $wpdb;$wpdb->query($wpdb->prepare('DELETE FROM '.taabeer_newsletter_table()." WHERE status = 'pending' AND updated_at < %s",gmdate('Y-m-d H:i:s',time()-30*DAY_IN_SECONDS)));});

function taabeer_newsletter_defaults() {
	return array('recipient_email'=>'','email_label'=>'Email address','submit_label'=>'Join the mailing list','consent_label'=>'I would like to receive TAABEER collections, stories and updates by email. I can unsubscribe at any time.','success_message'=>'Please check your inbox for a confirmation link.','error_message'=>'Please enter a valid email address and agree to receive updates.','mail_error_message'=>'We could not send your confirmation email. Please try again later.');
}
function taabeer_newsletter_signature($recipient,$consent) { return hash_hmac('sha256',$recipient."\n".$consent,wp_salt('auth')); }
function taabeer_newsletter_form($atts=array()) {
	$s=wp_parse_args($atts,taabeer_newsletter_defaults());$email=sanitize_email($s['recipient_email']);$consent=sanitize_text_field($s['consent_label']);
	$id=wp_unique_id('taabeer-newsletter-');$status=sanitize_key($_GET['newsletter_status']??'');
	ob_start(); ?>
	<div class="tb-newsletter-wrap">
	<?php if($status): ?><p class="form-status" role="status" tabindex="-1" data-form-status><?php echo esc_html('pending'===$status?$s['success_message']:('mail-error'===$status?$s['mail_error_message']:('limited'===$status?'Please wait before trying again.':$s['error_message']))); ?></p><?php endif; ?>
	<form class="tb-newsletter-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
	<input type="hidden" name="action" value="taabeer_newsletter_subscribe">
	<?php wp_nonce_field('taabeer_newsletter','newsletter_nonce'); ?>
	<input type="hidden" name="recipient" value="<?php echo esc_attr($email); ?>">
	<input type="hidden" name="consent_text" value="<?php echo esc_attr($consent); ?>">
	<input type="hidden" name="form_signature" value="<?php echo esc_attr(taabeer_newsletter_signature($email,$consent)); ?>">
	<div class="contact-form__honeypot" aria-hidden="true"><label for="<?php echo esc_attr($id); ?>-website">Website</label><input id="<?php echo esc_attr($id); ?>-website" name="website" tabindex="-1" autocomplete="off"></div>
	<label for="<?php echo esc_attr($id); ?>-email"><?php echo esc_html($s['email_label']); ?></label>
	<div class="tb-newsletter-row"><input id="<?php echo esc_attr($id); ?>-email" type="email" name="email" autocomplete="email" maxlength="190" required><button type="submit" class="button"><?php echo esc_html($s['submit_label']); ?></button></div>
	<label class="form-consent"><input type="checkbox" name="consent" value="1" required><span><?php echo esc_html($consent); ?> <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a></span></label>
	</form></div>
	<?php return ob_get_clean();
}
add_shortcode('taabeer_newsletter','taabeer_newsletter_form');

/** Return a status for the transport handler; never expose whether an address is already subscribed. */
function taabeer_newsletter_subscribe($input,$ip='',$source='') {
	if(!wp_verify_nonce(sanitize_text_field($input['newsletter_nonce']??''),'taabeer_newsletter')){return 'error';}
	if(!empty($input['website'])){return 'pending';}
	$recipient=sanitize_email($input['recipient']??'');$consent=sanitize_text_field($input['consent_text']??'');
	if(!hash_equals(taabeer_newsletter_signature($recipient,$consent),(string)($input['form_signature']??''))){return 'error';}
	$email=strtolower(trim((string)($input['email']??'')));
	if(!is_email($email) || strlen($email)>190 || '1'!==($input['consent']??'') || !$consent){return 'error';}
	$rate='tb_newsletter_'.hash_hmac('sha256',$ip,wp_salt('nonce'));$attempts=(int)get_transient($rate);
	if($attempts>=5){return 'limited';}set_transient($rate,$attempts+1,HOUR_IN_SECONDS);
	if(get_option('taabeer_newsletter_schema')!=='1'){return 'mail-error';}
	global $wpdb;$table=taabeer_newsletter_table();$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE email=%s",$email));
	if($row && ('confirmed'===$row->status || strtotime($row->updated_at.' UTC')>time()-5*MINUTE_IN_SECONDS)){return 'pending';}
	$token=bin2hex(random_bytes(32));$now=current_time('mysql',true);
	$data=array('status'=>'pending','updated_at'=>$now,'consent_text'=>$consent,'source_url'=>esc_url_raw(strtok($source,'?')?:home_url('/')),'notify_email'=>$recipient,'confirm_hash'=>hash('sha256',$token),'confirm_expires'=>gmdate('Y-m-d H:i:s',time()+2*DAY_IN_SECONDS),'unsubscribe_hash'=>'','confirmed_at'=>null);
	if($row){$saved=$wpdb->update($table,$data,array('id'=>$row->id));$id=(int)$row->id;}
	else{$saved=$wpdb->insert($table,array_merge($data,array('email'=>$email,'created_at'=>$now)));$id=(int)$wpdb->insert_id;}
	if(false===$saved){return 'mail-error';}
	$link=add_query_arg(array('action'=>'taabeer_newsletter_confirm','subscriber'=>$id,'token'=>$token),admin_url('admin-post.php'));
	$sent=wp_mail($email,'Confirm your TAABEER subscription',"Please confirm that you would like to receive TAABEER collections, stories and updates:\n\n".$link."\n\nThis link expires in 48 hours. If you did not request this, ignore this email. You will not be subscribed without confirmation.\n\nPrivacy: ".home_url('/privacy-policy/'));
	if(!$sent){$wpdb->update($table,array('updated_at'=>gmdate('Y-m-d H:i:s',time()-6*MINUTE_IN_SECONDS)),array('id'=>$id));}
	return $sent?'pending':'mail-error';
}
function taabeer_newsletter_post() {
	$result=taabeer_newsletter_subscribe(wp_unslash($_POST),$_SERVER['REMOTE_ADDR']??'',wp_get_referer()?:home_url('/'));
	$url=remove_query_arg('newsletter_status',wp_get_referer()?:home_url('/'));
	wp_safe_redirect(add_query_arg('newsletter_status',$result,$url));exit;
}
add_action('admin_post_nopriv_taabeer_newsletter_subscribe','taabeer_newsletter_post');
add_action('admin_post_taabeer_newsletter_subscribe','taabeer_newsletter_post');

function taabeer_newsletter_confirm($id,$token) {
	global $wpdb;$table=taabeer_newsletter_table();$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id));
	if(!$row || 'pending'!==$row->status || !$row->confirm_hash || !hash_equals($row->confirm_hash,hash('sha256',$token)) || strtotime($row->confirm_expires.' UTC')<time()){return false;}
	$unsubscribe=bin2hex(random_bytes(32));$now=current_time('mysql',true);
	$changed=$wpdb->update($table,array('status'=>'confirmed','confirmed_at'=>$now,'updated_at'=>$now,'confirm_hash'=>'','unsubscribe_hash'=>hash('sha256',$unsubscribe)),array('id'=>$row->id,'status'=>'pending','confirm_hash'=>$row->confirm_hash));
	if(1!==$changed){return false;}
	$url=add_query_arg(array('action'=>'taabeer_newsletter_unsubscribe','subscriber'=>$row->id,'token'=>$unsubscribe),admin_url('admin-post.php'));
	wp_mail($row->email,'Welcome to TAABEER',"Your subscription is confirmed. Thank you for joining TAABEER.\n\nYou can unsubscribe at any time:\n".$url."\n\nPrivacy: ".home_url('/privacy-policy/'));
	$recipient=is_email($row->notify_email)?$row->notify_email:get_option('admin_email');
	wp_mail($recipient,'TAABEER: new confirmed subscriber',"A visitor has confirmed their newsletter subscription.\n\nEmail: ".$row->email."\nManage subscribers: ".admin_url('admin.php?page=taabeer-newsletter'));
	return true;
}
function taabeer_newsletter_unsubscribe($id,$token) {
	global $wpdb;$table=taabeer_newsletter_table();$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id));
	if(!$row || !$row->unsubscribe_hash || !hash_equals($row->unsubscribe_hash,hash('sha256',$token))){return false;}
	return false!==$wpdb->update($table,array('status'=>'unsubscribed','updated_at'=>current_time('mysql',true)),array('id'=>$row->id));
}
function taabeer_newsletter_link_action() {
	nocache_headers();header('Referrer-Policy: no-referrer');$action=sanitize_key($_GET['action']??'');$id=absint($_GET['subscriber']??0);$token=sanitize_text_field(wp_unslash($_GET['token']??''));
	$confirmed='taabeer_newsletter_confirm'===$action;
	$ok=$confirmed?taabeer_newsletter_confirm($id,$token):taabeer_newsletter_unsubscribe($id,$token);
	$message=$ok?($confirmed?'Your TAABEER subscription is confirmed.':'You have been unsubscribed from TAABEER updates.'):'This link is invalid, expired or already used. Please return to the website to request a new confirmation.';
	wp_die('<p>'.esc_html($message).'</p><p><a href="'.esc_url(home_url('/')).'">Return to TAABEER</a></p>','TAABEER',array('response'=>$ok?200:400));
}
foreach(array('confirm','unsubscribe') as $action){add_action('admin_post_nopriv_taabeer_newsletter_'.$action,'taabeer_newsletter_link_action');add_action('admin_post_taabeer_newsletter_'.$action,'taabeer_newsletter_link_action');}

add_action('admin_menu',function(){add_submenu_page('taabeer-setup','Newsletter subscribers','Newsletter subscribers','manage_options','taabeer-newsletter','taabeer_newsletter_admin');});
function taabeer_newsletter_admin() {
	if(!current_user_can('manage_options')){return;}
	global $wpdb;$table=taabeer_newsletter_table();$page=max(1,absint($_GET['paged']??1));$offset=($page-1)*50;
	$rows=$wpdb->get_results($wpdb->prepare("SELECT id,email,status,created_at,confirmed_at FROM $table ORDER BY id DESC LIMIT 50 OFFSET %d",$offset));$total=(int)$wpdb->get_var("SELECT COUNT(*) FROM $table");
	echo '<div class="wrap"><h1>Newsletter subscribers</h1><p>Subscriptions are stored in WordPress. Pending addresses require email confirmation. Configure the notification inbox in the TAABEER Newsletter widget in Elementor. This panel records subscribers; it does not send bulk campaigns.</p><p>'.esc_html(number_format_i18n($total)).' records</p><table class="widefat striped"><thead><tr><th>Email</th><th>Status</th><th>Requested (UTC)</th><th>Confirmed (UTC)</th><th>Action</th></tr></thead><tbody>';
	foreach($rows as $row){echo '<tr><td>'.esc_html($row->email).'</td><td>'.esc_html(ucfirst($row->status)).'</td><td>'.esc_html($row->created_at).'</td><td>'.esc_html($row->confirmed_at?:'—').'</td><td>';if('confirmed'===$row->status){echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="taabeer_newsletter_admin_unsubscribe"><input type="hidden" name="subscriber" value="'.absint($row->id).'">';wp_nonce_field('tb_subscriber_'.$row->id);echo '<button class="button">Unsubscribe</button></form>';}echo '</td></tr>';}
	if(!$rows){echo '<tr><td colspan="5">No subscriptions yet.</td></tr>';}
	echo '</tbody></table>';echo wp_kses_post(paginate_links(array('base'=>add_query_arg('paged','%#%'),'total'=>(int)ceil($total/50),'current'=>$page)));echo '<p>Use Tools → Export Personal Data / Erase Personal Data for subscriber privacy requests.</p></div>';
}
add_action('admin_post_taabeer_newsletter_admin_unsubscribe',function(){if(!current_user_can('manage_options')){wp_die('Not permitted','',array('response'=>403));} $id=absint($_POST['subscriber']??0);check_admin_referer('tb_subscriber_'.$id);global $wpdb;$wpdb->update(taabeer_newsletter_table(),array('status'=>'unsubscribed','updated_at'=>current_time('mysql',true)),array('id'=>$id));wp_safe_redirect(admin_url('admin.php?page=taabeer-newsletter'));exit;});

add_filter('wp_privacy_personal_data_exporters',function($exporters){$exporters['taabeer-newsletter']=array('exporter_friendly_name'=>'TAABEER newsletter','callback'=>'taabeer_newsletter_export');return $exporters;});
function taabeer_newsletter_export($email,$page=1) {
	global $wpdb;$row=$wpdb->get_row($wpdb->prepare('SELECT email,status,created_at,confirmed_at,consent_text,source_url FROM '.taabeer_newsletter_table().' WHERE email=%s',strtolower($email)),ARRAY_A);$data=array();
	if($row){$fields=array();foreach($row as $name=>$value){$fields[]=array('name'=>ucwords(str_replace('_',' ',$name)),'value'=>(string)$value);}$data[]=array('group_id'=>'taabeer-newsletter','group_label'=>'TAABEER newsletter','item_id'=>'subscriber-'.hash('sha256',$email),'data'=>$fields);}
	return array('data'=>$data,'done'=>true);
}
add_filter('wp_privacy_personal_data_erasers',function($erasers){$erasers['taabeer-newsletter']=array('eraser_friendly_name'=>'TAABEER newsletter','callback'=>'taabeer_newsletter_erase');return $erasers;});
function taabeer_newsletter_erase($email,$page=1) {global $wpdb;$deleted=$wpdb->delete(taabeer_newsletter_table(),array('email'=>strtolower($email)));return array('items_removed'=>(bool)$deleted,'items_retained'=>false,'messages'=>false===$deleted?array('The subscriber record could not be removed. Please retry.'):array(),'done'=>false!==$deleted);}
