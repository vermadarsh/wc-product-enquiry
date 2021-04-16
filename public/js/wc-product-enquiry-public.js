/**
 * Public jQuery file.
 */
jQuery( document ).ready( function( $ ) {
	'use strict';

	/* eslint-disable */
	var {
		ajaxurl,
		processing_btn_txt,
		notification_success_header,
		notification_error_header,
		simple_product_quantity_err,
		is_wc_archive,
		is_product,
		invalid_ajax_response,
		remove_item_cnf_message,
		invalid_item_id_err,
		first_name_empty,
		last_name_empty,
		phone_number_empty,
		phone_number_invalid,
		email_address_empty,
		email_address_invalid,
		captcha_incorrect,
		privacy_policy_error,
		no_items_error,
		wcpe_ajax_nonce,
		ajax_nonce_failure,
	} = WCPE_Public_JS_Obj;
	/* eslint-enable */

	/**
	 * Add simple|external product to enquiry.
	 */
	$( document ).on( 'click', '.wcpe-add-simple-product-to-enquiry', function( e ) {
		e.preventDefault();

		var this_btn = $( this );
		var this_btn_text = this_btn.text();
		var product_id = this_btn.data( 'productid' );
		var quantity;

		// Check for archive page.
		if ( '1' === is_wc_archive ) {
			quantity = 1;
		} else {
			if ( '1' === is_product ) {
				// Check, if the product is simple or external.
				if ( this_btn.hasClass( 'is-simple' ) ) {
					quantity = parseInt( this_btn.siblings( '.quantity' ).find( '.qty' ).val() );
				} else if ( this_btn.hasClass( 'is-external' ) ) {
					quantity = 1;
				}
			}
		}

		if ( -1 === is_valid( quantity ) ) {
			// Show the error notification.
			wcpe_show_notification( 'fa fa-warning', notification_error_header, simple_product_quantity_err, 'error' );
			return false;
		}

		this_btn.text( processing_btn_txt );
		block_element( this_btn );

		// Send AJAX to add product to enquiry list.
		var data = {
			action: 'add_product_for_enquiry',
			product_id: product_id,
			quantity: quantity,
			wcpe_ajax_nonce: wcpe_ajax_nonce,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}
				
				var {code, notification_message} = response.data;
				this_btn.text( this_btn_text );
				unblock_element( this_btn );

				if ( 'wcpe-simple-product-added-for-enquiry' === code ) {
					var {notification_message} = response.data;
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
				} else if ( 'wcpe-simple-product-not-for-enquiry' === code ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, notification_message, 'error' );
				}
			},
		} );
	} );

	/**
	 * Enable/disable the enquiry button on variations select.
	 */
	$( document ).on( 'change', '.variations select', function() {
		var add_to_cart_class = $( '.woocommerce-variation-add-to-cart' ).attr( 'class' );

		// If the add to cart button is enabled, enable the enquiry button too.
		if ( -1 !== add_to_cart_class.indexOf( 'woocommerce-variation-add-to-cart-enabled' ) ) {
			unblock_element( $( '.wcpe-add-variation-to-enquiry' ) );
		} else {
			block_element( $( '.wcpe-add-variation-to-enquiry' ) );
		}
	} );

	/**
	 * Add variable product to enquiry.
	 */
	$( document ).on( 'click', '.wcpe-add-variation-to-enquiry', function( e ) {
		e.preventDefault();

		var this_btn = $( this );
		var variation_id = this_btn.siblings( 'input[name="variation_id"]' ).val();

		if ( -1 === is_valid( variation_id ) ) {
			// Show the error notification.
			wcpe_show_notification( 'fa fa-warning', notification_error_header, variation_id_err, 'error' );
			return false;
		}

		var quantity = parseInt( this_btn.siblings( '.quantity' ).find( '.qty' ).val() );

		if ( -1 === is_valid( quantity ) ) {
			// Show the error notification.
			wcpe_show_notification( 'fa fa-warning', notification_error_header, simple_product_quantity_err, 'error' );
			return false;
		}

		var this_btn_text = this_btn.text();
		this_btn.text( processing_btn_txt ).addClass( 'non-clickable' );

		// Send AJAX to add product to enquiry list.
		var data = {
			action: 'add_variation_for_enquiry',
			variation_id: variation_id,
			quantity: quantity,
			wcpe_ajax_nonce: wcpe_ajax_nonce,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				if ( 'wcpe-variation-added-for-enquiry' === response.data.code ) {
					var {notification_message} = response.data;
					this_btn.text( this_btn_text ).removeClass( 'non-clickable' );
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
				}
			},
		} );
	} );

	/**
	 * Add grouped products child products to array.
	 */
	var grouped_product_children_quantity = '.woocommerce-grouped-product-list tr td.woocommerce-grouped-product-list-item__quantity input[type="number"]';
	$( document ).on( 'keyup click', grouped_product_children_quantity, function( e ) {

		var enquiry_products = [];
		// Traverse the child products.
		$( grouped_product_children_quantity ).each( function() {
			var this_qty   = $( this );
			var quantity   = parseInt( this_qty.val() );
			var name_attr  = this_qty.attr( 'name' );
			var product_id = parseInt( name_attr.match( /\d+/ )[0] );

			if ( -1 === is_valid( quantity ) ) {
				return true;
			}

			var temp_arr = {};
			temp_arr['product_id'] = product_id;
			temp_arr['quantity']   = quantity;

			// Push the data into the array.
			enquiry_products.push( temp_arr );
		} );

		if ( 0 < enquiry_products.length ) {
			$( '.wcpe-add-grouped-product-to-enquiry' ).removeClass( 'non-clickable' );
		} else {
			$( '.wcpe-add-grouped-product-to-enquiry' ).addClass( 'non-clickable' );
		}
	} );

	/**
	 * Add grouped products child products to enquiry.
	 */
	$( document ).on( 'click', '.wcpe-add-grouped-product-to-enquiry', function( e ) {
		e.preventDefault();

		// Skip the action if enquiry products are not available.
		if ( 0 >= enquiry_products.length ) {
			return false;
		}

		var this_btn = $( this );
		var this_btn_text = this_btn.text();
		this_btn.text( processing_btn_txt ).addClass( 'non-clickable' );

		// Send AJAX to add product to enquiry list.
		var data = {
			action: 'add_grouped_products_for_enquiry',
			enquiry_products: enquiry_products,
			wcpe_ajax_nonce: wcpe_ajax_nonce,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message} = response.data;
				this_btn.text( this_btn_text ).removeClass( 'non-clickable' );

				if ( 'wcpe-products-added-for-enquiry' === code ) {
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );

					// Vacate the quantity input boxes.
					$( grouped_product_children_quantity ).each( function() {
						$( this ).val( '' );
					} );
				} else if ( 'wcpe-product-not-for-enquiry' === code ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, notification_message, 'error' );
				}
			},
		} );
	} );

	/**
	 * Remove the enquiry item from the enquiries.
	 */
	$( document ).on( 'click', '.wcpe-remove-enquiry-item', function( e ) {
		e.preventDefault();
		var this_btn = $( this );
		var item_id  = this_btn.parents( 'tr' ).data( 'id' );

		if ( -1 === is_valid( item_id ) ) {
			// Show the error notification.
			wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_item_id_err, 'error' );
			return false;
		}

		var remove_item_cnf = confirm( remove_item_cnf_message );
		if ( true !== remove_item_cnf ) {
			return false;
		}

		block_element( $( '.wcpe-enquiries-table' ) );
		// Send AJAX to remove enquiry item.
		var data = {
			action: 'remove_enquiry_item',
			item_id: item_id,
			wcpe_ajax_nonce: wcpe_ajax_nonce,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '.wcpe-enquiries-table' ) );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message, html} = response.data;
				
				if ( 'wcpe-enquiry-item-removed' === code ) {
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcpe-enquiries-container' ).html( html );
				} else if ( 'wcpe-invalid-item' === code ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, notification_message, 'error' );
				}
			},
		} );
	} );

	/**
	 * Update the enquiries.
	 */
	$( document ).on( 'click', '.wcpe-update-enquiries', function( e ) {
		e.preventDefault();

		var enquiries = [];

		// Traverse the table rows to fetch the enquiry details.
		$( '.wcpe-enquiries-table tbody tr' ).each( function() {
			var tr = $( this );
			var item_id = tr.data( 'id' );
			var quantity = parseInt( $( '#item-' + item_id + '-quantity' ).val() );

			if ( -1 === is_valid( quantity ) ) {
				return true;
			}
			
			var temp_arr = {};
			temp_arr['product_id'] = item_id;
			temp_arr['quantity']   = quantity;
			temp_arr['remarks']    = $( '#item-' + item_id + '-remarks' ).val();

			enquiries.push( temp_arr );
		} );

		block_element( $( '.wcpe-enquiries-table' ) );
		// Send AJAX to remove enquiry item.
		var data = {
			action: 'update_enquiries',
			enquiries: enquiries,
			wcpe_ajax_nonce: wcpe_ajax_nonce,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '.wcpe-enquiries-table' ) );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message, html} = response.data;

				if ( 'wcpe-enquiries-updated' === code ) {
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcpe-enquiries-container' ).html( html );
				}
			},
		} );
	} );

	/**
	 * Captcha numbers.
	 */
	if ( $( '.wcpe-enquiries-container' ).length ) {
		var num1 = Math.floor( Math.random() * 100 );
		$( '#wcpe-captcha-number-1' ).val( num1 );
		block_element( $( '#wcpe-captcha-number-1' ) );

		var num2 = Math.floor( Math.random() * 100 );
		$( '#wcpe-captcha-number-2' ).val( num2 );
		block_element( $( '#wcpe-captcha-number-2' ) );

		var captcha_answer = num1 + num2;
	}

	/**
	 * Submit the enquiry form.
	 */
	$( document ).on( 'click', '.wcpe-submit-enquiry', function( e ) {
		e.preventDefault();

		var first_name  = $( '#wcpe-enquiry-first-name' ).val();
		var last_name   = $( '#wcpe-enquiry-last-name' ).val();
		var phone       = $( '#wcpe-enquiry-phone' ).val();
		var email       = $( '#wcpe-enquiry-email' ).val();
		var comment     = $( '#wcpe-enquiry-comment' ).val();
		var captcha_ans = parseInt( $( '#wcpe-captcha-ans' ).val() );
		var error       = '';
		var error_li    = '';

		// Validate first name.
		if ( '' === first_name ) {
			error_li += '<li>' + first_name_empty + '</li>';
		}

		// Validate last name.
		if ( '' === last_name ) {
			error_li += '<li>' + last_name_empty + '</li>';
		}

		// Validate the phone number.
		if ( '' === phone ) {
			error_li += '<li>' + phone_number_empty + '</li>';
		} else if ( -1 === is_valid_phone_number( phone ) ) {
			error_li += '<li>' + phone_number_invalid + '</li>';
		}

		// Validate the email address.
		if ( '' === email ) {
			error_li += '<li>' + email_address_empty + '</li>';
		} else if ( -1 === is_valid_email( email ) ) {
			error_li += '<li>' + email_address_invalid + '</li>';
		}
		
		// Validate the captcha.
		if ( captcha_ans !== captcha_answer ) {
			error_li += '<li>' + captcha_incorrect + '</li>';
		}

		// Validate the privacy policy message checkbox, if available.
		if ( $( '#wcpe-privacy-policy-message' ).length && $( '#wcpe-privacy-policy-message' ).is( ':not(:checked)' ) ) {
			error_li += '<li>' + privacy_policy_error + '</li>';
		}

		// Show the error message.
		if ( '' !== error_li ) {
			error = '<ol type="1" style="margin: 0 0 0 10px;">' + error_li + '</ol>';
			wcpe_show_notification( 'fa fa-warning', notification_error_header, error, 'error' );
			return false;
		}

		var send_customer_a_copy = -1;
		if ( $( '#wcpe-enquiry-send-customer-a-copy' ).is( ':checked' ) ) {
			send_customer_a_copy = 1;
		}

		block_element( $( '.wcpe-enquiries-container' ) );
		// Send AJAX to remove enquiry item.
		var data = {
			action: 'submit_enquiry',
			first_name: first_name,
			last_name: last_name,
			phone: phone,
			email: email,
			comment: comment,
			send_customer_a_copy: send_customer_a_copy,
			wcpe_ajax_nonce: wcpe_ajax_nonce
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '.wcpe-enquiries-container' ) );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message, html} = response.data;

				if ( 'wcpe-enquiry-submitted' === code ) {
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcpe-enquiries-container' ).html( html );
				}
			},
		} );
	} );

	/**
	 * Add items to cart modal.
	 */
	$( document ).on( 'click', '.wcpe-add-to-cart', function() {
		$( '.wcpe-modal' ).addClass( 'open' );
		var items_html = '';

		// Traverse the table rows to fetch the enquiry details.
		$( '.wcpe-enquiries-table tbody tr' ).each( function() {
			var tr = $( this );

			// Check if tr has class: woocommerce-cart-form__cart-item
			var tr_has_class = tr.hasClass( 'woocommerce-cart-form__cart-item' );
			
			if ( ! tr_has_class ) {
				return true;
			}

			var item_id = tr.data( 'id' );
			var quantity = parseInt( $( '#item-' + item_id + '-quantity' ).val() );

			if ( -1 === is_valid( quantity ) ) {
				return true;
			}

			var permalink = tr.find( 'td.product-name a' ).attr( 'href' );
			var thumbnail = tr.find( 'td.product-thumbnail a img' ).attr( 'src' );
			var name = tr.find( 'td.product-name' ).html();
			
			items_html += '<tr class="woocommerce-cart-form__cart-item cart_item" data-id="' + item_id + '">';
			items_html += '<td class="product-thumbnail">';
			items_html += '<a href="' + permalink + '">';
			items_html += '<img width="324" height="324" src="' + thumbnail + '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">';
			items_html += '</a>';
			items_html += '</td>';
			items_html += '<td class="product-name">' + name + '</td>';
			items_html += '<td class="product-quantity">';
			items_html += '<div class="quantity">';
			items_html += '<input type="number" class="input-text qty text" step="1" min="0" value="' + quantity + '" title="Qty" size="4" inputmode="numeric">';
			items_html += '</div>';
			items_html += '</td>';
			items_html += '</tr>';
		} );

		$( '.wcpe-add-to-cart-table tbody' ).html( items_html );
	} );

	/**
	 * Close modal.
	 */
	$( document ).on( 'click', '.close', function() {
		$( '.wcpe-modal' ).removeClass( 'open' );
	} );

	$( document ).on( 'click', '.wcpe-add-items-to-cart', function( e ) {
		e.preventDefault();

		var items = [];
		// Traverse the table rows to fetch the enquiry details.
		$( '.wcpe-add-to-cart-table tbody tr' ).each( function() {
			var tr = $( this );
			var item_id = tr.data( 'id' );
			var quantity = parseInt( tr.find( 'td.product-quantity div input[type="number"]' ).val() );

			if ( -1 === is_valid( quantity ) ) {
				return true;
			}
			
			var temp_arr = {};
			temp_arr['product_id'] = item_id;
			temp_arr['quantity']   = quantity;

			items.push( temp_arr );
		} );

		if ( 0 === items.length ) {
			wcpe_show_notification( 'fa fa-warning', notification_error_header, no_items_error, 'error' );
			return false;
		}

		block_element( $( '.wcpe-modal .content' ) );
		block_element( $( '.wcpe-modal .modal-footer' ) );
		// Send AJAX to add these items to cart.
		var data = {
			action: 'add_to_cart',
			items: items,
			wcpe_ajax_nonce: wcpe_ajax_nonce
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				// Update the mini cart now.
				wcpe_update_mini_cart();

				unblock_element( $( '.wcpe-modal .content' ) );
				unblock_element( $( '.wcpe-modal .modal-footer' ) );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message} = response.data;

				if ( 'wcpe-items-added-to-cart' === code ) {
					wcpe_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcpe-modal' ).removeClass( 'open' );
				}
			},
		} );
	} );

	/**
	 * Check if a number is valid.
	 * 
	 * @param {number} data 
	 */
	function is_valid( data ) {
		if ( '' === data || undefined === data || isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}

	/**
	 * Validate email.
	 *
	 * @param {string} email 
	 */
	function is_valid_email( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if ( ! regex.test( email ) ) {
			return -1;
		}

		return 1;
	}

	/**
	 * Validate email.
	 *
	 * @param {string} email 
	 */
	function is_valid_phone_number( phone ) {
		var regex = /^[0-9\-\(\)\s]+$/;

		if ( ! regex.test( phone ) ) {
			return -1;
		}

		return 1;
	}

	/**
	 * Block element.
	 *
	 * @param {string} element 
	 */
	function block_element( element ) {
		element.addClass( 'non-clickable' );
	}

	/**
	 * Unblock element.
	 *
	 * @param {string} element 
	 */
	function unblock_element( element ) {
		element.removeClass( 'non-clickable' );
	}

	/**
	 * Function defined to show the notification.
	 *
	 * @param {string} icon_class
	 * @param {string} header_text
	 * @param {string} message
	 * @param {string} success_or_error
	 */
	function wcpe_show_notification( icon_class, header_text, message, success_or_error ) {
		$('.wcpe_notification_popup .wcpe_notification_icon i').removeClass().addClass( icon_class );
		$('.wcpe_notification_popup .wcpe_notification_message h3').html( header_text );
		$('.wcpe_notification_popup .wcpe_notification_message p').html( message );
		$('.wcpe_notification_popup').removeClass('is-success is-error');

		if ( 'error' === success_or_error ) {
			$( '.wcpe_notification_popup' ).addClass( 'active is-error' );
		} else if ( 'success' === success_or_error ) {
			$( '.wcpe_notification_popup' ).addClass( 'active is-success' );
		}

		// Dismiss the notification after 3 secs.
		setTimeout( function () {
			wcpe_hide_notification();
		}, 3000 );
	}

	/**
	 * Function to hide notification
	 */
	function wcpe_hide_notification() {
		$( '.wcpe_notification_popup' ).removeClass( 'active' );
	}

	// Update the mini cart.
	function wcpe_update_mini_cart() {
		var data = {
			action: 'update_mini_cart',
			wcpe_ajax_nonce: wcpe_ajax_nonce,
		};
		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				$( '.widget_shopping_cart_content' ).html( response );
			},
		} );
	}
} );
