<?php
if (!defined('ABSPATH')) exit;

class MKV_Product_Repository {
    public static function stock($id) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(quantity),0) FROM " . MKV_Database::table('mkv_inventory') . " WHERE product_id=%d", $id));
    }

    public static function duplicate($field, $value, $exclude) {
        global $wpdb;
        if ($value === '') return false;
        return (bool) $wpdb->get_var($wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID WHERE p.post_type='mkv_product' AND p.post_status NOT IN ('trash','auto-draft') AND p.ID != %d AND m.meta_key=%s AND m.meta_value=%s LIMIT 1", $exclude, '_mkv_' . $field, $value));
    }

    public static function generate_barcode($exclude) {
        for ($i = 0; $i < 20; $i++) {
            $code = '893' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
            if (!self::duplicate('barcode', $code, $exclude)) return $code;
        }
        return '893' . str_pad((string) (time() % 1000000000), 9, '0', STR_PAD_LEFT);
    }

    public static function alerts() {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT p.post_title AS name, COALESCE(SUM(inv.quantity),0) AS quantity, CAST(COALESCE(MAX(pm.meta_value),'5') AS SIGNED) AS min_stock FROM {$wpdb->posts} p LEFT JOIN " . MKV_Database::table('mkv_inventory') . " inv ON inv.product_id=p.ID LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='_mkv_min_stock' WHERE p.post_type='mkv_product' AND p.post_status='publish' GROUP BY p.ID, p.post_title", ARRAY_A);
        $out = [];
        $low = [];
        foreach ($rows as $row) {
            $qty = (int) $row['quantity'];
            $min = max(0, (int) $row['min_stock']);
            $row['quantity'] = $qty;
            $row['min_stock'] = $min;
            if ($qty <= 0) $out[] = $row;
            elseif ($min > 0 && $qty <= $min) $low[] = $row;
        }
        return ['out' => $out, 'low' => $low];
    }

    public static function low_stock($limit = 5) {
        $alerts = self::alerts();
        $rows = array_merge($alerts['low'], $alerts['out']);
        usort($rows, function ($a, $b) { return (int) $a['quantity'] <=> (int) $b['quantity']; });
        return array_slice($rows, 0, $limit);
    }
}
