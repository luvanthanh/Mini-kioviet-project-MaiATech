<?php
/**
 * AJAX Live Search for Products and Posts.
 *
 * Provides real-time search results as user types,
 * returning both products and blog posts.
 *
 * @package FE_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FE_Ajax_Search {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_fe_live_search', array( $this, 'live_search' ) );
		add_action( 'wp_ajax_nopriv_fe_live_search', array( $this, 'live_search' ) );
	}

	/**
	 * Handle AJAX live search request.
	 */
	public function live_search() {
		check_ajax_referer( 'fe_nonce', 'nonce' );

		$query = sanitize_text_field( $_POST['query'] ?? '' );

		if ( strlen( $query ) < 2 ) {
			wp_send_json_success( array(
				'products' => array(),
				'posts'    => array(),
				'total'    => 0,
			) );
		}

		$products = $this->search_products( $query );
		$posts    = $this->search_posts( $query );

		wp_send_json_success( array(
			'products'      => $products,
			'posts'         => $posts,
			'total'         => count( $products ) + count( $posts ),
			'products_count' => count( $products ),
			'posts_count'   => count( $posts ),
			'query'         => $query,
		) );
	}

	/**
	 * Search WooCommerce products.
	 */
	private function search_products( $query, $limit = 6 ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			's'              => $query,
			'orderby'        => 'relevance',
			'order'          => 'DESC',
		);

		$wp_query = new WP_Query( $args );
		$results  = array();

		if ( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}

				$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
				if ( ! $thumb_url ) {
					$thumb_url = wc_placeholder_img_src( 'thumbnail' );
				}

				$results[] = array(
					'id'        => get_the_ID(),
					'title'     => get_the_title(),
					'url'       => get_permalink(),
					'image'     => $thumb_url,
					'price'     => $product->get_price_html(),
					'rating'    => $product->get_average_rating(),
					'on_sale'   => $product->is_on_sale(),
					'type'      => 'product',
				);
			}
			wp_reset_postdata();
		}

		return $results;
	}

	/**
	 * Search blog posts.
	 */
	private function search_posts( $query, $limit = 3 ) {
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			's'              => $query,
			'orderby'        => 'relevance',
			'order'          => 'DESC',
		);

		$wp_query = new WP_Query( $args );
		$results  = array();

		if ( $wp_query->have_posts() ) {
			while ( $wp_query->have_posts() ) {
				$wp_query->the_post();

				$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );

				$results[] = array(
					'id'       => get_the_ID(),
					'title'    => get_the_title(),
					'url'      => get_permalink(),
					'image'    => $thumb_url ?: '',
					'date'     => get_the_date( 'M d, Y' ),
					'excerpt'  => wp_trim_words( get_the_excerpt(), 15 ),
					'type'     => 'post',
				);
			}
			wp_reset_postdata();
		}

		return $results;
	}
}
