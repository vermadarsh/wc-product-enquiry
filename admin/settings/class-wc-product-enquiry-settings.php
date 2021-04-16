<?php
/**
 * The admin-settings of the plugin.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Sync_Marketplace
 * @subpackage Sync_Marketplace/admin/settings
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( class_exists( 'WC_Product_Enquiry_Settings', false ) ) {
	return new WC_Product_Enquiry_Settings();
}

/**
 * Settings class for keeping data sync with marketplace.
 */
class WC_Product_Enquiry_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'wcpe';
		$this->label = __( 'Product Enquiry', 'wcpe' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			'' => __( 'General', 'wcpe' ),
		);

		if ( wcpe_is_product_enquiry_enabled() ) {
			$other_settings_sections = array(
				'email'   => __( 'Emails', 'wcpe' ),
				'privacy' => __( 'Privacy', 'wcpe' ),
			);

			$sections = array_merge( $sections, $other_settings_sections );
		}

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
		}
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section name.
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'privacy' === $current_section ) {
			$settings = $this->wcpe_privacy_settings_fields();
		} elseif ( 'email' === $current_section ) {
			$settings = $this->wcpe_email_settings_fields();
		} else {
			$settings = $this->wcpe_general_settings_fields();
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Return the fields for general settings.
	 *
	 * @return array
	 */
	public function wcpe_general_settings_fields() {
		// Get all the pages.
		$page_ids = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
			)
		);

		$pages = array();
		if ( ! empty( $page_ids ) && is_array( $page_ids ) ) {
			foreach ( $page_ids as $page_id ) {
				$pages[ $page_id ] = get_the_title( $page_id );
			}
		}

		// Enquiry page class.
		$enable_floating_button = wcpe_enable_floating_button();
		$enquiry_page_class     = ( false === $enable_floating_button ) ? 'dnone' : '';

		return apply_filters(
			'woocommerce_wcpe_general_settings',
			array(
				array(
					'title' => __( 'General Settings', 'wcpe' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wcpe_general_settings_title',
				),
				array(
					'name' => __( 'Enable Product Enquiry', 'wcpe' ),
					'desc' => __( 'Enable enquiring products.', 'wcpe' ),
					'id'   => 'wcpe_enable_product_enquiry',
					'type' => 'checkbox',
				),
				array(
					'name' => __( 'Product Enquiry Button on Archive Pages', 'wcpe' ),
					'desc' => __( 'Check this is you want to display button on archive pages that include shop and category pages.', 'wcpe' ),
					'id'   => 'wcpe_enquiry_button_on_archive',
					'type' => 'checkbox',
				),
				array(
					'name' => __( 'Product Enquiry Button on Single Product Page', 'wcpe' ),
					'desc' => __( 'Check this is you want to display button on single product page.', 'wcpe' ),
					'id'   => 'wcpe_enquiry_button_on_single_product',
					'type' => 'checkbox',
				),
				array(
					'title'             => __( 'Enquiry Button Label', 'wcpe' ),
					'desc'              => __( 'This sets the product enquiry button label.', 'wcpe' ),
					'desc_tip'          => true,
					'id'                => 'wcpe_product_enquiry_button_label',
					'type'              => 'text',
					'placeholder'       => __( 'E.g.: Add for Enquiry', 'wcpe' ),
					'custom_attributes' => array(
						'required' => 'required',
					),
				),
				array(
					'name' => __( 'Enable Floating Button', 'wcpe' ),
					'desc' => __( 'Check this is you want to display a floating button.', 'wcpe' ),
					'id'   => 'wcpe_enable_enquiry_floating_button',
					'type' => 'checkbox',
				),
				array(
					'id'       => 'wcpe_enquiry_page',
					'name'     => __( 'Enquiry Page', 'wcpe' ),
					'type'     => 'select',
					'options'  => $pages,
					'class'    => "wc-enhanced-select {$enquiry_page_class}",
					'desc'     => __( 'Holds the enquiry page where the enquiry items will be shown.', 'wcpe' ),
					'desc_tip' => true,
				),
				array(
					'title'       => __( 'Enquiry Floating Button Icon', 'wcpe' ),
					'desc'        => __( 'This sets the icon on the enquiry floating button.', 'wcpe' ),
					'desc_tip'    => true,
					'id'          => 'wcpe_product_enquiry_floating_button_icon',
					'type'        => 'text',
					'placeholder' => '<i class="fa fa-wpforms"></i>',
					'class'       => "{$enquiry_page_class}",
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wcpe_general_settings_end',
				),
			)
		);
	}

	/**
	 * Return the fields for Rest API settings.
	 *
	 * @return array
	 */
	public function wcpe_email_settings_fields() {

		return apply_filters(
			'woocommerce_wcpe_email_settings',
			array(
				array(
					'title' => __( 'Email Settings', 'wcpe' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wcpe_email_settings_title',
				),
				array(
					'title'             => __( 'Email Subject', 'wcpe' ),
					'desc'              => __( 'This holds the email subject, the email that is sent to the enquiry recipients.', 'wcpe' ),
					'desc_tip'          => true,
					'id'                => 'wcpe_product_enquiry_email_subject',
					'type'              => 'text',
					'placeholder'       => __( 'Product Enquiry', 'wcpe' ),
					'custom_attributes' => array(
						'required' => 'required',
					),
				),
				array(
					'title'             => __( 'Email Recipients', 'wcpe' ),
					'desc'              => __( 'This holds the extra email addresses that will receive the product enquiry email.', 'wcpe' ),
					'desc_tip'          => true,
					'id'                => 'wcpe_extra_email_recipients',
					'type'              => 'textarea',
					'placeholder'       => __( '1 per line.', 'wcpe' ),
					'custom_attributes' => array(
						'rows' => 8,
					),
				),
				array(
					'name' => __( 'Send Email to Administrator', 'wcpe' ),
					'desc' => __( 'Check this is you want to send the enquiry email to the site administrator.', 'wcpe' ),
					'id'   => 'wcpe_send_email_to_admin',
					'type' => 'checkbox',
				),
				array(
					'name' => __( 'Send Email to Product Author', 'wcpe' ),
					'desc' => __( 'Check this is you want to send the enquiry email to the product owner.', 'wcpe' ),
					'id'   => 'wcpe_send_email_to_product_author',
					'type' => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wcpe_email_settings_end',
				),

			)
		);
	}

	/**
	 * Return the fields for Rest API settings.
	 *
	 * @return array
	 */
	public function wcpe_privacy_settings_fields() {

		return apply_filters(
			'woocommerce_wcpe_privacy_settings',
			array(
				array(
					'title' => __( 'Privacy Policy Settings', 'wcpe' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wcpe_privacy_policy_settings_title',
				),
				array(
					'title'             => __( 'Privacy Policy Message', 'wcpe' ),
					'desc'              => __( 'This holds the privacy policy message that is shown at the product enquiry form.', 'wcpe' ),
					'desc_tip'          => true,
					'id'                => 'wcpe_privacy_policy_message',
					'type'              => 'textarea',
					'placeholder'       => __( 'I allow the Site owner to contact me via email/phone to discuss this Enquiry. (If you want to know more about the way this site handles the data, then please go through our privacy policy)', 'wcpe' ),
					'custom_attributes' => array(
						'rows' => 8,
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wcpe_privacy_policy_settings_end',
				),

			)
		);
	}
}

return new WC_Product_Enquiry_Settings();
