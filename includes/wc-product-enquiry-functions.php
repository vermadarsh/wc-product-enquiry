<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since 1.0.0
 * @package Wc_Product_Enquiry
 * @subpackage Wc_Product_Enquiry/includes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check if product enquiry is enabled.
 *
 * @return boolean
 */
function wcpe_is_product_enquiry_enabled() {
	$product_enquiry = get_option( 'wcpe_enable_product_enquiry' );

	return ( ! empty( $product_enquiry ) && 'yes' === $product_enquiry ) ? true : false;
}

/**
 * Check if product enquiry is enabled on archive pages.
 *
 * @return boolean
 */
function wcpe_show_enquiry_button_on_archive() {
	$on_archive = get_option( 'wcpe_enquiry_button_on_archive' );

	return ( ! empty( $on_archive ) && 'yes' === $on_archive ) ? true : false;
}

/**
 * Check if product enquiry is enabled on single product page.
 *
 * @return boolean
 */
function wcpe_show_enquiry_button_on_single_product() {
	$on_single_product = get_option( 'wcpe_enquiry_button_on_single_product' );

	return ( ! empty( $on_single_product ) && 'yes' === $on_single_product ) ? true : false;
}

/**
 * Check if product enquiry should be sent to admin email address.
 *
 * @return boolean
 */
function wcpe_send_enquiry_email_to_admin() {
	$send_to_admin = get_option( 'wcpe_send_email_to_admin' );

	return ( ! empty( $send_to_admin ) && 'yes' === $send_to_admin ) ? true : false;
}

/**
 * Check if product enquiry should be sent to author's email address.
 *
 * @return boolean
 */
function wcpe_send_enquiry_email_to_author() {
	$send_to_author = get_option( 'wcpe_send_email_to_product_author' );

	return ( ! empty( $send_to_author ) && 'yes' === $send_to_author ) ? true : false;
}

/**
 * Check if floating button should be shown or not.
 *
 * @return boolean
 */
function wcpe_enable_floating_button() {
	$floating_button = get_option( 'wcpe_enable_enquiry_floating_button' );

	return ( ! empty( $floating_button ) && 'yes' === $floating_button ) ? true : false;
}

/**
 * Return the floating button icon.
 *
 * @return boolean
 */
function wcpe_get_floating_button_icon() {
	$icon = get_option( 'wcpe_product_enquiry_floating_button_icon' );

	if ( false === $icon || empty( $icon ) ) {
		$icon = '<i class="fa fa-wpforms"></i>';
	}

	return $icon;
}

/**
 * Product enquiry email extra recipients.
 *
 * @return boolean
 */
function wcpe_enquiry_email_extra_recipients() {
	$extra_recipients = get_option( 'wcpe_extra_email_recipients' );

	if ( false === $extra_recipients ) {
		return array();
	}

	$extra_recipients = explode( "\n", $extra_recipients );

	return $extra_recipients;
}

/**
 * Return the product enquiry button label.
 *
 * @return string
 */
function wcpe_product_enquiry_button_text() {
	$product_enquiry_button_text = get_option( 'wcpe_product_enquiry_button_label' );

	return ( ! empty( $product_enquiry_button_text ) && ! is_bool( $product_enquiry_button_text ) ) ? $product_enquiry_button_text : __( 'Add for Enquiry', 'wcpe' );
}

/**
 * Get product enquiry button html on simple product page.
 *
 * @param object $product Holds the product object.
 * @return string
 */
function wcpe_get_simple_product_enquiry_button( $product ) {

	if ( empty( $product ) ) {
		return;
	}

	$product_id = $product->get_id();

	// Set the button class for simple and external products.
	if ( $product->is_type( 'simple' ) ) {
		$button_class = 'is-simple';
	} elseif ( $product->is_type( 'external' ) ) {
		$button_class = 'is-external';
	} else {
		$button_class = '';
	}

	return apply_filters(
		'wcpe_add_to_enquiry_link', // WPCS: XSS ok.
		sprintf(
			'<a href="#" data-productid="%d" class="%s">%s</a>',
			esc_attr( $product_id ),
			esc_attr(
				implode(
					' ',
					apply_filters(
						'wcpe_add_to_enquiry_button_classes_simple_product',
						array(
							'button',
							'wcpe-add-simple-product-to-enquiry',
							'wcpe-enquiry-product-button',
							$button_class,
						)
					)
				)
			),
			esc_html( wcpe_product_enquiry_button_text() )
		),
		$product_id
	);
}

/**
 * Get product enquiry button html on grouped product page.
 *
 * @return string
 */
function wcpe_get_grouped_product_enquiry_button() {

	return apply_filters(
		'wcpe_add_to_enquiry_link', // WPCS: XSS ok.
		sprintf(
			'<a href="#" class="%s">%s</a>',
			esc_attr(
				implode(
					' ',
					apply_filters(
						'wcpe_add_to_enquiry_button_classes_grouped_product',
						array(
							'button',
							'wcpe-add-grouped-product-to-enquiry',
							'wcpe-enquiry-product-button',
							'non-clickable',
						)
					)
				)
			),
			esc_html( wcpe_product_enquiry_button_text() )
		)
	);
}

/**
 * Get product enquiry button html on grouped product page.
 *
 * @return string
 */
function wcpe_get_variable_product_enquiry_button() {

	return apply_filters(
		'wcpe_add_to_enquiry_link', // WPCS: XSS ok.
		sprintf(
			'<a href="#" class="%s">%s</a>',
			esc_attr(
				implode(
					' ',
					apply_filters(
						'wcpe_add_to_enquiry_button_classes_variable_product',
						array(
							'button',
							'wcpe-add-variation-to-enquiry',
							'wcpe-enquiry-product-button',
							'non-clickable',
						)
					)
				)
			),
			esc_html( wcpe_product_enquiry_button_text() )
		)
	);
}

/**
 * Get enquiries.
 *
 * @return array
 */
function wcpe_get_enquiries() {
	$enquiries = WC()->session->get( wcpe_get_cookie_name() );

	if ( null === $enquiries ) {
		$enquiries = array();
	}

	return apply_filters( 'wcpe_enquiry_items', $enquiries );
}

/**
 * Get cookie name.
 *
 * @return string
 */
function wcpe_get_cookie_name() {

	return 'wcpe_enquiries';
}

/**
 * Add product to the enquiry list.
 *
 * @param int $product_id Holds the product ID.
 * @param int $quantity Holds the product quantity.
 * @return void
 */
function wcpe_add_product_to_enquiry_list( $product_id, $quantity ) {

	if ( empty( $product_id ) ) {
		return;
	}

	$enquiries = wcpe_get_enquiries();

	// Set the enquiry product quantity.
	if ( ! empty( $enquiries[ $product_id ]['quantity'] ) ) {
		$qty  = $enquiries[ $product_id ]['quantity'];
		$qty += $quantity;
	} else {
		$qty = $quantity;
	}

	$enquiries[ $product_id ]['quantity'] = $qty;

	// Set the enquiry product remarks.
	$enquiries[ $product_id ]['remarks'] = '';

	// Set the enquiry cookie.
	WC()->session->set( wcpe_get_cookie_name(), $enquiries );
}

/**
 * Returns the image src by attachment ID.
 *
 * @param int $img_id Holds the attachment ID.
 * @return boolean|string
 */
function wcpe_get_image_src_by_id( $img_id ) {

	if ( empty( $img_id ) ) {
		return false;
	}

	return wp_get_attachment_url( $img_id );

}

/**
 * Return the HTML markup for the enquired items table.
 *
 * @param array $enquiries Holds the list of enquiry items.
 * @return string
 */
function wcpe_get_enquiries_table_html( $enquiries ) {
	$enquiry_table_cols = apply_filters(
		'wcpe_enquiry_items_table_columns',
		array(
			'remove-item'   => array(
				'class' => 'product-remove',
				'text'  => '&nbsp;',
			),
			'thumbnail'     => array(
				'class' => 'product-thumbnail',
				'text'  => '&nbsp;',
			),
			'item-name'     => array(
				'class' => 'product-name',
				'text'  => __( 'Product', 'wcpe' ),
			),
			'item-quantity' => array(
				'class' => 'product-quantity',
				'text'  => __( 'Quantity', 'wcpe' ),
			),
			'item-subtotal' => array(
				'class' => 'product-subtotal',
				'text'  => __( 'Subtotal', 'wcpe' ),
			),
			'item-remarks'  => array(
				'class' => 'product-remarks',
				'text'  => __( 'Remarks', 'wcpe' ),
			),
		)
	);

	$actions_colspan = count( $enquiry_table_cols );
	$items_total     = 0.00;
	ob_start();
	?>
	<table class="wcpe-enquiries-table shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
		<tr>
			<?php
			if ( ! empty( $enquiry_table_cols ) && is_array( $enquiry_table_cols ) ) {
				foreach ( $enquiry_table_cols as $col ) {
					?>
					<th class="<?php echo esc_attr( $col['class'] ); ?>"><?php echo esc_html( $col['text'] ); ?></th>
					<?php
				}
			}
			?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $enquiries as $item_id => $item ) {
			$product = wc_get_product( $item_id );

			// Skip the product with blank ID.
			if ( '' === $item_id ) {
				continue;
			}
			?>
			<tr class="woocommerce-cart-form__cart-item cart_item" data-id="<?php echo esc_attr( $item_id ); ?>">
			<?php
			foreach ( $enquiry_table_cols as $col_index => $col ) {
				?>
				<td class="<?php echo esc_attr( $col['class'] ); ?>">
				<?php
				switch ( $col_index ) {
					case 'remove-item':
						?>
						<a href="#" class="remove wcpe-remove-enquiry-item">×</a>
						<?php
						break;

					case 'thumbnail':
						$attach_id = get_post_thumbnail_id( $item_id );
						$thumbnail = wcpe_get_image_src_by_id( $attach_id );
						?>
						<a href="<?php echo esc_url( get_permalink( $item_id ) ); ?>">
							<img width="324" height="324"
								src="<?php echo esc_url( $thumbnail ); ?>"
								class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail"
								alt="">
						</a>
						<?php
						break;

					case 'item-name':
						echo wp_kses_post( apply_filters( 'wcpe_enquiry_item_name', '<a href="' . esc_url( get_permalink( $item_id ) ) . '">' . esc_html( get_the_title( $item_id ) ) . '</a><br />', $item ) );
						echo wp_kses_post( apply_filters( 'wcpe_enquiry_item_cost', $product->get_price_html(), $item ) );
						break;

					case 'item-quantity':
						?>
						<div class="quantity">
							<input type="number" id="<?php echo esc_attr( "item-{$item_id}-quantity" ); ?>" class="input-text qty text" step="1"
									min="0" value="<?php echo esc_attr( $item['quantity'] ); ?>" title="<?php esc_attr_e( 'Qty', 'wcpe' ); ?>"
									size="4" inputmode="numeric">
						</div>
						<?php
						break;

					case 'item-subtotal':
						$price        = (float) $product->get_price();
						$subtotal     = $price * $item['quantity'];
						$items_total += $subtotal;
						echo wp_kses_post( wc_price( apply_filters( 'wcpe_enquiry_item_subtotal_cost', $subtotal, $item ) ) );
						break;

					case 'item-remarks':
						?>
						<textarea id="<?php echo esc_attr( "item-{$item_id}-remarks" ); ?>"><?php echo esc_html( $item['remarks'] ); ?></textarea>
						<?php
						break;

					default:
						echo wp_kses_post( apply_filters( "wcpe_enquiry_item_{$col_index}_data", '', $item_id, $item ) );
						break;
				}
				?>
				</td>
			<?php } ?>
			</tr>
		<?php } ?>
		<tr>
			<td class="actions" colspan="<?php echo esc_attr( $actions_colspan ); ?>">
				<button type="button" class="button wcpe-add-to-cart"><?php echo esc_html( apply_filters( 'wcpe_add_to_cart_button_text', __( 'Add to cart', 'wcpe' ) ) ); ?></button>
				<button type="button" class="button wcpe-update-enquiries"><?php esc_html_e( 'Update', 'wcpe' ); ?></button>
			</td>
		</tr>
		</tbody>
	</table>
	<div class="cart-collaterals">
		<div class="cart_totals ">
			<h2><?php echo esc_html( apply_filters( 'wcpe_items_total_heading', __( 'Item totals', 'wcpe' ) ) ); ?></h2>
			<table cellspacing="0" class="shop_table shop_table_responsive">
				<tbody>
				<tr class="order-total">
					<th><?php echo esc_html( apply_filters( 'wcpe_items_total_label', __( 'Total', 'wcpe' ) ) ); ?></th>
					<td data-title="Total"><strong><?php echo wp_kses_post( wc_price( apply_filters( 'wcpe_enquiry_items_total_cost', $items_total, $enquiries ) ) ); ?></strong></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Return the html when there are no enquiries available.
 *
 * @return string
 */
function wcpe_get_no_enquiries_html() {
	$shop_url = apply_filters( 'wcpe_shop_page_url', get_permalink( woocommerce_get_page_id( 'shop' ) ) );
	ob_start();
	?>
	<p class="cart-empty woocommerce-info"><?php echo esc_html( apply_filters( 'wcpe_empty_enquiry_list_message', __( 'You have not yet added any product to your enquiry list.', 'wcpe' ) ) ); ?></p>
	<p class="return-to-shop">
		<a class="button wc-backward"
			href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( apply_filters( 'wcpe_return_to_shop_button_text', __( 'Return to shop', 'wcpe' ) ) ); ?></a>
	</p>
	<?php
	return ob_get_clean();
}

/**
 * Validate the data submitted for enquiry.
 *
 * @param string $first_name Holds the first name.
 * @param string $last_name Holds the last name.
 * @param string $phone Holds the phone number.
 * @param string $email Holds the email address.
 * @return boolean|string
 */
function wcpe_validate_post_data_submit_enquiry( $first_name, $last_name, $phone, $email ) {

	$error_li = '';

	// Validate first name.
	$first_name = sanitize_title( $first_name );
	if ( empty( $first_name ) ) {
		$error_li .= '<li>' . __( 'First name is either empty or invalid.', 'wcpe' ) . '</li>';
	}

	// Validate last name.
	$last_name = sanitize_title( $last_name );
	if ( empty( $last_name ) ) {
		$error_li .= '<li>' . __( 'Last name is either empty or invalid.', 'wcpe' ) . '</li>';
	}

	// Validate email.
	$email = sanitize_email( $email );
	if ( empty( $email ) ) {
		$error_li .= '<li>' . __( 'Email address is either empty or invalid.', 'wcpe' ) . '</li>';
	}

	// Validate phone number.
	$phone = preg_replace( '/[^0-9]/', '', $phone );

	if ( 10 !== strlen( $phone ) ) {
		$error_li .= '<li>' . __( 'Phone number is either empty or invalid.', 'wcpe' ) . '</li>';
	}

	// Check if there exists any error.
	if ( ! empty( $error_li ) ) {
		return "<ol type='1' style='margin: 0 0 0 10px;'>{$error_li}</ol>";
	} else {
		return true;
	}
}

/**
 * Return the enquiries CPT slug.
 *
 * @return string
 */
function wcpe_get_enquiry_cpt_slug() {

	return 'enquiry';
}

/**
 * Register enquiries CPT.
 */
function wcpe_register_enquiries_cpt() {
	$cpt    = wcpe_get_enquiry_cpt_slug();
	$labels = array(
		'name'               => __( 'Enquiries', 'wcpe' ),
		'singular_name'      => __( 'Enquiry', 'wcpe' ),
		'menu_name'          => __( 'Enquiries', 'wcpe' ),
		'name_admin_bar'     => __( 'Enquiry', 'wcpe' ),
		'add_new'            => __( 'New Enquiry', 'wcpe' ),
		'add_new_item'       => __( 'New Enquiry', 'wcpe' ),
		'new_item'           => __( 'Manual3 Enquiry', 'wcpe' ),
		'edit_item'          => __( 'Edit Enquiry', 'wcpe' ),
		'view_item'          => __( 'View Enquiry', 'wcpe' ),
		'all_items'          => __( 'All Enquiries', 'wcpe' ),
		'search_items'       => __( 'Search Enquiries', 'wcpe' ),
		'parent_item_colon'  => __( 'Parent Enquiries:', 'wcpe' ),
		'not_found'          => __( 'No Enquiry Found.', 'wcpe' ),
		'not_found_in_trash' => __( 'No Enquiry Found In Trash.', 'wcpe' ),
	);

	/**
	 * CPT ARgumentes filter.
	 */
	$args = apply_filters(
		'wcpe_enquiry_cpt_args',
		array(
			'labels'             => $labels,
			'public'             => false,
			'menu_icon'          => '',
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'enquiries',
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'enquiries' ),
			'capability_type'    => 'post',
			'capabilities'       => array(
				'create_posts' => false,
			),
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'excerpt', 'author' ),
		)
	);

	register_post_type( $cpt, $args );

	$set = get_option( 'cpt_enquiry_flushed_rewrite_rules' );
	if ( 'yes' !== $set ) {
		flush_rewrite_rules( false );
		update_option( 'cpt_enquiry_flushed_rewrite_rules', 'yes', 'no' );
	}
}

/**
 * Return the list of enquiry email recipients.
 *
 * @param int    $send_customer_a_copy Holds the confirmation if the customer should receive an email copy.
 * @param string $email Holds the customer's email address.
 * @return array
 */
function wcpe_enquiry_email_recipients( $send_customer_a_copy, $email ) {
	$recipients = array();

	// If customer has requested for a copy.
	if ( 1 === $send_customer_a_copy ) {
		$recipients[] = $email;
	}

	// If email to admin is enabled.
	if ( wcpe_send_enquiry_email_to_admin() ) {
		$recipients[] = get_option( 'admin_email' );
	}

	// If email to product author is enabled.
	if ( wcpe_send_enquiry_email_to_author() ) {
		$enquiry_items = wcpe_get_enquiries();

		if ( ! empty( $enquiry_items ) && is_array( $enquiry_items ) ) {
			foreach ( $enquiry_items as $item_id => $item ) {
				$item_author_id = get_post_field( 'post_author', $item_id );

				if ( ! empty( $item_author_id ) ) {
					$item_author = get_userdata( $item_author_id );

					if ( false === $item_author ) {
						continue;
					}

					$recipients[] = $item_author->data->user_email;
				}
			}
		}
	}

	// Extra recipients.
	$extra_recipients = wcpe_enquiry_email_extra_recipients();

	if ( ! empty( $extra_recipients ) && is_array( $extra_recipients ) ) {
		$recipients = array_merge( $extra_recipients, $recipients );
	}

	// If no recipients are defined, send the email to site admin.
	if ( empty( $recipients ) ) {
		$recipients[] = get_option( 'admin_email' );
	}

	$recipients = array_unique( $recipients );

	return $recipients;
}

/**
 * Return the enquiry form HTML.
 *
 * @return string
 */
function wcpe_get_enquiry_form_html() {
	$privacy_policy = get_option( 'wcpe_privacy_policy_message' );
	ob_start();
	?>
	<div class="col2-set" id="customer_details">
		<div class="col-1">
			<div class="woocommerce-billing-fields">
				<h3><?php echo esc_html( apply_filters( 'wcpe_enquiry_form_heading', __( 'Complete your enquiry', 'wcpe' ) ) ); ?></h3>
				<div class="woocommerce-billing-fields__field-wrapper">
					<?php do_action( 'wcpe_before_enquiry_form_fields' ); ?>
					<!-- FIRST NAME -->
					<p class="form-row form-row-first">
						<label for="wcpe-enquiry-first-name"><?php esc_html_e( 'First name', 'wcpe' ); ?>&nbsp;
							<abbr class="required" title="required">*</abbr>
						</label>
						<span class="woocommerce-input-wrapper">
							<input type="text" class="input-text" id="wcpe-enquiry-first-name" placeholder="<?php esc_attr_e( 'John', 'wcpe' ); ?>">
						</span>
					</p>

					<!-- LAST NAME -->
					<p class="form-row form-row-last">
						<label for="wcpe-enquiry-last-name"><?php esc_html_e( 'Last name', 'wcpe' ); ?>&nbsp;
							<abbr class="required" title="required">*</abbr>
						</label>
						<span class="woocommerce-input-wrapper">
							<input type="text" class="input-text" id="wcpe-enquiry-last-name" placeholder="<?php esc_attr_e( 'Doe', 'wcpe' ); ?>">
						</span>
					</p>

					<!-- PHONE -->
					<p class="form-row form-row-wide">
						<label for="wcpe-enquiry-phone"><?php esc_html_e( 'Phone', 'wcpe' ); ?>&nbsp;
							<abbr class="required" title="required">*</abbr>
						</label>
						<span class="woocommerce-input-wrapper">
							<input type="tel" class="input-text" id="wcpe-enquiry-phone" placeholder="+91 9889988998">
						</span>
					</p>

					<!-- EMAIL -->
					<p class="form-row form-row-wide">
						<label for="wcpe-enquiry-email"><?php esc_html_e( 'Email address', 'wcpe' ); ?>&nbsp;
							<abbr class="required" title="required">*</abbr>
						</label>
						<span class="woocommerce-input-wrapper">
							<input type="email" class="input-text" id="wcpe-enquiry-email" placeholder="john.doe@example.com">
						</span>
					</p>

					<!-- ENQUIRY -->
					<p class="form-row notes">
						<label for="wcpe-enquiry-comment"><?php esc_html_e( 'Enquiry', 'wcpe' ); ?>&nbsp;
							<span class="optional"><?php esc_html_e( '(optional)', 'wcpe' ); ?></span>
						</label>
						<span class="woocommerce-input-wrapper">
							<textarea class="input-text" id="wcpe-enquiry-comment" placeholder="<?php esc_html_e( 'Notes about your enquiry..', 'wcpe' ); ?>"></textarea>
						</span>
					</p>

					<!-- CAPTCHA -->
					<p class="form-row form-row-wide">
						<label for="wcpe-captcha-ans"><?php esc_html_e( 'Are you human, or spambot?', 'wcpe' ); ?>&nbsp;
							<abbr class="required" title="required">*</abbr>
						</label>
						<span class="woocommerce-input-wrapper">
							<input type="number" class="input-text" id="wcpe-captcha-number-1">
							+
							<input type="number" class="input-text" id="wcpe-captcha-number-2">
							=
							<input type="number" class="input-text" id="wcpe-captcha-ans">
						</span>
					</p>

					<!-- SEND ME A COPY -->
					<p class="form-row wcpe-send-customer-copy-field">
						<label for="wcpe-enquiry-send-customer-a-copy"><?php esc_html_e( 'Send me a copy', 'wcpe' ); ?></label>
						<span class="woocommerce-input-wrapper">
							<input type="checkbox" id="wcpe-enquiry-send-customer-a-copy" />
						</span>
					</p>

					<!-- PRIVACY POLICY MESSAGE -->
					<?php if ( ! empty( $privacy_policy ) ) { ?>
						<p class="form-row wcpe-privacy-policy-field">
							<label for="wcpe-privacy-policy-message"><?php echo esc_html( $privacy_policy ); ?></label>
							<span class="woocommerce-input-wrapper">
								<input type="checkbox" id="wcpe-privacy-policy-message" />
							</span>
						</p>
					<?php } ?>

					<?php do_action( 'wcpe_after_enquiry_form_fields' ); ?>
				</div>
				<div class="wc-proceed-to-checkout">
					<a href="#" class="checkout-button button alt wc-forward wcpe-submit-enquiry"><?php echo esc_html( apply_filters( 'wcpe_submit_enquiry_button_label', __( 'Submit', 'wcpe' ) ) ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
