<?php

/**
 *
 * @link              http://www.angelleye.com/
 * @since             1.0.0
 * @package           Woo_Paypal_Plus
 *
 * @wordpress-plugin
 * Plugin Name:       PayPal Plus for WooCommerce
 * Plugin URI:        http://www.angelleye.com/product/woocommerce-paypal-plus-plugin/
 * Description:       PayPal PLUS is a solution where PayPal offers PayPal, Credit Card and ELV as individual payment options on the payment selection page, loaded within a PayPal hosted iFrame.
 * Version:           1.1.4
 * Author:            Angell EYE
 * Author URI:        http://www.angelleye.com/
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       woo-paypal-plus
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


if (!defined('PIWF_PLUGIN_DIR')) {
    define('PIWF_PLUGIN_DIR', dirname(__FILE__));
}
if (!defined('PAYPAL_PLUS_MAX_NUMBER')) {
    define ('PAYPAL_PLUS_MAX_NUMBER', 9223372036854775807);
}
if (!defined('PAYPAL_PLUS_BASENAME')) {
    define('PAYPAL_PLUS_BASENAME', plugin_basename(__FILE__));
}
if (!defined('PAYPAL_DIR_PATH')) {
    define('PAYPAL_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));
}
/**
 * define PIW_PLUGIN_URL constant for global use
 */
if (!defined('PIWF_PLUGIN_URL')) {
    define('PIWF_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('PIWF_PLUGIN_PATH')) {
    define('PIWF_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
}
/**
 * define plugin basename
 */
if (!defined('PIWF_PLUGIN_BASENAME')) {
    define('PIWF_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

if (!defined('AEU_ZIP_URL')) {
    define('AEU_ZIP_URL', 'http://downloads.angelleye.com/ae-updater/angelleye-updater/angelleye-updater.zip');
}

/**
 * Required functions
 */
if (!function_exists('angelleye_queue_update')) {
    include_once( 'includes/class-woo-paypal-plus-functions.php' );
}

/**
 * Plugin updates
 */
angelleye_queue_update(plugin_basename(__FILE__), '101', 'woo-paypal-plus');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-paypal-plus-activator.php
 */
function activate_woo_paypal_plus() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-woo-paypal-plus-activator.php';
    Woo_Paypal_Plus_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-paypal-plus-deactivator.php
 */
function deactivate_woo_paypal_plus() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-woo-paypal-plus-deactivator.php';
    Woo_Paypal_Plus_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_woo_paypal_plus');
register_deactivation_hook(__FILE__, 'deactivate_woo_paypal_plus');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-woo-paypal-plus.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_paypal_plus() {

    $plugin = new Woo_Paypal_Plus();
    $plugin->run();
}

add_action('plugins_loaded', 'load_woo_paypal_plus');
add_action('wp_loaded', 'init');

function load_woo_paypal_plus() {
    if ( class_exists('WC_Payment_Gateway') ) { 
        run_woo_paypal_plus();
    }
}

function init() {
    add_filter('woocommerce_gzdp_filter_template', 'angelleye_paypal_plus_woocommerce_gzdp_filter_template', 0, 3);
}

function angelleye_paypal_plus_woocommerce_gzdp_filter_template($template, $template_name, $template_path) {
    if ($template_path == 'woocommerce-germanized-pro/' && ($template_name == 'invoice/table.php' || $template_name == 'invoice/table-gross.php')) {
        return $template = PIWF_PLUGIN_PATH . '/templates/' . $template_name;
    }
    return $template;
}