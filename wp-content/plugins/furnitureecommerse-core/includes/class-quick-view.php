<?php
/**
 * Quick View Modal.
 *
 * Returns product data via AJAX for a modal popup,
 * including gallery, variations, and add-to-cart form.
 *
 * @package FE_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FE_Quick_View {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_fe_quick_view', array( $this, 'get_quick_view' ) );
		add_action( 'wp_ajax_nopriv_fe_quick_view', array( $this, 'get_quick_view' ) );

		// Add modal container to footer
		add_action( 'wp_footer', array( $this, 'render_modal_container' ) );
	}

	/**
	 * Handle AJAX quick view request.
	 */
	public function get_quick_view() {
		check_ajax_referer( 'fe_nonce', 'nonce' );

		$product_id = absint( $_POST['product_id'] ?? 0 );
		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => 'Invalid product.' ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => 'Product not found.' ) );
		}

		$html = $this->build_quick_view_html( $product );

		wp_send_json_success( array(
			'html'       => $html,
			'product_id' => $product_id,
		) );
	}

	/**
	 * Build the quick view HTML.
	 */
	private function build_quick_view_html( $product ) {
		$product_id = $product->get_id();
		$gallery    = $this->get_gallery_data( $product );
		$rating     = $product->get_average_rating();
		$count      = $product->get_rating_count();
		$cats       = wp_get_post_terms( $product_id, 'product_cat' );
		$cat_name   = ! empty( $cats ) ? $cats[0]->name : '';

		ob_start();
		?>
		<div class="fe-qv-content">
			<!-- Gallery -->
			<div class="fe-qv-gallery">
				<div class="fe-qv-gallery__main">
					<img src="<?php echo esc_url( $gallery[0]['full'] ?? '' ); ?>"
					     alt="<?php echo esc_attr( $product->get_name() ); ?>"
					     id="fe-qv-main-image">
				</div>
				<?php if ( count( $gallery ) > 1 ) : ?>
					<div class="fe-qv-gallery__thumbs">
						<?php foreach ( $gallery as $i => $img ) : ?>
							<div class="fe-qv-thumb <?php echo $i === 0 ? 'active' : ''; ?>"
							     data-full="<?php echo esc_url( $img['full'] ); ?>">
								<img src="<?php echo esc_url( $img['thumb'] ); ?>"
								     alt="Thumbnail <?php echo $i + 1; ?>">
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Info -->
			<div class="fe-qv-info">
				<?php if ( $cat_name ) : ?>
					<span class="fe-qv-category"><?php echo esc_html( $cat_name ); ?></span>
				<?php endif; ?>

				<h2 class="fe-qv-title"><?php echo esc_html( $product->get_name() ); ?></h2>

				<?php if ( $rating > 0 ) : ?>
					<?php echo fe_render_stars( $rating, $count ); ?>
				<?php endif; ?>

				<div class="fe-qv-price">
					<?php echo $product->get_price_html(); ?>
				</div>

				<div class="fe-qv-description">
					<?php echo wpautop( $product->get_short_description() ); ?>
				</div>

				<?php if ( $product->is_type( 'variable' ) ) : ?>
					<?php $this->render_variation_form( $product ); ?>
				<?php else : ?>
					<!-- Simple product add-to-cart -->
					<div class="fe-qv-addtocart">
						<div class="fe-quantity-control">
							<button class="fe-qty-btn fe-qty-minus" type="button">−</button>
							<input type="number" class="fe-qty-input" value="1" min="1"
							       max="<?php echo esc_attr( $product->get_stock_quantity() ?: 99 ); ?>">
							<button class="fe-qty-btn fe-qty-plus" type="button">+</button>
						</div>
						<button class="fe-btn fe-btn--primary fe-qv-add-btn"
						        data-product-id="<?php echo esc_attr( $product_id ); ?>">
							<i class="fas fa-shopping-bag"></i> Add to Cart
						</button>
					</div>
				<?php endif; ?>

				<div class="fe-qv-meta">
					<?php if ( $product->get_sku() ) : ?>
						<span><strong>SKU:</strong> <?php echo esc_html( $product->get_sku() ); ?></span>
					<?php endif; ?>
					<span>
						<strong>Availability:</strong>
						<?php echo $product->is_in_stock()
							? '<span class="fe-in-stock">In Stock</span>'
							: '<span class="fe-out-stock">Out of Stock</span>'; ?>
					</span>
				</div>

				<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="fe-qv-view-full">
					View Full Details <i class="fas fa-arrow-right"></i>
				</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render variation selection form.
	 */
	private function render_variation_form( $product ) {
		$attributes         = $product->get_variation_attributes();
		$available           = $product->get_available_variations();
		$variations_json     = wp_json_encode( $available );
		$default_attributes  = $product->get_default_attributes();
		?>
		<div class="fe-qv-variations" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
		     data-variations='<?php echo esc_attr( $variations_json ); ?>'>

			<?php foreach ( $attributes as $attr_name => $options ) :
				$label = wc_attribute_label( $attr_name );
				$attr_slug = sanitize_title( $attr_name );
				$default_val = $default_attributes[ $attr_slug ] ?? '';
				?>
				<div class="fe-qv-variation-group">
					<label class="fe-qv-variation-label">
						<?php echo esc_html( $label ); ?>:
						<span class="fe-qv-selected-value"><?php echo esc_html( $default_val ); ?></span>
					</label>
					<div class="fe-qv-variation-options" data-attribute="<?php echo esc_attr( $attr_name ); ?>">
						<?php
						// Determine if this is a color attribute for swatch rendering
						$is_color = ( stripos( $attr_name, 'color' ) !== false || stripos( $attr_name, 'colour' ) !== false );

						foreach ( $options as $option ) :
							$slug    = sanitize_title( $option );
							$active  = ( $slug === $default_val ) ? ' active' : '';

							if ( $is_color ) :
								// Color swatch
								$color_map = array(
									'black' => '#2d2d2d', 'white' => '#f5f5f5', 'brown' => '#8B4513',
									'gray'  => '#9e9e9e', 'grey'  => '#9e9e9e', 'beige' => '#d4c5a9',
									'navy'  => '#1a1a2e',
								);
								$bg = $color_map[ strtolower( $option ) ] ?? '#ccc';
								?>
								<button class="fe-swatch fe-swatch--color<?php echo $active; ?>"
								        data-value="<?php echo esc_attr( $slug ); ?>"
								        style="background-color: <?php echo esc_attr( $bg ); ?>"
								        title="<?php echo esc_attr( $option ); ?>">
								</button>
							<?php else : ?>
								<button class="fe-swatch fe-swatch--text<?php echo $active; ?>"
								        data-value="<?php echo esc_attr( $slug ); ?>">
									<?php echo esc_html( $option ); ?>
								</button>
							<?php endif;
						endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="fe-qv-variation-price">
				<?php echo $product->get_price_html(); ?>
			</div>

			<div class="fe-qv-addtocart">
				<div class="fe-quantity-control">
					<button class="fe-qty-btn fe-qty-minus" type="button">−</button>
					<input type="number" class="fe-qty-input" value="1" min="1" max="99">
					<button class="fe-qty-btn fe-qty-plus" type="button">+</button>
				</div>
				<button class="fe-btn fe-btn--primary fe-qv-add-variation-btn"
				        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
				        disabled>
					<i class="fas fa-shopping-bag"></i> Select Options
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Get gallery images data.
	 */
	private function get_gallery_data( $product ) {
		$images = array();

		// Main image
		$thumb_id = $product->get_image_id();
		if ( $thumb_id ) {
			$images[] = array(
				'full'  => wp_get_attachment_image_url( $thumb_id, 'large' ),
				'thumb' => wp_get_attachment_image_url( $thumb_id, 'thumbnail' ),
			);
		}

		// Gallery images
		$gallery_ids = $product->get_gallery_image_ids();
		foreach ( $gallery_ids as $gid ) {
			$images[] = array(
				'full'  => wp_get_attachment_image_url( $gid, 'large' ),
				'thumb' => wp_get_attachment_image_url( $gid, 'thumbnail' ),
			);
		}

		// Fallback
		if ( empty( $images ) ) {
			$images[] = array(
				'full'  => wc_placeholder_img_src( 'large' ),
				'thumb' => wc_placeholder_img_src( 'thumbnail' ),
			);
		}

		return $images;
	}

	/**
	 * Render the modal container in footer.
	 */
	public function render_modal_container() {
		?>
		<!-- Quick View Modal -->
		<div id="fe-quickview-modal" class="fe-modal" aria-hidden="true" role="dialog">
			<div class="fe-modal__overlay"></div>
			<div class="fe-modal__container">
				<button class="fe-modal__close" aria-label="Close">
					<i class="fas fa-times"></i>
				</button>
				<div class="fe-modal__body">
					<div class="fe-modal__loading">
						<i class="fas fa-spinner fa-spin"></i>
					</div>
				</div>
			</div>
		</div>

		<!-- Search Modal -->
		<div id="fe-search-modal" class="fe-modal fe-modal--search" aria-hidden="true" role="dialog">
			<div class="fe-modal__overlay"></div>
			<div class="fe-modal__container fe-search-container">
				<button class="fe-modal__close" aria-label="Close">
					<i class="fas fa-times"></i>
				</button>
				<div class="fe-search-inner">
					<form class="fe-search-form" role="search">
						<i class="fas fa-search fe-search-icon"></i>
						<input type="text" id="fe-search-input" placeholder="Search products, articles..."
						       autocomplete="off" autofocus>
						<span class="fe-search-clear" style="display:none;">&times;</span>
					</form>
					<div id="fe-search-results" class="fe-search-results"></div>
				</div>
			</div>
		</div>

		<!-- Cart Drawer -->
		<div id="fe-cart-drawer" class="fe-cart-drawer" aria-hidden="true">
			<div class="fe-cart-drawer__overlay"></div>
			<div class="fe-cart-drawer__panel">
				<div class="fe-cart-drawer__header">
					<h3>Shopping Cart</h3>
					<button class="fe-cart-drawer__close" aria-label="Close cart">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="fe-cart-drawer__body">
					<?php if ( function_exists( 'fe_get_mini_cart_html' ) ) {
						echo fe_get_mini_cart_html();
					} ?>
				</div>
			</div>
		</div>

		<!-- Mobile Menu Overlay -->
		<div id="fe-mobile-menu-overlay" class="fe-mobile-overlay" aria-hidden="true">
			<div class="fe-mobile-overlay__backdrop"></div>
			<div class="fe-mobile-overlay__panel">
				<div class="fe-mobile-overlay__header">
					<span class="fe-mobile-logo">FurnitureEcommerse</span>
					<button class="fe-mobile-overlay__close" aria-label="Close menu">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<nav class="fe-mobile-nav">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'fe-mobile-menu-list',
							'depth'          => 3,
						) );
					}
					?>
				</nav>
				<div class="fe-mobile-nav-footer">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<i class="far fa-user"></i> My Account
					</a>
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
						<i class="fas fa-store"></i> Shop
					</a>
				</div>
			</div>
		</div>

		<!-- Toast Notification -->
		<div id="fe-toast" class="fe-toast" aria-live="polite"></div>
		<?php
	}
}
