/**
 * Export Feedback
 *
 * @package    WordPress
 * @subpackage starfish
 * @version    1
 */

jQuery(
	function ($) {
		$( document ).ready(
			function () {
				$( '#srm-export-feedback' ).click(
					function () {
						const request = {
							security: feedback_vars.feedback_nonce,
							funnel_id: feedback_vars.funnel_id,
							month: feedback_vars.month,
							action: 'srm-export-feedback'
						};
						return $.ajax(
							{
								action: 'srm-export-feedback',
								type: 'POST',
								dataType: 'json',
								url: ajaxurl,
								data: request,
								success: function (data) {
									if (data.type == "success") {
										console.log( 'STARFISH: Export Feedback Successful' )
										$( "#srm-feedback-export-iframe" ).attr( 'src', data.url );
										$( '.srm-admin-actions.srm-admin-feedback' ).find( '.dropdown-menu' )
										.append( '<a class="dropdown-item" href="' + data.url + '">' + data.file_name + '</a> <i title="Delete Export"' + ' style="color: orangered" class="srm-admin-delete-feedback far fa-trash-alt"></i>' )
									} else {
										console.log( 'STARFISH: Export Feedback Failed!' )
										$( '.srm-admin-actions' ).append( "<div style='color: red'>Failed to Export</div>" )
									}
								},
								error: function (data) {
									console.log( 'STARFISH: Export Feedback Failed!' )
									console.log( data )
									$( '.srm-admin-actions' ).append( "<div style='color: red'>Failed to Export</div>" )
								}
							}
						)
					}
				)
				$( '.srm-admin-delete-feedback' ).click(
					function () {
						const file_element   = $( this )
						const file_to_delete = file_element.prev().text()
						const request        = {
							security: feedback_vars.feedback_nonce, file: file_to_delete, action: 'srm-delete-feedback-export'
						};
						return $.ajax(
							{
								action: 'srm-delete-feedback-export',
								type: 'POST',
								dataType: 'json',
								url: ajaxurl,
								data: request,
								success: function (data) {
									if (data.type == "success") {
										console.log( 'STARFISH: successfully deleted export ' + file_to_delete )
										file_element.prev().remove()
										file_element.remove()
									} else {
										console.log( 'STARFISH: failed to delete export ' + file_to_delete )
									}
								},
								error: function (data) {
									console.log( 'STARFISH: failed to delete export ' + file_to_delete )
									console.log( data )
								}
							}
						)
					}
				)
				$( '.srm-admin-actions .dropdown-menu' ).on(
					{
						"click": function (e) {
							e.stopPropagation();
						}
					}
				);
			}
		)
	}
)
