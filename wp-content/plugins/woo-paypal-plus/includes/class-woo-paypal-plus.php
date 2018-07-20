<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Paypal_Plus
 * @subpackage Woo_Paypal_Plus/includes
 * @author     Angell EYE <service@angelleye.com>
 */
class Woo_Paypal_Plus {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Woo_Paypal_Plus_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = 'woo-paypal-plus';
        $this->version = '1.0.12';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Woo_Paypal_Plus_Loader. Orchestrates the hooks of the plugin.
     * - Woo_Paypal_Plus_i18n. Defines internationalization functionality.
     * - Woo_Paypal_Plus_Admin. Defines all hooks for the admin area.
     * - Woo_Paypal_Plus_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-plus-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-plus-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-paypal-plus-admin.php';
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-plus-functions.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-paypal-plus-gateway.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/paypal-plus-gateway-calculations-angelleye.php';
    
        $this->loader = new Woo_Paypal_Plus_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Woo_Paypal_Plus_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Woo_Paypal_Plus_i18n();

        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        global $wpdb;
        $plugin_admin = new Woo_Paypal_Plus_Admin($this->get_plugin_name(), $this->get_version());
        $row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'woocommerce_paypal_plus_settings'));
        $this->setting = isset($row->option_value) ? maybe_unserialize($row->option_value) : array();
        $this->thirdPartyPaymentMethods_value = !empty($this->setting['thirdPartyPaymentMethods']) ? $this->setting['thirdPartyPaymentMethods'] : 'no';
        $this->thirdPartyPaymentMethods = 'yes' === $this->thirdPartyPaymentMethods_value;   
        $this->country = !empty($this->setting['country']) ? $this->setting['country'] : 'DE';
        $this->country = apply_filters( 'woocommerce_paypal_plus_country', $this->country);
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_filter('woocommerce_payment_gateways', $plugin_admin, 'angelleye_add_paypal_plus', 999, 1);
        $prefix = is_network_admin() ? 'network_admin_' : '';
        $this->loader->add_filter("{$prefix}plugin_action_links_" . PAYPAL_PLUS_BASENAME, $plugin_admin, 'paypal_plus_plugin_action_links', 10, 4);
        $this->loader->add_filter( 'angelleye_paypal_plus_ipn_url', $plugin_admin, 'angelleye_paypal_plus_ipn_url', 10, 1);
	$this->loader->add_action('init', $plugin_admin, 'angelleye_paypal_plus_remove_approvalurl');
        $this->loader->add_filter( 'woocommerce_locate_template', $plugin_admin, 'angelleye_paypal_plus_locate_template', 1, 3 ); 
        if( $this->thirdPartyPaymentMethods == true && ($this->country == 'US' || $this->country == 'DE')) {
            $this->loader->add_action( 'woocommerce_before_checkout_process', $plugin_admin , 'angelleye_paypal_plus_changed_payment_method' );
        }
        if( $this->country == 'BR') {
            $this->loader->add_action( 'woocommerce_billing_fields', $plugin_admin , 'angelleye_paypal_plus_br_woocommerce_billing_fields', 10, 1 );
        }
        $this->loader->add_filter( 'pre_set_site_transient_update_plugins', $plugin_admin , 'angelleye_paypal_plus_pre_set_site_transient_update_plugins', 10, 2 );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Woo_Paypal_Plus_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
    

}
