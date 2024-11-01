$s = jQuery.noConflict();
$s(
	function ($) {

		$( document ).ready(
			function ($) {

                /**
                 * JCONFIRM Defaults
                 * ***********************************************
                 */
                jconfirm.defaults = {
                    bootstrapClasses: {
                        container: 'bootstrap-srm'
                    },
                    escapeKey: true,
                    backgroundDismiss: true,
                    smoothContent: true,
                };

				/**
				 * TRIGGER PLUGIN MIGRATIONS
				 * ***********************************************
				 */
				$( "#srm-start-migrations" ).unbind( "click" ).on(
					"click",
					function () {
						$( this ).addClass( 'srm-processing' ).html( 'Processing... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' );
						srm_execute_migrations( false )
					}
				);
				$( "#srm-retry-migrations" ).unbind( "click" ).on(
					"click",
					function () {
						$( this ).addClass( 'srm-processing' ).html( 'Processing... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' );
						srm_execute_migrations( true )
					}
				);

                /**
                 * TRIGGER RICH REVIEWS MIGRATIONS
                 * ***********************************************
                 */
                $( "#srm-start-richreviews-migrations" ).unbind( "click" ).on(
                    "click",
                    function () {
                        $( this ).addClass( 'srm-processing' ).html( 'Processing... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' );
                        srm_execute_richreviews_migrations(this)
                    }
                );

                /**
                 * Add Help Icon next to Post Type's "Add New" button
                 * ***********************************************
                 */
                // Testimonials.
                $("<a class=\"srm-help-inline\" href=\"javascript:void(0)\" onclick=\"Beacon('article', '64434c153f390245bdeee87b', { type: 'modal' })\" title=\"Starfish Help\"><i class=\"fa fa-question-circle srm-help-icon\"></i></a>").insertBefore( "body.post-type-starfish_testimonial .wp-header-end" );

                // Profiles.
                $("<a class=\"srm-help-inline\" href=\"javascript:void(0)\" onclick=\"Beacon('article', '60aee2d32246b50b7f38f7bc', { type: 'modal' })\" title=\"Starfish Help\"><i class=\"fa fa-question-circle srm-help-icon\"></i></a>").insertBefore( "body.starfish-reviews_page_starfish-reviews-profiles .wp-header-end" );

			}
		);

        /**
         * Execute DB Migrations
         *
         * @param retry
         */
        function srm_execute_migrations(retry) {
            // Present loading indicator
            $( '#srm-start-migrations' ).html( 'Migrating... <i class=\'fa fa-cog fa-spin fa-fw\'></i>' ).attr( "disabled", true );

            const request = {
                action: 'starfish-execute-migrations', retry: retry
            };
            $.ajax(
                {
                    action: 'starfish-execute-migrations',
                    type: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    data: request,
                    success: function (data) {
                        window.location.reload();
                    }
                }
            );
        }

        /**
         * Execute Rich Reviews migration to Testimonials.
         *
         * @param ele
         */
        function srm_execute_richreviews_migrations(ele) {
            // Present loading indicator
            $(ele).addClass('srm-processing').html( 'Processing...<i class=\'fa fa-cog fa-spin fa-fw\'></i>' ).attr( "disabled", true );

            const request = {
                action: 'starfish-execute-richreviews-migrations'
            };
            $.ajax(
                {
                    action: 'starfish-execute-richreviews-migrations',
                    type: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    data: request,
                    success: function (data) {}
                }
            );
            // Check and update progress.
            srm_check_progress(ele, 'srm_richreviews_migration')
        }

        /**
         * Check the progress of an initiated process via Ajax, using a transient.
         *
         * @param element The element clicked to initiate process (updates the HTML to show percentage of progress)
         * @param transient The name of the transient storing the process's progress
         */
        function srm_check_progress(element, transient) {
            const request = {
                action: 'starfish-get-transient',
                transient: transient
            };
            $.ajax(
                {
                    action: 'starfish-get-transient',
                    type: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    data: request,
                    success: function (data) {
                        if(data.type === 'success' && data.content.percentage === 100) {
                            $(element).addClass('srm-processing-completed').html('Successfully Completed!').attr("disabled", true);
                        }
                        else {
                            $(element).addClass('srm-processing').html( 'Processing...(' + data.content.percentage + '%) <i class=\'fa fa-cog fa-spin fa-fw\'></i>' ).attr( "disabled", true );
                            setTimeout(function () {
                                srm_check_progress(element, transient);
                            }, 2000)
                        }
                    }
                }
            );

        }

	}
);
