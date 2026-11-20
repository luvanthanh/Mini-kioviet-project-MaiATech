<?php
/**
 * AJAX Product Filtering for Shop Page.
 *
 * Provides real-time filtering without page reload.
 * Supports: category, brand, color, size, price range,
 * availability (in stock, on sale), and sorting.
 *
 * @package FE_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FE_Ajax_Filter {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_fe_filter_products', array( $this, 'filter_products' ) );
		add_action( 'wp_ajax_nopriv_fe_filter_products', array( $this, 'filter_products' ) );
	}

	/**
	 * Handle AJAX filter request.
	 */
	public function filter_products() {
		check_ajax_referer( 'fe_nonce', 'nonce' );

		$filters = $this->sanitize_filters( $_POST );
		$args    = $this->build_query_args( $filters );
		$query   = new WP_Query( $args );

		$html = '';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= fe_render_product_card( get_the_ID() );
			}
			wp_reset_postdata();
		} else {
			$html = '<div class="fe-no-products">
				<i class="fas fa-search"></i>
				<h3>No products found</h3>
				<p>Try adjusting your filters or search criteria.</p>
			</div>';
		}

		wp_send_json_success( array(
			'html'  => $html,
			'count' => $query->found_posts,
			'pages' => $query->max_num_pages,
		) );
	}

	/**
	 * Sanitize filter inputs.
	 */
	private function sanitize_filters( $data ) {
		return array(
			'category'     => isset( $data['category'] ) ? array_map( 'sanitize_text_field', (array) $data['category'] ) : array(),
			'brand'        => isset( $data['brand'] ) ? array_map( 'sanitize_text_field', (array) $data['brand'] ) : array(),
			'color'        => isset( $data['color'] ) ? array_map( 'sanitize_text_field', (array) $data['color'] ) : array(),
			'size'         => isset( $data['size'] ) ? array_map( 'sanitize_text_field', (array) $data['size'] ) : array(),
			'min_price'    => isset( $data['min_price'] ) ? floatval( $data['min_price'] ) : 0,
			'max_price'    => isset( $data['max_price'] ) ? floatval( $data['max_price'] ) : 0,
			'in_stock'     => ! empty( $data['in_stock'] ),
			'on_sale'      => ! empty( $data['on_sale'] ),
			'orderby'      => sanitize_text_field( $data['orderby'] ?? 'date' ),
			'order'        => sanitize_text_field( $data['order'] ?? 'DESC' ),
			'paged'        => absint( $data['paged'] ?? 1 ),
			'search'       => sanitize_text_field( $data['search'] ?? '' ),
		);
	}

	/**
	 * Build WP_Query arguments from filters.
	 */
	private function build_query_args( $filters ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'paged'          => $filters['paged'],
		);

		// Search
		if ( ! empty( $filters['search'] ) ) {
			$args['s'] = $filters['search'];
		}

		// Sorting
		switch ( $filters['orderby'] ) {
			case 'price-asc':
				$args['meta_key'] = '_price';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'ASC';
				break;
			case 'price-desc':
				$args['meta_key'] = '_price';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'popularity':
				$args['meta_key'] = 'total_sales';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'rating':
				$args['meta_key'] = '_wc_average_rating';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'name':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			case 'date':
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
				break;
		}

		// Taxonomy queries
		$tax_query = array( 'relation' => 'AND' );

		// Category filter
		if ( ! empty( $filters['category'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $filters['category'],
			);
		}

		// Brand filter (custom taxonomy or attribute)
		if ( ! empty( $filters['brand'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_brand',
				'field'    => 'slug',
				'terms'    => $filters['brand'],
			);
		}

		// Color filter
		if ( ! empty( $filters['color'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_color',
				'field'    => 'slug',
				'terms'    => $filters['color'],
			);
		}

		// Size filter
		if ( ! empty( $filters['size'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_size',
				'field'    => 'slug',
				'terms'    => $filters['size'],
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}

		// Meta queries
		$meta_query = array( 'relation' => 'AND' );

		// Price range
		if ( $filters['min_price'] > 0 || $filters['max_price'] > 0 ) {
			$price_query = array(
				'key'     => '_price',
				'type'    => 'NUMERIC',
			);
			if ( $filters['min_price'] > 0 && $filters['max_price'] > 0 ) {
				$price_query['value']   = array( $filters['min_price'], $filters['max_price'] );
				$price_query['compare'] = 'BETWEEN';
			} elseif ( $filters['min_price'] > 0 ) {
				$price_query['value']   = $filters['min_price'];
				$price_query['compare'] = '>=';
			} else {
				$price_query['value']   = $filters['max_price'];
				$price_query['compare'] = '<=';
			}
			$meta_query[] = $price_query;
		}

		// In stock only
		if ( $filters['in_stock'] ) {
			$meta_query[] = array(
				'key'   => '_stock_status',
				'value' => 'instock',
			);
		}

		// On sale only
		if ( $filters['on_sale'] ) {
			$sale_ids = wc_get_product_ids_on_sale();
			if ( ! empty( $sale_ids ) ) {
				$args['post__in'] = $sale_ids;
			} else {
				// No sale products, return nothing
				$args['post__in'] = array( 0 );
			}
		}

		if ( count( $meta_query ) > 1 ) {
			$args['meta_query'] = $meta_query;
		}

		return $args;
	}

	/**
	 * Get filter options for the sidebar.
	 * Returns available filter values with counts.
	 */
	public static function get_filter_data() {
		$data = array();

		// Categories
		$data['categories'] = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'exclude'    => array( get_option( 'default_product_cat' ) ),
		) );

		// Brands
		$data['brands'] = get_terms( array(
			'taxonomy'   => 'pa_brand',
			'hide_empty' => false,
		) );

		// Colors
		$data['colors'] = get_terms( array(
			'taxonomy'   => 'pa_color',
			'hide_empty' => false,
		) );

		// Sizes
		$data['sizes'] = get_terms( array(
			'taxonomy'   => 'pa_size',
			'hide_empty' => false,
		) );

		// Price range
		global $wpdb;
		$price_range = $wpdb->get_row( "
			SELECT MIN( CAST(meta_value AS DECIMAL(10,2)) ) as min_price,
			       MAX( CAST(meta_value AS DECIMAL(10,2)) ) as max_price
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_price'
			AND meta_value != ''
			AND post_id IN (
				SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'product'
				AND post_status = 'publish'
			)
		" );

		$data['min_price'] = $price_range ? floor( $price_range->min_price ) : 0;
		$data['max_price'] = $price_range ? ceil( $price_range->max_price ) : 1500;

		return $data;
	}
}
