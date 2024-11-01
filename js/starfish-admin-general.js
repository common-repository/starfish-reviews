jQuery(function ($) {
	/**
	 * TRIGGER PLUGIN MIGRATIONS
	 * ***********************************************
	 */
	$("#srm-start-migrations").unbind("click").on("click", function () {
		const request = {
			action: 'starfish-execute-migrations'
		};
		$.ajax({
			action: 'starfish-execute-migrations',
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: request,
			success: function (data) {
				window.location.reload();
			}
		});
	});
});

/**
 * get success response for resetting default settings on Funnels
 */
function restore_success_response(response, elementRestoreButton) {
	if(response.type === 'success') {
		// Remove loading indicator, and disable button
		elementRestoreButton.addClass('srm-settings-button-completed').html('Completed! <i class=\'fa fa-check\'></i>').attr("disabled", true);
		// Add success message
		elementRestoreButton.after('<div><strong>Refresh the page to see changes.</strong></div>')
	}
	else {
		elementRestoreButton.addClass('srm-settings-button-failed').html('Oops!, It Failed. <i class=\'fa fa-times\'></i>').attr("disabled", true);
		// Add error message
		elementRestoreButton.after('<div><strong>Something went wrong. Refresh to try again.</strong></div>');
	}
}

/**
 * get error response for resetting default settings on Funnels
 */
function restore_error_response(data, elementRestoreButton) {
	elementRestoreButton.addClass('srm-settings-button-failed').html('Oops!, It Failed. <i class=\'fa fa-times\'></i>').attr("disabled", true);
	// Add error message
	elementRestoreButton.after('<div><strong>Something went wrong. Refresh to try again.</strong></div>');
}
