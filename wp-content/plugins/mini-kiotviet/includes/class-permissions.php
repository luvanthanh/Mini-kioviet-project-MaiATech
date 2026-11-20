    <?php
    if (!defined('ABSPATH')) exit;

    class MKV_Permissions {
        public static function register() {
            add_action('admin_init', [self::class, 'maybe_upgrade']);
        }

        public static function can($area = 'view') {
            return current_user_can('manage_options') || current_user_can('mkv_' . $area);
        }

        public static function require($area = 'view') {
            if (!self::can($area)) wp_die('Không có quyền truy cập.');
        }

        public static function menu_cap($capability) {
            return current_user_can('manage_options') ? 'manage_options' : $capability;
        }

        public static function product_caps() {
            $caps = [];
            foreach (['edit_mkv_product','read_mkv_product','delete_mkv_product','create_mkv_products','edit_mkv_products','edit_others_mkv_products','publish_mkv_products','read_private_mkv_products','delete_mkv_products','delete_private_mkv_products','delete_published_mkv_products','delete_others_mkv_products','edit_private_mkv_products','edit_published_mkv_products','manage_mkv_categories'] as $cap) {
                $caps[$cap] = true;
            }
            return $caps;
        }

        public static function install() {
            $product = self::product_caps();
            foreach (['mkv_manager' => 'Manager', 'mkv_sales' => 'Sales', 'mkv_warehouse' => 'Warehouse'] as $key => $label) {
                $caps = ['read' => true, 'mkv_view' => true];
                if ($key === 'mkv_manager') $caps += ['mkv_customers' => true, 'mkv_inventory' => true, 'mkv_orders' => true, 'mkv_reports' => true, 'mkv_settings' => true, 'upload_files' => true] + $product;
                if ($key === 'mkv_sales') $caps += ['mkv_customers' => true, 'mkv_orders' => true];
                if ($key === 'mkv_warehouse') $caps += ['mkv_inventory' => true];
                add_role($key, $label, $caps);
                $role = get_role($key);
                if ($role) foreach ($caps as $cap => $grant) $role->add_cap($cap, $grant);
            }
            $admin = get_role('administrator');
            if ($admin) {
                foreach ($product as $cap => $grant) $admin->add_cap($cap, $grant);
                $admin->add_cap('mkv_view');
            }
            update_option('mkv_caps_version', '2');
        }

        public static function maybe_upgrade() {
            $admin = get_role('administrator');

                if (!$admin) {
                    return;
            }

            $required_caps = self::product_caps();
            $required_caps['mkv_view'] = true;

            $needs_upgrade = get_option('mkv_caps_version') !== '2';

            foreach ($required_caps as $cap => $grant) {
                if (!$admin->has_cap($cap)) {
                    $needs_upgrade = true;
                    break;
                }
            }

            if ($needs_upgrade) {
                self::install();
            }
        }
    }
