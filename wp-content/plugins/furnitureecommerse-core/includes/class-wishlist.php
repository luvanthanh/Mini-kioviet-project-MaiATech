<?php
/**
 * Wishlist System.
 *
 * Stores wishlist data per user (in a custom DB table for logged-in users)
 * and in cookies/localStorage for guests.
 *
 * @package FE_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FE_Wishlist {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_fe_toggle_wishlist', array( $this, 'toggle_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fe_toggle_wishlist', array( $this, 'toggle_wishlist' ) );
		add_action( 'wp_ajax_fe_get_wishlist', array( $this, 'get_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fe_get_wishlist', array( $this, 'get_wishlist' ) );
		add_action( 'wp_ajax_fe_get_wishlist_ids', array( $this, 'get_wishlist_ids' ) );
		add_action( 'wp_ajax_nopriv_fe_get_wishlist_ids', array( $this, 'get_wishlist_ids' ) );

		// Add wishlist page shortcode
		add_shortcode( 'fe_wishlist', array( $this, 'wishlist_shortcode' ) );
	}

	/**
	 * Create the wishlist database table.
	 */
	public static function create_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'fe_wishlist';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			product_id bigint(20) NOT NULL,
			date_added datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY user_product (user_id, product_id),
			KEY user_id (user_id),
			KEY product_id (product_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Toggle product in wishlist (add/remove).
	 */
	public function toggle_wishlist() {
		check_ajax_referer( 'fe_nonce', 'nonce' );

		$product_id = absint( $_POST['product_id'] ?? 0 );
		if ( ! $product_id || ! wc_get_product( $product_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid product.' ) );
		}

		$user_id = get_current_user_id();

		if ( $user_id ) {
			// Logged-in user: use database
			$result = $this->toggle_db_wishlist( $user_id, $product_id );
		} else {
			// Guest: return action for JS to handle via localStorage
			$result = array(
				'action'  => 'toggle',
				'product' => $product_id,
				'guest'   => true,
			);
		}

		wp_send_json_success( $result );
	}

	/**
	 * Toggle wishlist in database.
	 */
	private function toggle_db_wishlist( $user_id, $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'fe_wishlist';

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE user_id = %d AND product_id = %d",
			$user_id,
			$product_id
		) );

		if ( $exists ) {
			$wpdb->delete( $table, array(
				'user_id'    => $user_id,
				'product_id' => $product_id,
			) );
			return array(
				'action'  => 'removed',
				'message' => 'Removed from wishlist',
				'count'   => $this->get_user_wishlist_count( $user_id ),
			);
		} else {
			$wpdb->insert( $table, array(
				'user_id'    => $user_id,
				'product_id' => $product_id,
			) );
			return array(
				'action'  => 'added',
				'message' => 'Added to wishlist',
				'count'   => $this->get_user_wishlist_count( $user_id ),
			);
		}
	}

	/**
	 * Get wishlist product IDs.
	 */
	public function get_wishlist_ids() {
		check_ajax_referer( 'fe_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_success( array( 'ids' => array(), 'guest' => true ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'fe_wishlist';
		$ids   = $wpdb->get_col( $wpdb->prepare(
			"SELECT product_id FROM $table WHERE user_id = %d ORDER BY date_added DESC",
			$user_id
		) );

		wp_send_json_success( array(
			'ids'   => array_map( 'intval', $ids ),
			'count' => count( $ids ),
		) );
	}

	/**
	 * Get full wishlist with product data.
	 */
	public function get_wishlist() {
		check_ajax_referer( 'fe_nonce', 'nonce' );

		$user_id = get_current_user_id();
		$products = array();

		if ( $user_id ) {
			global $wpdb;
			$table = $wpdb->prefix . 'fe_wishlist';
			// Sync guest items if passed
			if ( ! empty( $_POST['ids'] ) && is_array( $_POST['ids'] ) ) {
				foreach ( $_POST['ids'] as $gid ) {
					$gid = absint( $gid );
					if ( $gid && wc_get_product( $gid ) ) {
						$wpdb->replace( $table, array(
							'user_id'    => $user_id,
							'product_id' => $gid,
						) );
					}
				}
			}
			$ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT product_id FROM $table WHERE user_id = %d ORDER BY date_added DESC",
				$user_id
			) );
		} else {
			// Guest: IDs come from POST (sent from localStorage)
			$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
		}

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			$products[] = array(
				'id'          => $id,
				'name'        => $product->get_name(),
				'url'         => get_permalink( $id ),
				'image'       => get_the_post_thumbnail_url( $id, 'thumbnail' ) ?: wc_placeholder_img_src(),
				'price'       => $product->get_price_html(),
				'in_stock'    => $product->is_in_stock(),
				'stock_text'  => $product->is_in_stock() ? __( 'In Stock', 'furnitureecommerse' ) : __( 'Out of Stock', 'furnitureecommerse' ),
				'is_variable' => $product->is_type( 'variable' ),
			);
		}

		wp_send_json_success( array(
			'products' => $products,
			'count'    => count( $products ),
		) );
	}

	/**
	 * Get wishlist count for a user.
	 */
	private function get_user_wishlist_count( $user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'fe_wishlist';
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE user_id = %d",
			$user_id
		) );
	}

	/**
	 * Wishlist page shortcode.
	 */
	public function wishlist_shortcode() {
		ob_start();
		?>
		<div id="fe-wishlist-page" class="fe-wishlist-page">
			<div class="fe-wishlist-loading">
				<i class="fas fa-spinner fa-spin"></i>
				<p>Loading your wishlist...</p>
			</div>
			<div class="fe-wishlist-content" style="display:none;"></div>
			<div class="fe-wishlist-empty" style="display:none;">
				<i class="far fa-heart"></i>
				<h3>Your wishlist is empty</h3>
				<p>Browse our products and add your favorites!</p>
				<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="fe-btn fe-btn--primary">
					Browse Products
				</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
