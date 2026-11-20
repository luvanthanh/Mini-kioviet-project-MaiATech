<?php
/**
 * Plugin Name: Mini KiotViet
 * Description: Mini KiotViet - Dashboard giai đoạn 1 cho bài Test 3.
 * Version: 0.1.0
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) exit;

define('MKV_URL', plugin_dir_url(__FILE__));

function mkv_register_product_type() {
    register_post_type('mkv_product', [
        'labels' => ['name'=>'Sản phẩm', 'singular_name'=>'Sản phẩm'],
        'public'=>false, 'show_ui'=>true, 'show_in_menu'=>false,
        'supports'=>['title','editor','thumbnail'], 'show_in_rest'=>true,
        'capability_type'=>'post',
    ]);
    register_taxonomy('mkv_category','mkv_product',[
        'label'=>'Danh mục sản phẩm', 'hierarchical'=>true,
        'show_ui'=>true, 'show_in_rest'=>true,
    ]);
}
add_action('init','mkv_register_product_type');

function mkv_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $collate=$wpdb->get_charset_collate();
    $tables=[];
    $tables[]="CREATE TABLE {$wpdb->prefix}mkv_customers (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(190) NOT NULL,
        email varchar(190) NOT NULL DEFAULT '',
        phone varchar(30) NOT NULL DEFAULT '',
        address text NULL,
        points int NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY email (email)
    ) $collate;";
    $tables[]="CREATE TABLE {$wpdb->prefix}mkv_warehouses (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(190) NOT NULL,
        PRIMARY KEY  (id)
    ) $collate;";
    $tables[]="CREATE TABLE {$wpdb->prefix}mkv_inventory (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        warehouse_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        quantity int NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY warehouse_product (warehouse_id,product_id)
    ) $collate;";
    $tables[]="CREATE TABLE {$wpdb->prefix}mkv_stock_movements (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        product_id bigint(20) unsigned NOT NULL,
        warehouse_id bigint(20) unsigned NOT NULL,
        quantity_change int NOT NULL,
        movement_type varchar(30) NOT NULL,
        reference_id bigint(20) unsigned DEFAULT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY product_id (product_id)
    ) $collate;";
    $tables[]="CREATE TABLE {$wpdb->prefix}mkv_orders (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        customer_id bigint(20) unsigned DEFAULT NULL,
        status varchar(25) NOT NULL DEFAULT 'draft',
        total decimal(18,2) NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        paid_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY status_created (status,created_at)
    ) $collate;";
    $tables[]="CREATE TABLE {$wpdb->prefix}mkv_order_items (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        order_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        quantity int NOT NULL,
        unit_price decimal(18,2) NOT NULL,
        PRIMARY KEY  (id),
        KEY order_id (order_id)
    ) $collate;";
    foreach($tables as $sql) dbDelta($sql);
    mkv_register_product_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__,'mkv_install');

function mkv_menu() {
    add_menu_page('Mini KiotViet','Mini KiotViet','read','mkv-dashboard','mkv_dashboard_page','dashicons-store',3);
    add_submenu_page('mkv-dashboard','Dashboard','Dashboard','read','mkv-dashboard','mkv_dashboard_page');
    add_submenu_page('mkv-dashboard','Sản phẩm','Sản phẩm','manage_options','edit.php?post_type=mkv_product');
    add_submenu_page('mkv-dashboard','Danh mục','Danh mục','manage_options','edit-tags.php?taxonomy=mkv_category&post_type=mkv_product');
}
add_action('admin_menu','mkv_menu');

function mkv_assets($hook) {
    if ($hook !== 'toplevel_page_mkv-dashboard') return;
    wp_enqueue_style('mkv-dashboard',MKV_URL.'assets/dashboard.css',[], '0.1.0');
    wp_enqueue_script('mkv-chart','https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js',[], '4.4.8',true);
    wp_enqueue_script('mkv-dashboard',MKV_URL.'assets/dashboard.js',['mkv-chart'],'0.1.0',true);
    wp_localize_script('mkv-dashboard','mkvSettings',[
        'endpoint'=>esc_url_raw(rest_url('mkv/v1/dashboard')),
        'nonce'=>wp_create_nonce('wp_rest'),
    ]);
}
add_action('admin_enqueue_scripts','mkv_assets');

function mkv_dashboard_page() {
    if (!mkv_cap('view')) wp_die('Không có quyền truy cập.');
    ?>
    <div class="wrap mkv-wrap">
      <h1>Mini KiotViet — Dashboard</h1>
      <p class="description">Thống kê dữ liệu thực từ hệ thống. Chưa có đơn hàng sẽ hiển thị 0.</p>
      <div class="mkv-stats" id="mkv-stats">
        <div class="mkv-card"><span>Doanh thu hôm nay</span><strong data-stat="today_revenue">—</strong></div>
        <div class="mkv-card"><span>Số đơn hôm nay</span><strong data-stat="today_orders">—</strong></div>
        <div class="mkv-card"><span>Tổng sản phẩm</span><strong data-stat="total_products">—</strong></div>
        <div class="mkv-card"><span>Tổng khách hàng</span><strong data-stat="total_customers">—</strong></div>
        <div class="mkv-card"><span>Tổng doanh thu</span><strong data-stat="total_revenue">—</strong></div>
      </div>
      <div class="mkv-panel"><h2>Doanh thu 7 ngày gần nhất</h2><div class="mkv-chart"><canvas id="mkv-revenue-chart"></canvas></div></div>
      <div class="mkv-widgets">
        <div class="mkv-panel"><h2>Top sản phẩm</h2><div id="mkv-top-products">Đang tải...</div></div>
        <div class="mkv-panel"><h2>Top khách hàng</h2><div id="mkv-top-customers">Đang tải...</div></div>
        <div class="mkv-panel"><h2>Hoạt động gần đây</h2><div id="mkv-recent">Đang tải...</div></div>
        <div class="mkv-panel"><h2>Sản phẩm sắp hết hàng</h2><div id="mkv-low-stock">Đang tải...</div></div>
      </div>
      <p id="mkv-error" role="alert"></p>
    </div>
    <?php
}

add_action('rest_api_init',function(){
    register_rest_route('mkv/v1','/dashboard',[
        'methods'=>WP_REST_Server::READABLE,
        'callback'=>'mkv_dashboard_data',
        'permission_callback'=>function(){return mkv_cap('view');},
    ]);
});

function mkv_dashboard_data() {
    global $wpdb;
    $orders=$wpdb->prefix.'mkv_orders';
    $customers=$wpdb->prefix.'mkv_customers';
    $items=$wpdb->prefix.'mkv_order_items';
    $inventory=$wpdb->prefix.'mkv_inventory';
    $today=current_time('Y-m-d');
    // Doanh thu tính cho đơn đã thanh toán, theo thời điểm paid_at.
    $today_revenue=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(total),0) FROM $orders WHERE status IN ('paid','shipping','completed') AND paid_at >= %s AND paid_at < %s",$today.' 00:00:00',date('Y-m-d',strtotime($today.' +1 day')).' 00:00:00'));
    $today_orders=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $orders WHERE created_at >= %s AND created_at < %s",$today.' 00:00:00',date('Y-m-d',strtotime($today.' +1 day')).' 00:00:00'));
    $total_revenue=(float)$wpdb->get_var("SELECT COALESCE(SUM(total),0) FROM $orders WHERE status IN ('paid','shipping','completed')");
    $count=wp_count_posts('mkv_product');
    $days=[];
    for($i=6;$i>=0;$i--){
        $day=date('Y-m-d',strtotime($today." -$i days"));
        $next=date('Y-m-d',strtotime($day.' +1 day'));
        $value=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(total),0) FROM $orders WHERE status IN ('paid','shipping','completed') AND paid_at >= %s AND paid_at < %s",$day.' 00:00:00',$next.' 00:00:00'));
        $days[]=['date'=>$day,'revenue'=>$value];
    }
    $top_products=$wpdb->get_results("SELECT p.post_title AS name, SUM(i.quantity) AS qty FROM $items i INNER JOIN $orders o ON o.id=i.order_id INNER JOIN {$wpdb->posts} p ON p.ID=i.product_id WHERE o.status IN ('paid','shipping','completed') GROUP BY i.product_id,p.post_title ORDER BY qty DESC LIMIT 5",ARRAY_A);
    $top_customers=$wpdb->get_results("SELECT c.name AS name, SUM(o.total) AS spent FROM $orders o INNER JOIN $customers c ON c.id=o.customer_id WHERE o.status IN ('paid','shipping','completed') GROUP BY c.id,c.name ORDER BY spent DESC LIMIT 5",ARRAY_A);
    $recent=$wpdb->get_results("SELECT id,status,total,created_at FROM $orders ORDER BY created_at DESC LIMIT 5",ARRAY_A);
    $low_stock=$wpdb->get_results("SELECT p.post_title AS name, COALESCE(SUM(inv.quantity),0) AS quantity FROM {$wpdb->posts} p LEFT JOIN $inventory inv ON inv.product_id=p.ID WHERE p.post_type='mkv_product' AND p.post_status='publish' GROUP BY p.ID,p.post_title HAVING quantity <= 5 ORDER BY quantity ASC LIMIT 5",ARRAY_A);
    return rest_ensure_response([
        'today_revenue'=>$today_revenue,'today_orders'=>$today_orders,
        'total_products'=>(int)($count->publish ?? 0),
        'total_customers'=>(int)$wpdb->get_var("SELECT COUNT(*) FROM $customers"),
        'total_revenue'=>$total_revenue,'chart'=>$days,
        'top_products'=>$top_products,'top_customers'=>$top_customers,
        'recent'=>$recent,'low_stock'=>$low_stock,
    ]);
}

// Giai đoạn 2: quản lý sản phẩm.
require_once __DIR__ . '/includes/products.php';

require_once __DIR__ . '/includes/operations.php';
register_activation_hook(__FILE__,'mkv_roles_install');
