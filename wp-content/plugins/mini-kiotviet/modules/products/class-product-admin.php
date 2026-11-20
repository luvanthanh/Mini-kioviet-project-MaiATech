
<?php
if (!defined('ABSPATH')) exit;

class MKV_Product_Admin {

    public static function register_types() {
        register_post_type('mkv_product', [
            'labels' => [
                'name' => 'Sản phẩm',
                'singular_name' => 'Sản phẩm',
                'add_new_item' => 'Thêm sản phẩm',
                'edit_item' => 'Sửa sản phẩm',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
            'capability_type' => ['mkv_product', 'mkv_products'],
            'map_meta_cap' => true,
        ]);

        register_taxonomy('mkv_category', 'mkv_product', [
            'label' => 'Danh mục sản phẩm',
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'capabilities' => [
                'manage_terms' => 'manage_mkv_categories',
                'edit_terms' => 'manage_mkv_categories',
                'delete_terms' => 'manage_mkv_categories',
                'assign_terms' => 'edit_mkv_products',
            ],
        ]);
    }

    public static function register() {
        add_action('init', [self::class, 'register_types']);
        add_action('admin_menu', [self::class, 'menu']);
    }

    public static function menu() {
        add_submenu_page(
            'mkv-dashboard',
            'Sản phẩm',
            'Sản phẩm',
            'edit_mkv_products',
            'edit.php?post_type=mkv_product'
        );

        add_submenu_page(
            'mkv-dashboard',
            'Danh mục',
            'Danh mục',
            'manage_mkv_categories',
            'edit-tags.php?taxonomy=mkv_category&post_type=mkv_product'
        );
    }
}
