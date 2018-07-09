=== PayPal Plus for WooCommerce ===
Contributors: angelleye
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SG9SQU2GBXJNA
Tags: paypal, plus, woocommerce
Requires at least: 3.0.1
Tested up to: 4.7.4
Stable tag: 1.0.12
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

PayPal PLUS is a solution where PayPal offers PayPal, Credit Card and ELV as individual payment options on the payment selection page. The available payment methods are provided in a PayPal hosted iFrame.

== Description ==

PayPal PLUS is a solution where PayPal offers PayPal, Credit Card and ELV as individual payment options on the payment selection page. The available payment methods are provided in a PayPal hosted iFrame.

== Changelog ==

= 1.1.0 = xx.xx.2017 =
* Feature - Adds PayPal Ratenkauf for WooCommerce compatibility. [#114]
* Feature - Adds an option to disable Germanized email adjustments so it works more smoothly with PayPal Plus. [#98]
* Feature - Adds Pay by Invoice details to PDF invoice sent by PDF Invoices and Packing Slips plugin. [#123]
* Tweak - Adjustments to Mexico / Brazil integration for Plus. [#115]
* Tweak - Adjustments for better WPML compatibility. [#90]
* Tweak - Deletes database settings when plugin is uninstalled. [#102]
* Tweak - Allows you to enable PayPal Plus even with unsupported currency set in WC, but provides a notice that you'll need to use a currency conversion plugin. [#116]
* Fix - Resolves issue where additional fees added to an order caused errors. [#127]
* Fix - Resolves issues with information links. [#122]
* Fix - Resolves a problem with the gateway title not displaying correctly on the front-end during checkout. [#86]
* Fix - Resolves a conflict with WPML. [#105]
* Fix - Resolves an issue where sales tax would sometimes get calculated as a negative number. [#111]
* Fix - Resolves an issue with the option to replace the WC gateway list with the PayPal Plus iFrame. [#112]
* Fix - Resolves an issue where the PayPal Plus description was not showing up on the front end during checkout. [#113]
* Fix - Resolves an AJAX conflict when reloading the checkout / iFrame. [#117] [#118]

= 1.0.12 - 04.24.2017 =
* Feature - WooCommerce 3.0 Compatibility. [#100]

= 1.0.11 - 01.29.2017 =
* Fix - Resolves an issue where orders paid using Pay by Invoice were getting left with a Pending status. [#84]

= 1.0.10 - 01.18.2017 =
* Fix - Resolves a problem resulting in negative tax calculations. [#83]

= 1.0.9 - 12.22.2016 =
* Tweak - Minor adjustments to order total calculations. [#78]

= 1.0.8 - 12.05.2016 =
* Feature - WooCommerce Sequential Order Numbers Compatibility [#76]
* Fix - Resolves an issue with calculations involving discounts. [#77]

= 1.0.7 - 11.18.2016 =
* Fix - Resolves a bug in WooCommerce Germanized compatibility (after adjustments were made in Germanized plugin). [#70]
* Fix - Resolves problems with the PayPal Plus pay wall loading properly when refreshing the page or using the browser's back button. [#70]

= 1.0.6 - 11.03.2016 =
* Fix - Resolves a validation formatting error for some pricing outputs. [#69]

= 1.0.5 - 11.01.2016 =
* Feature - WooCommerce MultiStep Checkout Wizard Compatibility. [#56]
* Tweak - Adds PayPal bank details from Pay by Invoice to Order History in user account. [#63]
* Fix - Resolves an issue with the Plus payment wall in OSX Safari browsers. [#62]
* Fix - Resolves an issue with the Plus payment wall in IE and Edge browsers. [#66]
* Fix - Resolves an issue with priority of payment scripts. [#57]
* Fix - Resolves an issue with leftover settings in the database for Ratenzahlung. [#65]

= 1.0.4 - 10.17.2016 =
* Fix - Removes jQuery spinner/loader from Plus because WooCommerce has its own. [#55]
* Fix - Resolves a PHP fatal error conflict with some translation plugins. [#54]

= 1.0.3 - 10.13.2016 =
* Feature - Adds WooCommerce Germanized compatibility. [#52]
* Fix - Resolves issue on iOS / Safari browsers. [#51]

= 1.0.2 - 10.07.2016 =
* Feature - Address info filled out on WooCommerce checkout page now passed to PayPal for pre-population.
* Feature - Adds the ability to disable shipping requirement during PayPal checkout.  This is typically used when selling digital goods.
* Feature - Adds an option to include PayPal Plus like a regular payment gateway on the WooCommerce checkout page, or to replace the WooCommerce options entirely with the iFrame.
* Feature - WPML compatibility.
* Feature - Adds German translation file.
* Feature - Adds the ability to forward IPN data to an additional URL so that PayPal Plus can update itself with WooCommerce and you can also run your own IPN solution.
* Tweak - Adds cache FileName and valid path to reduce access token requests.
* Tweak - Adjustments to the way the experience profile IDs for sandbox and live accounts are generated in the plugin settings.
* Tweak - Cancel URL setting now uses a drop down of all WordPress pages to set the option more easily.
* Tweak - Billing address handling adjustments.
* Tweak - Add cache FileName and valid path to reduce access token requests.
* Tweak - General adjustments based on PayPal Germany rep feedback.
* Tweak - Log file format adjustments.
* Tweak - HIdes the option to enable / disable Ratenzahlung during checkout (per PayPal rep request).
* Fix - Adjustments to help reduce caching problems that result in 400 errors from PayPal.
* Fix - Resolves use of deprecated function, woocommerce_get_page_id() -> wc_get_page_id().

= 1.0.1 - 09.01.2016 =
* Tweak - Updates translation file.
* Tweak - Adds option to enable / disable Ratenzahlung during checkout.
* Tweak - Adds experience profile options to settings panel and adjusts experience profile generation.
* Fix - Resolves checkout conflict for Brazil / Mexico.
* Fix - Resolves problem with PATCH payments.
* Fix - Proper caching of access tokens and experience profile references.
* Fix - X-Frame-Options conflict resolved.

= 1.0.0 - 08.23.2016 =
* Initial stable release.