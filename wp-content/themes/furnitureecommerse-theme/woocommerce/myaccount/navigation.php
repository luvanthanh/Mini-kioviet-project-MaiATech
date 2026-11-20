<?php
/**
 * My Account Navigation Template Override
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$menu_items = wc_get_account_menu_items();

// Icons mapping for all tabs
$item_icons = array(
	'dashboard'       => 'fas fa-tachometer-alt',
	'orders'          => 'fas fa-shopping-bag',
	'downloads'       => 'fas fa-download',
	'edit-address'    => 'fas fa-map-marker-alt',
	'edit-account'    => 'fas fa-user-edit',
	'customer-logout' => 'fas fa-sign-out-alt',
);

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation fe-account-navigation" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<!-- Mobile / Android Accordion Toggle Button -->
	<button type="button" class="fe-account-nav-toggle" id="fe-account-nav-toggle" aria-expanded="false" aria-controls="fe-account-nav-menu">
		<span class="fe-account-nav-toggle__left">
			<i class="fas fa-bars"></i>
			<span class="fe-account-nav-toggle__label"><?php esc_html_e( 'Account Navigation Menu', 'furnitureecommerse' ); ?></span>
		</span>
		<i class="fas fa-chevron-down fe-account-nav-toggle__arrow"></i>
	</button>

	<!-- Account Navigation List -->
	<ul class="fe-account-nav-list" id="fe-account-nav-menu">
		<?php foreach ( $menu_items as $endpoint => $label ) : 
			$url = wc_get_account_endpoint_url( $endpoint );
			$is_active = wc_is_current_account_menu_item( $endpoint );
			$classes = wc_get_account_menu_item_classes( $endpoint );
			if ( $is_active ) {
				$classes .= ' is-active current-menu-item';
			}
			$icon = isset( $item_icons[ $endpoint ] ) ? $item_icons[ $endpoint ] : 'fas fa-circle';
		?>
			<li class="<?php echo esc_attr( $classes ); ?>">
				<a href="<?php echo esc_url( $url ); ?>" <?php echo $is_active ? 'aria-current="page"' : ''; ?>>
					<span class="fe-account-nav-link-left">
						<i class="<?php echo esc_attr( $icon ); ?> fe-account-nav-icon"></i>
						<span class="fe-account-nav-text"><?php echo esc_html( $label ); ?></span>
					</span>
					<i class="fas fa-chevron-right fe-account-nav-chevron"></i>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
