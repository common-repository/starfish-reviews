/**
 * Funnel processing
 *
 * @package WordPress
 * @subpackage starfish
 * @version 2.1
 */
$s = jQuery.noConflict();
$s(
	function ($) {

		const funnelStepper = new Stepper(
			document.querySelector( `.srm-funnel-${funnel_vars.id}` ),
			{
				linear: true, animation: false
			}
		);

		$( '#srm-funnel-positive' ).click(
			function () {
				log_decision( true );
			}
		);

		$( '#srm-funnel-negative' ).click(
			function () {
				log_decision( false );
			}
		);

		if ('p' === getQueryParams().feedback) {
			log_decision( true );
		}

		if ('n' === getQueryParams().feedback) {
			log_decision( false );
		}

        $(document).ready(function() {

            if(funnel_vars.embedded_dynamic_sizing === 'true') {
                document.querySelectorAll('.srm-funnel').forEach((el) => {
                    el.addEventListener('click', () => {
                        send_iframe_message(funnel_vars.embedded_target_origin);
                    });
                });

                window.addEventListener('load', () => {
                        send_iframe_message(funnel_vars.embedded_target_origin);
                });
            }

        });

        function send_iframe_message(targetOrigin) {
            docHeight = document.body.scrollHeight;
            window.parent.postMessage({
                height: docHeight + 50
            }, targetOrigin); //TODO: Support multiple target origins looping through array of origins
        }

		function log_decision(decision) {
			// Create the initial funnel's feedback record.
			const response = srm_create_feedback( $, funnel_vars, decision, funnel_vars.auto_redirect_seconds );
			var target     = '_self'
			if (funnel_vars.new_window) {
				target = "_blank"
			}
			if (response) {
				if (decision) {
					// Check if auto-redirect is set for immediately.
					if (funnel_vars.auto_redirect_seconds !== 'imm') {
						funnelStepper.to( 2 );
						auto_redirect();
					} else {
						window.open( funnel_vars.destination_url, target );
					}
				} else {
					funnelStepper.to( 3 );
				}
			} else {
				$( `#srm - funnel - ${funnel_vars.id} - 1` ).find( '.srm-funnel-heading' ).html( '<div class="srm-funnel-error">Failed to log decision</div>' );
				$( "#srm-funnel-questions" ).hide();
			}
		}

		// Click the single destination.
		$( ".srm-funnel-single-submit" ).click(
			function () {
				const payload = {
					"feedback_id": $( ".srm-funnel-feedback-id" ).text(),
					"destination_url": funnel_vars.destination_url,
					"destination_name": (new URL( funnel_vars.destination_url )).hostname
				};
				srm_update_feedback( "single_destination", $, funnel_vars, true, false, payload );
				var target = '_self'
				if (funnel_vars.new_window) {
					target = "_blank"
				}
				window.open( funnel_vars.destination_url, target );
			}
		);

		// Click a multi-destination.
		$( ".srm-funnel-multi-destination-submit" ).click(
			function () {
				const destinationUrl  = $( this ).data( "srm_desti_url" );
				const destinationName = $( this ).data( "srm_desti_name" );
				const payload         = {
					"feedback_id": $( ".srm-funnel-feedback-id" ).text(),
					"destination_url": destinationUrl,
					"destination_name": destinationName
				};
				srm_update_feedback( "multi_destination", $, funnel_vars, true, false, payload );

				var target = '_self'
				if (funnel_vars.new_window) {
					target = "_blank"
				}
				window.open( destinationUrl, target );
			}
		);

		// Click skip feedback form.
		$( "#srm-funnel-feedback-skip" ).unbind( "click" ).on(
			"click",
			function () {
				funnelStepper.to( 5 );
				const payload = {
					"feedback_id": $( ".srm-funnel-feedback-id" ).text(), "reviewer_text": "-- Skipped Feedback --"
				};
				srm_update_feedback( "skip_feedback", $, funnel_vars, false, false, payload );
				auto_redirect();
			}
		);

		// Click feedback form submit.
		$( "#srm-funnel-feedback-submit" ).unbind( "click" ).on(
			"click",
			function () {
				// Fetch all the forms we want to apply custom Bootstrap validation styles to.
				var forms = document.getElementsByClassName( 'needs-validation' );
				// Loop over them and prevent submission.
				Array.prototype.filter.call(
					forms,
					function (form) {
						form.addEventListener(
							'submit',
							function (event) {
								if (form.checkValidity() === false) {
									event.preventDefault();
									event.stopPropagation();
								} else {
									funnelStepper.to( 4 );
									const payload = {
										"feedback_id": $( ".srm-funnel-feedback-id" ).text(),
										"reviewer_text": $( "#srm-funnel-reviewer-text" ).val(),
										"reviewer_name": $( "#srm-funnel-reviewer-name" ).val(),
										"reviewer_email": $( "#srm-funnel-reviewer-email" ).val(),
										"reviewer_phone": $( "#srm-funnel-reviewer-phone" ).val()
									};
									srm_update_feedback( "submit_feedback", $, funnel_vars, false, true, payload )
								}
								form.classList.add( 'was-validated' );
							},
							false
						);
					}
				);
			}
		);

		function auto_redirect() {
			if ((funnel_vars.auto_redirect_seconds > 0) && ("single" === funnel_vars.destination_type)) {
				const autoForwardSeconds = funnel_vars.auto_redirect_seconds * 1000;
				setTimeout(
					function () {
						$( ".srm-funnel-single-submit" ).trigger( "click" );
					},
					autoForwardSeconds
				);
			}
		}

	}
);
