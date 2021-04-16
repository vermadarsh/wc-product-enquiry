<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://gurukullab.com/
 * @since      1.0.0
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 */

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
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 * @author     Gurukullab <info@gurukullab.com>
 */
class Wc_Product_Enquiry {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wc_Product_Enquiry_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'WCPE_PLUGIN_VERSION' ) ) {
			$this->version = WCPE_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wc-product-enquiry';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wc_Product_Enquiry_Loader. Orchestrates the hooks of the plugin.
	 * - Wc_Product_Enquiry_i18n. Defines internationalization functionality.
	 * - Wc_Product_Enquiry_Admin. Defines all hooks for the admin area.
	 * - Wc_Product_Enquiry_Public. Defines all hooks for the public side of the site.
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
		require_once __DIR__ . '/class-wc-product-enquiry-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once __DIR__ . '/class-wc-product-enquiry-i18n.php';

		/**
		 * The file responsible for defining plugin custom functions.
		 */
		require_once __DIR__ . '/wc-product-enquiry-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once __DIR__ . '/../admin/class-wc-product-enquiry-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once __DIR__ . '/../public/class-wc-product-enquiry-public.php';

		$this->loader = new Wc_Product_Enquiry_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wc_Product_Enquiry_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wc_Product_Enquiry_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Wc_Product_Enquiry_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wcpe_admin_enqueue_scripts_callback' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'wcpe_admin_init_callback' );
		$this->loader->add_filter( 'woocommerce_get_settings_pages', $plugin_admin, 'wcpe_woocommerce_get_settings_pages_callback' );

		if ( ! wcpe_is_product_enquiry_enabled() ) {
			return;
		}

		$cpt = wcpe_get_enquiry_cpt_slug();

		// Hook related to shortcode for enquiries list template.
		$this->loader->add_shortcode( 'wcpe_product_enquiries', $plugin_admin, 'wcpe_product_enquiries_callback' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'wcpe_admin_menu_callback' );
		$this->loader->add_filter( "manage_{$cpt}_posts_columns", $plugin_admin, 'wcpe_manage_enquiry_posts_columns' );
		$this->loader->add_action( "manage_{$cpt}_posts_custom_column", $plugin_admin, 'wcpe_manage_enquiry_posts_custom_column', 10, 2 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'wcpe_add_meta_boxes_callback' );
		$this->loader->add_filter( 'post_row_actions', $plugin_admin, 'wcpe_post_row_actions_callback', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Wc_Product_Enquiry_Public( $this->get_plugin_name(), $this->get_version() );

		if ( ! wcpe_is_product_enquiry_enabled() ) {
			return;
		}

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wcpe_wp_enqueue_scripts_callback' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wcpe_wp_footer_callback' );
		$this->loader->add_action( 'init', $plugin_public, 'wcpe_init_callback' );
		$this->loader->add_action( 'woocommerce_init', $plugin_public, 'wcpe_woocommerce_init_callback' );

		// Hooks related to product pages, for showing the add to enquiry button.
		$this->loader->add_action( 'woocommerce_after_shop_loop_item', $plugin_public, 'wcpe_woocommerce_after_shop_loop_item_callback' );
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'wcpe_woocommerce_after_add_to_cart_button_callback' );

		// Hooks related to managing products in enquiry.
		$this->loader->add_action( 'wp_ajax_add_variation_for_enquiry', $plugin_public, 'wpce_add_variation_for_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_variation_for_enquiry', $plugin_public, 'wpce_add_variation_for_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_add_grouped_products_for_enquiry', $plugin_public, 'wpce_add_grouped_products_for_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_grouped_products_for_enquiry', $plugin_public, 'wpce_add_grouped_products_for_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_add_product_for_enquiry', $plugin_public, 'wpce_add_product_for_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_product_for_enquiry', $plugin_public, 'wpce_add_product_for_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_remove_enquiry_item', $plugin_public, 'wcpe_remove_enquiry_item_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_remove_enquiry_item', $plugin_public, 'wcpe_remove_enquiry_item_callback' );
		$this->loader->add_action( 'wp_ajax_update_enquiries', $plugin_public, 'wcpe_update_enquiries_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_update_enquiries', $plugin_public, 'wcpe_update_enquiries_callback' );
		$this->loader->add_action( 'wp_ajax_submit_enquiry', $plugin_public, 'wcpe_submit_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_submit_enquiry', $plugin_public, 'wcpe_submit_enquiry_callback' );
		$this->loader->add_action( 'wp_ajax_add_to_cart', $plugin_public, 'wcpe_add_to_cart_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_to_cart', $plugin_public, 'wcpe_add_to_cart_callback' );
		$this->loader->add_action( 'wp_ajax_update_mini_cart', $plugin_public, 'wcpe_update_mini_cart_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_update_mini_cart', $plugin_public, 'wcpe_update_mini_cart_callback' );
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
	 * @return    Wc_Product_Enquiry_Loader    Orchestrates the hooks of the plugin.
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
