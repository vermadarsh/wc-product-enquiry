<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://gurukullab.com/
 * @since      1.0.0
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 * @author     Gurukullab <info@gurukullab.com>
 */
class Wc_Product_Enquiry_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wc-product-enquiry',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
