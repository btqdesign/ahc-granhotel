<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Paypal_Plus
 * @subpackage Woo_Paypal_Plus/admin
 * @author     Angell EYE <service@angelleye.com>
 */
class Woo_Paypal_Plus_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woo_Paypal_Plus_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woo_Paypal_Plus_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-paypal-plus-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woo_Paypal_Plus_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woo_Paypal_Plus_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-paypal-plus-admin.js', array('jquery'), $this->version, false);
    }

    public function angelleye_add_paypal_plus($methods) {
        $methods[] = 'Woo_Paypal_Plus_Gateway';
        return $methods;
    }

    public function paypal_plus_plugin_action_links($actions, $plugin_file, $plugin_data, $context) {
        $custom_actions = array(
            'setting' => sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=wc-settings&tab=checkout&section=paypal_plus'), __('Settings', 'woo-paypal-plus')),
            'docs' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://www.angelleye.com/category/docs/woocommerce-paypal-plus-documentation/', __('Docs', 'woo-paypal-plus')),
            'support' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://www.angelleye.com/support/', __('Support', 'woo-paypal-plus')),
            'review' => sprintf('<a href="%s" target="_blank">%s</a>', 'https://www.angelleye.com/product/woocommerce-paypal-plus-plugin/#reviews', __('Write a Review', 'woo-paypal-plus')),
        );
        return array_merge($custom_actions, $actions);
    }
    
    public function angelleye_paypal_plus_ipn_url($ipn_url) {
        if (!defined('PIW_PLUGIN_BASENAME')) {
            return $ipn_url;
        } else {
            return add_query_arg( 'keys', 'paypal_plus', site_url('?AngellEYE_Paypal_Ipn_For_Wordpress&action=ipn_handler') );
        } 
    }
    
    public function angelleye_paypal_plus_remove_approvalurl() {
	$wc_ajax_request = '';
	if( !empty($_GET['wc-ajax']) ) {
	    $wc_ajax_request = sanitize_text_field( $_GET['wc-ajax'] );
	}
	if( !empty($wc_ajax_request) && ($wc_ajax_request != 'update_order_review' && $wc_ajax_request != 'checkout')){
	    unset(WC()->session->approvalurl);
	}
    }

    public function angelleye_paypal_plus_locate_template($template, $template_name, $template_path) {
        if( $template_name != 'checkout/payment.php' ) {
            return $template;
        }
        global $woocommerce;
        $_template = $template;
        if ( ! $template_path ) {
            $template_path = $woocommerce->template_url;
        }
        $plugin_path = PAYPAL_DIR_PATH . '/templates/';
        $template = locate_template(array($template_path . $template_name, $template_name));
        if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
            $template = $plugin_path . $template_name;
        }
        if ( ! $template ) {
            $template = $_template;
        }
        return $template;
   }
   
   public function angelleye_paypal_plus_changed_payment_method() {
        $paypalplus_session = json_decode(stripslashes($_COOKIE['paypalplus_session_v2']), true);
        if( !empty($paypalplus_session['paymentMethod'])) {
            $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
            foreach ( $payment_gateways as $gateway ) {
                if( $gateway->title == $paypalplus_session['paymentMethod']) {
                    $_POST['payment_method'] = $gateway->id;
                    return true;
                }
            }
            if (strpos($paypalplus_session['paymentMethod'], 'pp') !== false) {
                $_POST['payment_method'] = 'paypal_plus';
                return true;
            }
        }
   }

    public function angelleye_paypal_plus_br_woocommerce_billing_fields($address_fields) {
        $address_fields['billing_persontype'] = array(
            'type' => 'select',
            'label' => __('Person type', 'woo-paypal-plus'),
            'class' => array('form-row-wide', 'person-type-field'),
            'input_class' => array('form-row-wide', 'address-field', 'wc-enhanced-select'),
            'required' => true,
            'options' => array(
                '1' => __('Individuals', 'woo-paypal-plus'),
                '2' => __('Legal Person', 'woo-paypal-plus'),
            ),
            'priority' => 1000,
        );
        $address_fields['billing_cpf_cnpj'] = array(
            'label' => __('CPF / CNPJ', 'woo-paypal-plus'),
            'required' => true,
            'class' => array('form-row-wide', 'address-field'),
            'priority' => 1001,
        );
        return $address_fields;
    }
    
    public function angelleye_paypal_plus_pre_set_site_transient_update_plugins($value, $transient) {
        if($transient == 'update_plugins') {
            if( !empty($value->no_update['woo-paypal-plus/woo-paypal-plus.php']) ) {
                unset($value->no_update['woo-paypal-plus/woo-paypal-plus.php']);
            }
        }
        return $value;
    }
    
    public function angelleye_email_subject_customer_paid_for_order($subject, $order) {
        $order_id = version_compare(WC_VERSION, '3.0', '<') ? $order->id : $order->get_id();
        $_instruction_type = get_post_meta($order_id, 'instruction_type', true);
        if ($_instruction_type == 'PAY_UPON_INVOICE') {
            $subject = str_replace("Payment received", "Order Received - Payment Instructions Included", $subject);
        }
        return $subject;
    }

    public function angelleye_email_heading_customer_paid_for_order($heading, $order) {
        $order_id = version_compare(WC_VERSION, '3.0', '<') ? $order->id : $order->get_id();
        $_instruction_type = get_post_meta($order_id, 'instruction_type', true);
        if ($_instruction_type == 'PAY_UPON_INVOICE') {
            $heading = str_replace("Payment received", "Order Received - Payment Instructions Included", $heading);
        }
        return $heading;
    }

}
