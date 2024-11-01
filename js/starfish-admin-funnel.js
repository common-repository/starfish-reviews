/**
 * Funnel review submission style choose option
 */
$s = jQuery.noConflict();
$s(
	function ($) {

		hljs.highlightAll();

		$( document ).ready(
			function ($) {

				$( '#srm_review_destination' ).blur(
					function () {
						validateFunnelUrl( $, $( this ) );
					}
				);
				$( '[name="srm_desti_url[]"]' ).blur(
					function () {
						validateFunnelUrl( $, $( this ) );
					}
				);
				if ($( '#srm_open_new_window' ).prop( "checked" ) == false) {
					$( '#srm-funnel-embed-snippet > .srm-sidebar-alert' ).show();
				}
				$( '#srm_open_new_window' ).click(
					function () {
						if ($( this ).is( ":checked" )) {
							$( '#srm-funnel-embed-snippet > .srm-sidebar-alert' ).hide();
						} else {
							$( '#srm-funnel-embed-snippet > .srm-sidebar-alert' ).show();
						}
					}
				)

			}
		);

		/**
		 * ***********************************************
		 * Funnel Defaults Button
		 */
		$( "#srm-funnel-defaults-restore" ).unbind( "click" ).on(
			"click",
			function () {
				if (confirm( "This will replace all existing values with the current defaults. Are you sure?" )) {
					// Present loading indicator
					$( this ).html( 'Restoring... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' ).attr( "disabled", true );
					// Set request payload
					const request = {
						action: 'starfish-execute-restore-default-options',
						options: settings.defaults,
						funnel_id: settings.funnel_id
					};
					// Make request
					$.ajax(
						{
							action: 'starfish-execute-restore-default-options',
							type: 'POST',
							dataType: 'json',
							url: ajaxurl,
							data: request,
							success: function (response) {
								restore_success_response( response, $( "#srm-funnel-defaults-restore" ) );
							},
							error: function (data) {
								restore_error_response( data, $( "#srm-funnel-defaults-restore" ) );
							}
						}
					);
				}
			}
		);
		/**
		 * END: Funnel Defaults Button
		 * ***********************************************
		 * ***********************************************
		 * Form Validations
		 */

		$( '#srm_button_style' ).change(
			function () {
				const optionVal = $( this ).val();
				const element   = $( '.button_style_preview' );
				if (optionVal === 'thumbs_outline') {
					element.html( '<span class="faicon iconyes far fa-thumbs-up"></span><span class="faicon iconno far fa-thumbs-down faicon_flip"></span>' );
				}
				if (optionVal === 'thumbs_solid') {
					element.html( '<span class="faicon iconyes fas fa-thumbs-up"></span><span class="faicon iconno fas fa-thumbs-down faicon_flip"></span>' );
				}
				if (optionVal === 'faces') {
					element.html( '<span class="faicon iconyes far fa-smile"></span><span class="faicon iconno far fa-frown"></span>' );
				}
				if (optionVal === 'scircle') {
					element.html( '<span class="faicon iconyes fas fa-check-circle"></span><span class="faicon iconno fas fa-times-circle"></span>' );
				}
			}
		);

		if ($( '#disable_review_gating' ).is( ":checked" )) {
			$( '#srm_public_review_text_field' ).show();
			$( '#srm_skip_feedback_text_field' ).show();
		}

		if ($( '#srm_ask_name' ).is( ":checked" )) {
			$( '#srm_ask_name_required' ).show();
			$( "label[for='srm_ask_name_required']" ).show();
		}

		if ($( '#srm_ask_email' ).is( ":checked" )) {
			$( '#srm_ask_email_required' ).show();
			$( "label[for='srm_ask_email_required']" ).show();
		}

		if ($( '#srm_ask_phone' ).is( ":checked" )) {
			$( '#srm_ask_phone_required' ).show();
			$( "label[for='srm_ask_phone_required']" ).show();
		}

		/**
		 * Toggle funnel feedback form field checkbox
		 *
		 * @param element the checkbox form field element
		 */
		function toggle_required(parent, element) {
			element.toggle().next().toggle();
			if ( ! parent.is( ":checked" )) {
				element.prop( "checked", false );
			}
		}

		$( '#srm_ask_name' ).change(
			function () {
				toggle_required( $( this ), $( '#srm_ask_name_required' ) )
			}
		);

		$( '#srm_ask_email' ).change(
			function () {
				toggle_required( $( this ), $( '#srm_ask_email_required' ) )
			}
		);

		$( '#srm_ask_phone' ).change(
			function () {
				toggle_required( $( this ), $( '#srm_ask_phone_required' ) )
			}
		);

		$( '#disable_review_gating' ).change(
			function () {
				$( '#srm_public_review_text_field' ).toggle();
				$( '#srm_skip_feedback_text_field' ).toggle();
			}
		);

		$( '#srm_no_destination' ).change(
			function () {
				const optionVal = $( this ).val();
				if (optionVal === 'multiple') {
					$( '.multiple-destination-edit' ).show();
					$( '.multiple-destination-layout' ).show();
					$( '.single-destination-edit' ).hide();
					$( '.srm-review-auto-redirect-edit' ).hide();
					$( '.srm-button-text-edit' ).hide();
				} else {
					$( '.multiple-destination-edit' ).hide();
					$( '.multiple-destination-layout' ).hide();
					$( '.single-destination-edit' ).show();
					$( '.srm-review-auto-redirect-edit' ).show();
					$( '.srm-button-text-edit' ).show();
				}
			}
		);

		/**
		 * ***********************************************
		 * HELP DIALOGUES
		 */
		$( "#srm-help-gating-skip-feedback-dialogue" ).dialog(
			{
				autoOpen: false,
				width: "auto",
				height: "auto",
				title: "Gating: Skip Feedback Text",
				modal: true,
				closeOnEscape: true,
				buttons: {},
				open: function (event, ui) {
					$( this ).parent().find( '.ui-dialog-titlebar' ).append( '<li class="fa fa-question-circle"></li>' );
				},
				close: function () {
					$( this ).parent().find( '.ui-dialog-titlebar' ).find( '.fa-question-circle' ).remove();
				}
			}
		);

		$( "#srm-help-gating-skip-feedback" ).click(
			function () {
				$( "#srm-help-gating-skip-feedback-dialogue" ).dialog( "open" );
			}
		);

		$( "#srm-help-gating-public-review-dialogue" ).dialog(
			{
				autoOpen: false,
				width: "auto",
				height: "auto",
				title: "Gating: Public Review Text",
				modal: true,
				closeOnEscape: true,
				buttons: {},
				open: function (event, ui) {
					$( this ).parent().find( '.ui-dialog-titlebar' ).append( '<li class="fa fa-question-circle"></li>' );
				},
				close: function () {
					$( this ).parent().find( '.ui-dialog-titlebar' ).find( '.fa-question-circle' ).remove();
				}
			}
		);

		$( "#srm-help-gating-public-review" ).click(
			function () {
				$( "#srm-help-gating-public-review-dialogue" ).dialog( "open" );
			}
		);
		/**
		 * END: HELP DIALOGUES
		 * ***********************************************
		 */

	}
);
