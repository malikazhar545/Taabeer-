<?php
/** Editable controls for functionality that Elementor Free does not provide. */
defined( 'ABSPATH' ) || exit;

class Taabeer_Visual_Navigation extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-navigation'; }
	public function get_title() { return 'TAABEER Navigation'; }
	public function get_icon() { return 'eicon-nav-menu'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Navigation' ) );
		$r = new \Elementor\Repeater();
		$r->add_control( 'text', array( 'label' => 'Label', 'type' => 'text' ) );
		$r->add_control( 'link', array( 'label' => 'Destination', 'type' => 'url' ) );
		$this->add_control( 'items', array( 'label' => 'Menu links', 'type' => 'repeater', 'fields' => $r->get_controls(), 'title_field' => '{{{ text }}}', 'default' => array() ) );
		$this->add_control( 'open_label', array( 'label' => 'Open menu label', 'type' => 'text', 'default' => 'Open navigation' ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle><span class="site-header__toggle-lines" aria-hidden="true"><span></span><span></span></span><span class="screen-reader-text">' . esc_html( $s['open_label'] ) . '</span></button><nav id="primary-navigation" class="primary-navigation" aria-label="Primary navigation" data-primary-nav><ul class="primary-navigation__list">';
		foreach ( $s['items'] as $item ) { echo '<li><a href="' . esc_url( $item['link']['url'] ?? '' ) . '">' . esc_html( $item['text'] ) . '</a></li>'; }
		echo '</ul></nav>';
	}
}

class Taabeer_Visual_Utility extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-utility'; }
	public function get_title() { return 'TAABEER Site Action'; }
	public function get_icon() { return 'eicon-button'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Action' ) );
		$this->add_control( 'kind', array( 'label' => 'Action', 'type' => 'select', 'options' => array( 'search' => 'Search', 'cookies' => 'Cookie preferences', 'copyright' => 'Copyright', 'cart' => 'Shopping bag' ), 'default' => 'search' ) );
		$this->add_control( 'label', array( 'label' => 'Label / brand', 'type' => 'text', 'default' => 'Search' ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'cookies' === $s['kind'] ) { echo '<button type="button" class="site-footer__cookie-button" data-cookie-settings>' . esc_html( $s['label'] ) . '</button>'; }
		elseif ( 'copyright' === $s['kind'] ) { echo '<p>&copy; ' . esc_html( gmdate( 'Y' ) . ' ' . $s['label'] ) . '</p>'; }
		elseif ( 'cart' === $s['kind'] ) { if ( function_exists( 'wc_get_cart_url' ) && taabeer_commerce_is_live() ) { echo '<a href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html( $s['label'] ) . '</a>'; } }
		else { echo '<a class="site-header__action" href="' . esc_url( home_url( '/?s=' ) ) . '" aria-label="' . esc_attr( $s['label'] ) . '"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></a>'; }
	}
}

class Taabeer_Visual_Dynamic extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-dynamic'; }
	public function get_title() { return 'TAABEER Dynamic Content'; }
	public function get_icon() { return 'eicon-post-content'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Dynamic content' ) );
		$this->add_control( 'kind', array( 'label' => 'Display', 'type' => 'select', 'options' => array( 'title' => 'Current title', 'description' => 'Archive description / excerpt', 'content' => 'Current content', 'image' => 'Featured image', 'stories' => 'Journal stories', 'profiles'=>'Creative profiles', 'products' => 'Product grid', 'product-gallery' => 'Product gallery', 'product-summary' => 'Product details and purchase controls', 'product-tabs' => 'Product information tabs', 'search' => 'Search form and results' ), 'default' => 'title' ) );
		$this->add_control( 'tag', array( 'label' => 'Heading tag', 'type' => 'select', 'options' => array( 'h1'=>'H1','h2'=>'H2','h3'=>'H3' ), 'default'=>'h1' ) );
		$this->add_control( 'empty', array( 'label' => 'Empty-state message', 'type'=>'textarea', 'default'=>'Our first stories are coming soon.' ) );
		$this->add_control( 'count', array( 'label'=>'Items per page', 'type'=>'number', 'default'=>9, 'min'=>1, 'max'=>24 ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display(); $object=get_queried_object();
		if('taabeer_layout'===get_post_type(get_queried_object_id()) && in_array($s['kind'],array('content','description','image','product-gallery','product-summary','product-tabs'),true)) {echo '<p class="tb-dynamic-preview">'.esc_html(ucwords(str_replace('-',' ',$s['kind']))).' — populated from the current story, collection or product on the website.</p>';return;}
		switch ( $s['kind'] ) {
			case 'title': $title=is_tax() ? single_term_title('',false) : (is_search() ? sprintf('Search: %s',get_search_query()) : (is_post_type_archive()?post_type_archive_title('',false):get_the_title(get_queried_object_id()))); echo '<'.esc_attr($s['tag']).'>'.esc_html($title).'</'.esc_attr($s['tag']).'>'; break;
			case 'description': echo wp_kses_post( is_tax() ? term_description() : wpautop(get_the_excerpt(get_queried_object_id())) ); break;
			case 'image': echo get_the_post_thumbnail(get_queried_object_id(),'full'); break;
			case 'content': $post=get_post(get_queried_object_id()); if($post) { echo apply_filters('the_content',$post->post_content); } break;
			case 'product-gallery': if(function_exists('woocommerce_show_product_images')) { woocommerce_show_product_images(); } break;
			case 'product-summary': if(function_exists('woocommerce_template_single_title')) { do_action('woocommerce_single_product_summary'); } break;
			case 'product-tabs': if(function_exists('woocommerce_output_product_data_tabs')) { woocommerce_output_product_data_tabs(); } break;
			case 'products':
				if(!taabeer_commerce_is_live()) { echo '<p class="empty-state">'.esc_html($s['empty']).'</p>'; break; }
				$category=$object instanceof WP_Term && 'product_cat'===$object->taxonomy?' category="'.esc_attr($object->slug).'"':'';
				echo do_shortcode('[products'.$category.' limit="'.absint($s['count']).'" columns="3" paginate="true"]'); break;
			case 'search': get_search_form(); if(is_search()) { global $wp_query; if(have_posts()) { echo '<div class="tb-search-results">'; while(have_posts()) { the_post(); echo '<article><h2><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h2><p>'.esc_html(get_the_excerpt()).'</p></article>'; } echo '</div>'; the_posts_pagination(); } else { echo '<p>'.esc_html($s['empty']).'</p>'; } } break;
			default:
				$args=array('post_type'=>'profiles'===$s['kind']?'creative_profile':'heritage_story','post_status'=>'publish','posts_per_page'=>absint($s['count']),'paged'=>max(1,get_query_var('paged')));
				if(is_tax() && $object instanceof WP_Term) { $args['tax_query']=array(array('taxonomy'=>$object->taxonomy,'field'=>'term_id','terms'=>$object->term_id)); }
				$q=new WP_Query($args); if($q->have_posts()) { echo '<div class="story-grid">'; while($q->have_posts()) { $q->the_post();get_template_part('template-parts/story','card'); } echo '</div>'; echo paginate_links(array('total'=>$q->max_num_pages)); } else { echo '<p class="empty-state">'.esc_html($s['empty']).'</p>'; } wp_reset_postdata();
		}
	}
}

class Taabeer_Visual_Cookies extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-cookie-notice'; }
	public function get_title() { return 'TAABEER Cookie Notice'; }
	public function get_icon() { return 'eicon-alert'; }
	protected function register_controls() {
		$this->start_controls_section('content',array('label'=>'Consent copy'));
		foreach(array('title'=>'Your privacy choices','body'=>'We use essential cookies to run the site. Optional analytics cookies will only be used with your permission.','essential'=>'Essential only','accept'=>'Accept analytics') as $key=>$default) {$this->add_control($key,array('label'=>ucfirst($key),'type'=>'textarea','default'=>$default));}
		$this->end_controls_section();
	}
	protected function render() {
		$s=$this->get_settings_for_display();
		echo '<div class="cookie-banner" role="dialog" aria-modal="false" aria-labelledby="cookie-title" hidden data-cookie-banner><div><h2 id="cookie-title">'.esc_html($s['title']).'</h2><p>'.esc_html($s['body']).'</p></div><div class="cookie-banner__actions"><button class="button button--outline" type="button" data-cookie-choice="essential">'.esc_html($s['essential']).'</button><button class="button" type="button" data-cookie-choice="all">'.esc_html($s['accept']).'</button></div></div>';
	}
}

/** Style tabs on all TAABEER widgets: text, backgrounds, fonts, borders and spacing. */
add_action('elementor/element/after_section_end', function($element,$section) {
	if('content'!==$section || ! $element instanceof Taabeer_Elementor_Widget_Base) { return; }
	$element->start_controls_section('tb_visual_style',array('label'=>'TAABEER styling','tab'=>\Elementor\Controls_Manager::TAB_STYLE));
	$element->add_control('tb_heading_color',array('label'=>'Heading colour','type'=>'color','selectors'=>array('{{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3'=>'color: {{VALUE}};')));
	$element->add_control('tb_text_color',array('label'=>'Text colour','type'=>'color','selectors'=>array('{{WRAPPER}}, {{WRAPPER}} p, {{WRAPPER}} a'=>'color: {{VALUE}};')));
	$element->add_group_control(\Elementor\Group_Control_Typography::get_type(),array('name'=>'tb_heading_font','label'=>'Heading typography','selector'=>'{{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3'));
	$element->add_group_control(\Elementor\Group_Control_Typography::get_type(),array('name'=>'tb_body_font','label'=>'Body typography','selector'=>'{{WRAPPER}} p, {{WRAPPER}} a'));
	$element->add_group_control(\Elementor\Group_Control_Background::get_type(),array('name'=>'tb_background','selector'=>'{{WRAPPER}} > .elementor-widget-container'));
	$element->end_controls_section();
},10,2);

// Native image widgets retain editable alternative text even for supplied theme images.
add_action('elementor/element/image/section_image/before_section_end',function($widget){
	$widget->add_control('taabeer_alt',array('label'=>'Alternative text override','type'=>'text','description'=>'Describe meaningful images. Leave empty for decorative images or to use Media Library alternative text.'));
});
add_filter('elementor/widget/render_content',function($html,$widget){
	if('image'===$widget->get_name() && $widget->get_settings_for_display('taabeer_alt')) {
		$processor=new WP_HTML_Tag_Processor($html);if($processor->next_tag('IMG')){$processor->set_attribute('alt',$widget->get_settings_for_display('taabeer_alt'));$html=$processor->get_updated_html();}
	}return $html;
},10,2);
