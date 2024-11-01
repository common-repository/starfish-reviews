$s = jQuery.noConflict();
$s(
	function ($) {

		$( document ).ready(
			function ($) {

				if ($( "#srm_logging" ).is( ":checked" )) {
					$( "#srm-logging-console" ).show();
				}

				// KBA Suggestions
				if ($.isFunction( $.fn.Beacon )) {
					Beacon( 'open' );
				}

                // Hide Settings Page SAVE BUTTON for information-only pages
                if ($("input[value=srm_testimonial_help_options]").length ||
                    $("input[value=srm_support_info_options]").length ||
                    $("input[value=srm_general_migrations_options]").length) {
                    $(".submit").hide();
                }

			}
		);

		/**
		 * SETTINGS: Refresh Plan Limits
		 * ***********************************************
		 */
		$( "#srm-support-site-limit-refresh" ).unbind( "click" ).on(
			"click",
			function () {

				// Present loading indicator
				$( this ).html( 'Refreshing... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' ).attr( "disabled", true ).removeClass( 'btn-outline-success' ).removeClass( 'btn-outline-error' ).addClass( 'btn-outline-primary' );
				// Set request payload
				const request = {
					action: 'starfish-execute-plan-limit-refresh',
				};
				// Make request
				$.ajax(
					{
						action: 'starfish-execute-plan-limit-refresh',
						type: 'POST',
						dataType: 'json',
						url: ajaxurl,
						data: request,
						success: function (response) {
							$( '#srm-support-site-limit-refresh' ).html( 'Refreshed <i class=\'fa fa-check-circle\'></i>' ).attr( "disabled", false ).removeClass( 'btn-outline-primary' ).addClass( 'btn-outline-success' );
							$( '#srm-support-site-planlimit-collections' ).html( 'Collections: ' + response.content.collections );
							$( '#srm-support-site-planlimit-profiles' ).html( 'Profiles: ' + response.content.profiles );
							$( '#srm-support-site-planlimit-funnels' ).html( 'Funnels: ' + response.content.funnels );
						},
						error: function (response) {
							$( '#srm-support-site-limit-refresh' ).html( 'Error <i class=\'fa fa-exclamation-circle\'></i>' ).attr( "disabled", true ).removeClass( 'btn-outline-primary' ).addClass( 'btn-outline-error' );
						}
					}
				);

			}
		)

		/**
		 * SETTINGS TOOLBOX OPTIONS
		 * ***********************************************
		 */
		$( "#srm-settings-defaults-restore" ).unbind( "click" ).on(
			"click",
			function () {
				if (confirm( "This will replace all existing default values with the system defaults. Are you sure?" )) {
					// Present loading indicator
					$( this ).html( 'Restoring... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' ).attr( "disabled", true );
					// Set request payload
					const request = {
						action: 'starfish-execute-restore-default-options', options: settings.defaults
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
								restore_success_response( response, $( "#srm-settings-defaults-restore" ) );
							},
							error: function (data) {
								restore_error_response( data, $( "#srm-settings-defaults-restore" ) );
							}
						}
					);
				}
			}
		);

		/**
		 * SETTINGS LOGGING CONSOLE
		 */
		$( "#srm_logging" ).unbind( "click" ).on(
			"click",
			function () {

				if ($( this ).is( ":checked" )) {
					$( "#srm-logging-console" ).show();
				} else {
					$( "#srm-logging-console" ).hide();
				}

			}
		)

		/**
		 * Purge Logs
		 */
		$( "#srm-purge-logs-button" ).unbind( "click" ).on(
			"click",
			function () {

				let request = {
					action: 'srm-settings-logs-purge', security: settings_vars.settings_nonce
				};
				$.ajax(
					{
						action: 'srm-settings-logs-purge',
						type: 'POST',
						dataType: 'json',
						url: settings_vars.ajax_url,
						data: request,
						success: function (data) {
							console.info( "Successfully Purged Logs." );
							$( "#srm-logging-content" ).html( '<pre>' + data.content + '</pre>' );
						},
						error: function (data) {
							console.error( "Failed to Purge Logs." + JSON.stringify( data ) );
						}
					}
				)

			}
		)

		/**
		 * Refresh Logs
		 */
		$( "#srm-refresh-logs-button" ).unbind( "click" ).on(
			"click",
			function () {

				let request = {
					action: 'srm-settings-logs-refresh', security: settings_vars.settings_nonce
				};
				$.ajax(
					{
						action: 'srm-settings-logs-refresh',
						type: 'POST',
						dataType: 'json',
						url: settings_vars.ajax_url,
						data: request,
						success: function (data) {
							console.info( "Successfully refreshed Logs." );
							$( "#srm-logging-content" ).html( '<pre>' + data.content + '</pre>' );
						},
						error: function (data) {
							console.error( "Failed to refreshed Logs." + JSON.stringify( data ) );
						}
					}
				)

			}
		)

		/**
		 * Send Logs
		 */
		$( "#srm-send-logs-button" ).unbind( "click" ).on(
			"click",
			function () {

				Beacon( 'open' );
				Beacon( 'navigate', '/ask/message/' );
				Beacon(
					'prefill',
					{
						name: settings_vars.help_data.user_display_name,
						email: settings_vars.help_data.user_email,
						subject: 'Please Review my Logs',
						text: $( "#srm-logging-content" ).text(),
						fields: [{
							id: 24071, // Product
							value: 119012, // "Starfish Reviews"
						}, {
							id: 24073, // Category
							value: 166501, // "Plugin Logs"
						},],
					}
				)

			}
		);

		/**
		 * HELP DIALOGUES
		 */
		$( "#srm-help-settings-support-notification-dialogue" ).dialog(
			{
				autoOpen: false,
				width: "auto",
				height: "auto",
				title: "Example Support Notification",
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

		$( "#srm-help-settings-support-notification" ).unbind( "click" ).on(
			"click",
			function () {
				$( "#srm-help-settings-support-notification-dialogue" ).dialog( "open" );
			}
		);

        /**
         * get success response for resetting default settings on Funnels
         */
        function restore_success_response(response, elementRestoreButton) {
            if (response.type === 'success') {
                // Remove loading indicator, and disable button
                elementRestoreButton.addClass( 'srm-settings-button-completed' ).html( 'Completed! <i class=\'fa fa-check\'></i>' ).attr( "disabled", true );
                // Add success message
                elementRestoreButton.after( '<div><strong>Refresh the page to see changes.</strong></div>' )
            } else {
                elementRestoreButton.addClass( 'srm-settings-button-failed' ).html( 'Oops!, It Failed. <i class=\'fa fa-times\'></i>' ).attr( "disabled", true );
                // Add error message
                elementRestoreButton.after( '<div><strong>Something went wrong. Refresh to try again.</strong></div>' );
            }
        }

        /**
         * get error response for resetting default settings on Funnels
         */
        function restore_error_response(data, elementRestoreButton) {
            elementRestoreButton.addClass( 'srm-settings-button-failed' ).html( 'Oops!, It Failed. <i class=\'fa fa-times\'></i>' ).attr( "disabled", true );
            // Add error message
            elementRestoreButton.after( '<div><strong>Something went wrong. Refresh to try again.</strong></div>' );
        }

	}
);
