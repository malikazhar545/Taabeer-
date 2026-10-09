<?php
/** Local regression checks for the native editorial pages and admin repairs. */
require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/admin.php';
if (!str_contains(home_url(), 'localhost')) { throw new RuntimeException('Local validation only.'); }
function verify_editorial($condition, $message) {
	if (!$condition) { throw new RuntimeException($message); }
	echo "PASS $message\n";
}
$kit = (int) get_option('elementor_active_kit');
$settings = get_post_meta($kit, '_elementor_page_settings', true);
try {
	update_option('elementor_active_kit', get_option('page_on_front'));
	$repaired = taabeer_ensure_elementor_kit();
	verify_editorial(Elementor\Plugin::instance()->kits_manager->is_kit($repaired), 'invalid active kit repaired');
} finally { update_option('elementor_active_kit', $kit); }
verify_editorial(get_post_meta($kit, '_elementor_page_settings', true) === $settings, 'original kit settings preserved');
$pages = array_merge(array_keys(taabeer_editorial_page_definitions()), array_map(fn($slug) => 'regions/' . $slug, array_keys(taabeer_region_definitions())), array('regions'));
$before = array();
foreach ($pages as $path) {
	$page = get_page_by_path('discover-pakistan/' . $path);
	verify_editorial($page && 'publish' === $page->post_status, 'published page ' . $path);
	$before[$page->ID] = get_post_meta($page->ID, '_elementor_data', true);
	$tree = json_decode($before[$page->ID], true);
	$scan = function ($nodes) use (&$scan) {
		foreach ($nodes as $node) {
			if (isset($node['widgetType'])) { verify_editorial(in_array($node['widgetType'], array('heading', 'text-editor', 'image', 'button'), true), 'native Elementor ' . $node['widgetType']); }
			if (!empty($node['elements'])) { $scan($node['elements']); }
		}
	};
	$scan($tree);
}
taabeer_migrate_editorial_pages_1_1_1();
foreach ($before as $id => $data) { verify_editorial(get_post_meta($id, '_elementor_data', true) === $data, 'repeat migration preserves page ' . $id); }
wp_set_current_user(1);
$GLOBALS['menu'] = array(); $GLOBALS['submenu'] = array();
do_action('admin_menu');
$GLOBALS['pagenow'] = 'tools.php'; $GLOBALS['plugin_page'] = 'taabeer-updates'; $GLOBALS['title'] = null;
do_action('load-tools_page_taabeer-updates');
verify_editorial(get_admin_page_title() === 'TAABEER Updates', 'legacy updater has a non-null admin title');
echo "EDITORIAL UPDATE VALIDATION PASSED\n";
