/**
 *
 * Change Freemius Account element references
 */
$s = jQuery.noConflict();
$s(
	function ($) {

		$( document ).ready(
			function ($) {
				const actions_list = $( 'div.fs-header-actions ul' );
				const actions      = actions_list.find( 'li' );
				// Custom Link: My Account
				const my_account_url = 'https://starfish.reviews/account';
				let separator        = actions_list.append( '<li>&nbsp;•&nbsp;</li>' );
				separator.append( '<li>' + '<a href="' + my_account_url + '" style="text-decoration: none; color: #007bff;">' + '<li class="dashicons dashicons-admin-users" style="font-size: 1.2em; height: 1.2em; width: 1.2em; color: #007bff;"></li>' + 'My Account' + '</a>' + '</li>' );
				// Change Plan
				let change_plan_url = 'https://starfish.reviews/plans-pricing';
				if (fa_data.has_license) {
					change_plan_url += '?has_license=true'
				}
				let change_plan = actions.eq( 4 ).find( 'a' );
				change_plan.attr( "href", change_plan_url ).attr( "target", "_blank" );
			}
		);

	}
);
