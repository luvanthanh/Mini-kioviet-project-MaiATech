<?php
/**
 * Plugin Name: FurnitureEcommerse Core
 * Plugin URI: http://localhost/furniture-store
 * Description: Core functionality for FurnitureEcommerse — AJAX search, AJAX filtering, wishlist, quick view.
 * Version: 1.0.0
 * Author: FurnitureEcommerse Team
 * Text Domain: fe-core
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 *
 * @package FE_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FE_CORE_VERSION', '1.0.0' );
define( 'FE_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FE_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
final class FE_Core {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	private function includes() {
		require_once FE_CORE_DIR . 'includes/class-ajax-search.php';
		require_once FE_CORE_DIR . 'includes/class-ajax-filter.php';
		require_once FE_CORE_DIR . 'includes/class-wishlist.php';
		require_once FE_CORE_DIR . 'includes/class-quick-view.php';
	}

	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Declare WooCommerce HPOS compatibility
		add_action( 'before_woocommerce_init', function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		} );
	}

	public function init() {
		// Initialize all modules
		FE_Ajax_Search::instance();
		FE_Ajax_Filter::instance();
		FE_Wishlist::instance();
		FE_Quick_View::instance();
	}

	public function admin_menu() {
		add_management_page(
			'FE Setup Data',
			'FE Setup Data',
			'manage_options',
			'fe-setup-data',
			array( $this, 'setup_data_page' )
		);
	}

	/**
	 * Admin page to trigger data setup.
	 */
	public function setup_data_page() {
		if ( isset( $_POST['fe_setup_data'] ) && check_admin_referer( 'fe_setup_data_nonce' ) ) {
			require_once FE_CORE_DIR . 'includes/class-setup-data.php';
			$setup = new FE_Setup_Data();
			$setup->run();
			echo '<div class="notice notice-success"><p>Data setup completed successfully!</p></div>';
		}
		?>
		<div class="wrap">
			<h1>FurnitureEcommerse — Setup Data</h1>
			<p>Click the button below to create categories, attributes, products, and blog posts.</p>
			<form method="post">
				<?php wp_nonce_field( 'fe_setup_data_nonce' ); ?>
				<input type="submit" name="fe_setup_data" class="button button-primary" value="Setup All Data">
			</form>
		</div>
		<?php
	}

	/**
	 * Plugin activation.
	 */
	public static function activate() {
		// Ensure file is loaded before calling
		require_once FE_CORE_DIR . 'includes/class-wishlist.php';
		// Create wishlist table
		FE_Wishlist::create_table();
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}

// Activation/deactivation hooks
register_activation_hook( __FILE__, array( 'FE_Core', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'FE_Core', 'deactivate' ) );

// Initialize
function fe_core() {
	return FE_Core::instance();
}
add_action( 'plugins_loaded', 'fe_core' );
