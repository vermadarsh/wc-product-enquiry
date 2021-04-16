jQuery( document ).ready( function( $ ) {
	'use strict';

	var {
		ajaxurl
	} = WCPE_Admin_JS_Obj;

	/**
	 * Enable/disable enquiry floating button.
	 */
	$( document ).on( 'click', '#wcpe_enable_enquiry_floating_button', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '#wcpe_enquiry_page, #wcpe_product_enquiry_floating_button_icon' ).parents( 'tr' ).show();
		} else {
			$( '#wcpe_enquiry_page, #wcpe_product_enquiry_floating_button_icon' ).parents( 'tr' ).hide();
		}
	} );

	/**
	 * Show/hide the api keys input boxes based on sandbox modes.
	 */
	if ( $( '#wcpe_enquiry_page' ).hasClass( 'dnone' ) ) {
		$( '#wcpe_enquiry_page' ).parents( 'tr' ).hide();
	}
	if ( $( '#wcpe_product_enquiry_floating_button_icon' ).hasClass( 'dnone' ) ) {
		$( '#wcpe_product_enquiry_floating_button_icon' ).parents( 'tr' ).hide();
	}
} );
