/**
 * Collection Admin Processing
 *
 * @package WordPress
 * @subpackage starfish
 * @version 2.4
 */
jQuery(function ($) {

    hljs.highlightAll();

	/**
	 * ***********************************************
	 * Collection Quick Actions
	 */
	$(document).ready(function ($) {

		// Collection Visibility
		// =============================
		// Update hide status to VISIBLE
		$('.srm-collection-hide-toggle .toggle-on').click(function () {
			let collection_id = $(this).parent().prev('input').data("srm_collection_id");
			srm_toggle_hidden(false, collection_id);
		});
		//Update hide status to HIDDEN
		$('.srm-collection-hide-toggle .toggle-off').click(function () {
                let collection_id = $(this).parent().prev('input').data("srm_collection_id");
                srm_toggle_hidden(true, collection_id);
            });

            // =============================
            // Disable Layout options
            disable_layout_options($('select#srm-collection-layout'));
            // Disable Slider options
            disable_slider_options($('select#srm-collection-slider'));
            // Disable Avatar options
            disable_avatar_options($('#srm-collection-elements-avatar'));
            // Disable Review Options
            disable_review_options($('#srm-collection-elements-review'));
            // Disable Name Options
            disable_name_options($('#srm-collection-elements-name'));
            // Disable Date Options
            disable_date_options($('#srm-collection-filters-age'));

            // Date Pickers
            $("#srm-collection-filters-startdate").datepicker({
                dateFormat: "mm/dd/yy"
            });
            $("#srm-collection-filters-enddate").datepicker({
                dateFormat: "mm/dd/yy"
            });

    });

    $('select#srm-collection-layout').change(function () {
        disable_layout_options(this);
    });

    $('select#srm-collection-slider').change(function () {
        disable_slider_options(this);
    });

    $('#srm-collection-elements-avatar').change(function () {
        disable_avatar_options(this);
    });

    $('#srm-collection-elements-review').change(function () {
        disable_review_options(this);
    });

    $('#srm-collection-elements-name').change(function () {
        disable_name_options(this);
    });

    $('#srm-collection-filters-age').change(function () {
        disable_date_options(this);
    });

    function disable_layout_options(select) {
        if ($(select).children(':selected').val() === 'notifications') {
            $('#srm-collection-columns').prop("disabled", true);
            $('#srm-collection-rows').prop("disabled", true);
        } else {
            $('#srm-collection-columns').prop("disabled", false);
            $('#srm-collection-rows').prop("disabled", false);
        }
    }

    function disable_slider_options(select) {
        if ($(select).children(':selected').val() === '0') {
            $('#srm-collection-autoadvance').prop("disabled", true);
            $('#srm-collection-navigation').prop("disabled", true);
        } else {
            $('#srm-collection-autoadvance').prop("disabled", false);
            $('#srm-collection-navigation').prop("disabled", false);
        }
    }

    function disable_avatar_options(avatar) {
        if ($(avatar).is(':checked')) {
            $('#srm-collection-avatar-position').prop("disabled", false).find("option.srm-disabled-element-option").remove();
            $('#srm-collection-avatar-size').prop("disabled", false).find("option.srm-disabled-element-option").remove();
        } else {
            $('#srm-collection-avatar-position').prop("disabled", true).append("<option class='srm-disabled-element-option' selected>Element Disabled</option>");
            $('#srm-collection-avatar-size').prop("disabled", true).append("<option class='srm-disabled-element-option' selected>Element Disabled</option>");
        }
    }

    function disable_review_options(review) {
        if ($(review).is(':checked')) {
            $('#srm-collection-style-body').removeClass("srm-collections-disable-col");
            $('#srm-collection-elements-maxchar').prop("disabled", false);
            $('#srm-collection-body-justification-left').prop("disabled", false);
            $('#srm-collection-body-justification-center').prop("disabled", false);
            $('#srm-collection-body-justification-right').prop("disabled", false);
            $('#srm-collection-body-justification-justify').prop("disabled", false);
            $('#srm-collection-body-font').prop("disabled", false);
            $('#srm-collection-body-font-size').prop("disabled", false);
        } else {
            $('#srm-collection-style-body').addClass("srm-collections-disable-col");
            $('#srm-collection-elements-maxchar').prop("disabled", true);
            $('#srm-collection-body-justification-left').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-body-justification-center').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-body-justification-right').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-body-justification-justify').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-body-font').prop("disabled", true);
            $('#srm-collection-body-font-size').prop("disabled", true);
        }
    }

    function disable_name_options(name) {
        if ($(name).is(':checked')) {
            $('#srm-collection-style-name').removeClass("srm-collections-disable-col");
            $('#srm-collection-name-justification-left').prop("disabled", false);
            $('#srm-collection-name-justification-center').prop("disabled", false);
            $('#srm-collection-name-justification-right').prop("disabled", false);
            $('#srm-collection-name-justification-justify').prop("disabled", false);
            $('#srm-collection-name-font').prop("disabled", false);
            $('#srm-collection-name-font-size').prop("disabled", false);
        } else {
            $('#srm-collection-style-name').addClass("srm-collections-disable-col");
            $('#srm-collection-name-justification-left').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-name-justification-center').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-name-justification-right').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-name-justification-justify').prop("disabled", true).parent().removeClass("active");
            $('#srm-collection-name-font').prop("disabled", true);
            $('#srm-collection-name-font-size').prop("disabled", true);
        }
    }

    function disable_date_options(age) {
        if ($(age).children(':selected').val() === 'range') {
            $('#srm-collection-filters-startdate').prop("disabled", false);
            $('#srm-collection-filters-enddate').prop("disabled", false);
        } else {
            $('#srm-collection-filters-startdate').prop("disabled", true);
            $('#srm-collection-filters-enddate').prop("disabled", true);
        }
    }

    function srm_toggle_hidden(is_hidden, collection_id) {
        let request = {
            action: 'srm-collection-hide',
            is_hidden: is_hidden,
            collection_id: collection_id,
            security: collection_vars.collection_nonce
        };
        $.ajax(
            {
                action: 'srm-collection-hide',
                type: 'POST',
                dataType: 'json',
                url: collection_vars.ajax_url,
                data: request,
                success: function (data) {
                    console.info("Successfully requested collection hidden status be updated. Response = " + data.message);
                },
                error: function (data) {
                    console.error("Failed to update collection hidden status. Response = " + data.message);
                }
            }
        )
    }

});