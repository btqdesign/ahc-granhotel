<?php

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\ItemList;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\Refund;
use PayPal\Api\Sale;
use PayPal\Api\PaymentOptions;

class Woo_Paypal_Plus_Gateway extends WC_Payment_Gateway {

    public $log_enabled = false;
    public $add_log = false;
    public $load_setting = false;
    public $calculation;

    public function __construct() {
        $this->id = 'paypal_plus';
        $this->icon = apply_filters('woocommerce_paypal_plus_icon', '');
        $this->has_fields = true;
        $this->home_url = is_ssl() ? home_url('/', 'https') : home_url('/');
        $this->relay_response_url = WC()->api_request_url( 'Woo_Paypal_Plus_Gateway' );
        $this->method_title = __('PayPal Plus', 'woo-paypal-plus');
        $this->secure_token_id = '';
        $this->securetoken = '';
        $this->supports = array(
            'products',
            'refunds'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $testmode = $this->get_option('testmode', 'no');
        $this->mode = ($testmode == 'yes') ? 'SANDBOX' : 'LIVE';
        if ($this->mode == "LIVE") {
            $this->rest_client_id = $this->get_option('rest_client_id');
            $this->rest_secret_id = $this->get_option('rest_secret_id');
        } else {
            $this->rest_client_id = $this->get_option('rest_client_id_sandbox');
            $this->rest_secret_id = $this->get_option('rest_secret_id_sandbox');
        }
        $this->debug = 'yes' === $this->get_option( 'debug', 'no' );
        $this->log_enabled    = $this->debug;
        $this->invoice_prefix = $this->get_option('invoice_prefix');
        $cancel_page_id = $this->get_option('cancel_url', wc_get_page_id( 'checkout' ));
        $this->cancel_url = $this->angelleye_paypal_plus_cancel_page_url($cancel_page_id);
        $this->allowed_currencies = apply_filters('woocommerce_paypal_plus_allowed_currencies', array('EUR', 'CAD', 'BRL', 'MXN'));
        $this->enabled = $this->get_option('enabled', 'no');
        $this->brand_name = $this->get_option('brand_name', get_bloginfo('name'));
        $this->checkout_logo = $this->get_option('checkout_logo', false);
        $this->order_cancellations = $this->get_option('order_cancellations', 'disabled');
        $this->supportedLocale = array(
		'da_DK', 'de_DE', 'en_AU', 'en_GB', 'en_US', 'es_ES', 'fr_CA', 'fr_FR', 'es_MX',
		'he_IL', 'id_ID', 'it_IT', 'ja_JP', 'nl_NL', 'no_NO', 'pl_PL', 'pt_BR',
		'pt_PT', 'ru_RU', 'sv_SE', 'th_TH', 'tr_TR', 'zh_CN', 'zh_HK', 'zh_TW',
	);
        $this->countryLocale = array('BR' => 'pt_BR', 'MX' => 'es_MX', 'DE' => 'de_DE');
        $this->country = apply_filters( 'woocommerce_paypal_plus_country', $this->get_option('country', 'DE'));
        $this->language = apply_filters( 'woocommerce_paypal_plus_language', $this->get_paypal_plus_locale() );
        $legal_note = __('Händler hat die Forderung gegen Sie im Rahmen eines laufenden Factoringvertrages an die PayPal (Europe) S.àr.l. et Cie, S.C.A. abgetreten. Zahlungen mit schuldbefreiender Wirkung können nur an die PayPal (Europe) S.àr.l. et Cie, S.C.A. geleistet werden.', 'woo-paypal-plus');
        $this->legal_note = $this->get_option('legal_note', $legal_note);
        $this->email_notify_order_cancellations = isset($this->settings['email_notify_order_cancellations']) && $this->settings['email_notify_order_cancellations'] == 'yes' ? true : false;
        $this->experience_profile_id = $this->angelleye_paypal_plus_get_experience_profile_id();
        $thirdPartyPaymentMethods = $this->get_option('thirdPartyPaymentMethods', 'no');
        $this->thirdPartyPaymentMethods = ($thirdPartyPaymentMethods == 'no') ? false : true;
        $this->disable_shipping = 'yes' === $this->get_option( 'disable_shipping', 'no' );
        $this->no_shipping = ( $this->disable_shipping == true) ? 1 : 0;
        if( $this->country != 'DE' ) {
            $this->no_shipping == 1;
        }
        $this->pay_upon_invoice_instructions = $this->get_option( 'pay_upon_invoice_instructions', __('Please transfere the complete amount to the bank account provided below.', 'woo-paypal-plus') );
        $this->disable_instant_order_confirmation = 'yes' === $this->get_option( 'disable_instant_order_confirmation', 'no' );
        if ( $this->country == 'US' || $this->country == 'DE') {
            
            add_action('woocommerce_receipt_paypal_plus', array($this, 'receipt_page_for_us_de')); // Payment form hook
        } else {
            $this->order_button_text = apply_filters('paypal_plus_order_button_text', __('Pasar a Pagar', 'woo-paypal-plus'), $this->country, $this->language);
            add_action('woocommerce_receipt_paypal_plus', array($this, 'receipt_page_for_br_mx')); // Payment form hook
        }
        add_action('woocommerce_api_' . strtolower(get_class()), array($this, 'executepay'), 12);
        include_once( 'lib/autoload.php' ); //include PayPal SDK
        if (!defined("PP_CONFIG_PATH")) {
            define("PP_CONFIG_PATH", __DIR__);
        }
        add_action('woocommerce_thankyou_paypal_plus', array($this, 'thankyou_page'), 10, 1);
        add_action('woocommerce_view_order', array($this, 'thankyou_page'), 9, 1);
        
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        add_action('woocommerce_email_customer_details', array($this, 'angelleye_paypal_plus_legal_note'), 30, 3);
        if ($this->is_available()) {
            if( $this->thirdPartyPaymentMethods == true && ($this->country == 'US' || $this->country == 'DE')) {
                add_filter('woocommerce_update_order_review_fragments', array($this, 'angelleye_woocommerce_update_order_review_fragments'));
                add_action('woocommerce_review_order_before_payment', array($this, 'angelleye_paypal_plus_render_iframe')); // Payment form hook
            } 
            add_action('wp_enqueue_scripts', array($this, 'paypal_plus_frontend_scripts'), 998);
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'), 999);
            remove_action('template_redirect', 'wc_send_frame_options_header');
            include_once( 'class-woo-paypal-plus-paypal-ipn-handler.php' );
            $mode = 'SANDBOX' === $this->mode;
            new Woo_Paypal_Plus_Paypal_IPN_Handler( $mode );
            $this->calculation = new PayPal_Plus_Gateway_Calculation_AngellEYE();
        }
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'angelleye_paypal_plus_web_profile'), 10);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'), 9999);
        if ( in_array( 'woocommerce-germanized/woocommerce-germanized.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_filter( 'woocommerce_germanized_send_instant_order_confirmation', array($this, 'angelleye_woocommerce_germanized_send_instant_order_confirmation'), 10, 2 );
            add_filter( 'woocommerce_gzd_instant_order_confirmation', array($this, 'angelleye_woocommerce_gzd_instant_order_confirmation'), 10, 1 );
        }
        add_action('angelleye_paypal_plus_pay_by_invoice_instructions', array($this, 'angelleye_paypal_plus_pay_by_invoice_instructions'), 10, 1);
    }

    /**
     * Check if this gateway is enabled and available in the user's country
     * @access public
     * @return boolean
     */
    public function is_available() {
        if ($this->enabled === "yes") {
            
            if (!$this->rest_client_id || !$this->rest_secret_id) {
                return false;
            }
            if ($this->is_web_profile_created() == false) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function angelleye_paypal_plus_is_credentials_set() {
        if ($this->enabled === "yes") {
            if (!in_array(get_option('woocommerce_currency'), $this->allowed_currencies)) {
                return false;
            }
            if (!$this->rest_client_id || !$this->rest_secret_id) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function is_web_profile_created() {
        if (empty($this->experience_profile_id)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Admin Panel Options
     * - Settings
     *
     * @access public
     * @return void
     */
    public function admin_options() {
        ?>
        <h3><?php _e('PayPal Plus', 'woo-paypal-plus'); ?></h3>
        <p><?php _e('PayPal PLUS is a solution where PayPal offers PayPal, Credit Card and ELV as individual payment options on the payment selection page. The available payment methods are provided in a PayPal hosted iFrame.', 'woo-paypal-plus'); ?></p>
        <table class="form-table">
            <?php
            if ($this->is_web_profile_created() == false && $this->angelleye_paypal_plus_is_credentials_set()) {
                ?>
                <div class="error inline">
                    <p><strong><?php _e('PayPal PLUS Disabled', 'woo-paypal-plus'); ?></strong>: <?php _e('No experience profile id. Check Client ID and Secret ID and then save settings to create a new experience profile.', 'woo-paypal-plus'); ?></p>
                </div>
                <?php
            }
            ?>
            <?php
            if (!in_array(get_option('woocommerce_currency'), $this->allowed_currencies)) {
                ?>
                <div class="inline error"><p><strong><?php _e('PayPal Plus Notice', 'woo-paypal-plus'); ?></strong>: <?php _e('PayPal Plus only supports for the following currencies: EUR, CAD, BRL, MXN.  You will need to use a currency conversion plugin if you have your site setup with an unsupported currency.', 'woo-paypal-plus'); ?></p></div>
                <?php
                
            }
                $this->generate_settings_html();
                ?>
                <script type="text/javascript">
                    jQuery('#woocommerce_paypal_plus_testmode').change(function () {
                        jQuery("#woocommerce_paypal_plus_live_experience_profile_id").prop("readonly", true);
                        jQuery("#woocommerce_paypal_plus_sandbox_experience_profile_id").prop("readonly", true);
                        sandbox = jQuery('#woocommerce_paypal_plus_rest_client_id_sandbox, #woocommerce_paypal_plus_rest_secret_id_sandbox, #woocommerce_paypal_plus_sandbox_experience_profile_id').closest('tr'),
                        production = jQuery('#woocommerce_paypal_plus_rest_client_id, #woocommerce_paypal_plus_rest_secret_id, #woocommerce_paypal_plus_live_experience_profile_id').closest('tr');
                        if (jQuery(this).is(':checked')) {
                            sandbox.show();
                            production.hide();
                        } else {
                            sandbox.hide();
                            production.show();
                        }
                    }).change();
                </script>
                <?php
           
            ?>
        </table>
        <?php
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
        $require_ssl = '';
        if (!woo_paypal_plus_is_ssl()) {
            $require_ssl = __('This image requires an SSL host.  If your site is not running on https:// you may upload your image to <a target="_blank" href="http://www.sslpic.com">www.sslpic.com</a> and enter the image URL here.', 'woo-paypal-plus');
        }
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'woo-paypal-plus'),
                'type' => 'checkbox',
                'label' => __('Enable PayPal Plus', 'woo-paypal-plus'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'woo-paypal-plus'),
                'type' => 'text',
                'description' => __('This controls the name of the payment gateway the user sees during checkout.', 'woo-paypal-plus'),
                'default' => __('PayPal Plus', 'woo-paypal-plus')
            ),
            'description' => array(
                'title' => __('Description', 'woo-paypal-plus'),
                'type' => 'text',
                'description' => __('This controls the payment gateway description the user sees during checkout.', 'woo-paypal-plus'),
                'default' => __('PayPal Plus', 'woo-paypal-plus')
            ),
            'country' => array(
                'title' => __('PayPal Account Country', 'woo-paypal-plus'),
                'type' => 'select',
                'description' => __('Set this to the country your PayPal account is based in.', 'woo-paypal-plus'),
                'default' => 'DE',
                'options' => array(
                    'BR' => 'Brazil',
                    'MX' => 'Mexico',
                    'DE' => 'Germany',
                ),
            ),
            'testmode' => array(
                'title' => __('PayPal Sandbox', 'woo-paypal-plus'),
                'type' => 'checkbox',
                'label' => __('Enable PayPal Sandbox', 'woo-paypal-plus'),
                'default' => 'yes',
                'description' => sprintf(__('The PayPal sandbox can be used to test payments. You will need to <a target="_blank" href="%s">create a sandbox account</a> to use as a seller in order to test this way.', 'woo-paypal-plus'), 'https://www.angelleye.com/create-paypal-sandbox-account/?utm_source=paypal_plus_for_woocommerce&utm_medium=settings_page'),
            ),
            'rest_client_id_sandbox' => array(
                'title' => __('Sandbox Client ID', 'woo-paypal-plus'),
                'type' => 'password',
                'description' => sprintf(__('Enter your PayPal REST Sandbox API Client ID.  <a target="_blank" href="%s">View documentation</a>.', 'woo-paypal-plus'), 'https://www.angelleye.com/woocommerce-paypal-plus-setup-guide/?utm_source=paypal_plus_for_woocommerce&utm_medium=settings_page'),
                'default' => '',
                'class' => 'credential_field'
            ),
            'rest_secret_id_sandbox' => array(
                'title' => __('Sandbox Secret ID', 'woo-paypal-plus'),
                'type' => 'password',
                'description' => sprintf(__('Enter your PayPal REST Sandbox API Secret ID.  <a target="_blank" href="%s">View documentation</a>.', 'woo-paypal-plus'), 'https://www.angelleye.com/woocommerce-paypal-plus-setup-guide/?utm_source=paypal_plus_for_woocommerce&utm_medium=settings_page'),
                'default' => '',
                'class' => 'credential_field'
            ),
            'sandbox_experience_profile_id' => array(
                'title'       => __( 'Sandbox Experience Profile ID', 'woo-paypal-plus' ),
                'type'        => 'text',
                'description' => __( "This value will be automatically generated and populated here when you save your settings.", 'woo-paypal-plus' ),
                'default'     => '',
                'class' => 'credential_field readonly'
            ),
            'rest_client_id' => array(
                'title' => __('Live Client ID', 'woo-paypal-plus'),
                'type' => 'password',
                'description' => sprintf(__('Enter your PayPal REST Live API Client ID.  <a target="_blank" href="%s">View documentation</a>.', 'woo-paypal-plus'), 'https://www.angelleye.com/woocommerce-paypal-plus-setup-guide/?utm_source=paypal_plus_for_woocommerce&utm_medium=settings_page'),
                'default' => '',
                'class' => 'credential_field'
            ),
            'rest_secret_id' => array(
                'title' => __('Live Secret ID', 'woo-paypal-plus'),
                'type' => 'password',
                'description' => sprintf(__('Enter your PayPal REST Live API Secret ID.  <a target="_blank" href="%s">View documentation</a>.', 'woo-paypal-plus'), 'https://www.angelleye.com/woocommerce-paypal-plus-setup-guide/?utm_source=paypal_plus_for_woocommerce&utm_medium=settings_page'),
                'default' => '',
                'class' => 'credential_field'
            ),
            'live_experience_profile_id' => array(
                'title'       => __( 'Experience Profile ID', 'woo-paypal-plus' ),
                'type'        => 'text',
                'description' => __( "This value will be automatically generated and populated here when you save your settings.", 'woo-paypal-plus' ),
                'default'     => '',
                'class' => 'credential_field readonly'
            ),
            'invoice_prefix' => array(
                'title' => __('Invoice Prefix', 'woo-paypal-plus'),
                'type' => 'text',
                'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'woo-paypal-plus'),
                'default' => 'WC-PP-PLUS-',
                'desc_tip' => true,
            ),
            'cancel_url' => array(
                'title' => __('Cancel Page', 'woo-paypal-plus'),
                'description' => __('Sets the page users will be returned to if they click the Cancel link on the PayPal checkout pages.', 'woo-paypal-plus'),
                'type' => 'select',
                'options' => $this->angelleye_paypal_plus_cancel_page_urls(),
                'default' => wc_get_page_id( 'checkout' )
            ),
            'brand_name' => array(
                'title' => __('Brand Name', 'woo-paypal-plus'),
                'type' => 'text',
                'description' => __('This will be displayed as your brand / company name on the PayPal checkout pages.', 'woo-paypal-plus'),
                'default' => __(get_bloginfo('name'), 'woo-paypal-plus')
            ),
            'checkout_logo' => array(
                'title' => __('PayPal Checkout Logo (190x60px)', 'woo-paypal-plus'),
                'type' => 'text',
                'description' => __('Set the URL for a logo to be displayed on the PayPal checkout pages.', 'woo-paypal-plus') . $require_ssl,
                'default' => ''
            ),
            'disable_shipping' => array(
                'title' => __('Disable Shipping Requirements', 'woo-paypal-plus'),
                'type' => 'checkbox',
                'label' => __('Disable Shipping Requirements', 'woo-paypal-plus'),
                'default' => 'no',
                'description' => __('Check this option to remove shipping options during checkout. This is typically used when selling digital goods that do not require shipping', 'woo-paypal-plus')
            ),
            'order_cancellations' => array(
                'title' => __('Auto Cancel / Refund Orders ', 'woo-paypal-plus'),
                'label' => '',
                'description' => __('Allows you to cancel and refund orders that do not meet PayPal\'s Seller Protection criteria.', 'woo-paypal-plus'),
                'type' => 'select',
                'class' => 'paypal_plus_order_cancellations',
                'options' => array(
                    'no_seller_protection' => __('Do *not* have PayPal Seller Protection', 'woo-paypal-plus'),
                    'no_unauthorized_payment_protection' => __('Do *not* have PayPal Unauthorized Payment Protection', 'woo-paypal-plus'),
                    'disabled' => __('Do not cancel any orders', 'woo-paypal-plus'),
                ),
                'default' => 'disabled'
            ),
            'email_notify_order_cancellations' => array(
                'title' => __('Order Canceled/Refunded Email Notifications', 'woo-paypal-plus'),
                'label' => __('Enable buyer email notifications for Order canceled/refunded', 'woo-paypal-plus'),
                'type' => 'checkbox',
                'description' => __('This will send buyer email notifications for Order canceled/refunded when Auto Cancel / Refund Orders option is selected.', 'woo-paypal-plus'),
                'default' => 'no',
                'class' => 'paypal_plus_email_notify_order_cancellations'
            ),
            'legal_note' => array(
                'title' => __('Legal Note for PAY UPON INVOICE Payment', 'woo-paypal-plus'),
                'type' => 'textarea',
                'description' => __('legal note that will be added to the thank you page and emails.', 'woo-paypal-plus'),
                'default' => __('Händler hat die Forderung gegen Sie im Rahmen eines laufenden Factoringvertrages an die PayPal (Europe) S.àr.l. et Cie, S.C.A. abgetreten. Zahlungen mit schuldbefreiender Wirkung können nur an die PayPal (Europe) S.àr.l. et Cie, S.C.A. geleistet werden.', 'woo-paypal-plus'),
                'desc_tip' => false,
            ),
            'thirdPartyPaymentMethods' => array(
                'title' => __('Checkout Page Display', 'woo-paypal-plus'),
                'type' => 'checkbox',
                'label' => __('Replace checkout page payment gateways with PayPal Plus iFrame.', 'woo-paypal-plus'),
                'default' => 'no',
                'description' => __('Enable this option to replace the WooCommerce payment gateways with the PayPal Plus iFrame and then add additional gateways to the iFrame as 3rd party options.  Leave disabled to keep the WooCommerce payment gateways intact and simply add PayPal Plus to the list.', 'woo-paypal-plus'),
            ),
            'pay_upon_invoice_instructions' => array(
                'title'       => __( 'Pay upon Invoice Instructions', 'woo-paypal-plus' ),
                'type'        => 'textarea',
                'description' => __( 'Pay upon Invoice Instructions that will be added to the thank you page and emails.', 'woo-paypal-plus' ),
                'default'     => __('Please transfere the complete amount to the bank account provided below.', 'woo-paypal-plus'),
                'desc_tip'    => false,
            ),
            'debug' => array(
                'title' => __('Debug Log', 'woo-paypal-plus'),
                'type' => 'checkbox',
                'label' => __('Enable logging', 'woo-paypal-plus'),
                'default' => 'no',
                'description' => sprintf(__('Log PayPal events, such as Secured Token requests, inside <code>%s</code>', 'woo-paypal-plus'), wc_get_log_file_path('paypal_plus'))
            )
        );

        if ( in_array( 'woocommerce-germanized/woocommerce-germanized.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            $this->form_fields['disable_instant_order_confirmation'] = array(
                'title'  => __( 'Germanized Email Handling', 'woo-paypal-plus' ),
                'type' => 'checkbox',
                'label' => __('Disable Germanized email notification adjustments.', 'woo-paypal-plus'),
                'default' => 'no',
                'description' => __('The Germanized plugin triggers an order confirmation email before the checkout / payment flow is completed.  This causes confusion when the payment is canceled because the buyer still receives an order confirmation.  Check this option to disable the Germanized email notification updates and let WooCommerce send notifications like usual.', 'woo-paypal-plus'),
            );
        }
    }

    /**
     * There are no payment fields for paypal, but we want to show the description if set.
     *
     * @access public
     * @return void
     * */
    public function payment_fields() {
        if (!$this->is_available()) {
            return;
        }
        echo wpautop(wptexturize($this->description));
        if (($this->country == 'US' || $this->country == 'DE') && $this->thirdPartyPaymentMethods == false) {
            $this->angelleye_paypal_plus_de_ui();
        }
    }

    /**
     * Process the payment
     *
     * @access public
     * @return void
     * */
    public function process_payment($order_id) {
        $order = new WC_Order($order_id);
        if (isset(WC()->session->token)) {
            unset(WC()->session->paymentId);
            unset(WC()->session->PayerID);
        }
        return array(
            'result' => 'success',
            'redirect' => $order->get_checkout_payment_url(true)
        );
    }

    /**
     * Limit the length of item names
     * @param  string $item_name
     * @return string
     */
    public function paypal_plus_item_name($item_name) {
        if (strlen($item_name) > 36) {
            $item_name = substr($item_name, 0, 33) . '...';
        }
        return html_entity_decode($item_name, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * Limit the length of item desc
     * @param  string $item_desc
     * @return string
     */
    public function paypal_plus_item_desc($item_desc) {
        if (strlen($item_desc) > 127) {
            $item_desc = substr($item_desc, 0, 124) . '...';
        }
        return html_entity_decode($item_desc, ENT_NOQUOTES, 'UTF-8');
    }

    public function add_log($message) {
       if ( $this->log_enabled && $this->mode == 'LIVE') {
            if ( empty( $this->add_log ) ) {
                $this->add_log = new WC_Logger();
            }
            $this->add_log->add( 'paypal_plus', $message );
        }
    }

    public function getAuth() {
        $auth = new ApiContext(new OAuthTokenCredential($this->rest_client_id, $this->rest_secret_id));
        $auth->setConfig(array('mode' => $this->mode, 'http.headers.PayPal-Partner-Attribution-Id' => 'AngellEYE_Cart_Plus', 'log.LogEnabled' => true, 'log.LogLevel' => 'DEBUG', 'log.FileName' => wc_get_log_file_path('paypal_plus'), 'cache.enabled' => true, 'cache.FileName' => wc_get_log_file_path('paypal_plus_cache')));
        return $auth;
    }

    public function get_approvalurl() {
        global $woocommerce;
	if( !empty(WC()->session->approvalurl) ) {
	    return WC()->session->approvalurl;
	}
        if (!empty($_GET['key'])) {
            $order_key = $_GET['key'];
            $order_id = wc_get_order_id_by_order_key($order_key);
            $order = new WC_Order($order_id);
            WC()->session->ppp_order_id = $order_id;
        } else {
            $order = null;
            $order_id = null;
        }
        try {
            try {
                if(is_null($order_id)) {
                    $PaymentData = $this->calculation->cart_calculation();
                } else {
                    $PaymentData = $this->calculation->order_calculation($order_id);
                }
                $OrderItems = array();
                $items_list = new ItemList();
                foreach ($PaymentData['order_items'] as $item) {
                    $item_object = new Item();
                    $item_object->setName($item['name'])
                            ->setCurrency(get_woocommerce_currency())
                            ->setQuantity($item['qty'])
                            ->setPrice($item['amt']);
                    //array_push($OrderItems, $item);
                    $items_list->addItem($item_object);
                 }
                $redirectUrls = new RedirectUrls();
                $redirectUrls->setReturnUrl($this->relay_response_url);
                $redirectUrls->setCancelUrl($this->cancel_url);
                $payer = new Payer();
                $payer->setPaymentMethod("paypal");
                $details = new Details();
                $details->setShipping($PaymentData['shippingamt']);
                $details->setTax($PaymentData['taxamt']);
                $details->setSubtotal($PaymentData['itemamt']); 
                $amount = new Amount();
                $amount->setCurrency(get_woocommerce_currency());
                $amount->setTotal(paypal_plus_number_format($this->get_order_total()));
                $amount->setDetails($details);
                
                if( $order_id != null) { 
                    $billing_first_name = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_first_name : $order->get_billing_first_name();
                    $billing_last_name = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_last_name : $order->get_billing_last_name();
                    $billing_address_1 = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_address_1 : $order->get_billing_address_1();
                    $billing_address_2 = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_address_2 : $order->get_billing_address_2();
                    $billing_city = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_city : $order->get_billing_city();
                    $billing_state = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_state : $order->get_billing_state();
                    $billing_postcode = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_postcode : $order->get_billing_postcode();
                    $billing_country = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_country : $order->get_billing_country();
                    $items_list->setShippingAddress(json_decode('{
                        "recipient_name": "' . $billing_first_name . ' ' . $billing_last_name . '",
                        "line1": "' . $billing_address_1 . '",
                        "line2": "' . $billing_address_2 . '",
                        "city": "' . $billing_city . '",
                        "state": "' . $billing_state . '",
                        "postal_code": "' . $billing_postcode . '",
                        "country_code": "' . $billing_country . '"
                    }'));
                }
               
                $transaction = new Transaction();
                if( $this->country != 'DE' ) {
                    $payment_options = new PaymentOptions();
                    $payment_options->setAllowedPaymentMethod('IMMEDIATE_PAY');
                    $transaction->setPaymentOptions($payment_options);
                }
                $transaction->setAmount($amount);
                $transaction->setDescription('This is the payment transaction description');
                if( $order_id != null) {
                    $order_key = version_compare(WC_VERSION, '3.0', '<') ? $order->order_key : $order->get_order_key();
                    $transaction->setCustom(json_encode(array('order_id' => $order_id, 'order_key' => $order_key)));
                }
                $transaction->setItemList($items_list);
                $transaction->setNotifyUrl( apply_filters('angelleye_paypal_plus_ipn_url', WC()->api_request_url( 'Woo_Paypal_Plus_Gateway' )));
                $payment = new Payment();
                $payment->setExperienceProfileId($this->experience_profile_id);
                $payment->setRedirectUrls($redirectUrls);
                $payment->setIntent("sale");
                $payment->setPayer($payer);
                $payment->setTransactions(array($transaction));
                try {
                    $payment->create($this->getAuth());
                } catch (PayPal\Exception\PayPalConnectionException $ex) {
                    wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
                    $this->angelleye_paypal_plus_redirect();
                } catch (Exception $ex) {
                    wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
                    $this->angelleye_paypal_plus_redirect();
                }
                $this->add_log(print_r($payment, true));
                if ($payment->state == "created" && $payment->payer->payment_method == "paypal") {
                    WC()->session->paymentId = $payment->id;
                    WC()->session->approvalurl = isset($payment->links[1]->href) ? $payment->links[1]->href : false;
                    return isset($payment->links[1]->href) ? $payment->links[1]->href : false;
                } else {
                    wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
                    $this->angelleye_paypal_plus_redirect();
                }
            } catch (Exception $ex) {
                wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
                $this->angelleye_paypal_plus_redirect();
            }
        } catch (Exception $ex) {
            wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
            $this->angelleye_paypal_plus_redirect();
        }
    }

    public function receipt_page_for_us_de($order_id) {
        $order = new WC_Order($order_id);
        global $woocommerce;
        WC()->session->ppp_order_id = $order_id;
        $order_key = version_compare(WC_VERSION, '3.0', '<') ? $order->order_key : $order->get_order_key();
        $PaymentData = $this->calculation->order_calculation($order_id);
        $payment = new Payment();
        $payment->setId(WC()->session->paymentId);
        $patchReplace = new \PayPal\Api\Patch();
        $patchReplace->setOp('replace')
                ->setPath('/transactions/0/amount')
                ->setValue(json_decode('{
                    "total": "' . paypal_plus_number_format($order->get_total()) . '",
                    "currency": "' . get_woocommerce_currency() . '",
                    "details": {
                        "subtotal": "' . $PaymentData['itemamt'] . '",
                        "shipping": "' . $PaymentData['shippingamt'] . '",
                        "tax":"' . $PaymentData['taxamt'] . '"
                    }
                }'));

        $patchRequest = new \PayPal\Api\PatchRequest();
        $invoice_number = preg_replace("/[^a-zA-Z0-9]/", "", $order->get_order_number());
        $patchAdd_custom = new \PayPal\Api\Patch();
        $patchAdd_custom->setOp('add')->setPath('/transactions/0/custom')->setValue(json_encode(array('order_id' => $order_id, 'order_key' => $order_key)));
        
        $billing_first_name = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_first_name : $order->get_billing_first_name();
        $billing_last_name = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_last_name : $order->get_billing_last_name();
        $billing_address_1 = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_address_1 : $order->get_billing_address_1();
        $billing_address_2 = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_address_2 : $order->get_billing_address_2();
        $billing_city = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_city : $order->get_billing_city();
        $billing_state = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_state : $order->get_billing_state();
        $billing_postcode = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_postcode : $order->get_billing_postcode();
        $billing_country = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_country : $order->get_billing_country();
                
        if (!empty($billing_country)) {
            $patchAdd = new \PayPal\Api\Patch();
            $patchAdd->setOp('add')
                    ->setPath('/transactions/0/item_list/shipping_address')
                    ->setValue(json_decode('{
                    "recipient_name": "' . $billing_first_name . ' ' . $billing_last_name . '",
                    "line1": "' . $billing_address_1 . '",
                    "line2": "' . $billing_address_2 . '",
                    "city": "' . $billing_city . '",
                    "state": "' . $billing_state . '",
                    "postal_code": "' . $billing_postcode . '",
                    "country_code": "' . $billing_country . '"
                }'));
            $patchAddone = new \PayPal\Api\Patch();
            $patchAddone->setOp('add')->setPath('/transactions/0/invoice_number')->setValue($this->invoice_prefix . $invoice_number);
            $patchRequest->setPatches(array($patchAdd, $patchReplace, $patchAddone, $patchAdd_custom));
        } else {
            $patchAdd = new \PayPal\Api\Patch();
            $patchAdd->setOp('add')->setPath('/transactions/0/invoice_number')->setValue($this->invoice_prefix . $invoice_number);
            $patchRequest->setPatches(array($patchAdd, $patchReplace, $patchAdd_custom));
        }
         
        
        try {
            $result = $payment->update($patchRequest, $this->getAuth());
            if ($result == true) {
                ?>
                 <script>
                        function paypal_plus_redirect() {
                            jQuery.blockUI({
                                message: "<?php echo esc_js(__('Thank you for your order. We are now redirecting you to PayPal to make payment.', 'woo-paypal-plus')) ?>",
                                baseZ: 99999,
                                overlayCSS:
                                        {
                                            background: "#fff",
                                            opacity: 0.6
                                        },
                                css: {
                                    padding: "20px",
                                    zindex: "9999999",
                                    textAlign: "center",
                                    color: "#555",
                                    border: "3px solid #aaa",
                                    backgroundColor: "#fff",
                                    cursor: "wait",
                                    lineHeight: "24px"
                                }
                            });
                            if (typeof PAYPAL != "undefined") {
                                PAYPAL.apps.PPP.doCheckout();
                            } else {
                                setTimeout(function () {
                                    PAYPAL.apps.PPP.doCheckout();
                                }, 500);
                            }
                        }
                        jQuery(window).load(function () {
                            paypal_plus_redirect();
                        });
                        jQuery(document).ready(function () {
                            paypal_plus_redirect();
                        });
                </script>
                <?php
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
            wp_redirect($woocommerce->cart->get_cart_url());
            exit;
        } catch (Exception $ex) {
            wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
            wp_redirect($woocommerce->cart->get_cart_url());
            exit;
        }
    }

    public function receipt_page_for_br_mx($order_id) {
        $order = new WC_Order($order_id);
        $this->angelleye_paypal_plus_br_mx_ui($order);
    }

    public function executepay() {
        if (isset($_GET["token"]) && !empty($_GET["token"]) && isset($_GET["PayerID"]) && !empty($_GET["PayerID"])) {
            global $woocommerce;
            WC()->session->token = $_GET["token"];
            $this->angelleye_paypal_plus_patchrequest();
            WC()->session->PayerID = $_GET["PayerID"];
            $order = new WC_Order(WC()->session->ppp_order_id);
            $order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
            $order_key = version_compare(WC_VERSION, '3.0', '<') ? $order->order_key : $order->get_order_key();
            $execution = new PaymentExecution();
            $execution->setPayerId(WC()->session->PayerID);
            try {
                $payment = Payment::get(WC()->session->paymentId, $this->getAuth());
                $payment->execute($execution, $this->getAuth());
                $transactions = $payment->getTransactions();
                $relatedResources = $transactions[0]->getRelatedResources();
                $sale = $relatedResources[0]->getSale();
                $saleId = $sale->getId();
                $ProtectionEligibility = $sale->getProtectionEligibility();
                if ($payment->state == "approved") {
                    if ($this->angelleye_woocommerce_sellerprotection_should_cancel_order($saleId, $ProtectionEligibility)) {
                        $this->add_log('Order ' . $order_id . ' (' . $saleId . ') did not meet our Seller Protection requirements. Cancelling and refunding order.');
                        $order->add_order_note(__('Transaction did not meet our Seller Protection requirements. Cancelling and refunding order.', 'woo-paypal-plus'));
                        $admin_email = get_option("admin_email");
                        if ($this->email_notify_order_cancellations == true) {
                            if (isset($user_email_address) && !empty($user_email_address)) {
                                wp_mail($user_email_address, __('PayPal Plus payment declined due to our Seller Protection Settings', 'woo-paypal-plus'), __('Order #', 'woo-paypal-plus') . $order_id);
                            }
                        }
                        wp_mail($admin_email, __('PayPal Plus payment declined due to our Seller Protection Settings', 'woo-paypal-plus'), __('Order #', 'woo-paypal-plus') . $order_id);
                        update_post_meta($order_id, '_transaction_id', $saleId);
                        $this->process_refund($order_id, $order->get_total(), __('There was a problem processing your order. Please contact customer support.', 'woo-paypal-plus'));
                        $order->cancel_order();
                        wc_add_notice(__('Thank you for your recent order. Unfortunately it has been cancelled and refunded. Please contact our customer support team.', 'woo-paypal-plus'), 'error');
                        wp_redirect(get_permalink(wc_get_page_id('cart')));
                        exit();
                    }
                    global $wpdb;
                    $instruction_type = '';
                    $this->add_log(sprintf(__('Response: %s', 'woo-paypal-plus'), print_r($payment, true)));
                    if( $this->disable_instant_order_confirmation == true ) {
                        do_action( 'woocommerce_before_pay_action', $order );
                    }
                    $payment_instruction_result = $payment->getPaymentInstruction();
                    if (isset($payment_instruction_result) && !empty($payment_instruction_result)) {
                        $instruction_type = $payment_instruction_result->getInstructionType();
                        if ($instruction_type == 'PAY_UPON_INVOICE') {
                            $reference_number = $payment_instruction_result->getReferenceNumber();
                            update_post_meta($order_id, 'reference_number', $reference_number);
                            update_post_meta($order_id, 'instruction_type', $instruction_type);
                            $payment_due_date = $payment_instruction_result->getPaymentDueDate();
                            update_post_meta($order_id, 'payment_due_date', $payment_due_date);
                            $RecipientBankingInstruction = $payment_instruction_result->getRecipientBankingInstruction();
                            $bank_name = $RecipientBankingInstruction->getBankName();
                            update_post_meta($order_id, 'bank_name', $bank_name);
                            $account_holder_name = $RecipientBankingInstruction->getAccountHolderName();
                            update_post_meta($order_id, 'account_holder_name', $account_holder_name);
                            $international_bank_account_number = $RecipientBankingInstruction->getInternationalBankAccountNumber();
                            update_post_meta($order_id, 'international_bank_account_number', $international_bank_account_number);
                            $bank_identifier_code = $RecipientBankingInstruction->getBankIdentifierCode();
                            update_post_meta($order_id, 'bank_identifier_code', $bank_identifier_code);
                            update_post_meta($order_id, 'payment_due_date', $payment_due_date);
                            $payment_instruction['reference_number'] = $reference_number;
                            $payment_instruction['instruction_type'] = $instruction_type;
                            $payment_instruction['instruction_type'] = $instruction_type;
                            $payment_instruction['recipient_banking_instruction']['bank_name'] = $bank_name;
                            $payment_instruction['recipient_banking_instruction']['account_holder_name'] = $account_holder_name;
                            $payment_instruction['recipient_banking_instruction']['international_bank_account_number'] = $international_bank_account_number;
                            $payment_instruction['recipient_banking_instruction']['bank_identifier_code'] = $bank_identifier_code;
                            update_post_meta($order_id, '_payment_instruction_result', $payment_instruction);
                        }
                    }
                    $this->angelleye_paypal_plus_update_billing_address($order, $payment);
                    if ($sale->getState() == 'pending') {
                        $order->add_order_note(sprintf(__('PayPal Reason code: %s.', 'woo-paypal-plus'), $sale->getReasonCode()));
                        $order->update_status('on-hold');
                        
                    } elseif ($sale->getState() == 'completed') {
                        $order->add_order_note(__('PayPal Plus payment completed', 'woo-paypal-plus'));
                        $order->payment_complete($saleId);
                        $order->add_order_note(sprintf(__('%s payment approved! Trnsaction ID: %s', 'woo-paypal-plus'), $this->title, $saleId));
                        WC()->cart->empty_cart();
                    } else {
                        $order->update_status( 'on-hold', __( 'Awaiting payment', 'woocommerce' ) );
        		$order->reduce_order_stock();
                    }
                    if (!empty($_POST['rememberedCards'])) {
                        $current_user = wp_get_current_user();
                        if (0 == $current_user->ID) {
                            
                        } else {
                            update_user_meta($current_user->ID, 'rememberedCards', $_POST['rememberedCards']);
                        }
                    }
                    WC()->cart->empty_cart();
                    if (method_exists($order, 'get_checkout_order_received_url')) {
                        $redirect = $order->get_checkout_order_received_url();
                    } else {
                        $redirect = add_query_arg('key', $order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id'))));
                    }
                    wp_redirect($redirect);
                    exit();
                } else {
                    wc_add_notice(__('Error Payment state:' . $payment->state, 'woo-paypal-plus'), 'error');
                    wp_redirect($woocommerce->cart->get_cart_url());
                    exit;
                }
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
                wp_redirect($woocommerce->cart->get_cart_url());
                exit;
            } catch (Exception $ex) {
                wc_add_notice(__("Error processing checkout. Please try again.", 'woo-paypal-plus'), 'error');
                wp_redirect($woocommerce->cart->get_cart_url());
                exit;
            }
        }
    }

    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = wc_get_order($order_id);
        $this->add_log('Begin Refund');
        $this->add_log('Order: ' . print_r($order, true));
        $this->add_log('Transaction ID: ' . print_r($order->get_transaction_id(), true));
        if (!$order || !$order->get_transaction_id() || !$this->rest_client_id || !$this->rest_secret_id) {
            return false;
        }
        if ($reason) {
            if (255 < strlen($reason)) {
                $reason = substr($reason, 0, 252) . '...';
            }
            $reason = html_entity_decode($reason, ENT_NOQUOTES, 'UTF-8');
        }
        $sale = Sale::get($order->get_transaction_id(), $this->getAuth());
        $amt = new Amount();
        $amt->setCurrency($order->get_order_currency());
        $amt->setTotal(paypal_plus_number_format($amount));
        $refund = new Refund();
        $refund->setAmount($amt);
        try {
            $this->add_log('Refund Request: ' . print_r($refund, true));
            $refundedSale = $sale->refund($refund, $this->getAuth());
            if ($refundedSale->state == 'completed') {
                $order->add_order_note('Refund Transaction ID:' . $refundedSale->getId());
                if (isset($reason) && !empty($reason)) {
                    $order->add_order_note('Reason for Refund :' . $reason);
                }
                $max_remaining_refund = wc_format_decimal($order->get_total() - $order->get_total_refunded());
                if (!$max_remaining_refund > 0) {
                    $order->update_status('refunded');
                }
                if (ob_get_length())
                    ob_end_clean();
                return true;
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            $error_data = json_decode($ex->getData());
            if (is_object($error_data) && !empty($error_data)) {
                $error_message = ($error_data->message) ? $error_data->message : $error_data->information_link;
                return new WP_Error('paypal_plus_refund-error', $error_message);
            } else {
                return new WP_Error('paypal_plus_refund-error', $ex->getData());
            }
        } catch (Exception $ex) {
              return new WP_Error('paypal_plus_refund-error', $ex->getMessage());
        }
    }
    
    public function angelleye_paypal_plus_web_profile() {
        $this->load_setting = new Woo_Paypal_Plus_Gateway();
        $comparison = $this->angelleye_paypal_plus_setting_comparison();
        $testmode = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('testmode'), 'no');
        if( $comparison == false ) {
            $sandbox_experience_profile_id = $this->angelleye_paypal_plus_get_option('sandbox_experience_profile_id', '');
            if ( !empty($sandbox_experience_profile_id) ) {
                $this->angelleye_paypal_plus_delete_option('sandbox_experience_profile_id');
            }
            $live_experience_profile_id = $this->angelleye_paypal_plus_get_option('live_experience_profile_id', '');
            if ( !empty($live_experience_profile_id) ) {
                $this->angelleye_paypal_plus_update_option('live_experience_profile_id');
            }
            $experience_profile_id = get_option('_experience_profile_id', '');
            if( !empty($experience_profile_id) ) {
                delete_option('_experience_profile_id');
            }
            $this->create_web_experience_profile();
        } else {
            if( $testmode == 'yes' ) {
                $sandbox_experience_profile_id = $this->angelleye_paypal_plus_get_option('sandbox_experience_profile_id', '');
                if ( empty($sandbox_experience_profile_id) ) {
                    $this->create_web_experience_profile();
                } elseif( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_client_id_sandbox'), 'null') != $this->get_option('rest_client_id_sandbox') ) {
                    $sandbox_experience_profile_id = $this->angelleye_paypal_plus_get_option('sandbox_experience_profile_id', '');
                    if ( !empty($sandbox_experience_profile_id) ) {
                        $this->angelleye_paypal_plus_delete_option('sandbox_experience_profile_id');
                    }
                    $experience_profile_id = get_option('_experience_profile_id', '');
                    if( !empty($experience_profile_id) ) {
                        delete_option('_experience_profile_id');
                    }
                    $this->create_web_experience_profile();
                } elseif( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_secret_id_sandbox'), null) != $this->get_option('rest_secret_id_sandbox') ) {
                    $sandbox_experience_profile_id = $this->angelleye_paypal_plus_get_option('sandbox_experience_profile_id', '');
                    if ( !empty($sandbox_experience_profile_id) ) {
                        $this->angelleye_paypal_plus_delete_option('sandbox_experience_profile_id');
                    }
                    $experience_profile_id = get_option('_experience_profile_id', '');
                    if( !empty($experience_profile_id) ) {
                        delete_option('_experience_profile_id');
                    }
                    $this->create_web_experience_profile();
                }
            } else {
                $live_experience_profile_id = $this->angelleye_paypal_plus_get_option('live_experience_profile_id', '');
                if ( empty($live_experience_profile_id) ) {
                    $this->create_web_experience_profile();
                } elseif( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_client_id'), null) != $this->get_option('rest_client_id') ) {
                    $live_experience_profile_id = $this->angelleye_paypal_plus_get_option('live_experience_profile_id', '');
                    if ( !empty($live_experience_profile_id) ) {
                        $this->angelleye_paypal_plus_update_option('live_experience_profile_id');
                    }
                    $experience_profile_id = get_option('_experience_profile_id', '');
                    if( !empty($experience_profile_id) ) {
                        delete_option('_experience_profile_id');
                    }
                    $this->create_web_experience_profile();
                } elseif( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_secret_id'), null) != $this->get_option('rest_secret_id') ) {
                    $live_experience_profile_id = $this->angelleye_paypal_plus_get_option('live_experience_profile_id', '');
                    if ( !empty($live_experience_profile_id) ) {
                        $this->angelleye_paypal_plus_update_option('live_experience_profile_id');
                    }
                    $experience_profile_id = get_option('_experience_profile_id', '');
                    if( !empty($experience_profile_id) ) {
                        delete_option('_experience_profile_id');
                    }
                    $this->create_web_experience_profile();
                }
            }
        }
    }

    public function paypal_plus_frontend_scripts() {
        if (is_checkout()) {
            if (wp_script_is('storefront-sticky-payment', 'enqueued') && $this->is_available()) {
                wp_dequeue_script('storefront-sticky-payment');
            }
        }
    }

    public function get_transaction_url($order) {
        if ($this->mode == 'SANDBOX') {
            $this->view_transaction_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s';
        } else {
            $this->view_transaction_url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s';
        }
        return parent::get_transaction_url($order);
    }

    public function payment_scripts() {
        if ($this->country == 'US' || $this->country == 'DE') {
            wp_enqueue_script('paypal_plus', 'https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js', array(), WC_VERSION, false);
        } else {
            wp_enqueue_script('paypal_plus_dcc', 'https://www.paypalobjects.com/webstatic/ppplusdcc/ppplusdcc.min.js', array(), WC_VERSION, false);
        }
    }

    /*
     * Check payment gateway settings to cancel order based on transaction's seller protection response
     * @param WC_Payment_Gateway $this
     * @param array $PayPalResult
     * @return bool
     */

    public function angelleye_woocommerce_sellerprotection_should_cancel_order($saleId, $ProtectionEligibility) {
        $order_cancellation_setting = $this->order_cancellations;
        $txn_protection_eligibility_response = isset($ProtectionEligibility) ? $ProtectionEligibility : 'ERROR!';
        $txn_id = isset($saleId) ? $saleId : 'ERROR!';
        switch ($order_cancellation_setting) {
            case 'no_seller_protection':
                if ($txn_protection_eligibility_response != 'ELIGIBLE' && $txn_protection_eligibility_response != 'PARTIALLY_ELIGIBLE') {
                    $this->add_log('Transaction ' . $txn_id . ' is BAD. Setting: no_seller_protection, Response: ' . $txn_protection_eligibility_response);
                    return true;
                }
                $this->add_log('Transaction ' . $txn_id . ' is OK. Setting: no_seller_protection, Response: ' . $txn_protection_eligibility_response);
                return false;
            case 'no_unauthorized_payment_protection':
                if ($txn_protection_eligibility_response != 'ELIGIBLE') {
                    $this->add_log('Transaction ' . $txn_id . ' is BAD. Setting: no_unauthorized_payment_protection, Response: ' . $txn_protection_eligibility_response);
                    return true;
                }
                $this->add_log('Transaction ' . $txn_id . ' is OK. Setting: no_unauthorized_payment_protection, Response: ' . $txn_protection_eligibility_response);
                return false;
            case 'disabled':
                $this->add_log('Transaction ' . $txn_id . ' is OK. Setting: disabled, Response: ' . $txn_protection_eligibility_response);
                return false;
            default:
                $this->add_log('ERROR! order_cancellations setting for ' . $this->method_title . ' is not valid!');
                return true;
        }
    }

    public function angelleye_paypal_plus_de_ui() {
        $paypal_plus_country = $this->angelleye_paypal_plus_get_country();
        $location = $this->get_approvalurl();
        $third_party = array();
        $third_party = apply_filters('angelleye_paypal_plus_third_party_payment_gateways', $third_party, $is_general_list = true);
        ?>
        <div id="ppplus"> </div>
        <script type="application/javascript">
            var initPAYPLUS = function(){
                if(typeof PAYPAL != "undefined") {
                var ppp = PAYPAL.apps.PPP({
                "approvalUrl": "<?php echo $location; ?>",
                "placeholder": "ppplus",
                "useraction": "commit",
                "buttonLocation": "outside",
                "country":  "<?php echo $paypal_plus_country; ?>",
                "language": "<?php echo $this->language; ?>",
                "mode": "<?php echo strtolower($this->mode); ?>",
                <?php if (isset($third_party)): //check if we have third party payment  ?>
                    "thirdPartyPaymentMethods": <?php echo json_encode($third_party); ?>,
                <?php endif; ?>
                "showPuiOnSandbox": true,
                });
                } else {
                    setTimeout(initPAYPLUS, 500);
                }
            }
            initPAYPLUS();
            jQuery('#payment_method_paypal_plus').on('change', function() {
                initPAYPLUS();
            });
        </script>
        <?php
        if(defined('WMC_VERSION')) {
            ?>
            <script type="application/javascript">
                jQuery("#wizard").on('onStepChanged', function (event, currentIndex, priorIndex) {     
                     initPAYPLUS();
                });
            </script>
            <?php 
        }
        ?>
        <style type="text/css">
            #ppplus iframe {
                height: 100% !important;
                width: 100% !important;
                *width: 100% !important;
            }
        </style>
        <?php 
    }

    public function angelleye_paypal_plus_br_mx_ui($order) {
        $paypal_plus_country = $this->angelleye_paypal_plus_get_country();
        $location = $this->get_approvalurl();
        $rememberedCards = '';
        parse_str($location, $urlparams);
        $this->relay_response_url = add_query_arg('token', $urlparams['token'], $this->relay_response_url);
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $rememberedCards = get_user_meta($current_user->ID, 'rememberedCards', true);
        }
        $is_mobile = 'false';
        if (wp_is_mobile()) {
            $is_mobile = 'true';
        }
        $order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
        $cpf_cnpj_meta = get_post_meta($order_id, '_billing_cpf_cnpj', true);
        $cpf_cnpj = ( !empty($cpf_cnpj_meta) ) ? $cpf_cnpj_meta : '';
        
        $billing_cpf_cnpj = '';
        $person_type = get_post_meta( $order_id, '_billing_persontype', true );
        if ( !empty($person_type)  ) {
            if ( $person_type === '1' ) {
                $billing_cpf_cnpj = 'BR_CPF';
            } else if ( $person_type === '2' ) {
                $billing_cpf_cnpj = 'BR_CNPJ';
            }
        }
        $order_button_text = apply_filters('woocommerce_order_button_text', __('Pagar', 'woo-paypal-plus'));
        ?>
        <div id="payment" class="woocommerce-checkout-payment">
            <ul class="wc_payment_methods payment_methods methods"><li><div id="ppplus"> </div></li></ul>
            <div class="form-row place-order">
                <button
                    style="position:relative; top:0px;"
                    type="submit"
                    id="place_order"
                    class="button alt"
                    onclick="doContinue(); return false;">
        <?php echo $order_button_text; ?>
                </button>
            </div>

        </div>

    <script type="application/javascript">
            var ppp = PAYPAL.apps.PPP({
            "approvalUrl": "<?php echo $location; ?>",
            "placeholder": "ppplus",
            "useraction": "commit",
            "buttonLocation": "outside",
            "country":  "<?php echo $paypal_plus_country; ?>",
            "language": "<?php echo $this->language; ?>",
            "mode": "<?php echo strtolower($this->mode); ?>",
            "payerEmail": "<?php echo version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_email : $order->get_billing_email(); ?>",
            "payerPhone": "<?php echo version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_phone : $order->get_billing_phone(); ?>",
            "payerFirstName": "<?php echo version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_first_name : $order->get_billing_first_name(); ?>",
            "payerLastName": "<?php echo version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_last_name : $order->get_billing_last_name(); ?>",
            "payerTaxId": "<?php echo $cpf_cnpj; ?>", //blank for MX, but required
            "payerTaxIdType": "<?php echo $billing_cpf_cnpj; ?>", //blank for MX, but required
            "iframeHeight": "500",
            "disableContinue": "place_order",
            "enableContinue": "place_order",
            "miniBrowser": "0",
            "rememberedCards": "<?php echo $rememberedCards; ?>",
            "onContinue": function (rememberedcards, payerid, token, term) {
                return true; 
            },
            "onError": function() {
                jQuery( ".woocommerce" ).unblock();
            }
            });
            if (window.addEventListener) { 
                window.addEventListener("message", messageListener, false);
            } else if (window.attachEvent) {
                window.attachEvent("onmessage", messageListener);
            } else {
                throw new Error("Can't attach message listener");
            }
            function messageListener(event) {
                try {
                    var data = JSON.parse(event.data);
                    if (data.action == 'resizeHeightOfTheIframe') {

                    } else if (data.action == "checkout") {
                        if (data.result.state == "APPROVED") {
                            redirect_href = "<?php echo $this->relay_response_url; ?>" + '&pp_action=executepay&PayerID=' + data.result.payer.payer_info.payer_id;
                            var f = document.createElement('form');
                            f.action=redirect_href;
                            f.method='POST';
                            var i=document.createElement('input');
                            i.type='hidden';
                            i.name='rememberedCards';
                            i.value=data.result.rememberedCards;
                            f.appendChild(i);
                            document.body.appendChild(f);
                            f.submit();
                        } else {
                           jQuery( ".woocommerce" ).unblock();
                       }
                    } else if( data.action == "enableContinueButton") {
                            jQuery( ".woocommerce" ).unblock();
                    } else if( data.action == "disableContinueButton") {
                            jQuery( ".woocommerce" ).block({
                                message: null,
                                overlayCSS: {
                                    background: '#fff',
                                    opacity: 0.6
                                }
                            });
                    } 
                } catch (exc) {
                    jQuery( ".woocommerce" ).unblock();
                }
            }
            function doContinue() {
                jQuery( ".woocommerce" ).block({
                    message: null,
                    overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
                });
               ppp.doContinue();
            }

        </script>
        <style type="text/css">
            .woocommerce .form-row::after {
                content: " ";
                display: table;
                clear: both;
            }
        </style>
        <?php
    }

    public function is_paypal_plus_form_submitted() {
        $is_submitted = false;
        if (!empty($_COOKIE)) {
            $cookie = $_COOKIE;
            foreach ($cookie as $key => $value) {
                if (strpos($key, 'paypalplus_session') === 0) {
                    $is_submitted = true;
                }
            }
        }
        return $is_submitted;
    }

    public function angelleye_paypal_plus_patchrequest() {
        $order = new WC_Order(WC()->session->ppp_order_id);
        $order_id = WC()->session->ppp_order_id;
        global $woocommerce;
        $PaymentData = $this->calculation->order_calculation($order_id);
        $payment = new Payment();
        $payment->setId(WC()->session->paymentId);
        $patchReplace = new \PayPal\Api\Patch();
        $patchReplace->setOp('replace')
                ->setPath('/transactions/0/amount')
                ->setValue(json_decode('{
                    "total": "' . paypal_plus_number_format($order->get_total()) . '",
                    "currency": "' . get_woocommerce_currency() . '",
                    "details": {
                        "subtotal": "' . $PaymentData['itemamt'] . '",
                        "shipping": "' . $PaymentData['shippingamt'] . '",
                        "tax":"' . $PaymentData['taxamt'] . '"
                    }
                }'));

        $patchRequest = new \PayPal\Api\PatchRequest();
        $invoice_number = preg_replace("/[^a-zA-Z0-9]/", "", $order->get_order_number());
        
        $shipping_first_name = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_first_name : $order->get_shipping_first_name();
        $shipping_last_name = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_last_name : $order->get_shipping_last_name();
        $shipping_address_1 = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_address_1 : $order->get_shipping_address_1();
        $shipping_address_2 = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_address_2 : $order->get_shipping_address_2();
        $shipping_city = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_city : $order->get_shipping_city();
        $shipping_state = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_state : $order->get_shipping_state();
        $shipping_postcode = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_postcode : $order->get_shipping_postcode();
        $shipping_country = version_compare(WC_VERSION, '3.0', '<') ? $order->shipping_country : $order->get_shipping_country();
        
        if (!empty($shipping_country)) {
            $patchAdd = new \PayPal\Api\Patch();
            $patchAdd->setOp('add')
                    ->setPath('/transactions/0/item_list/shipping_address')
                    ->setValue(json_decode('{
                    "recipient_name": "' . $shipping_first_name . ' ' . $shipping_last_name . '",
                    "line1": "' . $shipping_address_1 . '",
                    "line2": "' . $shipping_address_2 . '",
                    "city": "' . $shipping_city . '",
                    "state": "' . $shipping_state . '",
                    "postal_code": "' . $shipping_postcode . '",
                    "country_code": "' . $shipping_country . '"
                }'));
            $patchAddone = new \PayPal\Api\Patch();
            $patchAddone->setOp('add')->setPath('/transactions/0/invoice_number')->setValue($this->invoice_prefix . $invoice_number);
            $patchRequest->setPatches(array($patchAdd, $patchReplace, $patchAddone));
        } else {
            $patchAdd = new \PayPal\Api\Patch();
            $patchAdd->setOp('add')->setPath('/transactions/0/invoice_number')->setValue($this->invoice_prefix . $invoice_number);
            $patchRequest->setPatches(array($patchAdd, $patchReplace));
        }
        try {
            $result = $payment->update($patchRequest, $this->getAuth());
            if ($result == true) {
                return true;
            } else {
                return false;
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
            wp_redirect($woocommerce->cart->get_cart_url());
            exit;
        } catch (Exception $ex) {
            wc_add_notice(__("Error processing checkout. Please try again. ", 'woo-paypal-plus'), 'error');
            wp_redirect($woocommerce->cart->get_cart_url());
            exit;
        }
    }

    public function get_posted_variable($variable, $default = '') {
        return ( isset($_POST[$variable]) ? $_POST[$variable] : $default );
    }

    public function thankyou_page($order_id) {
        $instruction_type = get_post_meta($order_id, 'instruction_type', true);
        if (empty($instruction_type) || $instruction_type != 'PAY_UPON_INVOICE') {
            return false;
        }
        $bank_name = get_post_meta($order_id, 'bank_name', true);
        $account_holder_name = get_post_meta($order_id, 'account_holder_name', true);
        $international_bank_account_number = get_post_meta($order_id, 'international_bank_account_number', true);
        $payment_due_date = get_post_meta($order_id, 'payment_due_date', true);
        $reference_number = get_post_meta($order_id, 'reference_number', true);
        $bank_identifier_code = get_post_meta($order_id, 'bank_identifier_code', true);
        if ( $this->pay_upon_invoice_instructions ) {
            echo wpautop( wptexturize( wp_kses_post( $this->pay_upon_invoice_instructions ) ) );
	}
        ?>
        <h2><?php _e('PayPal Bank Details', 'woo-paypal-plus'); ?></h2>
        <table class="shop_table order_details">
            <tbody>
        <?php if (!empty($bank_name)) : ?>
                    <tr>
                        <th scope="row"><?php echo __('Bank name:', 'woo-paypal-plus'); ?></th>
                        <td><span><?php echo $bank_name; ?></span></td>
                    </tr>
                <?php endif; ?>
        <?php if (!empty($account_holder_name)) : ?>
                    <tr>
                        <th scope="row"><?php echo __('Account holder name:', 'woo-paypal-plus'); ?></th>
                        <td><span><?php echo $account_holder_name; ?></span></td>
                    </tr>
                <?php endif; ?>
        <?php if (!empty($international_bank_account_number)) : ?>
                    <tr>
                        <th scope="row"><?php echo __('IBAN:', 'woo-paypal-plus'); ?></th>
                        <td><span><?php echo $international_bank_account_number; ?></span></td>
                    </tr>
                <?php endif; ?>
        <?php if (!empty($bank_identifier_code)) : ?>
                    <tr>
                        <th scope="row"><?php echo __('BIC:', 'woo-paypal-plus'); ?></th>
                        <td><span><?php echo $bank_identifier_code; ?></span></td>
                    </tr>
                <?php endif; ?>
        <?php if (!empty($payment_due_date)) : ?>
                    <tr>
                        <th scope="row"><?php echo __('Payment due date:', 'woo-paypal-plus'); ?></th>
                        <td><span><?php echo date_i18n(get_option('date_format'), strtotime($payment_due_date)); ?></span></td>
                    </tr>
                <?php endif; ?>
        <?php if (!empty($reference_number)) : ?>
                    <tr>
                        <th scope="row"><?php echo __('Reference:', 'woo-paypal-plus'); ?></th>
                        <td><span><?php echo $reference_number; ?></span></td>
                    </tr>
        <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    public function email_instructions($order, $sent_to_admin, $plain_text = false) {
        $order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
        $payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
        $instruction_type = get_post_meta($order_id, 'instruction_type', true);
        if ( !empty($instruction_type) &&  $instruction_type == 'PAY_UPON_INVOICE' && $this->pay_upon_invoice_instructions && ! $sent_to_admin && 'paypal_plus' === $payment_method ) {
          echo wpautop( wptexturize( $this->pay_upon_invoice_instructions ) ) . PHP_EOL;
          $this->bank_details($order_id);
        }
    }

    /**
     * Get bank details and place into a list format.
     *
     * @param int $order_id
     */
    private function bank_details($order_id = '') {
        $instruction_type = get_post_meta($order_id, 'instruction_type', true);
        if (empty($instruction_type) || $instruction_type != 'PAY_UPON_INVOICE') {
            return false;
        }
        $bank_name = get_post_meta($order_id, 'bank_name', true);
        $account_holder_name = get_post_meta($order_id, 'account_holder_name', true);
        $international_bank_account_number = get_post_meta($order_id, 'international_bank_account_number', true);
        $payment_due_date = get_post_meta($order_id, 'payment_due_date', true);
        $reference_number = get_post_meta($order_id, 'reference_number', true);
        $bank_identifier_code = get_post_meta($order_id, 'bank_identifier_code', true);

        echo '<h2 class="wc-bacs-bank-details-heading">' . __('PayPal Bank Details', 'woo-paypal-plus') . '</h2>' . PHP_EOL;

        echo '<ul class="wc-bacs-bank-details order_details bacs_details">' . PHP_EOL;

        $account_fields = array(
            'bank_name' => array(
                'label' => __('Bank name', 'woo-paypal-plus'),
                'value' => $bank_name
            ),
            'account_holder_name' => array(
                'label' => __('Account holder name', 'woo-paypal-plus'),
                'value' => $account_holder_name
            ),
            'iban' => array(
                'label' => __('IBAN', 'woo-paypal-plus'),
                'value' => $international_bank_account_number
            ),
            'bic' => array(
                'label' => __('BIC', 'woo-paypal-plus'),
                'value' => $bank_identifier_code
            ),
            'payment_due_date' => array(
                'label' => __('Payment due date', 'woo-paypal-plus'),
                'value' => $payment_due_date
            ),
            'reference_number' => array(
                'label' => __('Reference', 'woo-paypal-plus'),
                'value' => $reference_number
        ));

        foreach ($account_fields as $field_key => $field) {
            if (!empty($field['value'])) {
                echo '<li class="' . esc_attr($field_key) . '">' . esc_attr($field['label']) . ': <strong>' . wptexturize($field['value']) . '</strong></li>' . PHP_EOL;
            }
        }

        echo '</ul>';
    }

    public function angelleye_paypal_plus_legal_note($order, $sent_to_admin, $plain_text = false) {
        $order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
        $payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
        $instruction_type = get_post_meta($order_id, 'instruction_type', true);
        if (!empty($instruction_type) && $instruction_type == 'PAY_UPON_INVOICE') {
            if (!$sent_to_admin && 'paypal_plus' === $payment_method) {
                if ($this->legal_note) {
                    echo wpautop(wptexturize($this->legal_note)) . PHP_EOL;
                }
            }
        }
    }
    
    public function angelleye_get_ec_token_from_approval_url($url){
        $query_str = parse_url($url, PHP_URL_QUERY);
        parse_str($query_str, $query_params);
        return $query_params['token'];
    }
    
    public function angelleye_paypal_plus_cancel_page_urls() {
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        $pages = get_pages($args);
        $cancel_page = array();
        foreach ($pages as $p) {
            $cancel_page[$p->ID] = $p->post_title;
        }
        return $cancel_page;
    }
   
    public function angelleye_paypal_plus_cancel_page_url($page_id) {
        $cancel_page_url = 0 < $page_id ? get_permalink( $page_id ) : get_home_url();
	if ( $cancel_page_url ) {
            // Force SSL if needed
            if ( is_ssl() || 'yes' === get_option( 'woocommerce_force_ssl_checkout' ) ) {
                    $cancel_page_url = str_replace( 'http:', 'https:', $cancel_page_url );
            }
	}
	return apply_filters( 'angelleye_paypal_plus_get_cancel_page_url', $cancel_page_url );
    }
    
    public function angelleye_paypal_plus_update_billing_address($order = null, $payment = null) {
        $order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
        if( !empty($order_id) ) {
            if ( !empty($payment->payer->payer_info->billing_address->line1)) {
                $billing_address = array(
                    'first_name' => $payment->payer->payer_info->first_name,
                    'last_name'  => $payment->payer->payer_info->last_name,
                    'address_1'  => $payment->payer->payer_info->billing_address->line1,
                    'address_2'  => $payment->payer->payer_info->billing_address->line2,
                    'city'       => $payment->payer->payer_info->billing_address->city,
                    'state'      => $payment->payer->payer_info->billing_address->state,
                    'postcode'   => $payment->payer->payer_info->billing_address->postal_code,
                    'country'    => $payment->payer->payer_info->billing_address->country_code,
		);
                $order->set_address( $billing_address, $type = 'billing' );
            }
        }
    }
    
    public function angelleye_paypal_plus_render_iframe() {
        if (!$this->is_available() || WC()->cart->total <= 0) {
            return;
        }
        $paypal_plus_country = $this->angelleye_paypal_plus_get_country();
        $location = $this->get_approvalurl();
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $third_party = array();
        if (!empty($available_gateways)) {
            foreach ($available_gateways as $gateway) {
                if ($gateway->id != $this->id) {
                    $third_party[] = array(
                        'methodName' => $gateway->get_title(),
                        'description' => $gateway->get_description(),
                        'redirectUrl' => home_url(),
                        'imageUrl' => $this->angelleye_paypal_plus_get_icon($gateway)
                    );
                }
            }
        }
        $third_party = apply_filters('angelleye_paypal_plus_third_party_payment_gateways', $third_party, $is_general_list = false);
        ?>
        <div id="paypal_plus_container">
        <div id="ppplus"></div>
        <script type="application/javascript">
             var initPAYPLUS = function(){
                if(typeof PAYPAL != "undefined") {
                    var ppp = PAYPAL.apps.PPP({
                    "approvalUrl": "<?php echo $location; ?>",
                    "placeholder": "ppplus",
                    "useraction": "commit",
                    "buttonLocation": "outside",
                    "country":  "<?php echo $paypal_plus_country; ?>",
                    "language": "<?php echo $this->language; ?>",
                    "mode": "<?php echo strtolower($this->mode); ?>",
                    <?php if (isset($third_party)): //check if we have third party payment  ?>
                        "thirdPartyPaymentMethods": <?php echo json_encode($third_party); ?>,
                    <?php endif; ?>
                    "enableContinue": 'place_order',
                    });
                } else {
                    setTimeout(initPAYPLUS, 500);
                }
             }
             window.setInterval(function(){
                if (jQuery('#ppplus').is(':empty')){
                    initPAYPLUS();
                } 
              }, 2000);
            jQuery('#payment_method_paypal_plus, .woocommerce-checkout-review-order-table').on('change', function() {
                initPAYPLUS();
            });
            initPAYPLUS();
        </script>
        <?php
        
        if(defined('WMC_VERSION')) {
            ?>
            <script type="application/javascript">
                jQuery("#wizard").on('onStepChanged', function (event, currentIndex, priorIndex) {     
                     initPAYPLUS();
                });
            </script>
            <?php 
        }
        ?>
        <style type="text/css">
            #ppplus iframe {
                height: 100% !important;
                width: 100% !important;
                *width: 100% !important;
            }
            .payment_methods  {display:none}
        </style>
        </div>
        <?php
    }
    
    public function angelleye_paypal_plus_get_country() {
        $paypal_plus_country = '';
        $country = $this->get_posted_variable('country');
        $s_country = $this->get_posted_variable('s_country');
        if (!empty($country)) {
            $paypal_plus_country = $country;
        } elseif (!empty($s_country)) {
            $paypal_plus_country = $s_country;
        } else {
            $paypal_plus_country = $this->country;
        }
        return $paypal_plus_country;
    }
    
    public function angelleye_paypal_plus_get_wpml_locale() {
        $locale = false;
        if(defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id')){
            global $sitepress;
            if ( isset( $sitepress )) { // avoids a fatal error with Polylang
                $locale = $sitepress->get_current_language();
            } else if ( function_exists( 'pll_current_language' ) ) { // adds Polylang support
                $locale = pll_current_language('locale'); //current selected language requested on the broswer
            } else if ( function_exists( 'pll_default_language' ) ) {
                $locale = pll_default_language('locale'); //default lanuage of the blog
            }
        } 
        return $locale;
    }
    
    public function angelleye_paypal_plus_get_wp_locale() {
        $locale = false;
        if (get_locale() != '') {
            $locale = substr(get_locale(), 0, 5);
        }
        return $locale;
    }
    
    public function create_web_experience_profile() {
        $this->debug = 'yes' === $this->angelleye_paypal_plus_get_field_value($this->get_field_key('debug'), 'no');
        $this->log_enabled    = $this->debug;
        $this->disable_shipping = 'yes' === $this->angelleye_paypal_plus_get_field_value($this->get_field_key('disable_shipping'), 'no');
        $testmode = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('testmode'), 'no');
        $this->mode = ($testmode == 'yes') ? 'SANDBOX' : 'LIVE';
        $this->checkout_logo = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('checkout_logo'), false);
        $this->brand_name = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('brand_name'), get_bloginfo('name'));
        $this->country = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('country'), 'DE');
        
        if ( $this->mode == 'SANDBOX') {
            $this->rest_client_id = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_client_id_sandbox'), ''); 
            $this->rest_secret_id = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_secret_id_sandbox'), ''); 
        } else {
            $this->rest_client_id = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_client_id'), '');
            $this->rest_secret_id = $this->angelleye_paypal_plus_get_field_value($this->get_field_key('rest_secret_id'), '');
        }
        if( empty($this->rest_client_id) && empty($this->rest_secret_id) ) {
            return false;
        }
        $presentation = new \PayPal\Api\Presentation();
        if ($this->checkout_logo) {
            $presentation->setLogoImage($this->checkout_logo);
        }
        if ( !empty($this->brand_name) ) {
            $presentation->setBrandName($this->brand_name);
        }
        if ( !empty($this->country) ) {
            $presentation->setLocaleCode($this->country);
        }
        $inputFields = new \PayPal\Api\InputFields();
        $inputFields->setNoShipping($this->no_shipping)->setAddressOverride(1);
        $webProfile = new \PayPal\Api\WebProfile();
        $webProfile->setName(substr($this->brand_name . uniqid(), 0, 50))
                ->setInputFields($inputFields)
                ->setPresentation($presentation);
        try {
            $createProfileResponse = $webProfile->create($this->getAuth());
            if (!empty($createProfileResponse->id)) {
                if ( $this->mode == 'SANDBOX') {
                    $this->angelleye_paypal_plus_update_option('sandbox_experience_profile_id', $createProfileResponse->id);
                } else {
                    $this->angelleye_paypal_plus_update_option('live_experience_profile_id', $createProfileResponse->id);
                }
                $this->experience_profile_id = $createProfileResponse->id;
                return false;
            }
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
           ?>
            <div class="error inline">
		<p><?php echo __( 'Error trying to create experience profile ID.', 'woo-paypal-plus' ); ?></p>
            </div>
          <?php
          return false;
        } catch (Exception $ex) {
            ?>
            <div class="error inline">
		<p><?php echo __( 'Error trying to create experience profile ID.', 'woo-paypal-plus' ); ?></p>
            </div>
          <?php
          return false;
        }
    }
    
    public function angelleye_paypal_plus_setting_comparison() {
        $comparison = true;
        
        if( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('checkout_logo'), null) != $this->checkout_logo ) {
            return $comparison = false;
        }
        if( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('brand_name'), null) != $this->brand_name ) {
            return $comparison = false;
        }
        if( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('country'), null) != $this->country ) {
            return $comparison = false;
        }
        if( $this->angelleye_paypal_plus_get_field_value($this->get_field_key('disable_shipping'), 'no') != $this->get_option( 'disable_shipping', 'no' ) ) {
            return $comparison = false;
        }
        return $comparison;
    }
    
    public function angelleye_paypal_plus_get_experience_profile_id() {
        $experience_profile_id = '';
        if ($this->mode == "LIVE") {
            $live_experience_profile_id = $this->get_option('live_experience_profile_id', '');
            if( !empty($live_experience_profile_id) ) {
                return $live_experience_profile_id;
            } else {
                $experience_profile_id = get_option('_experience_profile_id', '');
                if( !empty($experience_profile_id) ) {
                    $this->angelleye_paypal_plus_gateway_setting_update('live_experience_profile_id', $experience_profile_id);
                    return $experience_profile_id;
                }
            }
        }
        if ($this->mode == 'SANDBOX') {
            $sandbox_experience_profile_id = $this->get_option('sandbox_experience_profile_id', '');
            if( !empty($sandbox_experience_profile_id) ) {
                return $sandbox_experience_profile_id;
            } else {
                $experience_profile_id = get_option('_experience_profile_id', '');
                if( !empty($experience_profile_id) ) {
                    $this->angelleye_paypal_plus_gateway_setting_update('sandbox_experience_profile_id', $experience_profile_id);
                    return $experience_profile_id;
                }
            }
        }
        return $experience_profile_id;
    }
    
    public function angelleye_paypal_plus_get_field_value($key, $default = false) {
        $value = isset( $_POST[ $key ] ) ? wc_clean( $_POST[$key] ) : $default;
        if($value == '1') {
            return 'yes';
        } elseif( $value != $default) {
            return $value;
        } else {
            return $default;
        }
    }
    
    public function angelleye_paypal_plus_get_option($key = null, $default = null) {
        $data_value = $this->angelleye_paypal_plus_get_field_value($this->get_field_key($key));
        if( !empty( $data_value )) {
            return $this->angelleye_paypal_plus_get_field_value($this->get_field_key($key));
        } else {
            return $default;
        }
    }
    public function angelleye_paypal_plus_update_option($key = null, $value = null) {
        $_POST[$this->get_field_key($key)] = $value;
    }
    public function angelleye_paypal_plus_delete_option($key = null) {
        unset($_POST[$this->get_field_key($key)]);
    }
    public function angelleye_paypal_plus_gateway_setting_update($key = null, $value = null) {
        $woocommerce_paypal_plus_settings = get_option('woocommerce_paypal_plus_settings');
        $woocommerce_paypal_plus_settings[$key] = wc_clean($value);
        update_option('woocommerce_paypal_plus_settings', $woocommerce_paypal_plus_settings);
    }
    
    public function angelleye_paypal_plus_redirect() {
        global $woocommerce;
        if(is_ajax()) {
            return array(
                'result'   => 'fail',
                'redirect' => ''
            );
        } else {
            wp_redirect($woocommerce->cart->get_cart_url());
            exit;
        }
    }
    
    public function get_paypal_plus_locale() {
        $wpml_locale = $this->angelleye_paypal_plus_get_wpml_locale();
        if($wpml_locale=='es_ES') {
            $wpml_locale = 'es_MX';
            return $wpml_locale;
        }else if( $wpml_locale ) {
            if ( in_array( $wpml_locale, $this->supportedLocale ) ) {
                return $wpml_locale;
            }
        }
        $wp_locale = $this->angelleye_paypal_plus_get_wp_locale();
        if($wp_locale=='es_ES') {
            $wp_locale = 'es_MX';
            return $wp_locale;
        }else if( $wp_locale ) {
            if ( in_array( $wp_locale, $this->supportedLocale ) ) {
                return $wp_locale;
            }
        }
        $country_locale = $this->countryLocale[$this->country];
        if($country_locale=='es_ES') {
            $country_locale = 'es_MX';
            return $country_locale;
        }else if( $country_locale ) {
            if ( in_array( $country_locale, $this->supportedLocale ) ) {
                return $country_locale;
            }
        }
        $base_country = WC()->countries->get_base_country();
        $country_base_locale = $this->countryLocale[$base_country];
        if($country_base_locale=='es_ES') {
            $country_base_locale = 'es_MX';
            return $country_base_locale;
        }else if( $country_base_locale ) {
            if ( in_array( $country_base_locale, $this->supportedLocale ) ) {
                return $country_base_locale;
            }
        }
        return $locale = 'en_US';
    }
    
    public function angelleye_woocommerce_update_order_review_fragments($fragments) {
       $fragments['#paypal_plus_container'] = '';
        return $fragments;
    }
    
    public function angelleye_woocommerce_germanized_send_instant_order_confirmation($do_send, $order) {
        $payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
        if ($this->disable_instant_order_confirmation == true && $payment_method === 'paypal_plus') {
            return false;
        }
        return $do_send;
    }
    
    public function angelleye_woocommerce_gzd_instant_order_confirmation($bool) {
        if($this->disable_instant_order_confirmation == true) {
            return false;
        }
        return $bool;
    }
    
    public function angelleye_paypal_plus_get_icon($gateway) {
        $icon_url = '';
        if( !empty($gateway->icon) ) {
            $icon_url = preg_replace("/^http:/i", "https:", $gateway->icon);
        }
        return apply_filters('angelleye_paypal_plus_iframe_' . $gateway->id . '_icon', $icon_url);
    }
    
    public function angelleye_paypal_plus_pay_by_invoice_instructions($order) {
        $payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
        $order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
        if($payment_method == 'paypal_plus') {
            $payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
            $instruction_type = get_post_meta($order_id, 'instruction_type', true);
            if ( !empty($instruction_type) &&  $instruction_type == 'PAY_UPON_INVOICE' && 'paypal_plus' === $payment_method ) {
                echo wpautop( wptexturize( $this->pay_upon_invoice_instructions ) ) . PHP_EOL;
                $this->bank_details($order_id);
            }
        }
    }
}