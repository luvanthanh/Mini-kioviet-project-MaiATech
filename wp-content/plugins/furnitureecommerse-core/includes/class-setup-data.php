<?php
/**
 * Data Setup — Creates categories, attributes, products, blog posts, pages, and menus.
 *
 * Run via: WordPress Admin → Tools → FE Setup Data
 * Or via WP-CLI: wp eval-file wp-content/plugins/furnitureecommerse-core/includes/class-setup-data.php
 *
 * @package FE_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FE_Setup_Data {

	private $category_ids   = array();
	private $attribute_ids  = array();
	private $product_ids    = array();

	/**
	 * Run the full setup.
	 */
	public function run() {
		$this->create_product_attributes();
		$this->create_product_categories();
		$this->create_products();
		$this->create_blog_categories_and_posts();
		$this->create_pages();
		$this->create_menus();
		$this->configure_woocommerce();

		error_log( 'FE_Setup_Data: Setup completed successfully.' );
	}

	// ============================================
	// PRODUCT ATTRIBUTES
	// ============================================
	private function create_product_attributes() {
		$attributes = array(
			'color' => array(
				'name'    => 'Color',
				'slug'    => 'color',
				'terms'   => array( 'Black', 'White', 'Brown', 'Gray', 'Beige', 'Navy' ),
			),
			'size' => array(
				'name'    => 'Size',
				'slug'    => 'size',
				'terms'   => array( 'Small', 'Medium', 'Large' ),
			),
			'brand' => array(
				'name'    => 'Brand',
				'slug'    => 'brand',
				'terms'   => array( 'NordCraft', 'ScandiHome', 'MinimalLux', 'UrbanNest' ),
			),
		);

		foreach ( $attributes as $key => $attr ) {
			// Check if attribute already exists
			$attribute_id = wc_attribute_taxonomy_id_by_name( $attr['slug'] );

			if ( ! $attribute_id ) {
				$attribute_id = wc_create_attribute( array(
					'name'         => $attr['name'],
					'slug'         => $attr['slug'],
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => true,
				) );

				// Register taxonomy
				$taxonomy = 'pa_' . $attr['slug'];
				register_taxonomy( $taxonomy, 'product', array(
					'label'        => $attr['name'],
					'hierarchical' => false,
				) );
			}

			$this->attribute_ids[ $key ] = $attribute_id;

			// Create terms
			$taxonomy = 'pa_' . $attr['slug'];
			foreach ( $attr['terms'] as $term_name ) {
				if ( ! term_exists( $term_name, $taxonomy ) ) {
					wp_insert_term( $term_name, $taxonomy );
				}
			}
		}
	}

	// ============================================
	// PRODUCT CATEGORIES
	// ============================================
	private function create_product_categories() {
		$categories = array(
			array(
				'name'        => 'Chairs',
				'slug'        => 'chairs',
				'description' => 'Comfortable and stylish chairs for every room.',
			),
			array(
				'name'        => 'Tables',
				'slug'        => 'tables',
				'description' => 'Beautiful tables for dining, working, and living.',
			),
			array(
				'name'        => 'Sofas',
				'slug'        => 'sofas',
				'description' => 'Premium sofas for ultimate comfort and style.',
			),
			array(
				'name'        => 'Beds',
				'slug'        => 'beds',
				'description' => 'Luxurious beds for the perfect night\'s sleep.',
			),
			array(
				'name'        => 'Lighting',
				'slug'        => 'lighting',
				'description' => 'Modern lighting solutions for ambient living.',
			),
			array(
				'name'        => 'Decoration',
				'slug'        => 'decoration',
				'description' => 'Decorative accents to complete your space.',
			),
		);

		foreach ( $categories as $cat ) {
			$existing = term_exists( $cat['slug'], 'product_cat' );
			if ( $existing ) {
				$this->category_ids[ $cat['slug'] ] = $existing['term_id'];
			} else {
				$result = wp_insert_term( $cat['name'], 'product_cat', array(
					'slug'        => $cat['slug'],
					'description' => $cat['description'],
				) );
				if ( ! is_wp_error( $result ) ) {
					$this->category_ids[ $cat['slug'] ] = $result['term_id'];
				}
			}
		}
	}

	// ============================================
	// PRODUCTS
	// ============================================
	private function create_products() {
		// Simple Products (10)
		$simple_products = array(
			array(
				'name'              => 'Nordic Dining Chair',
				'sku'               => 'FE-NDC-001',
				'regular_price'     => '149',
				'short_description' => 'Clean-lined dining chair inspired by Scandinavian design with solid oak legs and ergonomic curved backrest.',
				'description'       => '<p>The Nordic Dining Chair combines minimalist aesthetics with everyday comfort. Crafted from solid oak with a gently curved backrest that supports your posture naturally.</p><h3>Features</h3><ul><li>Solid oak construction</li><li>Ergonomic curved backrest</li><li>Non-slip floor protectors</li><li>Easy assembly (15 minutes)</li><li>Weight capacity: 120kg</li></ul>',
				'category'          => 'chairs',
				'tags'              => array( 'dining', 'scandinavian', 'wood' ),
				'brand'             => 'NordCraft',
				'color'             => array( 'White', 'Brown' ),
				'stock'             => 45,
				'featured'          => true,
				'rating'            => 4.7,
			),
			array(
				'name'              => 'Round Coffee Table',
				'sku'               => 'FE-RCT-002',
				'regular_price'     => '199',
				'short_description' => 'Elegant round coffee table with marble top and brushed gold metal base.',
				'description'       => '<p>A statement piece for any living room. The marble top sits on a sleek brushed gold base, creating a perfect blend of luxury and minimalism.</p><h3>Features</h3><ul><li>Genuine marble top</li><li>Brushed gold metal base</li><li>Diameter: 80cm, Height: 45cm</li><li>Scratch-resistant surface</li></ul>',
				'category'          => 'tables',
				'tags'              => array( 'coffee-table', 'marble', 'gold' ),
				'brand'             => 'MinimalLux',
				'color'             => array( 'White' ),
				'stock'             => 30,
				'featured'          => true,
				'rating'            => 4.5,
			),
			array(
				'name'              => 'Floor Lamp Arc',
				'sku'               => 'FE-FLA-003',
				'regular_price'     => '89',
				'short_description' => 'Modern arc floor lamp with adjustable height and warm LED bulb included.',
				'description'       => '<p>Illuminate your reading corner or living space with this elegant arc floor lamp. Features adjustable height and comes with a warm-tone LED bulb.</p><h3>Features</h3><ul><li>Adjustable height: 150-180cm</li><li>Warm LED bulb included (2700K)</li><li>Heavy marble base for stability</li><li>Rotary dimmer switch</li></ul>',
				'category'          => 'lighting',
				'tags'              => array( 'floor-lamp', 'led', 'modern' ),
				'brand'             => 'ScandiHome',
				'color'             => array( 'Black' ),
				'stock'             => 60,
				'featured'          => false,
				'rating'            => 4.3,
			),
			array(
				'name'              => 'Ceramic Vase Set',
				'sku'               => 'FE-CVS-004',
				'regular_price'     => '45',
				'short_description' => 'Set of 3 minimalist ceramic vases in varying heights. Perfect for dried flowers or standalone display.',
				'description'       => '<p>These handcrafted ceramic vases bring organic beauty to any shelf or table. The set includes three vases of different heights with a matte finish.</p><h3>Features</h3><ul><li>Set of 3 vases</li><li>Heights: 15cm, 20cm, 25cm</li><li>Matte ceramic finish</li><li>Waterproof interior</li></ul>',
				'category'          => 'decoration',
				'tags'              => array( 'vase', 'ceramic', 'set' ),
				'brand'             => 'NordCraft',
				'color'             => array( 'White', 'Beige' ),
				'stock'             => 80,
				'featured'          => false,
				'rating'            => 4.8,
			),
			array(
				'name'              => 'Wooden Bookshelf',
				'sku'               => 'FE-WBS-005',
				'regular_price'     => '279',
				'short_description' => 'Five-tier open bookshelf in solid walnut with minimalist metal frame.',
				'description'       => '<p>Display your book collection and decor pieces on this beautiful five-tier bookshelf. Solid walnut shelves are mounted on a slim matte black metal frame.</p><h3>Features</h3><ul><li>5 walnut wood shelves</li><li>Matte black metal frame</li><li>Dimensions: 80W × 35D × 180H cm</li><li>Wall-anchoring hardware included</li><li>Weight capacity: 15kg per shelf</li></ul>',
				'category'          => 'decoration',
				'tags'              => array( 'bookshelf', 'storage', 'walnut' ),
				'brand'             => 'UrbanNest',
				'color'             => array( 'Brown', 'Black' ),
				'stock'             => 25,
				'featured'          => true,
				'rating'            => 4.6,
			),
			array(
				'name'              => 'Bedside Table Oak',
				'sku'               => 'FE-BTO-006',
				'regular_price'     => '129',
				'short_description' => 'Compact bedside table with one drawer and open shelf, crafted in solid oak.',
				'description'       => '<p>Keep your nighttime essentials within reach with this charming oak bedside table. Features a soft-close drawer and an open shelf below.</p><h3>Features</h3><ul><li>Solid oak construction</li><li>Soft-close drawer</li><li>Open shelf for books</li><li>Dimensions: 45W × 35D × 55H cm</li></ul>',
				'category'          => 'tables',
				'tags'              => array( 'bedside', 'nightstand', 'oak' ),
				'brand'             => 'ScandiHome',
				'color'             => array( 'Brown', 'White' ),
				'stock'             => 40,
				'featured'          => false,
				'rating'            => 4.4,
			),
			array(
				'name'              => 'Pendant Light Dome',
				'sku'               => 'FE-PLD-007',
				'regular_price'     => '159',
				'short_description' => 'Statement dome pendant light in brushed brass. Perfect above dining tables.',
				'description'       => '<p>This dome pendant light is a showstopper. The brushed brass exterior and white enamel interior create a warm, focused glow ideal for dining areas.</p><h3>Features</h3><ul><li>Brushed brass finish</li><li>White enamel interior</li><li>Adjustable cord length: up to 150cm</li><li>Compatible with E27 bulbs</li><li>Diameter: 40cm</li></ul>',
				'category'          => 'lighting',
				'tags'              => array( 'pendant', 'brass', 'dining' ),
				'brand'             => 'MinimalLux',
				'color'             => array( 'Black', 'White' ),
				'stock'             => 35,
				'featured'          => true,
				'rating'            => 4.9,
			),
			array(
				'name'              => 'Wall Mirror Round',
				'sku'               => 'FE-WMR-008',
				'regular_price'     => '95',
				'short_description' => 'Large round wall mirror with thin brass frame. Diameter: 60cm.',
				'description'       => '<p>Open up any room with this elegant round mirror. The thin brass frame adds a touch of warmth without overwhelming the minimalist design.</p><h3>Features</h3><ul><li>Diameter: 60cm</li><li>Thin brass metal frame</li><li>Shatter-resistant glass</li><li>Mounting hardware included</li></ul>',
				'category'          => 'decoration',
				'tags'              => array( 'mirror', 'wall-decor', 'brass' ),
				'brand'             => 'NordCraft',
				'color'             => array( 'Black' ),
				'stock'             => 50,
				'featured'          => false,
				'rating'            => 4.2,
			),
			array(
				'name'              => 'Linen Throw Pillow',
				'sku'               => 'FE-LTP-009',
				'regular_price'     => '35',
				'short_description' => 'Soft linen throw pillow with hidden zipper. 45×45cm. Insert included.',
				'description'       => '<p>Add texture and comfort to your sofa or bed with these premium linen throw pillows. Each pillow comes with a down-alternative insert.</p><h3>Features</h3><ul><li>100% European linen cover</li><li>Down-alternative insert included</li><li>Hidden zipper closure</li><li>Machine washable cover</li><li>Size: 45 × 45 cm</li></ul>',
				'category'          => 'decoration',
				'tags'              => array( 'pillow', 'linen', 'textile' ),
				'brand'             => 'ScandiHome',
				'color'             => array( 'Beige', 'Gray', 'White' ),
				'stock'             => 100,
				'featured'          => false,
				'rating'            => 4.5,
			),
			array(
				'name'              => 'Plant Stand Metal',
				'sku'               => 'FE-PSM-010',
				'regular_price'     => '69',
				'sale_price'        => '49',
				'short_description' => 'Modern metal plant stand with three tiers. Holds pots up to 25cm diameter.',
				'description'       => '<p>Elevate your indoor plants with this stylish three-tier metal stand. The powder-coated finish resists rust and scratches.</p><h3>Features</h3><ul><li>Three-tier design</li><li>Powder-coated steel</li><li>Max pot diameter: 25cm per tier</li><li>Height: 80cm</li><li>Rust-resistant</li></ul>',
				'category'          => 'decoration',
				'tags'              => array( 'plant-stand', 'metal', 'indoor' ),
				'brand'             => 'UrbanNest',
				'color'             => array( 'Black' ),
				'stock'             => 55,
				'featured'          => false,
				'rating'            => 4.1,
			),
		);

		// Sale Products (2)
		$sale_products = array(
			array(
				'name'              => 'Accent Chair Velvet',
				'sku'               => 'FE-ACV-019',
				'regular_price'     => '349',
				'sale_price'        => '249',
				'short_description' => 'Luxurious velvet accent chair with gold-tipped wooden legs. A perfect reading companion.',
				'description'       => '<p>Sink into luxury with this velvet accent chair. The deep seat and angled backrest provide exceptional comfort, while the gold-tipped legs add a touch of glamour.</p><h3>Features</h3><ul><li>Premium velvet upholstery</li><li>High-density foam cushion</li><li>Gold-tipped solid oak legs</li><li>Seat height: 45cm</li><li>Weight capacity: 130kg</li></ul>',
				'category'          => 'chairs',
				'tags'              => array( 'accent-chair', 'velvet', 'luxury' ),
				'brand'             => 'MinimalLux',
				'color'             => array( 'Gray', 'Navy', 'Beige' ),
				'stock'             => 20,
				'featured'          => true,
				'rating'            => 4.8,
			),
			array(
				'name'              => 'Table Lamp Brass',
				'sku'               => 'FE-TLB-020',
				'regular_price'     => '129',
				'sale_price'        => '89',
				'short_description' => 'Elegant brass table lamp with linen shade. Touch-sensitive dimmer.',
				'description'       => '<p>This brass table lamp combines vintage charm with modern functionality. The touch-sensitive base lets you adjust brightness with a simple tap.</p><h3>Features</h3><ul><li>Solid brass base</li><li>Natural linen drum shade</li><li>Touch-sensitive 3-level dimmer</li><li>Height: 45cm</li><li>Compatible with E14 bulbs</li></ul>',
				'category'          => 'lighting',
				'tags'              => array( 'table-lamp', 'brass', 'dimmer' ),
				'brand'             => 'NordCraft',
				'color'             => array( 'Black', 'White' ),
				'stock'             => 30,
				'featured'          => false,
				'rating'            => 4.6,
			),
		);

		// Variable Products (8)
		$variable_products = array(
			array(
				'name'              => 'Modern Lounge Chair',
				'sku'               => 'FE-MLC-011',
				'short_description' => 'Contemporary lounge chair with premium upholstery and solid wood frame. Available in multiple colors and sizes.',
				'description'       => '<p>The Modern Lounge Chair is where comfort meets contemporary design. The ergonomic shape cradles your body while the solid wood frame ensures lasting durability.</p><h3>Features</h3><ul><li>Premium fabric upholstery</li><li>Solid ash wood frame</li><li>High-resilience foam</li><li>360° swivel base</li><li>Easy spot-clean</li></ul>',
				'category'          => 'chairs',
				'tags'              => array( 'lounge', 'modern', 'swivel' ),
				'brand'             => 'ScandiHome',
				'featured'          => true,
				'rating'            => 4.8,
				'colors'            => array( 'Black', 'White', 'Brown' ),
				'sizes'             => array( 'Small', 'Medium', 'Large' ),
				'base_price'        => 299,
				'price_increment'   => 50,
			),
			array(
				'name'              => 'Sectional Sofa Cloud',
				'sku'               => 'FE-SSC-012',
				'short_description' => 'Ultra-comfortable modular sectional sofa with deep cushions and removable covers.',
				'description'       => '<p>The Cloud Sectional Sofa lives up to its name — sitting on it feels like sitting on a cloud. Deep cushions filled with down-alternative fiber provide unmatched comfort.</p><h3>Features</h3><ul><li>Down-alternative fiber filling</li><li>Removable, washable covers</li><li>Modular design (reconfigure anytime)</li><li>Stain-resistant fabric</li><li>Reinforced hardwood frame</li></ul>',
				'category'          => 'sofas',
				'tags'              => array( 'sectional', 'modular', 'comfortable' ),
				'brand'             => 'ScandiHome',
				'featured'          => true,
				'rating'            => 4.9,
				'colors'            => array( 'Gray', 'Beige', 'Navy' ),
				'sizes'             => array( 'Medium', 'Large' ),
				'base_price'        => 899,
				'price_increment'   => 200,
			),
			array(
				'name'              => 'Platform Bed Frame',
				'sku'               => 'FE-PBF-013',
				'short_description' => 'Minimalist platform bed frame with integrated headboard and under-bed storage.',
				'description'       => '<p>Sleep in style on this platform bed frame. The integrated headboard and hidden under-bed storage keep your bedroom clean and clutter-free.</p><h3>Features</h3><ul><li>Solid wood slat system (no box spring needed)</li><li>Integrated upholstered headboard</li><li>Under-bed storage drawers</li><li>Noise-free joints</li><li>Easy assembly</li></ul>',
				'category'          => 'beds',
				'tags'              => array( 'platform-bed', 'storage', 'minimalist' ),
				'brand'             => 'UrbanNest',
				'featured'          => false,
				'rating'            => 4.7,
				'colors'            => array( 'Black', 'White', 'Brown' ),
				'sizes'             => array( 'Small', 'Medium', 'Large' ),
				'base_price'        => 549,
				'price_increment'   => 125,
			),
			array(
				'name'              => 'Extendable Dining Table',
				'sku'               => 'FE-EDT-014',
				'short_description' => 'Solid wood dining table with hidden extension leaf. Seats 4-8 people.',
				'description'       => '<p>This clever dining table extends from 140cm to 200cm using a hidden butterfly leaf mechanism. Perfect for intimate dinners and large gatherings alike.</p><h3>Features</h3><ul><li>Solid oak tabletop</li><li>Hidden butterfly leaf extension</li><li>Seats 4-8 people</li><li>Closed: 140 × 90cm, Extended: 200 × 90cm</li><li>Tapered solid wood legs</li></ul>',
				'category'          => 'tables',
				'tags'              => array( 'dining-table', 'extendable', 'oak' ),
				'brand'             => 'NordCraft',
				'featured'          => false,
				'rating'            => 4.5,
				'colors'            => array( 'White', 'Brown' ),
				'sizes'             => array( 'Medium', 'Large' ),
				'base_price'        => 399,
				'price_increment'   => 100,
			),
			array(
				'name'              => 'Modular Sofa Unit',
				'sku'               => 'FE-MSU-015',
				'short_description' => 'Build your perfect sofa. Each modular unit connects seamlessly for endless configurations.',
				'description'       => '<p>The ultimate customizable seating solution. Each unit locks together with hidden connectors, allowing you to create the perfect sofa for your space.</p><h3>Features</h3><ul><li>Interlocking connector system</li><li>High-density foam + pocket springs</li><li>Removable, washable covers</li><li>Solid wood legs with felt pads</li><li>Mix and match configurations</li></ul>',
				'category'          => 'sofas',
				'tags'              => array( 'modular', 'customizable', 'sofa' ),
				'brand'             => 'UrbanNest',
				'featured'          => false,
				'rating'            => 4.4,
				'colors'            => array( 'Gray', 'Beige' ),
				'sizes'             => array( 'Small', 'Medium', 'Large' ),
				'base_price'        => 699,
				'price_increment'   => 150,
			),
			array(
				'name'              => 'Office Desk Modern',
				'sku'               => 'FE-ODM-016',
				'short_description' => 'Clean-lined office desk with cable management and spacious worktop.',
				'description'       => '<p>Work in style with this modern office desk. Built-in cable management keeps your workspace tidy, while the spacious top fits your monitor, keyboard, and more.</p><h3>Features</h3><ul><li>Engineered wood top with oak veneer</li><li>Powder-coated steel legs</li><li>Built-in cable management tray</li><li>Two soft-close drawers</li><li>Adjustable leveling feet</li></ul>',
				'category'          => 'tables',
				'tags'              => array( 'office', 'desk', 'workspace' ),
				'brand'             => 'MinimalLux',
				'featured'          => false,
				'rating'            => 4.3,
				'colors'            => array( 'Black', 'White' ),
				'sizes'             => array( 'Medium', 'Large' ),
				'base_price'        => 349,
				'price_increment'   => 75,
			),
			array(
				'name'              => 'Recliner Chair Leather',
				'sku'               => 'FE-RCL-017',
				'short_description' => 'Premium full-grain leather recliner with smooth reclining mechanism and lumbar support.',
				'description'       => '<p>Experience luxury relaxation with this full-grain leather recliner. The smooth push-back mechanism lets you find your perfect angle, while built-in lumbar support keeps you comfortable for hours.</p><h3>Features</h3><ul><li>Full-grain Italian leather</li><li>Push-back reclining (no lever needed)</li><li>Built-in adjustable lumbar support</li><li>Solid hardwood frame</li><li>Weight capacity: 150kg</li></ul>',
				'category'          => 'chairs',
				'tags'              => array( 'recliner', 'leather', 'premium' ),
				'brand'             => 'MinimalLux',
				'featured'          => true,
				'rating'            => 4.7,
				'colors'            => array( 'Black', 'Brown' ),
				'sizes'             => array( 'Medium', 'Large' ),
				'base_price'        => 499,
				'price_increment'   => 100,
			),
			array(
				'name'              => 'King Size Bed Upholstered',
				'sku'               => 'FE-KBU-018',
				'short_description' => 'Upholstered bed frame with channel-tufted headboard and solid slat base.',
				'description'       => '<p>Make a statement in your bedroom with this upholstered bed. The channel-tufted headboard adds a sophisticated touch, while the solid slat base provides excellent mattress support.</p><h3>Features</h3><ul><li>Channel-tufted headboard</li><li>Premium fabric upholstery</li><li>Solid wood slat system</li><li>No box spring required</li><li>Low-profile design</li></ul>',
				'category'          => 'beds',
				'tags'              => array( 'king-bed', 'upholstered', 'tufted' ),
				'brand'             => 'ScandiHome',
				'featured'          => false,
				'rating'            => 4.6,
				'colors'            => array( 'Gray', 'Beige', 'Navy' ),
				'sizes'             => array( 'Medium', 'Large' ),
				'base_price'        => 799,
				'price_increment'   => 200,
			),
		);

		// Create simple products
		foreach ( $simple_products as $data ) {
			$this->create_simple_product( $data );
		}

		// Create sale products (same as simple but with sale_price)
		foreach ( $sale_products as $data ) {
			$this->create_simple_product( $data );
		}

		// Create variable products
		foreach ( $variable_products as $data ) {
			$this->create_variable_product( $data );
		}
	}

	/**
	 * Create a simple product.
	 */
	private function create_simple_product( $data ) {
		// Check if product already exists by SKU
		$existing_id = wc_get_product_id_by_sku( $data['sku'] );
		if ( $existing_id ) {
			$this->product_ids[] = $existing_id;
			return $existing_id;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $data['name'] );
		$product->set_sku( $data['sku'] );
		$product->set_regular_price( $data['regular_price'] );
		if ( ! empty( $data['sale_price'] ) ) {
			$product->set_sale_price( $data['sale_price'] );
		}
		$product->set_short_description( $data['short_description'] );
		$product->set_description( $data['description'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_manage_stock( true );
		$product->set_stock_quantity( $data['stock'] );
		$product->set_stock_status( 'instock' );
		$product->set_featured( $data['featured'] ?? false );

		// Category
		if ( ! empty( $data['category'] ) && isset( $this->category_ids[ $data['category'] ] ) ) {
			$product->set_category_ids( array( $this->category_ids[ $data['category'] ] ) );
		}

		// Tags
		if ( ! empty( $data['tags'] ) ) {
			$tag_ids = array();
			foreach ( $data['tags'] as $tag ) {
				$term = term_exists( $tag, 'product_tag' );
				if ( ! $term ) {
					$term = wp_insert_term( $tag, 'product_tag' );
				}
				if ( ! is_wp_error( $term ) ) {
					$tag_ids[] = is_array( $term ) ? $term['term_id'] : $term;
				}
			}
			$product->set_tag_ids( $tag_ids );
		}

		// Attributes (non-variation)
		$attributes = array();
		if ( ! empty( $data['brand'] ) ) {
			$attr = new WC_Product_Attribute();
			$attr->set_id( wc_attribute_taxonomy_id_by_name( 'brand' ) );
			$attr->set_name( 'pa_brand' );
			$term = get_term_by( 'name', $data['brand'], 'pa_brand' );
			if ( $term ) {
				$attr->set_options( array( $term->term_id ) );
			}
			$attr->set_visible( true );
			$attr->set_variation( false );
			$attributes[] = $attr;
		}
		if ( ! empty( $data['color'] ) ) {
			$attr = new WC_Product_Attribute();
			$attr->set_id( wc_attribute_taxonomy_id_by_name( 'color' ) );
			$attr->set_name( 'pa_color' );
			$term_ids = array();
			foreach ( (array) $data['color'] as $color ) {
				$term = get_term_by( 'name', $color, 'pa_color' );
				if ( $term ) {
					$term_ids[] = $term->term_id;
				}
			}
			$attr->set_options( $term_ids );
			$attr->set_visible( true );
			$attr->set_variation( false );
			$attributes[] = $attr;
		}
		if ( ! empty( $attributes ) ) {
			$product->set_attributes( $attributes );
		}

		$product_id = $product->save();

		// Set rating manually via meta (for demo purposes)
		if ( ! empty( $data['rating'] ) ) {
			update_post_meta( $product_id, '_wc_average_rating', $data['rating'] );
			update_post_meta( $product_id, '_wc_rating_count', array( 5 => rand( 5, 30 ) ) );
			update_post_meta( $product_id, '_wc_review_count', rand( 5, 30 ) );
		}

		// Set total_sales for best sellers
		update_post_meta( $product_id, 'total_sales', rand( 10, 200 ) );

		$this->product_ids[] = $product_id;
		return $product_id;
	}

	/**
	 * Create a variable product with variations.
	 */
	private function create_variable_product( $data ) {
		// Check if product already exists by SKU
		$existing_id = wc_get_product_id_by_sku( $data['sku'] );
		if ( $existing_id ) {
			$this->product_ids[] = $existing_id;
			return $existing_id;
		}

		$product = new WC_Product_Variable();
		$product->set_name( $data['name'] );
		$product->set_sku( $data['sku'] );
		$product->set_short_description( $data['short_description'] );
		$product->set_description( $data['description'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_featured( $data['featured'] ?? false );

		// Category
		if ( ! empty( $data['category'] ) && isset( $this->category_ids[ $data['category'] ] ) ) {
			$product->set_category_ids( array( $this->category_ids[ $data['category'] ] ) );
		}

		// Tags
		if ( ! empty( $data['tags'] ) ) {
			$tag_ids = array();
			foreach ( $data['tags'] as $tag ) {
				$term = term_exists( $tag, 'product_tag' );
				if ( ! $term ) {
					$term = wp_insert_term( $tag, 'product_tag' );
				}
				if ( ! is_wp_error( $term ) ) {
					$tag_ids[] = is_array( $term ) ? $term['term_id'] : $term;
				}
			}
			$product->set_tag_ids( $tag_ids );
		}

		// Setup attributes for variations
		$attributes = array();

		// Brand (non-variation)
		if ( ! empty( $data['brand'] ) ) {
			$attr = new WC_Product_Attribute();
			$attr->set_id( wc_attribute_taxonomy_id_by_name( 'brand' ) );
			$attr->set_name( 'pa_brand' );
			$term = get_term_by( 'name', $data['brand'], 'pa_brand' );
			if ( $term ) {
				$attr->set_options( array( $term->term_id ) );
			}
			$attr->set_visible( true );
			$attr->set_variation( false );
			$attributes[] = $attr;
		}

		// Color (variation attribute)
		if ( ! empty( $data['colors'] ) ) {
			$attr = new WC_Product_Attribute();
			$attr->set_id( wc_attribute_taxonomy_id_by_name( 'color' ) );
			$attr->set_name( 'pa_color' );
			$term_ids = array();
			foreach ( $data['colors'] as $color ) {
				$term = get_term_by( 'name', $color, 'pa_color' );
				if ( $term ) {
					$term_ids[] = $term->term_id;
				}
			}
			$attr->set_options( $term_ids );
			$attr->set_visible( true );
			$attr->set_variation( true );
			$attributes[] = $attr;
		}

		// Size (variation attribute)
		if ( ! empty( $data['sizes'] ) ) {
			$attr = new WC_Product_Attribute();
			$attr->set_id( wc_attribute_taxonomy_id_by_name( 'size' ) );
			$attr->set_name( 'pa_size' );
			$term_ids = array();
			foreach ( $data['sizes'] as $size ) {
				$term = get_term_by( 'name', $size, 'pa_size' );
				if ( $term ) {
					$term_ids[] = $term->term_id;
				}
			}
			$attr->set_options( $term_ids );
			$attr->set_visible( true );
			$attr->set_variation( true );
			$attributes[] = $attr;
		}

		$product->set_attributes( $attributes );
		$product_id = $product->save();

		// Create variations
		$base_price = $data['base_price'];
		$increment  = $data['price_increment'];
		$var_num    = 0;

		foreach ( $data['colors'] as $color ) {
			foreach ( $data['sizes'] as $size ) {
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $product_id );

				$color_slug = sanitize_title( $color );
				$size_slug  = sanitize_title( $size );

				$variation->set_attributes( array(
					'pa_color' => $color_slug,
					'pa_size'  => $size_slug,
				) );

				// Price increases with size
				$size_index = array_search( $size, $data['sizes'] );
				$price      = $base_price + ( $size_index * $increment );

				$variation->set_regular_price( $price );
				$variation->set_sku( $data['sku'] . '-' . strtoupper( substr( $color_slug, 0, 2 ) ) . '-' . strtoupper( substr( $size_slug, 0, 1 ) ) );
				$variation->set_manage_stock( true );
				$variation->set_stock_quantity( rand( 5, 30 ) );
				$variation->set_stock_status( 'instock' );
				$variation->set_status( 'publish' );

				$variation->save();
				$var_num++;
			}
		}

		// Set rating manually
		if ( ! empty( $data['rating'] ) ) {
			update_post_meta( $product_id, '_wc_average_rating', $data['rating'] );
			update_post_meta( $product_id, '_wc_rating_count', array( 5 => rand( 10, 50 ) ) );
			update_post_meta( $product_id, '_wc_review_count', rand( 10, 50 ) );
		}

		// Set total_sales
		update_post_meta( $product_id, 'total_sales', rand( 20, 300 ) );

		// Sync variable product data
		WC_Product_Variable::sync( $product_id );

		$this->product_ids[] = $product_id;
		return $product_id;
	}

	// ============================================
	// BLOG
	// ============================================
	private function create_blog_categories_and_posts() {
		// Blog categories
		$blog_cats = array(
			'Interior Design Tips' => 'interior-design-tips',
			'Furniture Care'       => 'furniture-care',
			'Home Inspiration'     => 'home-inspiration',
		);
		$blog_cat_ids = array();

		foreach ( $blog_cats as $name => $slug ) {
			$existing = term_exists( $slug, 'category' );
			if ( $existing ) {
				$blog_cat_ids[ $slug ] = $existing['term_id'];
			} else {
				$result = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
				if ( ! is_wp_error( $result ) ) {
					$blog_cat_ids[ $slug ] = $result['term_id'];
				}
			}
		}

		// Blog posts
		$posts = array(
			array(
				'title'    => '10 Scandinavian Design Principles for Your Home',
				'category' => 'interior-design-tips',
				'content'  => '<p>Scandinavian design is more than just a trend — it\'s a timeless approach to creating beautiful, functional spaces. Here are 10 key principles to bring Nordic aesthetics into your home.</p>
				<h2>1. Embrace Minimalism</h2><p>Less is more in Scandinavian design. Focus on quality over quantity, choosing pieces that serve both function and beauty.</p>
				<h2>2. Prioritize Natural Light</h2><p>Large windows, light curtains, and strategically placed mirrors maximize the flow of natural light through your space.</p>
				<h2>3. Use a Neutral Color Palette</h2><p>Whites, grays, and warm beiges create a serene backdrop. Add interest with texture rather than bold colors.</p>
				<h2>4. Incorporate Natural Materials</h2><p>Wood, leather, wool, and linen bring warmth and texture to minimalist spaces.</p>
				<h2>5. Focus on Functionality</h2><p>Every piece in a Scandinavian home should serve a purpose. Beautiful storage solutions keep clutter at bay.</p>
				<h2>6. Add Organic Shapes</h2><p>Curves and organic forms soften the clean lines that define Nordic design.</p>
				<h2>7. Layer Textiles</h2><p>Throw blankets, cushions, and rugs add cozy warmth — essential for the hygge feeling.</p>
				<h2>8. Bring Nature Indoors</h2><p>Houseplants, dried flowers, and botanical prints connect your space to the natural world.</p>
				<h2>9. Choose Timeless Over Trendy</h2><p>Invest in classic pieces that will look beautiful for decades, not just one season.</p>
				<h2>10. Create Cozy Corners</h2><p>A reading nook with good lighting and a comfortable chair embodies the essence of Scandinavian living.</p>',
				'excerpt'  => 'Discover the core principles of Scandinavian design and learn how to create a home that\'s both beautiful and functional.',
			),
			array(
				'title'    => 'How to Choose the Perfect Sofa for Your Living Room',
				'category' => 'interior-design-tips',
				'content'  => '<p>A sofa is often the centerpiece of your living room — and one of the biggest furniture investments you\'ll make. Here\'s how to choose wisely.</p>
				<h2>Consider Your Space</h2><p>Measure your room carefully before shopping. A sofa that\'s too large will overwhelm the space, while one that\'s too small will look lost. Leave at least 45cm of walkway space around the sofa.</p>
				<h2>Think About Your Lifestyle</h2><p>Have kids or pets? Choose performance fabrics that resist stains. Love to nap? A deep-seated sofa with soft cushions is your best friend.</p>
				<h2>Test the Comfort</h2><p>Sit in the sofa for at least 10 minutes. The seat depth should allow your feet to touch the floor while your back rests against the cushions.</p>
				<h2>Check the Frame</h2><p>A quality sofa starts with a solid frame. Hardwood frames (oak, maple, or ash) last the longest. Avoid softwood or particle board frames.</p>
				<h2>Choose the Right Cushion Fill</h2><p>Foam cushions hold their shape well. Down fill is luxuriously soft but needs regular fluffing. A foam-and-down combination offers the best of both worlds.</p>
				<h2>Pick Your Style</h2><p>From mid-century modern to contemporary sectionals, choose a style that complements your existing decor and will age gracefully.</p>',
				'excerpt'  => 'Your complete guide to selecting the perfect sofa — from measuring your space to testing comfort and choosing the right materials.',
			),
			array(
				'title'    => 'Essential Furniture Care Tips for Every Season',
				'category' => 'furniture-care',
				'content'  => '<p>Your furniture is an investment worth protecting. These seasonal care tips will keep your pieces looking beautiful for years to come.</p>
				<h2>Spring: Deep Clean</h2><p>Vacuum upholstered furniture thoroughly, including under cushions. Use a wood cleaner on all wooden surfaces. Check for any scratches that need touching up.</p>
				<h2>Summer: Protect from Sun</h2><p>UV rays can fade fabrics and dry out wood. Use window treatments to filter harsh sunlight. Apply UV-protective wood polish to exposed surfaces.</p>
				<h2>Autumn: Moisture Check</h2><p>As humidity levels change, wood furniture can expand and contract. Use felt pads under hot and cold drinks. Apply wood conditioner to prevent cracking.</p>
				<h2>Winter: Combat Dry Air</h2><p>Heating systems dry out indoor air. Use a humidifier to maintain 40-60% humidity, which protects both wood and leather furniture.</p>
				<h2>Year-Round Tips</h2><ul><li>Rotate cushions monthly for even wear</li><li>Use coasters and placemats consistently</li><li>Lift furniture when moving — never drag</li><li>Keep furniture away from heat sources</li><li>Clean spills immediately</li></ul>',
				'excerpt'  => 'Keep your furniture looking its best year-round with these essential seasonal care and maintenance tips.',
			),
			array(
				'title'    => 'The Complete Guide to Cleaning Upholstery',
				'category' => 'furniture-care',
				'content'  => '<p>Upholstered furniture adds comfort and style to your home, but it also requires regular cleaning to maintain its beauty. Here\'s your complete guide.</p>
				<h2>Know Your Fabric Code</h2><p>Check the tag on your furniture for the cleaning code: W (water-based cleaner), S (solvent-based cleaner), WS (either), or X (vacuum only).</p>
				<h2>Weekly Maintenance</h2><p>Vacuum your upholstery weekly using the brush attachment. This removes dust, pet hair, and small debris before they become embedded in the fabric.</p>
				<h2>Spot Cleaning</h2><p>Blot (never rub!) spills immediately with a clean white cloth. Test any cleaning solution on a hidden area first. Work from the outside of the stain inward.</p>
				<h2>Deep Cleaning Fabric</h2><p>For W-coded fabrics, use a mix of mild dish soap and warm water. Apply with a soft brush in circular motions. Blot excess moisture and allow to air dry completely.</p>
				<h2>Caring for Leather</h2><p>Dust regularly with a soft cloth. Apply leather conditioner every 6-12 months. Avoid placing leather in direct sunlight or near heat sources.</p>
				<h2>Professional Cleaning</h2><p>Have your upholstered furniture professionally cleaned every 12-18 months for the best results.</p>',
				'excerpt'  => 'Everything you need to know about keeping your upholstered furniture clean, fresh, and looking like new.',
			),
			array(
				'title'    => 'Small Space Living: How to Maximize Your Apartment',
				'category' => 'home-inspiration',
				'content'  => '<p>Living in a small space doesn\'t mean sacrificing style or comfort. With smart furniture choices and clever design tricks, you can make any apartment feel spacious and inviting.</p>
				<h2>Choose Multi-Functional Furniture</h2><p>A storage ottoman, a sofa bed, or a dining table that doubles as a desk — multi-functional pieces are essential for small spaces. Look for pieces that serve at least two purposes.</p>
				<h2>Go Vertical</h2><p>When floor space is limited, think up! Wall-mounted shelves, tall bookcases, and hanging planters draw the eye upward and use every inch of available space.</p>
				<h2>Use Mirrors Strategically</h2><p>A large mirror opposite a window doubles the perceived light and space in a room. Mirrored furniture also creates an illusion of more space.</p>
				<h2>Stick to a Light Color Palette</h2><p>Light colors make rooms feel larger. Use whites and pastels for walls and large furniture. Add personality with colorful accessories that can be easily changed.</p>
				<h2>Embrace Minimalism</h2><p>The fewer items in a small space, the larger it feels. Be ruthless about decluttering and only keep items that you truly love or need.</p>
				<h2>Create Zones</h2><p>Use rugs, lighting, and furniture arrangement to create distinct zones for sleeping, working, and relaxing — even in a studio apartment.</p>',
				'excerpt'  => 'Transform your small apartment into a stylish, functional home with these expert tips on maximizing limited space.',
			),
			array(
				'title'    => '2024 Home Decor Trends to Watch',
				'category' => 'home-inspiration',
				'content'  => '<p>As we move through 2024, several exciting design trends are shaping how we decorate our homes. Here are the top trends that interior designers are embracing.</p>
				<h2>1. Warm Minimalism</h2><p>Cold, stark minimalism is giving way to warmer interpretations. Think clean lines paired with rich textures, warm wood tones, and earthy color palettes.</p>
				<h2>2. Curved Furniture</h2><p>Soft, rounded forms continue to dominate furniture design. From curved sofas to arched mirrors, organic shapes add visual interest and comfort.</p>
				<h2>3. Sustainable Materials</h2><p>Eco-conscious design is no longer optional. Recycled materials, sustainably sourced wood, and natural fabrics are becoming the standard for quality furniture.</p>
				<h2>4. Bold Stone</h2><p>Marble and terrazzo are making a comeback in everything from coffee tables to lamp bases. These natural stone elements add luxury and permanence to any room.</p>
				<h2>5. Japandi Style</h2><p>The fusion of Japanese and Scandinavian aesthetics continues to grow. Clean lines, natural materials, and intentional simplicity define this popular hybrid style.</p>
				<h2>6. Textured Walls</h2><p>From limewash paint to textured wallpaper, walls are becoming canvases for tactile expression. These treatments add depth and character to minimalist spaces.</p>
				<h2>7. Personal Collections</h2><p>Curated displays of personal objects, art, and travel souvenirs are replacing generic decor. Your home should tell your story.</p>',
				'excerpt'  => 'Stay ahead of the curve with the most influential home decor trends of 2024, from warm minimalism to sustainable design.',
			),
		);

		foreach ( $posts as $post_data ) {
			// Check if post already exists
			$existing = get_page_by_title( $post_data['title'], OBJECT, 'post' );
			if ( $existing ) {
				continue;
			}

			$cat_id = $blog_cat_ids[ $post_data['category'] ] ?? 0;

			$post_id = wp_insert_post( array(
				'post_title'   => $post_data['title'],
				'post_content' => $post_data['content'],
				'post_excerpt' => $post_data['excerpt'],
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_author'  => 1,
				'post_category' => array( $cat_id ),
			) );

			if ( ! is_wp_error( $post_id ) ) {
				// We'll set featured images later or use placeholders
				update_post_meta( $post_id, '_fe_blog_post', true );
			}
		}
	}

	// ============================================
	// PAGES
	// ============================================
	private function create_pages() {
		$pages = array(
			array(
				'title'   => 'Wishlist',
				'slug'    => 'wishlist',
				'content' => '[fe_wishlist]',
			),
			array(
				'title'   => 'About Us',
				'slug'    => 'about-us',
				'content' => '<h2>Our Story</h2>
				<p>FurnitureEcommerse was born from a simple belief: everyone deserves beautiful, well-crafted furniture that transforms a house into a home.</p>
				<p>Founded in Copenhagen in 2015, we partner with the finest Scandinavian designers and craftsmen to bring you furniture that combines timeless aesthetics with everyday functionality.</p>
				<h2>Our Values</h2>
				<ul>
				<li><strong>Quality Craftsmanship</strong> — Every piece is built to last generations</li>
				<li><strong>Sustainable Design</strong> — We source responsibly and design for longevity</li>
				<li><strong>Minimalist Beauty</strong> — Clean lines, natural materials, purposeful design</li>
				<li><strong>Customer First</strong> — Free shipping, easy returns, lifetime support</li>
				</ul>',
			),
			array(
				'title'   => 'Contact',
				'slug'    => 'contact',
				'content' => '<h2>Get in Touch</h2>
				<p>We\'d love to hear from you. Whether you have a question about a product, need design advice, or want to share feedback, our team is here to help.</p>
				<p><strong>Email:</strong> hello@furnitureecommerse.com</p>
				<p><strong>Phone:</strong> +1 (555) 123-4567</p>
				<p><strong>Hours:</strong> Mon-Fri 9am-6pm EST</p>
				<p><strong>Address:</strong> 123 Design Street, Copenhagen, Denmark</p>',
			),
		);

		foreach ( $pages as $page ) {
			if ( get_page_by_path( $page['slug'] ) ) {
				continue;
			}
			wp_insert_post( array(
				'post_title'   => $page['title'],
				'post_name'    => $page['slug'],
				'post_content' => $page['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => 1,
			) );
		}
	}

	// ============================================
	// MENUS
	// ============================================
	private function create_menus() {
		// Main menu
		$menu_name = 'Primary Menu';
		$menu_exists = wp_get_nav_menu_object( $menu_name );

		if ( ! $menu_exists ) {
			$menu_id = wp_create_nav_menu( $menu_name );

			// Home
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => 'Home',
				'menu-item-url'    => home_url( '/' ),
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			) );

			// Shop
			$shop_page_id = wc_get_page_id( 'shop' );
			$shop_item_id = wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => 'Shop',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $shop_page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );

			// Shop sub-items (categories)
			foreach ( $this->category_ids as $slug => $cat_id ) {
				$cat = get_term( $cat_id, 'product_cat' );
				if ( $cat && ! is_wp_error( $cat ) ) {
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'      => $cat->name,
						'menu-item-object'     => 'product_cat',
						'menu-item-object-id'  => $cat_id,
						'menu-item-type'       => 'taxonomy',
						'menu-item-status'     => 'publish',
						'menu-item-parent-id'  => $shop_item_id,
					) );
				}
			}

			// About
			$about_page = get_page_by_path( 'about-us' );
			if ( $about_page ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => 'About',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $about_page->ID,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}

			// Blog
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'  => 'Blog',
				'menu-item-url'    => home_url( '/blog/' ),
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			) );

			// Contact
			$contact_page = get_page_by_path( 'contact' );
			if ( $contact_page ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => 'Contact',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $contact_page->ID,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}

			// Assign menu to location
			$locations = get_theme_mod( 'nav_menu_locations' );
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	// ============================================
	// WOOCOMMERCE SETTINGS
	// ============================================
	private function configure_woocommerce() {
		// Currency
		update_option( 'woocommerce_currency', 'USD' );
		update_option( 'woocommerce_currency_pos', 'left' );
		update_option( 'woocommerce_price_decimal_sep', '.' );
		update_option( 'woocommerce_price_thousand_sep', ',' );
		update_option( 'woocommerce_price_num_decimals', '0' );

		// Store
		update_option( 'woocommerce_default_country', 'US:CA' );
		update_option( 'woocommerce_calc_taxes', 'no' );

		// Products
		update_option( 'woocommerce_shop_page_display', '' );
		update_option( 'woocommerce_category_archive_display', '' );
		update_option( 'woocommerce_default_catalog_orderby', 'date' );

		// Enable COD
		$cod_settings = get_option( 'woocommerce_cod_settings', array() );
		$cod_settings['enabled'] = 'yes';
		$cod_settings['title'] = 'Cash on Delivery';
		$cod_settings['description'] = 'Pay with cash upon delivery.';
		update_option( 'woocommerce_cod_settings', $cod_settings );

		// Enable Bank Transfer
		$bacs_settings = get_option( 'woocommerce_bacs_settings', array() );
		$bacs_settings['enabled'] = 'yes';
		$bacs_settings['title'] = 'Direct Bank Transfer';
		$bacs_settings['description'] = 'Make your payment directly into our bank account.';
		update_option( 'woocommerce_bacs_settings', $bacs_settings );

		// Enable guest checkout
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );

		// Set front page to static
		update_option( 'show_on_front', 'page' );

		// Create and set homepage
		$home = get_page_by_path( 'home' );
		if ( ! $home ) {
			$home_id = wp_insert_post( array(
				'post_title'  => 'Home',
				'post_name'   => 'home',
				'post_status' => 'publish',
				'post_type'   => 'page',
			) );
			update_option( 'page_on_front', $home_id );
		} else {
			update_option( 'page_on_front', $home->ID );
		}

		// Blog page
		$blog = get_page_by_path( 'blog' );
		if ( ! $blog ) {
			$blog_id = wp_insert_post( array(
				'post_title'  => 'Blog',
				'post_name'   => 'blog',
				'post_status' => 'publish',
				'post_type'   => 'page',
			) );
			update_option( 'page_for_posts', $blog_id );
		} else {
			update_option( 'page_for_posts', $blog->ID );
		}

		// Permalink structure
		update_option( 'permalink_structure', '/%postname%/' );

		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
