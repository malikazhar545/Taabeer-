<?php
/** Check WordPress submenu resolution, not only the panel rendering function. */
require '/var/www/html/wp-load.php';require_once ABSPATH.'wp-admin/includes/plugin.php';
if(!str_contains(home_url(),'localhost')){exit(1);}wp_set_current_user(1);
function tb_menu_assert($ok,$text){if(!$ok){throw new RuntimeException($text);}echo "PASS $text\n";}
$menu=array();$submenu=array();$admin_page_hooks=array();$_registered_pages=array();$_parent_pages=array();$_wp_real_parent_file=array();
foreach($GLOBALS['wp_filter']['admin_menu']->callbacks as $priority=>$hooks){
	foreach($hooks as $hook){$callback=$hook['function'];
		if('taabeer_setup_menu'===$callback){call_user_func($callback);}
		elseif($callback instanceof Closure && str_ends_with((new ReflectionFunction($callback))->getFileName(),'/inc/newsletter.php')){call_user_func($callback);}
	}
}
$handler=get_plugin_page_hook('taabeer-newsletter','taabeer-setup');
tb_menu_assert('taabeer_page_taabeer-newsletter'===$handler,'newsletter resolves beneath the registered TAABEER menu');
tb_menu_assert(false!==has_action($handler,'taabeer_newsletter_admin'),'WordPress page hook has the newsletter callback');
$plugin_page='taabeer-newsletter';$parent_file='taabeer-setup';$pagenow='admin.php';$title='';
tb_menu_assert('Newsletter subscribers'===get_admin_page_title(),'admin screen has the correct non-null title');
set_error_handler(function($level,$message){throw new RuntimeException($message);},E_WARNING|E_DEPRECATED);
try{ob_start();do_action($handler);$panel=ob_get_clean();}finally{restore_error_handler();}
tb_menu_assert(str_contains($panel,'Newsletter subscribers') && str_contains($panel,'<table'),'registered callback renders the subscribers table');
$widget=Elementor\Plugin::instance()->elements_manager->create_element_instance(taabeer_visual_widget('taabeer-navigation',array()));
$stack=$widget->get_stack();$controls=array_merge($stack['controls']??array(),$stack['style_controls']??array());
foreach(array('drawer_background','drawer_link','drawer_hover') as $control){tb_menu_assert(isset($controls[$control]),'expanded menu has editable '.$control.' control');}
tb_menu_assert(str_contains($controls['tb_text_color']['selectors']['{{WRAPPER}}']??'','--taabeer-navigation-link'),'desktop link setting does not directly override expanded links');
echo "NEWSLETTER MENU CHECKS PASSED\n";
