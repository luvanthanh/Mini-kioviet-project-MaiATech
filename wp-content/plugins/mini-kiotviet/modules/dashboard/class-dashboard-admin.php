<?php
if (!defined('ABSPATH')) exit;

class MKV_Dashboard_Admin {
    public static function register() {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
    }

    public static function menu() {
        add_menu_page('Mini KiotViet', 'Mini KiotViet', 'mkv_view', 'mkv-dashboard', [self::class, 'page'], 'dashicons-store', 3);
        add_submenu_page('mkv-dashboard', 'Dashboard', 'Dashboard', 'mkv_view', 'mkv-dashboard', [self::class, 'page']);
    }

    public static function assets($hook) {
        if ($hook !== 'toplevel_page_mkv-dashboard') return;
        wp_enqueue_style('mkv-dashboard', MKV_URL . 'assets/dashboard.css', [], MKV_VERSION);
        wp_enqueue_script('mkv-chart', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js', [], '4.4.8', true);
        wp_enqueue_script('mkv-dashboard', MKV_URL . 'assets/dashboard.js', ['mkv-chart'], MKV_VERSION, true);
        wp_localize_script('mkv-dashboard', 'mkvSettings', [
            'endpoint' => esc_url_raw(rest_url('mkv/v1/dashboard')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public static function page() {
        MKV_Permissions::require('view');
        MKV_Plugin::render('dashboard/index');
    }
}
