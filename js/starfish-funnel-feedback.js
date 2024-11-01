/**
 * Create Feedback record from Funnel
 *
 * @package WordPress
 * @subpackage starfish
 * @version 2
 */

$s = jQuery.noConflict();

function srm_create_feedback($s, funnelVars, decision, auto_redirect) {

	const request = {
		security: funnelVars.feedback_nonce,
		decision: decision,
		funnel_id: funnelVars.id,
		tracking_id: funnelVars.query_tracking_id,
		action: 'srm-create-feedback',
		destination_type: funnelVars.destination_type,
		auto_redirect: auto_redirect
	};
    console.info('SRM: Create Funnel Feedback ' + JSON.stringify(request))
	if (auto_redirect === 'imm') {
		request.destination_name = (new URL( funnel_vars.destination_url )).hostname
	}
	return $s.ajax(
		{
			action: 'srm-create-feedback',
			type: 'POST',
			dataType: 'json',
			url: funnelVars.ajax_url,
			data: request,
			success: function (data) {
				$s( '.srm-funnel-feedback-id' ).text( data.feedback_id );
			},
			error: function (data) {
				$s( '.srm-funnel-feedback-id' ).text( data );
			}
		}
	)

}

function srm_update_feedback(action, $s, funnelVars, decision, sendEmail, payload) {

	const request = {
		action: 'srm-update-feedback',
		funnel_action: action,
		send_email: sendEmail,
		destination_type: funnelVars.destination_type,
		security: funnelVars.feedback_nonce,
		decision: decision,
		funnel_id: funnelVars.id,
		feedback_id: payload.feedback_id,
		tracking_id: funnelVars.tracking_id
	};

	if (action === "skip_feedback") {
		request.reviewer_text = payload.reviewer_text;
	}

	if (action === "single_destination" || action === "multi_destination") {
		request.destination_url  = payload.destination_url;
		request.destination_name = payload.destination_name;
	}

	if (action === "submit_feedback") {
		request.reviewer_text  = payload.reviewer_text;
		request.reviewer_name  = payload.reviewer_name;
		request.reviewer_email = payload.reviewer_email;
		request.reviewer_phone = payload.reviewer_phone;
	}

	return $s.ajax(
		{
			action: 'srm-update-feedback', type: 'POST', dataType: 'json', url: funnel_vars.ajax_url, data: request,
		}
	);

}
