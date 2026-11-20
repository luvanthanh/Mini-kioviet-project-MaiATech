<?php
/**
 * FurnitureEcommerse Child Theme Functions
 *
 * Custom e-commerce furniture store built on Blocksy framework.
 * Implements: custom header/footer, WooCommerce overrides,
 * product cards, AJAX cart, mega menu, and homepage sections.
 *
 * @package FurnitureEcommerse
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ============================================
// CONSTANTS
// ============================================
define( 'FE_THEME_VERSION', '1.0.0' );
define( 'FE_THEME_DIR', get_stylesheet_directory() );
define( 'FE_THEME_URI', get_stylesheet_directory_uri() );

// ============================================
// 1. THEME SETUP
// ============================================
function fe_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 50,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
	) );

	// WooCommerce support
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 400,
		'gallery_thumbnail_image_width' => 100,
		'single_image_width' => 800,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Navigation menus
	register_nav_menus( array(
		'primary'   => __( 'Primary Menu', 'flavflavor' ),
		'mobile'    => __( 'Mobile Menu', 'flavflavor' ),
		'footer'    => __( 'Footer Menu', 'flavflavor' ),
	) );

	// Custom image sizes
	add_image_size( 'product-card', 400, 500, true );
	add_image_size( 'product-gallery', 800, 800, false );
	add_image_size( 'hero-banner', 1920, 800, true );
	add_image_size( 'category-thumb', 600, 400, true );
	add_image_size( 'blog-card', 600, 400, true );
}
add_action( 'after_setup_theme', 'fe_theme_setup' );

// ============================================
// 2. ENQUEUE STYLES & SCRIPTS
// ============================================
function fe_enqueue_assets() {
	$ver = FE_THEME_VERSION;

	// Google Fonts — Outfit + Inter + DM Sans
	wp_enqueue_style(
		'fe-google-fonts',
		'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap',
		array(),
		null
	);

	// Font Awesome 6
	wp_enqueue_style(
		'fe-fontawesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
		array(),
		'6.5.1'
	);

	// Main stylesheet
	wp_enqueue_style( 'fe-main', FE_THEME_URI . '/assets/css/main.css', array( 'fe-google-fonts' ), $ver );

	// Responsive stylesheet
	wp_enqueue_style( 'fe-responsive', FE_THEME_URI . '/assets/css/responsive.css', array( 'fe-main' ), $ver );

	// WooCommerce specific styles
	if ( class_exists( 'woocommerce' ) ) {
		wp_enqueue_style( 'fe-woocommerce', FE_THEME_URI . '/assets/css/woocommerce.css', array( 'fe-main' ), $ver );
	}

	// Main JavaScript
	wp_enqueue_script( 'fe-main', FE_THEME_URI . '/assets/js/main.js', array( 'jquery' ), $ver, true );

	// Localize script data for AJAX
	$localize_data = array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'fe_nonce' ),
		'siteUrl'   => home_url( '/' ),
		'currency'  => '$',
		'cartCount' => 0,
	);

	if ( function_exists( 'WC' ) && WC()->cart ) {
		$localize_data['shopUrl']   = get_permalink( wc_get_page_id( 'shop' ) );
		$localize_data['cartUrl']   = wc_get_cart_url();
		$localize_data['currency']  = get_woocommerce_currency_symbol();
		$localize_data['cartCount'] = WC()->cart->get_cart_contents_count();
	}

	wp_localize_script( 'fe-main', 'feData', $localize_data );
}
add_action( 'wp_enqueue_scripts', 'fe_enqueue_assets', 20 );

// ============================================
// 3. WIDGET AREAS
// ============================================
function fe_register_sidebars() {
	register_sidebar( array(
		'name'          => 'Shop Sidebar',
		'id'            => 'shop-sidebar',
		'description'   => 'Sidebar filters for shop pages',
		'before_widget' => '<div id="%1$s" class="fe-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="fe-widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Blog Sidebar',
		'id'            => 'blog-sidebar',
		'description'   => 'Sidebar for blog pages',
		'before_widget' => '<div id="%1$s" class="fe-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="fe-widget-title">',
		'after_title'   => '</h4>',
	) );

	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar( array(
			'name'          => "Footer Column $i",
			'id'            => "footer-col-$i",
			'before_widget' => '<div id="%1$s" class="fe-footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="fe-footer-title">',
			'after_title'   => '</h4>',
		) );
	}
}
add_action( 'widgets_init', 'fe_register_sidebars' );

// ============================================
// 4. WOOCOMMERCE CUSTOMIZATIONS
// ============================================

// Products per page
add_filter( 'loop_shop_per_page', function () {
	return 12;
} );

// Products per row
add_filter( 'loop_shop_columns', function () {
	return 4;
} );

// Remove default WooCommerce styles (we have our own)
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Hide products without a price
add_action( 'woocommerce_product_query', function( $q ) {
	$meta_query = $q->get( 'meta_query' );
	if ( empty( $meta_query ) ) {
		$meta_query = array();
	}
	$meta_query[] = array(
		'key'     => '_price',
		'value'   => '',
		'compare' => '!=',
	);
	$q->set( 'meta_query', $meta_query );
} );

// Remove default product card hooks — we build our own in content-product.php
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// Add Specifications, Shipping Information & Return Policy tabs to single product
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	$tabs['specifications'] = array(
		'title'    => __( 'Specifications', 'furnitureecommerse' ),
		'priority' => 25,
		'callback' => 'fe_specifications_tab_content',
	);
	$tabs['shipping_info'] = array(
		'title'    => __( 'Shipping Information', 'furnitureecommerse' ),
		'priority' => 30,
		'callback' => 'fe_shipping_tab_content',
	);
	$tabs['return_policy'] = array(
		'title'    => __( 'Return Policy', 'furnitureecommerse' ),
		'priority' => 35,
		'callback' => 'fe_return_tab_content',
	);
	return $tabs;
} );

function fe_specifications_tab_content() {
	global $product;
	$attributes = $product->get_attributes();
	if ( empty( $attributes ) ) {
		echo '<p>Solid European Oak, natural tactile fabrics, non-toxic lacquer finishing.</p>';
		return;
	}
	echo '<table class="fe-specs-table" style="width:100%; border-collapse:collapse; margin-top:10px;">';
	echo '<tbody>';
	foreach ( $attributes as $attribute ) {
		$name   = wc_attribute_label( $attribute->get_name() );
		$values = array();
		if ( $attribute->is_taxonomy() ) {
			$terms = wp_get_post_terms( $product->get_id(), $attribute->get_name() );
			foreach ( $terms as $term ) {
				$values[] = $term->name;
			}
		} else {
			$values = $attribute->get_options();
		}
		echo '<tr style="border-bottom:1px solid #eee;"><th style="text-align:left; padding:12px 16px; font-weight:600; width:30%; background:#fcfcfc;">' . esc_html( $name ) . '</th><td style="padding:12px 16px;">' . esc_html( implode( ', ', $values ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
}

function fe_shipping_tab_content() {
	?>
	<div class="fe-tab-text-content" style="line-height:1.8; color:var(--fe-color-text-light);">
		<h4 style="margin:0 0 10px; color:var(--fe-color-text);">Worldwide & Nationwide Express Delivery</h4>
		<p>We partner with certified freight handlers to ensure your furniture arrives safely. All orders over $130 qualify for complimentary doorstep delivery.</p>
		<ul style="padding-left:20px; margin:15px 0;">
			<li><strong>Standard Delivery:</strong> 3–5 business days across all provinces.</li>
			<li><strong>White-Glove In-Home Assembly:</strong> Available for dining tables, beds, and modular sofas.</li>
			<li><strong>Full Insurance:</strong> 100% full-value transit protection included.</li>
		</ul>
	</div>
	<?php
}

function fe_return_tab_content() {
	?>
	<div class="fe-tab-text-content" style="line-height:1.8; color:var(--fe-color-text-light);">
		<h4 style="margin:0 0 10px; color:var(--fe-color-text);">30-Day Risk-Free In-Home Trial</h4>
		<p>Experience our Scandinavian craftsmanship in your own space. If your furniture doesn't match your layout, return it within 30 days for a full refund or exchange.</p>
		<ul style="padding-left:20px; margin:15px 0;">
			<li><strong>Condition:</strong> Items must be in original condition with tags and packaging.</li>
			<li><strong>Doorstep Pickup:</strong> We arrange direct courier pickup from your home.</li>
			<li><strong>3-Year Warranty:</strong> Comprehensive warranty covering joinery, hinges, and structure.</li>
		</ul>
	</div>
	<?php
}

// ============================================
// 5. CART FRAGMENTS (AJAX Cart Counter)
// ============================================
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	ob_start();
	?>
	<span class="fe-cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
	<?php
	$fragments['.fe-cart-count'] = ob_get_clean();

	ob_start();
	?>
	<span class="fe-cart-total"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
	<?php
	$fragments['.fe-cart-total'] = ob_get_clean();

	return $fragments;
} );

// ============================================
// 6. AJAX MINI CART
// ============================================
add_action( 'wp_ajax_fe_get_mini_cart', 'fe_ajax_mini_cart' );
add_action( 'wp_ajax_nopriv_fe_get_mini_cart', 'fe_ajax_mini_cart' );
function fe_ajax_mini_cart() {
	echo fe_get_mini_cart_html();
	wp_die();
}

add_action( 'wp_ajax_fe_remove_mini_cart_item', 'fe_ajax_remove_mini_cart_item' );
add_action( 'wp_ajax_nopriv_fe_remove_mini_cart_item', 'fe_ajax_remove_mini_cart_item' );
function fe_ajax_remove_mini_cart_item() {
	check_ajax_referer( 'fe_nonce', 'nonce' );

	$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] ?? '' );
	if ( $cart_item_key && function_exists( 'WC' ) && WC()->cart ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );
		if ( $cart_item ) {
			WC()->cart->remove_cart_item( $cart_item_key );
			WC()->cart->calculate_totals();

			wp_send_json_success( array(
				'cartCount' => WC()->cart->get_cart_contents_count(),
				'miniCart'  => fe_get_mini_cart_html(),
				'message'   => __( 'Item removed from cart', 'furnitureecommerse' ),
			) );
		}
	}

	wp_send_json_error( array( 'message' => __( 'Could not remove item from cart.', 'furnitureecommerse' ) ) );
}

function fe_get_mini_cart_html() {
	ob_start();
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		?>
		<div class="fe-mini-cart-empty">
			<div class="fe-mini-cart-empty__icon">
				<i class="fas fa-shopping-bag"></i>
			</div>
			<h4>Your cart is empty</h4>
			<p>Looks like you haven't added anything to your cart yet.</p>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="fe-btn fe-btn--primary">
				Explore Products
			</a>
		</div>
		<?php
	} else {
		?>
		<div class="fe-mini-cart-wrap">
			<div class="fe-mini-cart-items">
				<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
					$product   = $cart_item['data'];
					$quantity  = $cart_item['quantity'];
					$permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
					?>
					<div class="fe-mini-cart-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
						<div class="fe-mini-cart-item__img">
							<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo $product->get_image( 'thumbnail' ); ?></a>
							<?php else : ?>
								<?php echo $product->get_image( 'thumbnail' ); ?>
							<?php endif; ?>
						</div>
						<div class="fe-mini-cart-item__info">
							<h4>
								<?php if ( $permalink ) : ?>
									<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $product->get_name() ); ?>
								<?php endif; ?>
							</h4>
							<div class="fe-mini-cart-item__price">
								<span class="fe-mini-cart-item__qty"><?php echo $quantity; ?> &times;</span>
								<span class="fe-mini-cart-item__amount"><?php echo WC()->cart->get_product_price( $product ); ?></span>
							</div>
						</div>
						<button type="button" class="fe-mini-cart-item__remove" data-cart-item="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Remove item', 'furnitureecommerse' ); ?>">
							<i class="fas fa-times"></i>
						</button>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="fe-mini-cart-footer">
				<div class="fe-mini-cart-total">
					<span>Subtotal:</span>
					<strong><?php echo WC()->cart->get_cart_subtotal(); ?></strong>
				</div>
				<div class="fe-mini-cart-actions">
					<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="fe-btn fe-btn--outline">View Cart</a>
					<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="fe-btn fe-btn--primary">Checkout</a>
				</div>
			</div>
		</div>
		<?php
	}
	return ob_get_clean();
}

// ============================================
// 7. TEMPLATE HELPER FUNCTIONS
// ============================================

/**
 * Get featured products query.
 */
function fe_get_featured_products( $limit = 8 ) {
	return new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			),
		),
	) );
}

/**
 * Get new arrivals query.
 */
function fe_get_new_arrivals( $limit = 8 ) {
	return new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
}

/**
 * Get best sellers query.
 */
function fe_get_best_sellers( $limit = 8 ) {
	return new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'meta_key'       => 'total_sales',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );
}

/**
 * Get on-sale products query.
 */
function fe_get_sale_products( $limit = 8 ) {
	$on_sale_ids = wc_get_product_ids_on_sale();
	if ( empty( $on_sale_ids ) ) {
		$on_sale_ids = array( 0 );
	}
	return new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'post__in'       => $on_sale_ids,
	) );
}

/**
 * Get product categories.
 */
function fe_get_product_categories( $limit = 6 ) {
	return get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'number'     => $limit,
		'exclude'    => array( get_option( 'default_product_cat' ) ),
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
}

/**
 * Render star rating HTML.
 */
function fe_render_stars( $rating, $count = 0 ) {
	$rating = floatval( $rating );
	$html   = '<div class="fe-stars" aria-label="Rated ' . $rating . ' out of 5">';
	for ( $i = 1; $i <= 5; $i++ ) {
		if ( $i <= floor( $rating ) ) {
			$html .= '<i class="fas fa-star"></i>';
		} elseif ( $i - 0.5 <= $rating ) {
			$html .= '<i class="fas fa-star-half-alt"></i>';
		} else {
			$html .= '<i class="far fa-star"></i>';
		}
	}
	$html .= '<span class="fe-rating-value">' . number_format( $rating, 1 ) . '</span>';
	if ( $count > 0 ) {
		$html .= '<span class="fe-rating-count">(' . $count . ')</span>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * Render a product card.
 */
function fe_render_product_card( $product_id = null ) {
	if ( ! $product_id ) {
		$product_id = get_the_ID();
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return '';
	}

	$permalink  = get_permalink( $product_id );
	$thumb_id   = get_post_thumbnail_id( $product_id );
	$image      = $thumb_id
		? wp_get_attachment_image( $thumb_id, 'product-card', false, array( 'class' => 'fe-product-card__main-img' ) )
		: '<img src="' . esc_url( wc_placeholder_img_src( 'product-card' ) ) . '" alt="' . esc_attr( $product->get_name() ) . '" class="fe-product-card__main-img">';
	$name       = $product->get_name();
	$price_html = $product->get_price_html();
	$rating     = $product->get_average_rating();
	$count      = $product->get_rating_count();
	$on_sale    = $product->is_on_sale();

	// Hover image from gallery
	$gallery_ids  = $product->get_gallery_image_ids();
	$hover_image  = '';
	if ( ! empty( $gallery_ids ) ) {
		$hover_image = wp_get_attachment_image(
			$gallery_ids[0],
			'product-card',
			false,
			array( 'class' => 'fe-product-card__hover-img' )
		);
	}

	// Category
	$cats      = wp_get_post_terms( $product_id, 'product_cat' );
	$cat_name  = ! empty( $cats ) ? $cats[0]->name : '';
	$cat_slugs = ! empty( $cats ) ? wp_list_pluck( $cats, 'slug' ) : array();

	// Sale percentage
	$sale_pct = '';
	if ( $on_sale && $product->is_type( 'simple' ) ) {
		$regular = floatval( $product->get_regular_price() );
		$sale    = floatval( $product->get_sale_price() );
		if ( $regular > 0 ) {
			$sale_pct = round( ( ( $regular - $sale ) / $regular ) * 100 );
		}
	}

	ob_start();
	?>
	<div class="fe-product-card" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-category="<?php echo esc_attr( implode( ' ', $cat_slugs ) ); ?>">
		<div class="fe-product-card__image">
			<a href="<?php echo esc_url( $permalink ); ?>" class="fe-product-card__img-link">
				<?php echo $image; ?>
				<?php echo $hover_image; ?>
			</a>

			<?php if ( $on_sale ) : ?>
				<span class="fe-badge fe-badge--sale">
					<?php echo $sale_pct ? "-{$sale_pct}%" : 'Sale'; ?>
				</span>
			<?php endif; ?>

			<?php if ( $product->is_featured() ) : ?>
				<span class="fe-badge fe-badge--new">New</span>
			<?php endif; ?>

			<div class="fe-product-card__actions">
				<button class="fe-action-btn fe-wishlist-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>" title="Add to Wishlist" aria-label="Add to Wishlist">
					<i class="far fa-heart"></i>
				</button>
				<button class="fe-action-btn fe-quickview-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>" title="Quick View" aria-label="Quick View">
					<i class="far fa-eye"></i>
				</button>
				<?php if ( $product->is_type( 'simple' ) ) : ?>
					<button class="fe-action-btn fe-addtocart-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>" title="Add to Cart" aria-label="Add to Cart">
						<i class="fas fa-shopping-bag"></i>
					</button>
				<?php else : ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="fe-action-btn" title="Select Options" aria-label="Select Options">
						<i class="fas fa-sliders-h"></i>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="fe-product-card__info">
			<?php if ( $cat_name ) : ?>
				<span class="fe-product-card__category"><?php echo esc_html( $cat_name ); ?></span>
			<?php endif; ?>

			<h3 class="fe-product-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
			</h3>

			<?php if ( $rating > 0 ) : ?>
				<?php echo fe_render_stars( $rating, $count ); ?>
			<?php endif; ?>

			<div class="fe-product-card__price">
				<?php echo $price_html; ?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

// ============================================
// 8. MEGA MENU WALKER
// ============================================
class FE_Mega_Menu_Walker extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$classes = ( $depth === 0 ) ? 'fe-mega-submenu' : 'fe-submenu-child';
		$output .= '<ul class="' . $classes . '">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );

		if ( $depth === 0 ) {
			$classes[] = 'fe-mega-item';
			if ( $has_children ) {
				$classes[] = 'fe-has-dropdown';
			}
		}

		$class_string = implode( ' ', array_filter( $classes ) );
		$output      .= '<li class="' . esc_attr( $class_string ) . '">';

		$link_classes = 'fe-menu-link';
		if ( $depth === 0 ) {
			$link_classes .= ' fe-menu-link--top';
		}

		$output .= '<a href="' . esc_url( $item->url ) . '" class="' . esc_attr( $link_classes ) . '">';
		$output .= esc_html( $item->title );
		if ( $has_children ) {
			$output .= ' <i class="fas fa-chevron-down fe-menu-arrow"></i>';
		}
		$output .= '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

// ============================================
// 9. BREADCRUMB
// ============================================
function fe_breadcrumb() {
	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb( array(
			'wrap_before' => '<nav class="fe-breadcrumb" aria-label="Breadcrumb"><div class="fe-container">',
			'wrap_after'  => '</div></nav>',
			'before'      => '<span class="fe-breadcrumb__item">',
			'after'       => '</span>',
			'delimiter'   => '<span class="fe-breadcrumb__sep"><i class="fas fa-chevron-right"></i></span>',
		) );
	}
}

// ============================================
// 10. AJAX ADD TO CART (for simple products)
// ============================================
add_action( 'wp_ajax_fe_add_to_cart', 'fe_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_fe_add_to_cart', 'fe_ajax_add_to_cart' );
function fe_ajax_add_to_cart() {
	check_ajax_referer( 'fe_nonce', 'nonce' );

	$product_id = absint( $_POST['product_id'] ?? 0 );
	$quantity   = absint( $_POST['quantity'] ?? 1 );

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product.' ) );
	}

	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_type( 'simple' ) ) {
		wp_send_json_error( array( 'message' => 'This product cannot be added directly.' ) );
	}

	$result = WC()->cart->add_to_cart( $product_id, $quantity );

	if ( $result ) {
		wp_send_json_success( array(
			'message'   => $product->get_name() . ' added to cart!',
			'cartCount' => WC()->cart->get_cart_contents_count(),
			'cartTotal' => WC()->cart->get_cart_subtotal(),
			'miniCart'  => fe_get_mini_cart_html(),
		) );
	} else {
		wp_send_json_error( array( 'message' => 'Could not add to cart.' ) );
	}
}

// ============================================
// 11. CUSTOM WOOCOMMERCE SINGLE PRODUCT HOOKS
// ============================================

// Add shipping info below add-to-cart on single product
add_action( 'fe_woocommerce_single_product_summary', 'fe_shipping_info', 35 );
function fe_shipping_info() {
	?>
	<div class="fe-product-shipping-info">
		<div class="fe-shipping-item">
			<i class="fas fa-truck"></i>
			<div>
				<strong>Free Shipping</strong>
				<span>On orders over $99</span>
			</div>
		</div>
		<div class="fe-shipping-item">
			<i class="fas fa-undo-alt"></i>
			<div>
				<strong>Easy Returns</strong>
				<span>30-day return policy</span>
			</div>
		</div>
		<div class="fe-shipping-item">
			<i class="fas fa-shield-alt"></i>
			<div>
				<strong>2 Year Warranty</strong>
				<span>Full coverage</span>
			</div>
		</div>
	</div>
	<?php
}

// ============================================
// 12. CUSTOM BODY CLASSES
// ============================================
add_filter( 'body_class', function ( $classes ) {
	$classes[] = 'fe-theme';

	if ( is_front_page() ) {
		$classes[] = 'fe-home';
	}
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$classes[] = 'fe-shop';
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes[] = 'fe-single-product';
	}

	return $classes;
} );

// ============================================
// 13. DISABLE BLOCKSY DEFAULT HEADER/FOOTER
// ============================================
// Our child theme provides its own header.php and footer.php
// which completely override Blocksy's header/footer rendering.

// Optionally disable some Blocksy scripts we don't need
add_action( 'wp_enqueue_scripts', function () {
	// Keep Blocksy core styles but we override most visuals
}, 999 );

// ============================================
// 14. ALLOW SVG UPLOADS
// ============================================
add_filter( 'upload_mimes', function ( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
} );

// ============================================
// 15. CUSTOM EXCERPT LENGTH
// ============================================
add_filter( 'excerpt_length', function () {
	return 25;
} );

add_filter( 'excerpt_more', function () {
	return '...';
} );

// ============================================
// 16. WOODMART CHECKOUT STEPS & CART TRUST
// ============================================
/**
 * Render WoodMart 3-Step Checkout Progress Bar
 */
function fe_render_checkout_steps_bar() {
	$is_cart = is_cart();
	$is_checkout = is_checkout() && ! is_wc_endpoint_url( 'order-received' );
	$is_order_received = is_wc_endpoint_url( 'order-received' );

	$step1_class = $is_cart ? 'step-active' : ( ( $is_checkout || $is_order_received ) ? 'step-completed' : 'step-inactive' );
	$step2_class = $is_checkout ? 'step-active' : ( $is_order_received ? 'step-completed' : 'step-inactive' );
	$step3_class = $is_order_received ? 'step-active' : 'step-inactive';
	?>
	<div class="fe-checkout-steps-wrap">
		<ul class="fe-checkout-steps">
			<li class="fe-step-item <?php echo esc_attr( $step1_class ); ?>">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>">
					<span class="fe-step-num">1</span>
					<span class="fe-step-title">Shopping Cart</span>
				</a>
			</li>
			<li class="fe-step-arrow"><i class="fas fa-chevron-right"></i></li>
			<li class="fe-step-item <?php echo esc_attr( $step2_class ); ?>">
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
					<span class="fe-step-num">2</span>
					<span class="fe-step-title">Checkout</span>
				</a>
			</li>
			<li class="fe-step-arrow"><i class="fas fa-chevron-right"></i></li>
			<li class="fe-step-item <?php echo esc_attr( $step3_class ); ?>">
				<span class="fe-step-num">3</span>
				<span class="fe-step-title">Order Complete</span>
			</li>
		</ul>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart', 'fe_render_checkout_steps_bar', 5 );
add_action( 'woocommerce_before_checkout_form', 'fe_render_checkout_steps_bar', 5 );
add_action( 'woocommerce_before_thankyou', 'fe_render_checkout_steps_bar', 5 );

/**
 * Render WoodMart Cart Trust Box
 */
function fe_render_cart_trust_box() {
	?>
	<div class="fe-cart-trust-box">
		<div class="fe-cart-trust-item">
			<i class="fas fa-lock"></i>
			<div>
				<strong>Secure Shopping</strong>
				<span>256-Bit SSL Encrypted Checkout</span>
			</div>
		</div>
		<div class="fe-cart-trust-item">
			<i class="fas fa-headset"></i>
			<div>
				<strong>Have Questions?</strong>
				<span>Support: (686) 492-1044</span>
			</div>
		</div>
		<div class="fe-cart-trust-item">
			<i class="fas fa-shield-halved"></i>
			<div>
				<strong>Privacy Protection</strong>
				<span>Your personal data is always protected</span>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_after_cart_totals', 'fe_render_cart_trust_box', 20 );

/**
 * Add BUY NOW button to Single Product Add-To-Cart Form
 */
function fe_add_buy_now_button() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$product_id = $product->get_id();
	echo '<button type="button" class="button alt fe-buy-now-btn" id="fe-buy-now-btn" data-product-id="' . esc_attr( $product_id ) . '"><i class="fas fa-bolt"></i> ' . esc_html__( 'BUY NOW', 'furnitureecommerse' ) . '</button>';
	echo '<input type="hidden" name="fe_buy_now" id="fe_buy_now" value="0">';
}
add_action( 'woocommerce_after_add_to_cart_button', 'fe_add_buy_now_button', 20 );

/**
 * Redirect immediately to Checkout when BUY NOW is clicked
 */
function fe_buy_now_redirect( $url ) {
	if ( ! empty( $_REQUEST['fe_buy_now'] ) && '1' === (string) $_REQUEST['fe_buy_now'] ) {
		return wc_get_checkout_url();
	}
	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'fe_buy_now_redirect', 99, 1 );

/**
 * Ensure all 6 My Account tabs are explicitly available and ordered properly
 */
function fe_custom_account_menu_items( $items ) {
	return array(
		'dashboard'       => __( 'Dashboard', 'woocommerce' ),
		'orders'          => __( 'Orders', 'woocommerce' ),
		'downloads'       => __( 'Downloads', 'woocommerce' ),
		'edit-address'    => __( 'Addresses', 'woocommerce' ),
		'edit-account'    => __( 'Account Details', 'woocommerce' ),
		'customer-logout' => __( 'Logout', 'woocommerce' ),
	);
}
add_filter( 'woocommerce_account_menu_items', 'fe_custom_account_menu_items', 99, 1 );

/**
 * Prevent proxy/edge and browser cache from serving stale HTML to visitors (especially on free hosts like InfinityFree)
 */
function fe_disable_stale_cache_headers() {
	if ( ! is_admin() ) {
		header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}
}
add_action( 'send_headers', 'fe_disable_stale_cache_headers' );


