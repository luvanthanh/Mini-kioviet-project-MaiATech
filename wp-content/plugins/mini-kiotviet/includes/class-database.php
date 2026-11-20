<?php
if (!defined('ABSPATH')) exit;

class MKV_Database {
    public static function table($name) {
        global $wpdb;
        return $wpdb->prefix . $name;
    }

    public static function change_stock($product, $warehouse, $delta, $type, $reference = 0) {
        global $wpdb;
        $inventory = self::table('mkv_inventory');
        $movements = self::table('mkv_stock_movements');
        $wpdb->query($wpdb->prepare("INSERT INTO $inventory (warehouse_id,product_id,quantity) VALUES (%d,%d,0) ON DUPLICATE KEY UPDATE id=id", $warehouse, $product));
        $current = $wpdb->get_var($wpdb->prepare("SELECT quantity FROM $inventory WHERE warehouse_id=%d AND product_id=%d FOR UPDATE", $warehouse, $product));
        if ($current === null || (int) $current + $delta < 0) return false;
        $ok = $wpdb->update($inventory, ['quantity' => (int) $current + $delta], ['warehouse_id' => $warehouse, 'product_id' => $product]);
        $logged = $wpdb->insert($movements, [
            'product_id' => $product,
            'warehouse_id' => $warehouse,
            'quantity_change' => $delta,
            'movement_type' => $type,
            'reference_id' => $reference ?: null,
            'created_at' => current_time('mysql'),
        ]);
        return $ok !== false && $logged;
    }
}
