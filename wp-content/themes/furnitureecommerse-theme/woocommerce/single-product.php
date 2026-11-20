<?php
/**
 * The Template for displaying all single products - WoodMart Furniture 2 Style
 *
 * @package FurnitureEcommerse
 */

defined( 'ABSPATH' ) || exit;

// Ensure WooCommerce variation scripts are loaded
if ( wp_script_is( 'wc-add-to-cart-variation', 'registered' ) ) {
	wp_enqueue_script( 'wc-add-to-cart-variation' );
}

get_header( 'shop' );
?>

<div class="fe-single-product-page">
	<div class="fe-container">
		
		<?php
		while ( have_posts() ) {
			the_post();
			global $product;
			
			$main_image_id = $product->get_image_id();
			$gallery_ids   = $product->get_gallery_image_ids();
			$rating        = $product->get_average_rating();
			$review_count  = $product->get_review_count();
			$cats          = wp_get_post_terms( get_the_ID(), 'product_cat' );
			$cat_name      = ! empty( $cats ) ? $cats[0]->name : 'Furniture';
			$on_sale       = $product->is_on_sale();
			?>
			
			<!-- Breadcrumb -->
			<div class="fe-single-product__breadcrumbs">
				<?php fe_breadcrumb(); ?>
			</div>

			<!-- Product Showcase Main Wrap -->
			<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'fe-product-detail-layout', $product ); ?>>
				
				<!-- 1. Product Gallery (Left) -->
				<div class="fe-product-detail__gallery">
					<div class="fe-product-gallery-stage">
						<?php if ( $on_sale ) : ?>
							<span class="fe-badge fe-badge--sale">Sale</span>
						<?php endif; ?>
						
						<?php if ( $main_image_id ) : ?>
							<?php echo wp_get_attachment_image( $main_image_id, 'large', false, array( 'id' => 'fe-main-product-img', 'class' => 'fe-main-gallery-img' ) ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( wc_placeholder_img_src( 'large' ) ); ?>" alt="Product placeholder" id="fe-main-product-img" class="fe-main-gallery-img">
						<?php endif; ?>
					</div>
					
					<?php if ( ! empty( $gallery_ids ) ) : ?>
						<div class="fe-product-gallery-thumbs">
							<?php if ( $main_image_id ) : ?>
								<div class="fe-thumb-wrap active" data-full="<?php echo esc_url( wp_get_attachment_image_url( $main_image_id, 'large' ) ); ?>" data-srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $main_image_id, 'large' ) ?: '' ); ?>">
									<?php echo wp_get_attachment_image( $main_image_id, 'thumbnail', false ); ?>
								</div>
							<?php endif; ?>
							
							<?php foreach ( $gallery_ids as $attachment_id ) : ?>
								<div class="fe-thumb-wrap" data-full="<?php echo esc_url( wp_get_attachment_image_url( $attachment_id, 'large' ) ); ?>" data-srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $attachment_id, 'large' ) ?: '' ); ?>">
									<?php echo wp_get_attachment_image( $attachment_id, 'thumbnail', false ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- 2. Product Summary & Purchase (Right) -->
				<div class="fe-product-detail__summary">
					
					<!-- Category kicker -->
					<div class="fe-product-kicker">
						<span class="fe-product-kicker__cat"><?php echo esc_html( $cat_name ); ?></span>
						<span class="fe-product-kicker__divider">•</span>
						<span class="fe-product-kicker__collection">WoodMart Living Collection</span>
					</div>

					<h1 class="product_title entry-title fe-product-title"><?php the_title(); ?></h1>
					
					<!-- Rating & Reviews Counter -->
					<div class="fe-product-rating-wrap">
						<div class="fe-stars">
							<?php
							$stars_count = round( $rating );
							for ( $i = 1; $i <= 5; $i++ ) {
								echo $i <= $stars_count ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
							}
							?>
						</div>
						<a href="#tab-reviews" class="fe-review-link">
							(<?php echo esc_html( $review_count ); ?> customer <?php echo _n( 'review', 'reviews', $review_count ); ?>)
						</a>
						<span class="fe-product-kicker__divider">|</span>
						<div class="fe-stock-pill in-stock">
							<i class="fas fa-check"></i> In Stock
						</div>
					</div>

					<!-- Price -->
					<div class="price fe-product-price">
						<?php echo $product->get_price_html(); ?>
					</div>

					<!-- Short Description -->
					<div class="woocommerce-product-details__short-description fe-product-short-desc">
						<?php the_excerpt(); ?>
					</div>

					<!-- Add to Cart Form -->
					<div class="fe-product-cart-form-wrap">
						<?php
						if ( $product->is_type( 'variable' ) ) {
							woocommerce_variable_add_to_cart();
						} else {
							woocommerce_simple_add_to_cart();
						}
						?>
					</div>

					<!-- Actions (Wishlist) -->
					<div class="fe-product-extra-actions">
						<button class="fe-wishlist-action-btn fe-wishlist-btn" data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
							<i class="far fa-heart"></i> Add to Wishlist
						</button>
						<button class="fe-share-btn" onclick="navigator.clipboard.writeText(window.location.href); alert('Product link copied to clipboard!');">
							<i class="fas fa-share-nodes"></i> Share
						</button>
					</div>

					<!-- WoodMart Trust Guarantees Box -->
					<div class="fe-product-trust-box">
						<div class="fe-trust-row">
							<div class="fe-trust-icon-wrap"><i class="fas fa-truck-fast"></i></div>
							<div class="fe-trust-row__text">
								<strong>Free Delivery</strong>
								<span>Complimentary doorstep delivery on orders over $130</span>
							</div>
						</div>
						<div class="fe-trust-row">
							<div class="fe-trust-icon-wrap"><i class="fas fa-rotate-left"></i></div>
							<div class="fe-trust-row__text">
								<strong>30-Day In-Home Trial</strong>
								<span>Try it in your space. Easy returns & exchanges</span>
							</div>
						</div>
						<div class="fe-trust-row">
							<div class="fe-trust-icon-wrap"><i class="fas fa-shield-halved"></i></div>
							<div class="fe-trust-row__text">
								<strong>3-Year Warranty</strong>
								<span>100% solid Scandinavian FSC-certified craftsmanship</span>
							</div>
						</div>
					</div>

					<!-- Meta (SKU, Categories, Tags) -->
					<div class="product_meta fe-product-meta">
						<?php do_action( 'woocommerce_product_meta_start' ); ?>
						<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
							<span class="sku_wrapper">
								<strong>SKU:</strong> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span>
							</span>
						<?php endif; ?>
						<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in"><strong>Categories:</strong> ', '</span>' ); ?>
						<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as"><strong>Tags:</strong> ', '</span>' ); ?>
						<?php do_action( 'woocommerce_product_meta_end' ); ?>
					</div>

				</div>
			</div>

			<!-- 3. Product Data Tabs (WoodMart Style) -->
			<div class="woocommerce-tabs wc-tabs-wrapper fe-woodmart-tabs">
				<?php
				/**
				 * Hook: woocommerce_after_single_product_summary.
				 *
				 * @hooked woocommerce_output_product_data_tabs - 10
				 * @hooked woocommerce_upsell_display - 15
				 * @hooked woocommerce_output_related_products - 20
				 */
				woocommerce_output_product_data_tabs();
				?>
			</div>

			<!-- 4. Related Products Showcase -->
			<section class="fe-related-products-section">
				<div class="fe-section-header">
					<span class="fe-section-header__subtitle">HANDPICKED FOR YOU</span>
					<h2 class="fe-section-header__title">Related Products</h2>
				</div>

				<div class="fe-product-grid">
					<?php
					$related_ids = wc_get_related_products( $product->get_id(), 4 );
					if ( ! empty( $related_ids ) ) {
						foreach ( $related_ids as $rel_id ) {
							echo fe_render_product_card( $rel_id );
						}
					} else {
						$fallback_query = new WP_Query( array(
							'post_type'      => 'product',
							'posts_per_page' => 4,
							'post__not_in'   => array( $product->get_id() ),
							'post_status'    => 'publish',
						) );
						if ( $fallback_query->have_posts() ) {
							while ( $fallback_query->have_posts() ) {
								$fallback_query->the_post();
								echo fe_render_product_card();
							}
							wp_reset_postdata();
						}
					}
					?>
				</div>
			</section>

			<?php
		}
		?>
	</div>
</div>

<?php
get_footer( 'shop' );
