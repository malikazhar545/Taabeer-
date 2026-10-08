<?php
/**
 * Elementor widgets bundled with the Taabeer theme.
 *
 * @package Taabeer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Taabeer_Elementor_Widget_Base extends \Elementor\Widget_Base {
	public function get_categories() {
		return array( 'taabeer' );
	}

	protected function image_url( $setting, $fallback ) {
		return ! empty( $setting['url'] ) ? $setting['url'] : taabeer_demo_image_url( $fallback );
	}
}

class Taabeer_Elementor_Header_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-header'; }
	public function get_title() { return __( 'TAABEER Header', 'taabeer' ); }
	public function get_icon() { return 'eicon-header'; }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Header', 'taabeer' ) ) );
		$this->add_control( 'announcement', array( 'label' => __( 'Announcement', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'A London-based curated house of Pakistani design', 'taabeer' ) ) );
		$this->add_control( 'brand', array( 'label' => __( 'English brand name', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'TAABEER' ) );
		$this->add_control( 'urdu', array( 'label' => __( 'Urdu brand name', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'تعبیر' ) );
		$menus = wp_get_nav_menus();
		$options = array( '' => __( 'Primary menu location', 'taabeer' ) );
		foreach ( $menus as $menu ) { $options[ $menu->term_id ] = $menu->name; }
		$this->add_control( 'menu', array( 'label' => __( 'Menu', 'taabeer' ), 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $options, 'default' => '' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<?php if ( $s['announcement'] ) : ?><div class="taabeer-announcement" role="note"><span><?php echo esc_html( $s['announcement'] ); ?></span></div><?php endif; ?>
		<header class="site-header" data-site-header>
			<div class="site-header__inner">
				<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle><span class="site-header__toggle-lines" aria-hidden="true"><span></span><span></span></span><span class="screen-reader-text"><?php esc_html_e( 'Open navigation', 'taabeer' ); ?></span></button>
				<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Taabeer home', 'taabeer' ); ?>"><span class="site-brand__english"><?php echo esc_html( $s['brand'] ); ?></span><?php if ( $s['urdu'] ) : ?><span class="site-brand__urdu" lang="ur" dir="rtl"><?php echo esc_html( $s['urdu'] ); ?></span><?php endif; ?></a>
				<nav id="primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'taabeer' ); ?>" data-primary-nav>
					<?php wp_nav_menu( array( 'menu' => $s['menu'] ? absint( $s['menu'] ) : '', 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'primary-navigation__list', 'fallback_cb' => 'taabeer_fallback_menu' ) ); ?>
				</nav>
				<div class="site-header__actions"><a class="site-header__action" href="<?php echo esc_url( home_url( '/?s=' ) ); ?>" aria-label="<?php esc_attr_e( 'Search', 'taabeer' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m16 16 4 4" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></a><?php if ( class_exists( 'WooCommerce' ) && function_exists( 'taabeer_commerce_is_live' ) && taabeer_commerce_is_live() ) : ?><a class="site-header__action" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Shopping bag', 'taabeer' ); ?>"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><path d="M5 8h14l-1 13H6L5 8Z" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M9 9V6a3 3 0 0 1 6 0v3" fill="none" stroke="currentColor" stroke-width="1.5"/></svg></a><?php endif; ?></div>
			</div>
		</header>
		<?php
	}
}

class Taabeer_Elementor_Footer_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-footer'; }
	public function get_title() { return __( 'TAABEER Footer', 'taabeer' ); }
	public function get_icon() { return 'eicon-footer'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Footer', 'taabeer' ) ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Pakistani design, thoughtfully curated.', 'taabeer' ) ) );
		$this->add_control( 'address', array( 'label' => __( 'Approved public location', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'London, United Kingdom', 'taabeer' ) ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'CTA text', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Begin a conversation', 'taabeer' ) ) );
		$this->add_control( 'cta_url', array( 'label' => __( 'CTA link', 'taabeer' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => home_url( '/contact/' ) ) ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<footer class="site-footer"><div class="site-footer__lead"><p class="eyebrow">TAABEER</p><h2><?php echo esc_html( $s['heading'] ); ?></h2><a class="text-link text-link--light" href="<?php echo esc_url( $s['cta_url']['url'] ); ?>"><?php echo esc_html( $s['cta_text'] ); ?></a></div><div class="site-footer__grid"><div><h3><?php esc_html_e( 'Explore', 'taabeer' ); ?></h3><?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'site-footer__menu', 'fallback_cb' => false ) ); ?></div><div><h3><?php esc_html_e( 'Studio', 'taabeer' ); ?></h3><p><?php echo nl2br( esc_html( $s['address'] ) ); ?></p></div><div><h3><?php esc_html_e( 'Information', 'taabeer' ); ?></h3><ul class="site-footer__menu"><li><a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'taabeer' ); ?></a></li><li><a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'taabeer' ); ?></a></li></ul></div></div><div class="site-footer__bottom"><p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.</p><nav aria-label="<?php esc_attr_e( 'Legal', 'taabeer' ); ?>"><a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'taabeer' ); ?></a><a href="<?php echo esc_url( home_url( '/cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookies', 'taabeer' ); ?></a><button class="site-footer__cookie-button" type="button" data-cookie-settings><?php esc_html_e( 'Cookie settings', 'taabeer' ); ?></button></nav></div></footer>
		<?php
	}
}

class Taabeer_Elementor_Hero_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-hero'; }
	public function get_title() { return __( 'TAABEER Cinematic Hero', 'taabeer' ); }
	public function get_icon() { return 'eicon-banner'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero Content', 'taabeer' ) ) );
		$this->add_control( 'image', array( 'label' => __( 'Background image', 'taabeer' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => taabeer_demo_image_url( 'hero-weaver.webp' ) ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'A curated house of Pakistani design', 'taabeer' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Pakistani design, thoughtfully curated', 'taabeer' ) ) );
		$this->add_control( 'intro', array( 'label' => __( 'Introduction', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Discover heritage craftsmanship and contemporary design, thoughtfully curated from Pakistan.', 'taabeer' ) ) );
		$this->add_control( 'primary_text', array( 'label' => __( 'Primary button', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore the collections', 'taabeer' ) ) );
		$this->add_control( 'primary_url', array( 'label' => __( 'Primary link', 'taabeer' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => home_url( '/collections/' ) ) ) );
		$this->add_control( 'secondary_text', array( 'label' => __( 'Secondary button', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Our story', 'taabeer' ) ) );
		$this->add_control( 'secondary_url', array( 'label' => __( 'Secondary link', 'taabeer' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => home_url( '/about/' ) ) ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="home-hero"><img class="home-hero__image" src="<?php echo esc_url( $this->image_url( $s['image'], 'hero-weaver.webp' ) ); ?>" alt="" width="2200" height="1283"><div class="home-hero__veil" aria-hidden="true"></div><div class="home-hero__content shell"><p class="eyebrow eyebrow--light"><?php echo esc_html( $s['eyebrow'] ); ?></p><h1><?php echo esc_html( $s['title'] ); ?></h1><p class="home-hero__intro"><?php echo esc_html( $s['intro'] ); ?></p><div class="button-row"><a class="button button--light" href="<?php echo esc_url( $s['primary_url']['url'] ); ?>"><?php echo esc_html( $s['primary_text'] ); ?></a><a class="button button--ghost-light" href="<?php echo esc_url( $s['secondary_url']['url'] ); ?>"><?php echo esc_html( $s['secondary_text'] ); ?></a></div></div></section>
		<?php
	}
}

class Taabeer_Elementor_Collections_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-collections'; }
	public function get_title() { return __( 'TAABEER Collections', 'taabeer' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Collections', 'taabeer' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Collections', 'taabeer' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Five perspectives on design', 'taabeer' ) ) );
		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'name', array( 'label' => __( 'Name', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$repeater->add_control( 'description', array( 'label' => __( 'Description', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
		$repeater->add_control( 'image', array( 'label' => __( 'Image', 'taabeer' ), 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$repeater->add_control( 'link', array( 'label' => __( 'Link', 'taabeer' ), 'type' => \Elementor\Controls_Manager::URL ) );
		$defaults = array();
		$items = array(
			array( 'The Art of Wear', 'Textiles and clothing chosen for character, detail and ease of wear.', 'collection-wear.webp' ),
			array( 'The Art of Adornment', 'Jewellery and decorative accessories with a distinctive finishing touch.', 'collection-adornment.webp' ),
			array( 'The Art of Living', 'Furniture, decorative pieces and useful designs for expressive spaces.', 'collection-living.webp' ),
			array( 'The Art of Expression', 'Art and collectible work selected for its visual language.', 'collection-expression.webp' ),
			array( 'The Art of Leather', 'Bags and leather accessories chosen for shape, finish and purpose.', 'collection-leather.webp' ),
		);
		foreach ( $items as $item ) {
			$term = get_term_by( 'slug', sanitize_title( $item[0] ), 'taabeer_collection' );
			$link = $term ? get_term_link( $term ) : home_url( '/collections/' );
			$defaults[] = array( 'name' => $item[0], 'description' => $item[1], 'image' => array( 'url' => taabeer_demo_image_url( $item[2] ) ), 'link' => array( 'url' => is_wp_error( $link ) ? home_url( '/collections/' ) : $link ) );
		}
		$this->add_control( 'collections', array( 'label' => __( 'Collection cards', 'taabeer' ), 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'default' => $defaults, 'title_field' => '{{{ name }}}' ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="collection-section section"><div class="shell section-heading"><div><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><h2><?php echo esc_html( $s['title'] ); ?></h2></div></div><div class="collection-editorial-grid shell"><?php foreach ( $s['collections'] as $index => $item ) : ?><article class="collection-card collection-card--<?php echo esc_attr( (string) ( $index + 1 ) ); ?> reveal-on-scroll"><a href="<?php echo esc_url( $item['link']['url'] ); ?>"><div class="collection-card__media"><img src="<?php echo esc_url( $this->image_url( $item['image'], 'collection-wear.webp' ) ); ?>" alt="" loading="lazy"></div><p class="collection-card__number">0<?php echo esc_html( (string) ( $index + 1 ) ); ?></p><h3><?php echo esc_html( $item['name'] ); ?></h3><p><?php echo esc_html( $item['description'] ); ?></p></a></article><?php endforeach; ?></div></section>
		<?php
	}
}

class Taabeer_Elementor_Split_Feature_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-split-feature'; }
	public function get_title() { return __( 'TAABEER Editorial Feature', 'taabeer' ); }
	public function get_icon() { return 'eicon-image-box'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Feature', 'taabeer' ) ) );
		$this->add_control( 'image', array( 'label' => __( 'Image', 'taabeer' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => taabeer_demo_image_url( 'discover-pakistan.webp' ) ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Discover Pakistan', 'taabeer' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'A culture in the making', 'taabeer' ) ) );
		$this->add_control( 'body', array( 'label' => __( 'Body', 'taabeer' ), 'type' => \Elementor\Controls_Manager::WYSIWYG, 'default' => __( 'Explore the regions, creative voices and evolving ideas behind the TAABEER selection.', 'taabeer' ) ) );
		$this->add_control( 'link_text', array( 'label' => __( 'Link text', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Discover Pakistan', 'taabeer' ) ) );
		$this->add_control( 'link', array( 'label' => __( 'Link', 'taabeer' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => home_url( '/discover-pakistan/' ) ) ) );
		$this->add_control( 'tone', array( 'label' => __( 'Colour treatment', 'taabeer' ), 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'green', 'options' => array( 'green' => __( 'Deep green', 'taabeer' ), 'ivory' => __( 'Warm ivory', 'taabeer' ) ) ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$class = 'green' === $s['tone'] ? 'discover-feature section section--green' : 'about-feature section shell';
		?>
		<section class="<?php echo esc_attr( $class ); ?> reveal-on-scroll"><div class="<?php echo 'green' === $s['tone'] ? 'discover-feature__media' : 'about-feature__media'; ?>"><img src="<?php echo esc_url( $this->image_url( $s['image'], 'discover-pakistan.webp' ) ); ?>" alt="" loading="lazy"></div><div class="<?php echo 'green' === $s['tone'] ? 'discover-feature__content' : 'about-feature__content'; ?>"><p class="eyebrow <?php echo 'green' === $s['tone'] ? 'eyebrow--gold' : ''; ?>"><?php echo esc_html( $s['eyebrow'] ); ?></p><h2><?php echo esc_html( $s['title'] ); ?></h2><div><?php echo wp_kses_post( $s['body'] ); ?></div><a class="text-link <?php echo 'green' === $s['tone'] ? 'text-link--light' : ''; ?>" href="<?php echo esc_url( $s['link']['url'] ); ?>"><?php echo esc_html( $s['link_text'] ); ?></a></div></section>
		<?php
	}
}

class Taabeer_Elementor_Story_Grid_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-story-grid'; }
	public function get_title() { return __( 'TAABEER Journal Grid', 'taabeer' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Journal', 'taabeer' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'taabeer' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Behind the pieces', 'taabeer' ) ) );
		$this->add_control( 'count', array( 'label' => __( 'Number of stories', 'taabeer' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 9 ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$q = new WP_Query( array( 'post_type' => 'heritage_story', 'post_status' => 'publish', 'posts_per_page' => absint( $s['count'] ) ) );
		?><section class="journal-preview section shell"><div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Journal', 'taabeer' ); ?></p><h2><?php echo esc_html( $s['title'] ); ?></h2></div></div><?php if ( $q->have_posts() ) : ?><div class="story-grid"><?php while ( $q->have_posts() ) : $q->the_post(); get_template_part( 'template-parts/story', 'card' ); endwhile; ?></div><?php else : ?><div class="journal-preview__coming-soon"><p><?php esc_html_e( 'Our first stories are coming soon.', 'taabeer' ); ?></p></div><?php endif; wp_reset_postdata(); ?></section><?php
	}
}

class Taabeer_Elementor_Contact_Widget extends Taabeer_Elementor_Widget_Base {
	public function get_name() { return 'taabeer-contact'; }
	public function get_title() { return __( 'TAABEER Contact Form', 'taabeer' ); }
	public function get_icon() { return 'eicon-form-horizontal'; }
	protected function register_controls() {}
	protected function render() { echo do_shortcode( '[taabeer_contact_form]' ); }
}
