<?php
if (!defined('ABSPATH')) exit;

class MKV_Product_Service {
    public static function validate($data, $postarr, $unsanitized, $update) {
        if (($data['post_type'] ?? '') !== 'mkv_product' || !isset($_POST['mkv_product_nonce'])) return $data;
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkv_product_nonce'])), 'mkv_save_product')) return $data;
        if (!current_user_can('edit_mkv_products')) return $data;
        if (in_array($data['post_status'] ?? '', ['auto-draft', 'trash'], true)) return $data;
        $id = (int) ($postarr['ID'] ?? 0);
        $name = trim(wp_strip_all_tags($data['post_title'] ?? ''));
        $sku = sanitize_text_field(wp_unslash($_POST['mkv_sku'] ?? ''));
        $barcode = sanitize_text_field(wp_unslash($_POST['mkv_barcode'] ?? ''));
        $cost = wp_unslash($_POST['mkv_cost_price'] ?? '');
        $price = wp_unslash($_POST['mkv_sale_price'] ?? '');
        $min = wp_unslash($_POST['mkv_min_stock'] ?? '0');
        $errors = [];
        if ($name === '') $errors[] = 'Tên sản phẩm bắt buộc.';
        if ($sku === '') $errors[] = 'SKU bắt buộc.';
        if ($barcode === '') $barcode = MKV_Product_Repository::generate_barcode($id);
        $_POST['mkv_barcode'] = $barcode;
        if (MKV_Product_Repository::duplicate('sku', $sku, $id)) $errors[] = 'SKU đã tồn tại.';
        if (MKV_Product_Repository::duplicate('barcode', $barcode, $id)) $errors[] = 'Barcode đã tồn tại.';
        if (!is_numeric($cost) || (float) $cost <= 0) $errors[] = 'Giá nhập phải lớn hơn 0.';
        if (!is_numeric($price) || (float) $price <= 0) $errors[] = 'Giá bán phải lớn hơn 0.';
        if (filter_var($min, FILTER_VALIDATE_INT) === false || (int) $min < 0) $errors[] = 'Ngưỡng tồn kho phải là số nguyên không âm.';
        if ($errors) wp_die(esc_html(implode(' ', $errors)) . ' Quay lại trình duyệt để sửa thông tin.', 'Dữ liệu sản phẩm không hợp lệ', ['back_link' => true, 'response' => 400]);
        return $data;
    }

    public static function save($id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['mkv_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkv_product_nonce'])), 'mkv_save_product')) return;
        if (!current_user_can('edit_post', $id)) return;
        foreach (['sku', 'barcode', 'cost_price', 'sale_price', 'min_stock'] as $key) {
            if (!isset($_POST['mkv_' . $key])) continue;
            $raw = wp_unslash($_POST['mkv_' . $key]);
            $value = in_array($key, ['cost_price', 'sale_price'], true) ? (string) (float) $raw : ($key === 'min_stock' ? (string) (int) $raw : sanitize_text_field($raw));
            update_post_meta($id, '_mkv_' . $key, $value);
        }
    }
}
