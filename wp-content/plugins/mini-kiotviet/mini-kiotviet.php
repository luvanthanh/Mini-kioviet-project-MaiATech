<?php
/**
 * Plugin Name: Mini KiotViet
 * Description: Mini KiotViet — bài Test 3: dashboard, sản phẩm, kho, khách hàng, đơn hàng, báo cáo, phân quyền, cài đặt và thông báo.
 * Version: 0.2.0
 * Requires PHP: 8.0
 * Text Domain: mini-kiotviet
 */
if (!defined('ABSPATH')) exit;

define('MKV_FILE', __FILE__);
define('MKV_PATH', plugin_dir_path(__FILE__));
define('MKV_URL', plugin_dir_url(__FILE__));
define('MKV_VERSION', '0.2.0');

require_once MKV_PATH . 'includes/class-database.php';
require_once MKV_PATH . 'includes/class-permissions.php';
require_once MKV_PATH . 'includes/class-activator.php';
require_once MKV_PATH . 'includes/class-plugin.php';

MKV_Plugin::boot();
register_activation_hook(MKV_FILE, ['MKV_Activator', 'activate']);
