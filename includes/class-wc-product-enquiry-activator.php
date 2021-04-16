<?php
/**
 * Fired during plugin activation
 *
 * @link       https://gurukullab.com/
 * @since      1.0.0
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 * @author     Gurukullab <info@gurukullab.com>
 */
class Wc_Product_Enquiry_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Redirect to plugin settings.
		add_option( 'wcpe_do_activation_redirect', 1, '', 'no' );
	}

}
