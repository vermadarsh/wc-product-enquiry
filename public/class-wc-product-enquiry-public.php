<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://gurukullab.com/
 * @since      1.0.0
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/public
 * @author     Gurukullab <info@gurukullab.com>
 */
class Wc_Product_Enquiry_Public {

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
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wcpe_wp_enqueue_scripts_callback() {
		global $post;

		if (
			( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wcpe_product_enquiries' ) ) ||
			true === wcpe_enable_floating_button()
		) {
			wp_enqueue_style(
				$this->plugin_name . '-font-awesome',
				WCPE_PLUGIN_URL . 'admin/css/font-awesome.min.css',
				array(),
				filemtime( WCPE_PLUGIN_PATH . 'admin/css/font-awesome.min.css' ),
				'all'
			);
		}

		wp_enqueue_style(
			$this->plugin_name,
			WCPE_PLUGIN_URL . 'public/css/wc-product-enquiry-public.css',
			array(),
			filemtime( WCPE_PLUGIN_PATH . 'public/css/wc-product-enquiry-public.css' ),
			'all'
		);

		wp_enqueue_script(
			$this->plugin_name,
			WCPE_PLUGIN_URL . 'public/js/wc-product-enquiry-public.js',
			array( 'jquery' ),
			filemtime( WCPE_PLUGIN_PATH . 'public/css/wc-product-enquiry-public.css' ),
			true
		);

		wp_localize_script(
			$this->plugin_name,
			'WCPE_Public_JS_Obj',
			array(
				'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
				'wcpe_ajax_nonce'             => wp_create_nonce( 'wcpe-ajax-nonce' ),
				'processing_btn_txt'          => apply_filters( 'wcpe_processing_button_text', __( 'Processing...', 'wcpe' ) ),
				'notification_success_header' => apply_filters( 'wcpe_notification_success_header', __( 'Success', 'wcpe' ) ),
				'notification_error_header'   => apply_filters( 'wcpe_notification_error_header', __( 'Error', 'wcpe' ) ),
				'simple_product_quantity_err' => apply_filters( 'wcpe_simple_product_quantity_error', __( 'Quantity invalid, product not added for enquiry.', 'wcpe' ) ),
				'variation_id_err'            => apply_filters( 'wcpe_variation_id_error', __( 'Variation ID invalid, hence not added for enquiry.', 'wcpe' ) ),
				'is_wc_archive'               => ( is_shop() || is_product_category() || is_product_tag() ) ? '1' : '-1',
				'is_product'                  => ( is_product() ) ? '1' : '-1',
				'invalid_ajax_response'       => apply_filters( 'wcpe_invalid_ajax_response', __( 'Invalid AJAX response.', 'wcpe' ) ),
				'remove_item_cnf_message'     => apply_filters( 'wcpe_remove_item_confirmation_message', __( 'Are you sure you want to remove this item?', 'wcpe' ) ),
				'invalid_item_id_err'         => apply_filters( 'wcpe_invalid_item_id_error', __( 'Invalid item. Exit !!', 'wcpe' ) ),
				'first_name_empty'            => apply_filters( 'wcpe_first_name_empty_error', __( 'First name is empty.', 'wcpe' ) ),
				'last_name_empty'             => apply_filters( 'wcpe_last_name_empty_error', __( 'Last name is empty.', 'wcpe' ) ),
				'phone_number_empty'          => apply_filters( 'wcpe_phone_number_empty_error', __( 'Phone number is empty.', 'wcpe' ) ),
				'phone_number_invalid'        => apply_filters( 'wcpe_phone_number_invalid_error', __( 'Phone number is invalid.', 'wcpe' ) ),
				'email_address_empty'         => apply_filters( 'wcpe_email_address_empty_error', __( 'Email address is empty.', 'wcpe' ) ),
				'email_address_invalid'       => apply_filters( 'wcpe_email_address_invalid_error', __( 'Email address is invalid.', 'wcpe' ) ),
				'captcha_incorrect'           => apply_filters( 'wcpe_captcha_incorrect_error', __( 'Captcha incorrect.', 'wcpe' ) ),
				'privacy_policy_error'        => apply_filters( 'wcpe_privacy_policy_error', __( 'You must go for privacy policy to continue.', 'wcpe' ) ),
				'no_items_error'              => apply_filters( 'wcpe_no_items_error', __( 'No item available to be processed.', 'wcpe' ) ),
				'ajax_nonce_failure'          => apply_filters( 'wcpe_ajax_nonce_failure_error', __( 'Action couldn\'t be taken due to security failure. Please try again later.', 'wcpe' ) ),
			)
		);
	}

	/**
	 * Add custom assets to WordPress footer section.
	 */
	public function wcpe_wp_footer_callback() {
		ob_start();
		?>
		<div class="wcpe_notification_popup">
			<span class="wcpe_notification_close"></span>
			<div class="wcpe_notification_icon"><i class="fa" aria-hidden="true"></i></div>
			<div class="wcpe_notification_message">
				<h3 class="title"></h3>
				<p class="message"></p>
			</div>
		</div>
		<?php
		echo wp_kses_post( ob_get_clean() );

		// Include the modal html.
		$enquiry_items = wcpe_get_enquiries();

		if ( ! empty( $enquiry_items ) && is_array( $enquiry_items ) ) {
			echo wp_kses_post( $this->wcpe_add_to_cart_modal_html() );
		}

		// Include the floating button.
		$enable_floating_button = wcpe_enable_floating_button();

		if ( $enable_floating_button ) {
			$enquiry_page = get_permalink( get_option( 'wcpe_enquiry_page' ) );
			$icon         = wcpe_get_floating_button_icon();
			echo wp_kses_post( '<a title="' . __( 'Enquiry Page', 'wcpe' ) . '" href="' . esc_url( $enquiry_page ) . '" class="wcpe-floating-button">' . $icon . '</a>' );
		}
	}

	/**
	 * Modal HTML for add to cart functionality.
	 *
	 * @return string
	 */
	private function wcpe_add_to_cart_modal_html() {
		ob_start();
		?>
		<div class="wcpe-modal" data-modal="trigger-1">
			<article class="content-wrapper">
				<button class="close"></button>
				<header class="modal-header">
					<h2><?php esc_html_e( 'Add to cart', 'wcpe' ); ?></h2>
				</header>
				<div class="content">
					<table class="wcpe-add-to-cart-table shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
						<thead>
							<tr>
								<th class="product-thumbnail">&nbsp;</th>
								<th class="product-name"><?php esc_html_e( 'Product', 'wcpe' ); ?></th>
								<th class="product-quantity"><?php esc_html_e( 'Quantity', 'wcpe' ); ?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<footer class="modal-footer">
					<button type="button" class="button wcpe-add-items-to-cart"><?php echo esc_html( apply_filters( 'wcpe_add_to_cart_modal_button_text', __( 'Proceed', 'wcpe' ) ) ); ?></button>
				</footer>
			</article>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Do some work on WordPress initialization.
	 */
	public function wcpe_init_callback() {
		// Register CPT.
		wcpe_register_enquiries_cpt();
	}

	/**
	 * Do some work on WooCommerce initialization.
	 */
	public function wcpe_woocommerce_init_callback() {
		// Set customer session.
		if ( ! is_user_logged_in() && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}
	}

	/**
	 * Add the enquiry button on the archive pages.
	 */
	public function wcpe_woocommerce_after_shop_loop_item_callback() {
		global $product;

		if ( ! $product->is_type( 'simple' ) && ! $product->is_type( 'external' ) ) {
			return;
		}

		if ( ! wcpe_show_enquiry_button_on_archive() ) {
			return;
		}

		echo wp_kses_post( wcpe_get_simple_product_enquiry_button( $product ) );
	}

	/**
	 * Add the enquiry button on the product details page.
	 */
	public function wcpe_woocommerce_after_add_to_cart_button_callback() {
		global $product;

		if ( ! wcpe_show_enquiry_button_on_single_product() ) {
			return;
		}

		// Simple or external product.
		if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {
			echo wp_kses_post( wcpe_get_simple_product_enquiry_button( $product ) );
		}

		// Grouped product.
		if ( $product->is_type( 'grouped' ) ) {
			echo wp_kses_post( wcpe_get_grouped_product_enquiry_button() );
		}

		// Variable product.
		if ( $product->is_type( 'variable' ) ) {
			echo wp_kses_post( wcpe_get_variable_product_enquiry_button() );
		}
	}

	/**
	 * AJAX served to add variation for enquiry.
	 */
	public function wpce_add_variation_for_enquiry_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'add_variation_for_enquiry' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$variation_id = (int) filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT );
		$quantity     = (int) filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );
		wcpe_add_product_to_enquiry_list( $variation_id, $quantity );

		wp_send_json_success(
			array(
				'code'                 => 'wcpe-variation-added-for-enquiry',
				/* translators: %s: product title */
				'notification_message' => sprintf( __( '%1$s has been added to enquiry list.', 'wcpe' ), get_the_title( $variation_id ) ),
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to add variation for enquiry.
	 */
	public function wpce_add_grouped_products_for_enquiry_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'add_grouped_products_for_enquiry' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$enquiry_porducts = ( ! empty( $_POST['enquiry_porducts'] ) ) ? wp_unslash( $_POST['enquiry_porducts'] ) : array();

		if ( empty( $enquiry_porducts ) || ! is_array( $enquiry_porducts ) ) {
			wp_send_json_error(
				array(
					'code'                 => 'wcpe-product-not-for-enquiry',
					'notification_message' => __( 'Products couldn\'t be added to enquiry list due to some technical error. Please try again.', 'wcpe' ),
				)
			);
			wp_die();
		}

		$success_html = '<ul class="wcpe-products-list-in-notification">';
		foreach ( $enquiry_porducts as $product ) {
			wcpe_add_product_to_enquiry_list( $product['product_id'], $product['quantity'] );
			/* translators: %s: product title */
			$success_html .= '<li>' . sprintf( __( '%1$s has been added to enquiry list.', 'wcpe' ), get_the_title( $product['product_id'] ) ) . '</li>';
		}
		$success_html .= '</ul>';

		wp_send_json_success(
			array(
				'code'                 => 'wcpe-products-added-for-enquiry',
				'notification_message' => $success_html,
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to add variation for enquiry.
	 */
	public function wpce_add_product_for_enquiry_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'add_product_for_enquiry' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$product_id = (int) filter_input( INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		$quantity   = (int) filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $product_id ) || empty( $quantity ) ) {
			wp_send_json_error(
				array(
					'code'                 => 'wcpe-simple-product-not-for-enquiry',
					'notification_message' => __( 'Product couldn\'t be added to enquiry list due to some technical error. Please try again.', 'wcpe' ),
				)
			);
			wp_die();
		}

		wcpe_add_product_to_enquiry_list( $product_id, $quantity );

		wp_send_json_success(
			array(
				'code'                 => 'wcpe-simple-product-added-for-enquiry',
				/* translators: %s: product title */
				'notification_message' => sprintf( __( '%1$s has been added to enquiry list.', 'wcpe' ), get_the_title( $product_id ) ),
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to remove enquiry item.
	 */
	public function wcpe_remove_enquiry_item_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'remove_enquiry_item' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$item_id = (int) filter_input( INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $item_id ) ) {
			wp_send_json_error(
				array(
					'code'                 => 'wcpe-invalid-item',
					'notification_message' => __( 'Product couldn\'t be removed from enquiry list due to some technical error. Please try again.', 'wcpe' ),
				)
			);
			wp_die();
		}

		// Get the enquiries.
		$enquiries = wcpe_get_enquiries();

		if ( ! array_key_exists( $item_id, $enquiries ) ) {
			wp_send_json_error(
				array(
					'code'                 => 'wcpe-invalid-item',
					'notification_message' => __( 'Product couldn\'t be removed from enquiry list due to some technical error. Please try again.', 'wcpe' ),
				)
			);
			wp_die();
		}

		// Remove the item now.
		unset( $enquiries[ $item_id ] );

		// Set the enquiry cookie.
		WC()->session->set( wcpe_get_cookie_name(), $enquiries );

		if ( empty( $enquiries ) ) {
			$html = wcpe_get_no_enquiries_html();
		} else {
			$html = wcpe_get_enquiries_table_html( $enquiries ) . wcpe_get_enquiry_form_html();
		}

		wp_send_json_success(
			array(
				'code'                 => 'wcpe-enquiry-item-removed',
				/* translators: %s: product title */
				'notification_message' => sprintf( __( '%1$s has been removed from the enquiry list.', 'wcpe' ), get_the_title( $item_id ) ),
				'html'                 => $html,
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to update enquiries.
	 */
	public function wcpe_update_enquiries_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'update_enquiries' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		// Get the enquiry items.
		$enquiries = ( ! empty( $_POST['enquiries'] && is_array( $_POST['enquiries'] ) ) ) ? wp_unslash( $_POST['enquiries'] ) : array();

		if ( empty( $enquiries ) ) {
			$html = wcpe_get_no_enquiries_html();
		} else {
			$enquiry_items = array();
			foreach ( $enquiries as $enquiry_item ) {
				$enquiry_items[ $enquiry_item['product_id'] ] = array(
					'quantity' => $enquiry_item['quantity'],
					'remarks'  => $enquiry_item['remarks'],
				);
			}
		}

		// Set the enquiry cookie.
		WC()->session->set( wcpe_get_cookie_name(), $enquiry_items );

		// Get the html.
		$html = wcpe_get_enquiries_table_html( $enquiry_items ) . wcpe_get_enquiry_form_html();

		wp_send_json_success(
			array(
				'code'                 => 'wcpe-enquiries-updated',
				'notification_message' => __( 'Enquiry items have been updated.', 'wcpe' ),
				'html'                 => $html,
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to submit enquiry.
	 */
	public function wcpe_submit_enquiry_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'submit_enquiry' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		global $wpdb;

		$first_name           = filter_input( INPUT_POST, 'first_name', FILTER_SANITIZE_STRING );
		$last_name            = filter_input( INPUT_POST, 'last_name', FILTER_SANITIZE_STRING );
		$phone                = filter_input( INPUT_POST, 'phone', FILTER_SANITIZE_STRING );
		$email                = filter_input( INPUT_POST, 'email', FILTER_SANITIZE_STRING );
		$comment              = filter_input( INPUT_POST, 'comment', FILTER_SANITIZE_STRING );
		$send_customer_a_copy = (int) filter_input( INPUT_POST, 'send_customer_a_copy', FILTER_SANITIZE_NUMBER_INT );

		// Gather email recipients.
		$recipients    = wcpe_enquiry_email_recipients( $send_customer_a_copy, $email );
		$email_subject = get_option( 'wcpe_product_enquiry_email_subject' );

		// Validate post data.
		$is_post_data_valid = wcpe_validate_post_data_submit_enquiry( $first_name, $last_name, $phone, $email );

		if ( true !== $is_post_data_valid ) {
			wp_send_json_error(
				array(
					'code'                 => 'wcpe-enquiry-not-submitted',
					'notification_message' => $is_post_data_valid,
				)
			);
			wp_die();
		}

		// Submit the enquiry.
		$cpt       = wcpe_get_enquiry_cpt_slug();
		$enquiries = wcpe_get_enquiries();
		$user_id   = get_current_user_id();

		$args = array(
			'post_type'    => $cpt,
			'post_status'  => 'publish',
			'post_title'   => 'Enquiry' . time(),
			'post_excerpt' => $comment,
		);

		if ( 0 !== $user_id ) {
			$args['post_author'] = $user_id;
		}

		// Add an enquiry.
		$enquiry_id = wp_insert_post( $args );

		/**
		 * Direct SQL query is written to update the estimate title.
		 * Otherwise it calls the save_post action, which in turn disturbs the custom code in admin.php file.
		 */
		$wpdb->update(
			$wpdb->posts,
			array(
				/* translators: 1: %d enquiry id, 2: %s enquirer name */
				'post_title' => sprintf( __( '#%1$d @ %2$s', 'wcpe' ), $enquiry_id, "{$first_name} {$last_name}" ),
			),
			array(
				'ID' => $enquiry_id,
			)
		);

		// Prepare meta data.
		$meta = array(
			'_by_first_name' => $first_name,
			'_by_last_name'  => $last_name,
			'_by_email'      => $email,
			'_by_phone'      => $phone,
			'_enquiries'     => $enquiries,
		);

		// Update the meta data now.
		foreach ( $meta as $mkey => $mdata ) {
			update_post_meta( $enquiry_id, $mkey, $mdata );
		}

		// Unset the cookie.
		WC()->session->__unset( wcpe_get_cookie_name() );

		// Send the notifications now.

		// Send the AJAX response now.
		wp_send_json_success(
			array(
				'code'                 => 'wcpe-enquiry-submitted',
				'notification_message' => __( 'Thanks for submitting the enquiry. One team shall contact you soon.', 'wcpe' ),
				'html'                 => wcpe_get_no_enquiries_html(),
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to add items to cart.
	 */
	public function wcpe_add_to_cart_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'add_to_cart' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$items = ( ! empty( $_POST['items'] ) && is_array( $_POST['items'] ) ) ? wp_unslash( $_POST['items'] ) : array();

		if ( empty( $items ) ) {
			echo 0;
			wp_die();
		}

		foreach ( $items as $item ) {
			WC()->cart->add_to_cart( $item['product_id'], $item['quantity'] );
		}

		wp_send_json_success(
			array(
				'code'                 => 'wcpe-items-added-to-cart',
				'notification_message' => __( 'Items have been added to the cart.', 'wcpe' ),
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to update mini cart.
	 */
	public function wcpe_update_mini_cart_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'update_mini_cart' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcpe_ajax_nonce = filter_input( INPUT_POST, 'wcpe_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcpe_ajax_nonce, 'wcpe-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		echo wp_kses_post( woocommerce_mini_cart() );
		wp_die();
	}
}
