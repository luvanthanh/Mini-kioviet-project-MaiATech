<?php
/**
 * The template for the homepage - WoodMart Furniture 2 Style
 * Strict compliance with Assignment Section A specifications.
 *
 * @package FurnitureEcommerse
 */

get_header(); ?>

<main id="primary" class="site-main">

	<!-- ========================================================
	     1. HERO BANNER ("MAKE YOUR SPACE, FEEL LIKE HOME" + SHOP NOW)
	     ======================================================== -->
	<section class="fe-hero fe-hero--woodmart">
		<div class="fe-container">
			<div class="fe-hero__grid">
				<div class="fe-hero__content">
					<span class="fe-hero__tag">
						<i class="fas fa-sparkles"></i> WoodMart Furniture • Collection 2024
					</span>
					<h1 class="fe-hero__title">MAKE YOUR SPACE, FEEL LIKE HOME</h1>
					<p class="fe-hero__desc">
						Designed with clean silhouettes, natural solid oak, and tactile fabrics. Bring understated Scandinavian elegance and enduring comfort into your daily living.
					</p>
					<div class="fe-hero__actions">
						<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="fe-btn fe-btn--primary">
							SHOP NOW <i class="fas fa-arrow-right"></i>
						</a>
						<a href="#fe-featured-products" class="fe-btn fe-btn--outline">
							EXPLORE LOOKBOOK
						</a>
					</div>
				</div>

				<div class="fe-hero__showcase">
					<div class="fe-hero__image-wrap">
						<img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=85" alt="WoodMart Furniture Lookbook" class="fe-hero__main-img">
						
						<!-- Floating Featured Product Callout Card -->
						<div class="fe-hero-callout">
							<span class="fe-hero-callout__badge">Trending Choice</span>
							<div class="fe-hero-callout__body">
								<img src="https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=400&q=80" alt="Nordic Modular Lounge Chair" class="fe-hero-callout__thumb">
								<div class="fe-hero-callout__info">
									<h4 class="fe-hero-callout__title">Modern Lounge Chair</h4>
									<div class="fe-hero-callout__price">From $299.00</div>
								</div>
								<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="fe-hero-callout__btn" aria-label="View product">
									<i class="fas fa-arrow-up-right-from-square"></i>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     2. TRUST BADGES / VALUE PROPS BAR
	     ======================================================== -->
	<section class="fe-trust-bar">
		<div class="fe-container">
			<div class="fe-trust-grid">
				<div class="fe-trust-item">
					<div class="fe-trust-icon"><i class="fas fa-truck-fast"></i></div>
					<div class="fe-trust-text">
						<h4>Free Shipping</h4>
						<p>Free delivery for all orders over $130</p>
					</div>
				</div>
				<div class="fe-trust-item">
					<div class="fe-trust-icon"><i class="fas fa-comments"></i></div>
					<div class="fe-trust-text">
						<h4>Expert Advice</h4>
						<p>In-store & online consultations 7 days a week</p>
					</div>
				</div>
				<div class="fe-trust-item">
					<div class="fe-trust-icon"><i class="fas fa-tree"></i></div>
					<div class="fe-trust-text">
						<h4>Sustainable Oak</h4>
						<p>100% certified Scandinavian timber & fabrics</p>
					</div>
				</div>
				<div class="fe-trust-item">
					<div class="fe-trust-icon"><i class="fas fa-shield-halved"></i></div>
					<div class="fe-trust-text">
						<h4>30-Day In-Home Trial</h4>
						<p>Risk-free returns & 3-year warranty included</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     3. CATEGORY SHOWCASE (Chairs, Tables, Sofas, Lighting, Decoration, Storage)
	     ======================================================== -->
	<section class="fe-section fe-section--categories">
		<div class="fe-container">
			<div class="fe-section-header">
				<span class="fe-section-header__subtitle">DISCOVER BY ROOM</span>
				<h2 class="fe-section-header__title">Top Categories</h2>
			</div>

			<div class="fe-cat-zoom-grid">
				<?php
				$desired_cat_slugs = array( 'chair', 'tables', 'sofas', 'lighting', 'decoration', 'storage' );
				$cat_images = array(
					'chair'      => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?auto=format&fit=crop&w=700&q=80',
					'tables'     => 'https://images.unsplash.com/photo-1533090481720-856c6e3c1fdc?auto=format&fit=crop&w=700&q=80',
					'sofas'      => 'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=700&q=80',
					'lighting'   => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=700&q=80',
					'decoration' => 'https://images.unsplash.com/photo-1513161455079-7dc1de15ef3e?auto=format&fit=crop&w=700&q=80',
					'storage'    => 'https://images.unsplash.com/photo-1595428774223-ef52624120d2?auto=format&fit=crop&w=700&q=80',
				);

				foreach ( $desired_cat_slugs as $slug ) :
					$cat = get_term_by( 'slug', $slug, 'product_cat' );
					if ( ! $cat || is_wp_error( $cat ) ) {
						continue;
					}
					$img_url   = $cat_images[ $slug ] ?? wc_placeholder_img_src();
					$term_link = get_term_link( $cat );
					?>
					<a href="<?php echo esc_url( $term_link ); ?>" class="fe-cat-zoom-card">
						<div class="fe-cat-zoom-card__thumb">
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $cat->name ); ?>">
						</div>
						<div class="fe-cat-zoom-card__content">
							<h3 class="fe-cat-zoom-card__title"><?php echo esc_html( $cat->name ); ?></h3>
							<span class="fe-cat-zoom-card__count"><?php echo esc_html( $cat->count ); ?> products</span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     4. FEATURED PRODUCTS (Grid: Image, Name, Price, Rating, Badges, Wishlist, Quick View, Add to Cart)
	     ======================================================== -->
	<section id="fe-featured-products" class="fe-section">
		<div class="fe-container">
			<div class="fe-section-header">
				<span class="fe-section-header__subtitle">HANDPICKED FOR YOU</span>
				<h2 class="fe-section-header__title">Featured Products</h2>
				<p class="fe-section-header__desc">Our most iconic Scandinavian designs, meticulously curated for refined living spaces.</p>
			</div>

			<div class="fe-product-grid">
				<?php
				$featured_query = new WP_Query( array(
					'post_type'      => 'product',
					'posts_per_page' => 8,
					'post_status'    => 'publish',
					'tax_query'      => array(
						'relation' => 'OR',
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => 'featured',
						),
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => array( 'chair', 'sofas', 'tables', 'lighting' ),
						),
					),
				) );

				if ( $featured_query->have_posts() ) {
					while ( $featured_query->have_posts() ) {
						$featured_query->the_post();
						echo fe_render_product_card();
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     5. PROMOTION BANNER ("Summer Collection, Up to 30% OFF")
	     ======================================================== -->
	<section class="fe-promo-banner-section">
		<div class="fe-container">
			<div class="fe-promo-banner-card" style="background-image: url('https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1600&q=85');">
				<div class="fe-promo-banner-overlay"></div>
				<div class="fe-promo-banner-content">
					<span class="fe-promo-badge">LIMITED TIME OFFER</span>
					<h2 class="fe-promo-title">Summer Collection, Up to 30% OFF</h2>
					<p class="fe-promo-desc">
						Upgrade your living space with sustainably sourced FSC oak tables, tactile wool armchairs, and architectural pendant lights.
					</p>
					<div class="fe-promo-actions">
						<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="fe-btn fe-btn--primary">
							SHOP THE SALE <i class="fas fa-arrow-right"></i>
						</a>
						<span class="fe-promo-code">Use code: <strong>SUMMER30</strong> at checkout</span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     6. NEW ARRIVALS (Latest 4 products)
	     ======================================================== -->
	<section class="fe-section fe-section--new-arrivals" style="background-color: var(--fe-color-surface);">
		<div class="fe-container">
			<div class="fe-section-header">
				<span class="fe-section-header__subtitle">FRESH IN OUR STORE</span>
				<h2 class="fe-section-header__title">New Arrivals</h2>
			</div>

			<div class="fe-product-grid">
				<?php
				$new_query = new WP_Query( array(
					'post_type'      => 'product',
					'posts_per_page' => 4,
					'post_status'    => 'publish',
					'orderby'        => 'date',
					'order'          => 'DESC',
				) );

				if ( $new_query->have_posts() ) {
					while ( $new_query->have_posts() ) {
						$new_query->the_post();
						echo fe_render_product_card();
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     7. BEST SELLERS (Customer favorite 4 products)
	     ======================================================== -->
	<section class="fe-section fe-section--best-sellers">
		<div class="fe-container">
			<div class="fe-section-header">
				<span class="fe-section-header__subtitle">CUSTOMER FAVORITES</span>
				<h2 class="fe-section-header__title">Best Sellers</h2>
			</div>

			<div class="fe-product-grid">
				<?php
				$bestseller_query = new WP_Query( array(
					'post_type'      => 'product',
					'posts_per_page' => 4,
					'post_status'    => 'publish',
					'meta_key'       => '_price',
					'orderby'        => 'meta_value_num',
					'order'          => 'ASC',
				) );

				if ( $bestseller_query->have_posts() ) {
					while ( $bestseller_query->have_posts() ) {
						$bestseller_query->the_post();
						echo fe_render_product_card();
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</div>
	</section>

	<!-- ========================================================
	     8. LATEST ARTICLES (3 latest blog posts)
	     ======================================================== -->
	<section class="fe-section fe-section--blog" style="background-color: var(--fe-color-surface);">
		<div class="fe-container">
			<div class="fe-section-header">
				<span class="fe-section-header__subtitle">FROM OUR JOURNAL</span>
				<h2 class="fe-section-header__title">Latest Articles</h2>
			</div>

			<div class="fe-blog-grid">
				<?php
				$blog_query = new WP_Query( array(
					'post_type'      => 'post',
					'posts_per_page' => 3,
					'post_status'    => 'publish',
					'orderby'        => 'date',
					'order'          => 'DESC',
				) );

				$blog_fallbacks = array(
					'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=800&q=80',
					'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80',
					'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
				);

				$blog_idx = 0;
				if ( $blog_query->have_posts() ) :
					while ( $blog_query->have_posts() ) :
						$blog_query->the_post();
						$meta_img = get_post_meta( get_the_ID(), '_fe_post_banner_img', true );
						$img_src  = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : ( $meta_img ?: $blog_fallbacks[ $blog_idx % 3 ] );
						$categories = get_the_category();
						$cat_label = ! empty( $categories ) ? $categories[0]->name : 'Interior Journal';
						$blog_idx++;
						?>
						<article class="fe-blog-card">
							<a href="<?php the_permalink(); ?>" class="fe-blog-card__image-link">
								<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php the_title_attribute(); ?>">
								<span class="fe-blog-card__category"><?php echo esc_html( $cat_label ); ?></span>
							</a>
							<div class="fe-blog-card__body">
								<div class="fe-blog-card__meta">
									<span><i class="far fa-user"></i> <?php the_author(); ?></span>
									<span><i class="far fa-calendar"></i> <?php echo get_the_date(); ?></span>
								</div>
								<h3 class="fe-blog-card__title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<p class="fe-blog-card__excerpt">
									<?php echo wp_trim_words( get_the_excerpt(), 18, '...' ); ?>
								</p>
								<a href="<?php the_permalink(); ?>" class="fe-blog-card__more">
									Read Article <i class="fas fa-arrow-right"></i>
								</a>
							</div>
						</article>
					<?php
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>

			<div class="fe-blog-footer">
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="fe-btn fe-btn--outline">
					View All Journal Articles <i class="fas fa-arrow-right"></i>
				</a>
			</div>
		</div>
	</section>

</main>

<?php get_footer(); ?>
