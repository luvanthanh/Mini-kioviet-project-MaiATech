<?php
if (!defined('ABSPATH')) exit;

class MKV_Activator {
    public static function activate() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $collate = $wpdb->get_charset_collate();
        $tables = [];
        $tables[] = "CREATE TABLE {$wpdb->prefix}mkv_customers (
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
        $tables[] = "CREATE TABLE {$wpdb->prefix}mkv_warehouses (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(190) NOT NULL,
        PRIMARY KEY  (id)
    ) $collate;";
        $tables[] = "CREATE TABLE {$wpdb->prefix}mkv_inventory (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        warehouse_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        quantity int NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY warehouse_product (warehouse_id,product_id)
    ) $collate;";
        $tables[] = "CREATE TABLE {$wpdb->prefix}mkv_stock_movements (
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
        $tables[] = "CREATE TABLE {$wpdb->prefix}mkv_orders (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        customer_id bigint(20) unsigned DEFAULT NULL,
        status varchar(25) NOT NULL DEFAULT 'draft',
        total decimal(18,2) NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        paid_at datetime DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY status_created (status,created_at)
    ) $collate;";
        $tables[] = "CREATE TABLE {$wpdb->prefix}mkv_order_items (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        order_id bigint(20) unsigned NOT NULL,
        product_id bigint(20) unsigned NOT NULL,
        quantity int NOT NULL,
        unit_price decimal(18,2) NOT NULL,
        PRIMARY KEY  (id),
        KEY order_id (order_id)
    ) $collate;";
        foreach ($tables as $sql) dbDelta($sql);
        MKV_Product_Admin::register_types();
        flush_rewrite_rules();
        MKV_Permissions::install();
    }
}
