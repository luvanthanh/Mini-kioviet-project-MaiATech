<?php
if (!defined('ABSPATH')) exit;


class MKV_Plugin {
    public static function enqueue_assets() {
        $page = sanitize_key($_GET['page'] ?? '');

        if ($page !== 'mkv-customers') {
            return;
        }

        $css_path = MKV_PATH . 'assets/Customer.css';

        if (is_readable($css_path)) {
            wp_enqueue_style(
                'mkv-customers-css',
                MKV_URL . 'assets/Customer.css',
                [],
                filemtime($css_path)
            );
        }
    }
    public static function boot() {
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        require_once MKV_PATH . 'modules/dashboard/class-dashboard-admin.php';
        require_once MKV_PATH . 'modules/dashboard/class-dashboard-api.php';

        // Products
        require_once MKV_PATH . 'modules/products/class-product-repository.php';
        require_once MKV_PATH . 'modules/products/class-product-admin.php';

        // Chức năng cũ vẫn được giữ nguyên
        require_once MKV_PATH . 'includes/products.php';
        require_once MKV_PATH . 'includes/operations.php';

        // Đăng ký các module
        MKV_Permissions::register();
        MKV_Product_Admin::register();
        MKV_Dashboard_Admin::register();
        MKV_Dashboard_Api::register();
    }

    public static function render($template, array $vars = []) {
        $path = MKV_PATH . 'templates/' . $template . '.php';
        if (!is_readable($path)) return;
        extract($vars, EXTR_SKIP);
        include $path;
    }

    public static function post_text($key) {
        return sanitize_text_field(wp_unslash($_POST[$key] ?? ''));
    }

    public static function is_action($scope) {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
            && isset($_POST['mkv_action'])
            && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_mkv_nonce'] ?? '')), 'mkv_' . $scope);
    }

    public static function form_open($scope, $action) {
        echo '<form method="post">';
        wp_nonce_field('mkv_' . $scope, '_mkv_nonce');
        echo '<input type="hidden" name="mkv_action" value="' . esc_attr($action) . '">';
    }

    public static function notice($message, $type = 'success') {
        if (!$message) return;
        echo '<div class="notice notice-' . esc_attr($type) . '"><p>' . esc_html($message) . '</p></div>';
    }
}
