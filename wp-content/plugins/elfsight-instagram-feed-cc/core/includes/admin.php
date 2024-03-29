<?php

if (!defined('ABSPATH')) exit;


if (!class_exists('ElfsightPluginAdmin')) {
    class ElfsightPluginAdmin {
        private $name;
        private $description;
        private $slug;
        private $version;
        private $textDomain;
        private $editorSettings;
        private $editorPreferences;
        private $menuIcon;
        private $menuId;

        private $pluginName;
        private $pluginFile;

        private $updateUrl;
        private $previewUrl;
        private $observerUrl;

        private $productUrl;
        private $supportUrl;

        private $widgetsApi;

        private $capability;
        private $roleCapabitily = array(
            'admin' => 'manage_options',
            'editor' => 'edit_pages',
            'author' => 'publish_posts'
        );

        private $pages;
        private $customPages;
        private $menu;

        public function __construct($config, $widgetsApi) {
            $this->name = $config['name'];
            $this->description = $config['description'];
            $this->slug = $config['slug'];
            $this->version = $config['version'];
            $this->textDomain = $config['text_domain'];
            $this->editorSettings = $config['editor_settings'];
            $this->editorPreferences = $config['editor_preferences'];
            $this->menuIcon = $config['menu_icon'];

            $this->pluginName = $config['plugin_name'];
            $this->pluginFile = $config['plugin_file'];

            $this->updateUrl = $config['update_url'];
            $this->previewUrl = $config['preview_url'];
            $this->observerUrl = !empty($config['observer_url']) ? $config['observer_url'] : null;
            $this->customScriptUrl = !empty($config['admin_custom_script_url']) ? $config['admin_custom_script_url'] : null;
            $this->customStyleUrl = !empty($config['admin_custom_style_url']) ? $config['admin_custom_style_url'] : null;

            $this->productUrl = $config['product_url'];
            $this->supportUrl = $config['support_url'];

            $this->customPages = !empty($config['admin_custom_pages']) ? $config['admin_custom_pages'] : array();
            $this->pages = $this->generatePages();
            $this->menu = $this->generateMenu();

            $this->widgetsApi = $widgetsApi;

            add_action('admin_menu', array($this, 'addMenuPage'));
            add_action('admin_init', array($this, 'registerAssets'));
            add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));
            add_action('wp_ajax_' . $this->getOptionName('update_preferences'), array($this, 'updatePreferences'));
            add_action('wp_ajax_' . $this->getOptionName('update_activation_data'), array($this, 'updateActivationData'));

            $this->capability = apply_filters('elfsight_admin_capability', $this->roleCapabitily[get_option($this->getOptionName('access_role'), 'admin')]);
        }

        public function addMenuPage() {
            $this->menuId = add_menu_page($this->name, $this->name, $this->capability, $this->slug, array($this, 'getPage'), $this->menuIcon);
        }

        public function registerAssets() {
            wp_register_style($this->slug . '-admin', plugins_url('assets/elfsight-admin.css', $this->pluginFile), array(), $this->version);

            if ($this->customStyleUrl) {
                wp_register_style($this->slug . '-admin-custom', $this->customStyleUrl, array($this->slug . '-admin'), $this->version);
            }

            wp_register_script($this->slug . '-admin', plugins_url('assets/elfsight-admin.js', $this->pluginFile), array(), $this->version, true);

            if ($this->customScriptUrl) {
                wp_register_script($this->slug . '-admin-custom', $this->customScriptUrl, array('jquery', $this->slug . '-admin'), $this->version, true);
            }
        }

        public function enqueueAssets($hook) {
            if ($hook == $this->menuId) {
                wp_enqueue_style($this->slug . '-admin');
                if ($this->customStyleUrl) {
                    wp_enqueue_style($this->slug . '-admin-custom');
                }

                wp_enqueue_script($this->slug . '-admin');
                if ($this->customScriptUrl) {
                    wp_enqueue_script($this->slug . '-admin-custom');
                }

                // remove emoji
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('wp_print_styles', 'print_emoji_styles');
                remove_action('admin_print_scripts', 'print_emoji_detection_script');
                remove_action('admin_print_styles', 'print_emoji_styles');
            }
        }

        public function getPage() {
            $this->widgetsApi->upgrade();

            $widgets_clogged = get_option($this->getOptionName('widgets_clogged'), '');

            // preferences
            $uploads_dir_params = wp_upload_dir();

            $uploads_dir = $uploads_dir_params['basedir'] . '/' . $this->slug;

            $custom_css_path = $uploads_dir . '/' . $this->slug . '-custom.css';
            $custom_js_path = $uploads_dir . '/' . $this->slug . '-custom.js';
            $preferences_custom_css = is_readable($custom_css_path) ? file_get_contents($custom_css_path) : '';
            $preferences_custom_js = is_readable($custom_js_path) ? file_get_contents($custom_js_path) : '';
            $preferences_force_script_add = get_option($this->getOptionName('force_script_add'));
            $preferences_access_role = get_option($this->getOptionName('access_role'), 'admin');
            $preferences_auto_upgrade = get_option($this->getOptionName('auto_upgrade'), 'on');

            // activation
            $purchase_code = get_option($this->getOptionName('purchase_code'), '');
            $activated = get_option($this->getOptionName('activated'), '') === 'true';
            $supported_until = get_option($this->getOptionName('supported_until'), 0);
            $latest_version = get_option($this->getOptionName('latest_version'), '');
            $last_check_datetime = get_option($this->getOptionName('last_check_datetime'), '');
            $has_new_version = !empty($latest_version) && version_compare($this->version, $latest_version, '<');
            $host = parse_url(site_url(), PHP_URL_HOST);

            // support
            $supportUrlParts = explode('#', $this->supportUrl);
            $supportEmbedUrl = $supportUrlParts[0] . '?embed=true&purchase_code=' . $purchase_code . '#' . $supportUrlParts[1];

            // upgrade
            $last_upgraded_at = get_option($this->getOptionName('last_upgraded_at'));

            $activation_css_classes = '';
            if ($activated) {
                $activation_css_classes .= 'elfsight-admin-activation-activated ';
            } else if (!empty($purchase_code)) {
                $activation_css_classes .= 'elfsight-admin-activation-invalid ';
            }
            if ($has_new_version) {
                $activation_css_classes .= 'elfsight-admin-activation-has-new-version ';
            }

            ?>
            <div class="<?php echo $activation_css_classes; ?>elfsight-admin wrap">
            <h2 class="elfsight-admin-wp-notifications-hack"></h2>

            <div class="elfsight-admin-wrapper">
                <?php require_once(plugin_dir_path(__FILE__) . implode(DIRECTORY_SEPARATOR, array('templates', 'header.php'))); ?>

                <main class="elfsight-admin-main elfsight-admin-loading"
                      data-elfsight-admin-slug="<?php echo $this->slug; ?>"
                      data-elfsight-admin-widgets-clogged="<?php echo $widgets_clogged; ?>">
                    <div class="elfsight-admin-loader"></div>

                    <div class="elfsight-admin-menu-container">
                        <?php require_once(plugin_dir_path(__FILE__) . implode(DIRECTORY_SEPARATOR, array('templates', 'menu.php'))); ?>

                        <?php require_once(plugin_dir_path(__FILE__) . implode(DIRECTORY_SEPARATOR, array('templates', 'menu-actions.php'))); ?>
                    </div>

                    <div class="elfsight-admin-pages-container">
                        <?php
                            foreach ($this->pages as $page) {
                                require_once($page['template']);
                            }
                        ?>
                    </div>
                </main>

                <?php //require_once(plugin_dir_path(__FILE__) . implode(DIRECTORY_SEPARATOR, array('templates', 'other-products.php'))); ?>
            </div>
            </div>
        <?php }

        public function updatePreferences() {
            if (!wp_verify_nonce($_REQUEST['nonce'], $this->getOptionName('update_preferences_nonce'))) {
                exit;
            }

            $result = array();

            // force script add
            if (isset($_REQUEST['preferences_force_script_add'])) {
                $result['success'] = true;

                update_option($this->getOptionName('force_script_add'), $_REQUEST['preferences_force_script_add']);
            }

            // custom css
            if (isset($_REQUEST['preferences_custom_css'])) {
                $file_type = 'css';
                $file_content = $_REQUEST['preferences_custom_css'];
            }

            // custom js
            if (isset($_REQUEST['preferences_custom_js'])) {
                $file_type = 'js';
                $file_content = $_REQUEST['preferences_custom_js'];
            }

            // user role select
            if (isset($_REQUEST['access_role'])) {
                $result['success'] = true;

                update_option($this->getOptionName('access_role'), $_REQUEST['access_role']);
            }

            // auto-upgrade
            if (isset($_REQUEST['preferences_auto_upgrade'])) {
                $result['success'] = true;

                update_option($this->getOptionName('auto_upgrade'), $_REQUEST['preferences_auto_upgrade']);
            }

            if (isset($file_content)) {
                $uploads_dir_params = wp_upload_dir();
                $uploads_dir = $uploads_dir_params['basedir'] . '/' . $this->slug;

                if (!is_dir($uploads_dir)) {
                    wp_mkdir_p($uploads_dir);
                }

                $path = $uploads_dir . '/' . $this->slug . '-custom.' . $file_type;

                if (file_exists($path) && !is_writable($path)) {
                    $result['success'] = false;
                    $result['error'] = __('The file can not be overwritten. Please check the file permissions.', $this->textDomain);

                } else {
                    $result['success'] = true;

                    file_put_contents($path, stripslashes($file_content));
                }
            }

            exit(json_encode($result));
        }

        public function updateActivationData() {
            if (!wp_verify_nonce($_REQUEST['nonce'], $this->getOptionName('update_activation_data_nonce'))) {
                exit;
            }

            update_option($this->getOptionName('purchase_code'), !empty($_REQUEST['purchase_code']) ? $_REQUEST['purchase_code'] : '');
            update_option($this->getOptionName('activated'), !empty($_REQUEST['activated']) ? $_REQUEST['activated'] : '');
            update_option($this->getOptionName('supported_until'), !empty($_REQUEST['supported_until']) ? $_REQUEST['supported_until'] : '');
        }

        private function getOptionName($name) {
            return str_replace('-', '_', $this->slug) . '_' . $name;
        }

        private function generatePages() {
            $plugin_dir = plugin_dir_path(__FILE__);
            $default_pages = array(
                array(
                    'id' => 'welcome',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-welcome.php'))
                ),
                array(
                    'id' => 'widgets',
                    'menu_title' => 'Widgets',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-widgets.php'))
                ),
                array(
                    'id' => 'edit-widget',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-edit-widget.php'))
                ),
                array(
                    'id' => 'preferences',
                    'menu_title' => 'Preferences',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-preferences.php'))
                ),
                array(
                    'id' => 'support',
                    'menu_title' => 'Support',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-support.php'))
                ),
                array(
                    'id' => 'activation',
                    'menu_title' => 'Activation',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-activation.php')),
                    'notification' => __('The plugin is not activated', $this->textDomain)
                ),
                array(
                    'id' => 'error',
                    'template' => $plugin_dir . implode(DIRECTORY_SEPARATOR, array('templates', 'page-error.php'))
                )
            );

            return array_merge($default_pages, $this->customPages);
        }

        private function generateMenu() {
            $menu = array();

            foreach ($this->pages as $page) {
                if (!empty($page['menu_title'])) {
                    array_splice($menu, isset($page['menu_index']) ? $page['menu_index'] : count($this->pages), 0, array($page));
                }
            }

            return $menu;
        }
    }

}

?>