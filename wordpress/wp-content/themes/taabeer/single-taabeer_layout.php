<?php
/** Administrator-only preview for editable shared content templates. */
defined('ABSPATH') || exit;
if(!current_user_can('edit_post',get_queried_object_id())) {
	global $wp_query;$wp_query->set_404();status_header(404);nocache_headers();include get_404_template();return;
}
get_header();
echo '<main id="main-content">';
while(have_posts()){the_post();the_content();}
echo '</main>';
get_footer();
