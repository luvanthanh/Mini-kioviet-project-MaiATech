<?php
if (!defined('ABSPATH')) exit;

add_action('add_meta_boxes_mkv_product', function () {
    add_meta_box('mkv_product_details', 'Thông tin sản phẩm – Test 3', 'mkv_product_fields', 'mkv_product', 'normal', 'high');
});
function mkv_product_fields($post) {
    wp_nonce_field('mkv_save_product', 'mkv_product_nonce');
    $fields = ['sku'=>'SKU','barcode'=>'Barcode','cost_price'=>'Giá nhập (VNĐ)','sale_price'=>'Giá bán (VNĐ)','min_stock'=>'Ngưỡng cảnh báo tồn kho'];
    echo '<p><strong>Tên sản phẩm</strong> nhập ở ô tiêu đề; <strong>Mô tả</strong> nhập trong trình soạn thảo. Danh mục và Ảnh đại diện nằm ở thanh bên.</p>';
    foreach ($fields as $key=>$label) {
        $val = get_post_meta($post->ID, '_mkv_'.$key, true);
        $type = in_array($key, ['cost_price','sale_price','min_stock'], true) ? 'number' : 'text';
        $attrs = $type === 'number' ? 'min="0" step="'.($key === 'min_stock'?'1':'0.01').'"' : 'maxlength="190"';
        printf('<p><label for="mkv_%1$s"><strong>%2$s</strong></label><br><input style="width:100%%;max-width:450px" id="mkv_%1$s" name="mkv_%1$s" type="%3$s" value="%4$s" %5$s></p>', esc_attr($key), esc_html($label), esc_attr($type), esc_attr($val), $attrs);
    }
    echo '<p><strong>Tồn kho hiện tại:</strong> '.esc_html(mkv_product_stock($post->ID)).' (được quản lý bằng nghiệp vụ nhập/xuất/chuyển kho ở Giai đoạn 3, không sửa trực tiếp tại đây).</p>';
}
function mkv_product_stock($id) {
    global $wpdb;
    return (int)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(quantity),0) FROM {$wpdb->prefix}mkv_inventory WHERE product_id=%d", $id));
}
function mkv_product_duplicate($field, $value, $exclude) {
    global $wpdb;
    if ($value === '') return false;
    return (bool)$wpdb->get_var($wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID WHERE p.post_type='mkv_product' AND p.post_status NOT IN ('trash','auto-draft') AND p.ID != %d AND m.meta_key=%s AND m.meta_value=%s LIMIT 1", $exclude, '_mkv_'.$field, $value));
}
// Kiểm tra trước khi WP ghi bài: trả về lỗi rõ ràng và tránh lưu dữ liệu sai.
add_filter('wp_insert_post_data', function($data, $postarr, $unsanitized, $update) {
    if (($data['post_type'] ?? '') !== 'mkv_product' || !isset($_POST['mkv_product_nonce'])) return $data;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkv_product_nonce'])), 'mkv_save_product')) return $data;
    if (!current_user_can('edit_posts')) return $data;
    if (in_array($data['post_status'] ?? '', ['auto-draft','trash'], true)) return $data;
    $id = (int)($postarr['ID'] ?? 0);
    $name = trim(wp_strip_all_tags($data['post_title'] ?? ''));
    $sku = sanitize_text_field(wp_unslash($_POST['mkv_sku'] ?? ''));
    $barcode = sanitize_text_field(wp_unslash($_POST['mkv_barcode'] ?? ''));
    $cost = wp_unslash($_POST['mkv_cost_price'] ?? '');
    $price = wp_unslash($_POST['mkv_sale_price'] ?? '');
    $min = wp_unslash($_POST['mkv_min_stock'] ?? '0');
    $errors=[];
    if ($name === '') $errors[]='Tên sản phẩm bắt buộc.';
    if ($sku === '') $errors[]='SKU bắt buộc.';
    if ($barcode === '') $errors[]='Barcode bắt buộc.';
    if (mkv_product_duplicate('sku',$sku,$id)) $errors[]='SKU đã tồn tại.';
    if (mkv_product_duplicate('barcode',$barcode,$id)) $errors[]='Barcode đã tồn tại.';
    if (!is_numeric($cost) || (float)$cost <= 0) $errors[]='Giá nhập phải lớn hơn 0.';
    if (!is_numeric($price) || (float)$price <= 0) $errors[]='Giá bán phải lớn hơn 0.';
    if (filter_var($min, FILTER_VALIDATE_INT) === false || (int)$min < 0) $errors[]='Ngưỡng tồn kho phải là số nguyên không âm.';
    if ($errors) wp_die(esc_html(implode(' ', $errors)).' Quay lại trình duyệt để sửa thông tin.', 'Dữ liệu sản phẩm không hợp lệ', ['back_link'=>true,'response'=>400]);
    return $data;
}, 10, 4);
add_action('save_post_mkv_product', function($id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['mkv_product_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mkv_product_nonce'])), 'mkv_save_product')) return;
    if (!current_user_can('edit_post', $id)) return;
    foreach (['sku','barcode','cost_price','sale_price','min_stock'] as $key) {
        if (!isset($_POST['mkv_'.$key])) continue;
        $raw = wp_unslash($_POST['mkv_'.$key]);
        $value = in_array($key, ['cost_price','sale_price'], true) ? (string)(float)$raw : ($key==='min_stock' ? (string)(int)$raw : sanitize_text_field($raw));
        update_post_meta($id, '_mkv_'.$key, $value);
    }
});
add_filter('manage_mkv_product_posts_columns', function($cols) {
    return ['cb'=>$cols['cb'], 'mkv_image'=>'Ảnh','title'=>'Tên sản phẩm','mkv_sku'=>'SKU','mkv_barcode'=>'Barcode','taxonomy-mkv_category'=>'Danh mục','mkv_cost_price'=>'Giá nhập','mkv_sale_price'=>'Giá bán','mkv_stock'=>'Tồn kho','date'=>'Ngày'];
});
add_action('manage_mkv_product_posts_custom_column', function($col,$id) {
    if ($col==='mkv_image') echo get_the_post_thumbnail($id,[45,45]) ?: '—';
    elseif ($col==='mkv_stock') echo esc_html(mkv_product_stock($id));
    elseif (in_array($col,['mkv_sku','mkv_barcode','mkv_cost_price','mkv_sale_price'],true)) {
        $value=get_post_meta($id,'_'.$col,true);
        echo esc_html(in_array($col,['mkv_cost_price','mkv_sale_price'],true) && $value!=='' ? number_format((float)$value,0,',','.').' đ' : ($value ?: '—'));
    }
},10,2);
add_filter('manage_edit-mkv_product_sortable_columns', function($cols) {
    $cols['mkv_sku']='mkv_sku'; $cols['mkv_sale_price']='mkv_sale_price'; $cols['mkv_cost_price']='mkv_cost_price'; return $cols;
});
add_action('pre_get_posts', function($q) {
    if (!is_admin() || !$q->is_main_query() || $q->get('post_type')!=='mkv_product') return;
    $sort=$q->get('orderby');
    if (in_array($sort,['mkv_sku','mkv_sale_price','mkv_cost_price'],true)) {
        $q->set('meta_key','_mkv_'.$sort);
        $q->set('orderby',$sort==='mkv_sku'?'meta_value':'meta_value_num');
    }
    if (!empty($_GET['mkv_category_filter'])) {
        $term=absint($_GET['mkv_category_filter']);
        $q->set('tax_query', [['taxonomy'=>'mkv_category','field'=>'term_id','terms'=>[$term]]]);
    }
    if (!empty($_GET['s'])) {
        $q->set('mkv_search_term', sanitize_text_field(wp_unslash($_GET['s'])));
    }
});
add_filter('posts_search', function($search,$q) {
    if (!is_admin() || !$q->is_main_query() || $q->get('post_type')!=='mkv_product' || !$q->get('mkv_search_term')) return $search;
    global $wpdb;
    $like='%'.$wpdb->esc_like($q->get('mkv_search_term')).'%';
    return $wpdb->prepare(" AND ({$wpdb->posts}.post_title LIKE %s OR EXISTS (SELECT 1 FROM {$wpdb->postmeta} mkv_s WHERE mkv_s.post_id={$wpdb->posts}.ID AND mkv_s.meta_key IN ('_mkv_sku','_mkv_barcode') AND mkv_s.meta_value LIKE %s))",$like,$like);
},10,2);
add_action('restrict_manage_posts', function($post_type) {
    if ($post_type!=='mkv_product') return;
    wp_dropdown_categories(['taxonomy'=>'mkv_category','name'=>'mkv_category_filter','show_option_all'=>'Tất cả danh mục','hide_empty'=>false,'selected'=>absint($_GET['mkv_category_filter'] ?? 0),'value_field'=>'term_id']);
    $url=wp_nonce_url(admin_url('admin-post.php?action=mkv_export_products'),'mkv_export_products');
    echo ' <a class="button" href="'.esc_url($url).'">Xuất CSV</a>';
});
add_action('admin_post_mkv_export_products', function() {
    if (!current_user_can('edit_posts')) wp_die('Không có quyền.');
    check_admin_referer('mkv_export_products');
    $posts=get_posts(['post_type'=>'mkv_product','post_status'=>'publish','numberposts'=>-1]);
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mini-kiotviet-products.csv"');
    $out=fopen('php://output','w');
    fwrite($out,"\xEF\xBB\xBF");
    fputcsv($out,['ID','Tên','SKU','Barcode','Danh mục','Giá nhập','Giá bán','Tồn kho','Ảnh','Mô tả']);
    foreach ($posts as $p) {
        $terms=wp_get_post_terms($p->ID,'mkv_category',['fields'=>'names']);
        fputcsv($out,[$p->ID,$p->post_title,get_post_meta($p->ID,'_mkv_sku',true),get_post_meta($p->ID,'_mkv_barcode',true),is_wp_error($terms)?'':implode('; ',$terms),get_post_meta($p->ID,'_mkv_cost_price',true),get_post_meta($p->ID,'_mkv_sale_price',true),mkv_product_stock($p->ID),get_the_post_thumbnail_url($p->ID,'full') ?: '',wp_strip_all_tags($p->post_content)]);
    }
    fclose($out); exit;
});
add_action('admin_notices', function() {
    $screen=get_current_screen();
    if ($screen && $screen->id==='edit-mkv_product') echo '<div class="notice notice-info"><p><strong>Mini KiotViet:</strong> Chọn “Thêm Bài Viết” để thêm sản phẩm. Tồn kho được quản lý ở Giai đoạn 3.</p></div>';
});
