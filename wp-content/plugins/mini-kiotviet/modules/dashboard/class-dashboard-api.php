<?php
if (!defined('ABSPATH')) exit;

class MKV_Dashboard_Api {
    public static function register() {
        add_action('rest_api_init', [self::class, 'routes']);
    }

    public static function routes() {
        register_rest_route('mkv/v1', '/dashboard', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [self::class, 'data'],
            'permission_callback' => function () { return MKV_Permissions::can('view'); },
        ]);
    }

    public static function data() {
        global $wpdb;
        $orders = MKV_Database::table('mkv_orders');
        $customers = MKV_Database::table('mkv_customers');
        $items = MKV_Database::table('mkv_order_items');
        $today = current_time('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime($today . ' +1 day'));
        $today_revenue = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(total),0) FROM $orders WHERE status IN ('paid','shipping','completed') AND paid_at >= %s AND paid_at < %s", $today . ' 00:00:00', $tomorrow . ' 00:00:00'));
        $today_orders = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $orders WHERE created_at >= %s AND created_at < %s", $today . ' 00:00:00', $tomorrow . ' 00:00:00'));
        $total_revenue = (float) $wpdb->get_var("SELECT COALESCE(SUM(total),0) FROM $orders WHERE status IN ('paid','shipping','completed')");
        $count = wp_count_posts('mkv_product');
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime($today . " -$i days"));
            $next = date('Y-m-d', strtotime($day . ' +1 day'));
            $value = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(total),0) FROM $orders WHERE status IN ('paid','shipping','completed') AND paid_at >= %s AND paid_at < %s", $day . ' 00:00:00', $next . ' 00:00:00'));
            $days[] = ['date' => $day, 'revenue' => $value];
        }
        $top_products = $wpdb->get_results("SELECT p.post_title AS name, SUM(i.quantity) AS qty FROM $items i INNER JOIN $orders o ON o.id=i.order_id INNER JOIN {$wpdb->posts} p ON p.ID=i.product_id WHERE o.status IN ('paid','shipping','completed') GROUP BY i.product_id,p.post_title ORDER BY qty DESC LIMIT 5", ARRAY_A);
        $top_customers = $wpdb->get_results("SELECT c.name AS name, SUM(o.total) AS spent FROM $orders o INNER JOIN $customers c ON c.id=o.customer_id WHERE o.status IN ('paid','shipping','completed') GROUP BY c.id,c.name ORDER BY spent DESC LIMIT 5", ARRAY_A);
        $recent = $wpdb->get_results("SELECT id,status,total,created_at FROM $orders ORDER BY created_at DESC LIMIT 5", ARRAY_A);
        return rest_ensure_response([
            'today_revenue' => $today_revenue,
            'today_orders' => $today_orders,
            'total_products' => (int) ($count->publish ?? 0),
            'total_customers' => (int) $wpdb->get_var("SELECT COUNT(*) FROM $customers"),
            'total_revenue' => $total_revenue,
            'chart' => $days,
            'top_products' => $top_products,
            'top_customers' => $top_customers,
            'recent' => $recent,
            'low_stock' => MKV_Product_Repository::low_stock(5),
        ]);
    }
}
