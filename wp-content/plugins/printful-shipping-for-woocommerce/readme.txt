=== Printful Integration for WooCommerce ===
Contributors: girts_u
Tags: woocommerce, printful, drop shipping, shipping, shipping rates, fulfillment, printing, fedex, carriers, checkout, t-shirts
Requires at least: 3.8
Tested up to: 4.8
Stable tag: 1.2.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Calculate live shipping rates and tax rates based on actual Printful shipping costs

== Description ==

Display actual live shipping rates from carriers like FedEx on your WooCommerce checkout page. This plugin will return a list of available shipping rates specific to the shipping address your customer provides when checking out. These rates are identical to the list you get when you submit an order manually via Printful dashboard.

This plugin will also automatically calculate taxes where it is required for Printful so that your originally intended profit margin stays intact.

= Known Limitations =

* Works with WooCommmerce 2.1 and up

== Installation ==
1. Upload 'printful-shipping' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add your Printful API key to WooCommerce->Settings->Integration->Integration tab
1. Enable shipping rate calculation in WooCommerce->Settings->Shipping->Printful Shipping tab
1. To automatically calculate taxes please check 'Enable taxes and tax calculations' under WooCommerce Tax settings.
1. Then go to 'Integration' tab and check 'Calculate sales tax for locations where it is required for Printful orders'.

== Frequently Asked Questions ==

= How do I get Printful API key? =

Go to https://www.theprintful.com/dashboard/store , select your WooCommerce store, click "Edit" and then click "Enable API Access". Your API key will be generated and displayed there.

== Screenshots ==

1. Plugin settings dialog
2. Shipping rate dialog
3. Shipping rate selection

== Upgrade Notice ==

= 1.2.8 =
Fixed bug that caused tax rates to become invisible on checkout since WC 3.0

= 1.2.7 =
Do not calculate shipping rates for US addresses while ZIP or state is not entered

= 1.2.6 =
Include shipping rates in tax calculation for states that require that

= 1.2.5 =
Added option to allow Woocommerce default rates together with Printful rates for Printful products

= 1.2.4 =
Prevent virtual products from requiring shipping rate when bought together with Printful products

= 1.2.3 =
Fixed issue introduced in 1.2.2

= 1.2.2 =
Fixed PHP warning on Woocommerce 2.6

= 1.2.1 =
Fixed bug

= 1.2 =
Support calculating shipping rates for both Printful and non-Printful products at the same time

= 1.1.2 =
Removed check for Curl extension

= 1.1.1 =
Ignore virtual and downloadable products when calculating shipping rates

= 1.1 =
Added tax rate calculation

= 1.0.2 =
Added option to disable SSL

= 1.0.1 =
Minor improvements

= 1.0 =
First release

== Changelog ==

= 1.2.8 =
* Fixed bug that caused tax rates to become invisible on checkout since WC 3.0

= 1.2.7 =
* Do not calculate shipping rates for US addresses while ZIP or state is not entered

= 1.2.6 =
* Include shipping rates in tax calculation for states that require that

= 1.2.5 =
* Added option to allow Woocommerce default rates together with Printful rates for Printful products

= 1.2.4 =
* Prevent virtual products from requiring shipping rate when bought together with Printful products

= 1.2.3 =
* Fixed issue introduced in 1.2.2

= 1.2.2 =
* Fixed PHP warning on Woocommerce 2.6 due to changed method signature
* Fixed conflict with "Multiple Packages for WooCommerce" plugin

= 1.2.1 =
* Fixed bug that could have show error message when calculating shipping rates

= 1.2 =
* Support calculating shipping rates for both Printful and non-Printful products at the same time (non-Printful
products will get default rates provided by Woocommerce)
* Added caching to tax rates
* Improved compatibility with Woocommerce 2.6

= 1.1.2 =
* Removed check for Curl extension (since we already used wp_remote_get and it is no longer necessary)

= 1.1.1 =
* Ignore virtual and downloadable products when calculating shipping rates

= 1.1 =
* Added option to calculate sales tax rates for locations where it is required for Printful orders
* Added automatic conversion of shipping rates to the currency used by Woocommerce
* Printful API client library updated to use Wordpress internal wp_remote_get method instead of CURL directly
* Changed plugin code structure for easier implementation of new features in the future

= 1.0.2 =
* Added option to disable SSL for users that do not have a valid CA certificates in their PHP installation

= 1.0.1 =
* Removed CURLOPT_FOLLOWLOCATION that caused problems on some hosting environments
* Added option to display reason status messages if the rate API request has failed

= 1.0 =
* First release
