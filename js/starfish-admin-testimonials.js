/**
 * Testimonial Admin Processing
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0
 */
$s = jQuery.noConflict();
$s(
	function ($) {

		hljs.highlightAll();
		$( document ).ready(
			function() {
				// Star Rating Selection.
				$( "#srm-testimonial-rating" ).rating(
					{
						min: 0,
						max: 5,
						step: 1,
						size: 'md',
						hoverOnClear: false,
						starCaptions: {1: 'Very Poor', 2: 'Poor', 3: 'Ok', 4: 'Good', 5: 'Very Good'},
						// starCaptionClasses: {1: 'text-danger', 2: 'text-warning', 3: 'text-info', 4: 'text-primary', 5: 'text-success'}
					}
				);
				// Testimonial Visibility
				// =============================
				// Update hide status to VISIBLE.
				$( '.srm-testimonial-hide-toggle .toggle-on' ).click(
					function () {
						let testimonial_id = $( this ).parent().prev( 'input' ).data( "srm_testimonial_id" );
						srm_hide_testimonial( false, testimonial_id );
					}
				);
				// Update hide status to HIDDEN.
				$( '.srm-testimonial-hide-toggle .toggle-off' ).click(
					function () {
						let testimonial_id = $( this ).parent().prev( 'input' ).data( "srm_testimonial_id" );
						srm_hide_testimonial( true, testimonial_id );
					}
				);
			}
		);

		function srm_hide_testimonial(is_hidden, testimonial_id) {
			let request = {
				action: 'srm-testimonial-hide',
				is_hidden: is_hidden,
				review_id: testimonial_id,
				security: testimonial_vars.testimonial_nonce
			};
			$.ajax(
				{
					action: 'srm-testimonial-hide',
					type: 'POST',
					dataType: 'json',
					url: testimonial_vars.ajax_url,
					data: request,
					success: function (data) {
						console.info( "Successfully requested testimonial hidden status be updated. Response = " + data.message );
					},
					error: function (data) {
						console.error( "Failed to update testimonial hidden status. Response = " + data.message );
					}
				}
			)
		}

	}
);
