<?php
if (!defined('ABSPATH')) exit;

class MKV_Plugin {
    public static function boot() {
        // Dashboard and product module are available; remaining business pages
        // are temporarily backed by the original, fully implemented operations.php.
        require_once MKV_PATH . 'modules/dashboard/class-dashboard-admin.php';
        require_once MKV_PATH . 'modules/dashboard/class-dashboard-api.php';
        require_once MKV_PATH . 'modules/products/class-product-admin.php';
        require_once MKV_PATH . 'includes/products.php';
        require_once MKV_PATH . 'includes/operations.php';

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
