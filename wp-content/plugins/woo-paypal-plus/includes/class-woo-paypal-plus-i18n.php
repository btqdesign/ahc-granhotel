<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woo_Paypal_Plus
 * @subpackage Woo_Paypal_Plus/includes
 * @author     Angell EYE <service@angelleye.com>
 */
class Woo_Paypal_Plus_i18n {

    /**
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
                'woo-paypal-plus', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}