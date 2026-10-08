<?php
/**
 * Front page.
 *
 * @package Taabeer
 */
get_header();
?>
<main id="main-content">
	<?php if ( have_posts() ) : the_post(); ?>
		<?php if ( taabeer_is_built_with_elementor() && trim( get_the_content() ) ) : ?>
			<?php the_content(); ?>
		<?php else : ?>
			<section class="home-hero">
				<img class="home-hero__image" src="<?php echo esc_url( taabeer_demo_image_url( 'hero-weaver.webp' ) ); ?>" alt="<?php esc_attr_e( 'Concept image showing textile making; replace with approved Taabeer photography before publication', 'taabeer' ); ?>" width="2200" height="1283" fetchpriority="high">
				<div class="home-hero__veil" aria-hidden="true"></div>
				<div class="home-hero__content shell">
					<p class="eyebrow eyebrow--light"><?php esc_html_e( 'A curated house of Pakistani design', 'taabeer' ); ?></p>
					<h1><?php esc_html_e( 'Pakistani design, thoughtfully curated', 'taabeer' ); ?></h1>
					<p class="home-hero__intro"><?php esc_html_e( 'Discover heritage craftsmanship and contemporary design, thoughtfully curated from Pakistan.', 'taabeer' ); ?></p>
					<div class="button-row">
						<a class="button button--light" href="<?php echo esc_url( home_url( '/collections/' ) ); ?>"><?php esc_html_e( 'Explore the collections', 'taabeer' ); ?></a>
						<a class="button button--ghost-light" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Our story', 'taabeer' ); ?></a>
					</div>
				</div>
			</section>

			<section class="home-intro section shell reveal-on-scroll">
				<div class="home-intro__label">
					<p class="eyebrow"><?php esc_html_e( 'The TAABEER perspective', 'taabeer' ); ?></p>
				</div>
				<div class="home-intro__copy">
					<h2><?php esc_html_e( 'A considered perspective on Pakistani design', 'taabeer' ); ?></h2>
					<p><?php esc_html_e( 'From textiles and pieces for the home to art and adornment, our focus is on work with a distinctive point of view. We look at the detail, the material and the story behind each piece, creating a selection that connects cultural expression with the vision of its designers today.', 'taabeer' ); ?></p>
				</div>
			</section>

			<?php
			$collections = array(
				array( 'The Art of Wear', 'Textiles and clothing chosen for their character, detail and ease of wear.', 'collection-wear.webp', 'the-art-of-wear' ),
				array( 'The Art of Adornment', 'Jewellery and decorative accessories with a distinctive finishing touch.', 'collection-adornment.webp', 'the-art-of-adornment' ),
				array( 'The Art of Living', 'Furniture, decorative pieces and useful designs for expressive spaces.', 'collection-living.webp', 'the-art-of-living' ),
				array( 'The Art of Expression', 'Art and collectible work selected for its visual language and point of view.', 'collection-expression.webp', 'the-art-of-expression' ),
				array( 'The Art of Leather', 'Bags and leather accessories chosen for shape, finish and everyday purpose.', 'collection-leather.webp', 'the-art-of-leather' ),
			);
			?>
			<section class="collection-section section" id="collections">
				<div class="shell section-heading">
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Collections', 'taabeer' ); ?></p>
						<h2><?php esc_html_e( 'Five perspectives on design', 'taabeer' ); ?></h2>
					</div>
					<a class="text-link" href="<?php echo esc_url( home_url( '/collections/' ) ); ?>"><?php esc_html_e( 'View all collections', 'taabeer' ); ?></a>
				</div>
				<div class="collection-editorial-grid shell">
					<?php foreach ( $collections as $index => $collection ) : ?>
						<?php
						$term = get_term_by( 'slug', $collection[3], 'taabeer_collection' );
						$link = $term ? get_term_link( $term ) : home_url( '/collections/' );
						?>
						<article class="collection-card collection-card--<?php echo esc_attr( (string) ( $index + 1 ) ); ?> reveal-on-scroll">
							<a href="<?php echo esc_url( $link ); ?>">
								<div class="collection-card__media">
									<img src="<?php echo esc_url( taabeer_demo_image_url( $collection[2] ) ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Concept image for %s; replace with approved photography', 'taabeer' ), $collection[0] ) ); ?>" loading="lazy" width="900" height="1100">
								</div>
								<p class="collection-card__number">0<?php echo esc_html( (string) ( $index + 1 ) ); ?></p>
								<h3><?php echo esc_html( $collection[0] ); ?></h3>
								<p><?php echo esc_html( $collection[1] ); ?></p>
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="discover-feature section section--green reveal-on-scroll">
				<div class="discover-feature__media">
					<img src="<?php echo esc_url( taabeer_demo_image_url( 'discover-pakistan.webp' ) ); ?>" alt="<?php esc_attr_e( 'Concept image for Discover Pakistan; replace with approved heritage and contemporary photography', 'taabeer' ); ?>" loading="lazy" width="2200" height="1283">
				</div>
				<div class="discover-feature__content">
					<p class="eyebrow eyebrow--gold"><?php esc_html_e( 'Discover Pakistan', 'taabeer' ); ?></p>
					<h2><?php esc_html_e( 'A culture in the making', 'taabeer' ); ?></h2>
					<p><?php esc_html_e( 'Explore the regions, creative voices and evolving ideas behind the TAABEER selection. From heritage practices to contemporary design, discover the stories that bring each piece into focus.', 'taabeer' ); ?></p>
					<a class="text-link text-link--light" href="<?php echo esc_url( home_url( '/discover-pakistan/' ) ); ?>"><?php esc_html_e( 'Discover Pakistan', 'taabeer' ); ?></a>
				</div>
			</section>

			<section class="about-feature section shell reveal-on-scroll">
				<div class="about-feature__media">
					<img src="<?php echo esc_url( taabeer_demo_image_url( 'about-material.webp' ) ); ?>" alt="<?php esc_attr_e( 'Concept image showing detailed handwork; replace with approved Taabeer photography', 'taabeer' ); ?>" loading="lazy" width="1200" height="1500">
				</div>
				<div class="about-feature__content">
					<p class="eyebrow"><?php esc_html_e( 'About TAABEER', 'taabeer' ); ?></p>
					<h2><?php esc_html_e( 'Heritage and contemporary design, side by side', 'taabeer' ); ?></h2>
					<p><?php esc_html_e( 'Based in London, TAABEER is building a curated selection that presents Pakistani design through the work itself, the people behind it and the place it can hold in our lives today.', 'taabeer' ); ?></p>
					<a class="text-link" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Read our story', 'taabeer' ); ?></a>
				</div>
			</section>

			<section class="journal-preview section shell">
				<div class="section-heading">
					<div><p class="eyebrow"><?php esc_html_e( 'Journal', 'taabeer' ); ?></p><h2><?php esc_html_e( 'Behind the pieces', 'taabeer' ); ?></h2></div>
					<a class="text-link" href="<?php echo esc_url( get_post_type_archive_link( 'heritage_story' ) ); ?>"><?php esc_html_e( 'Read our journal', 'taabeer' ); ?></a>
				</div>
				<?php
				$stories = new WP_Query( array( 'post_type' => 'heritage_story', 'post_status' => 'publish', 'posts_per_page' => 3 ) );
				if ( $stories->have_posts() ) :
					?>
					<div class="story-grid">
						<?php while ( $stories->have_posts() ) : $stories->the_post(); get_template_part( 'template-parts/story', 'card' ); endwhile; ?>
					</div>
				<?php else : ?>
					<div class="journal-preview__coming-soon">
						<p><?php esc_html_e( 'A closer look at the materials, ideas and people behind Pakistani design.', 'taabeer' ); ?></p>
						<p class="eyebrow"><?php esc_html_e( 'Our first stories are coming soon.', 'taabeer' ); ?></p>
					</div>
				<?php endif; wp_reset_postdata(); ?>
			</section>

			<section class="newsletter-section section section--ivory-dark">
				<div class="shell newsletter-section__inner">
					<div>
						<p class="eyebrow"><?php esc_html_e( 'Stay close to TAABEER', 'taabeer' ); ?></p>
						<h2><?php esc_html_e( 'New collections, stories and upcoming releases', 'taabeer' ); ?></h2>
					</div>
					<p><?php esc_html_e( 'Mailing-list registration will open once the subscription service and privacy wording are approved.', 'taabeer' ); ?></p>
				</div>
			</section>

			<section class="partnership-cta section shell reveal-on-scroll">
				<p class="eyebrow"><?php esc_html_e( 'Partnerships', 'taabeer' ); ?></p>
				<h2><?php esc_html_e( 'Create a connection with TAABEER', 'taabeer' ); ?></h2>
				<p><?php esc_html_e( 'We welcome conversations with Pakistani designers, artists, makers and brands whose work brings a distinctive perspective to our collections.', 'taabeer' ); ?></p>
				<a class="button" href="<?php echo esc_url( home_url( '/contact/?enquiry=partnership' ) ); ?>"><?php esc_html_e( 'Discuss a partnership', 'taabeer' ); ?></a>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>

