/**
 * Reviews Admin Processing
 *
 * @package WordPress
 * @subpackage starfish
 * @version 2.4
 */

jQuery(function ($) {

    /**
     * ***********************************************
     * Reviews Quick Actions
     */
    $(document).ready(function ($) {

        // Review Visibility
        // =============================

        // Update hide status to VISIBLE
        $('.srm-review-hide-toggle .toggle-on').click(function () {
            let review_id = $(this).parent().prev('input').data("srm_review_id");
            srm_hide_review(false, review_id);
        });
        //Update hide status to HIDDEN
            $('.srm-review-hide-toggle .toggle-off').click(function () {
                let review_id = $(this).parent().prev('input').data("srm_review_id");
                srm_hide_review(true, review_id);
            });

        });

        function srm_hide_review(is_hidden, review_id) {
            let request = {
                action: 'srm-review-hide',
                is_hidden: is_hidden,
                review_id: review_id,
                security: review_vars.review_nonce
            };
            $.ajax(
                {
                    action: 'srm-review-hide',
                    type: 'POST',
                    dataType: 'json',
                    url: review_vars.ajax_url,
                    data: request,
                    success: function (data) {
                        console.info("Successfully requested review hidden status be updated. Response = " + data.message);
                    },
                    error: function (data) {
                        console.error("Failed to update review hidden status. Response = " + data.message);
                    }
                }
            )
        }

        /**
         * ***********************************************
         * REVIEW DIALOGUE - Review Details
         */
        $("#srm-review-details-dialogue").dialog({
            autoOpen: false,
            width: "600px",
            height: "auto",
            title: "Review Details",
            modal: true,
            closeOnEscape: true,
            buttons: [
                {
                    text: "Close",
                    icon: "",
                    click: function () {
                        $(this).dialog("close");
                        window.location = "?post_type=starfish_feedback&page=starfish-reviews";
                    },
                    "class": 'button button-secondary',
                    showText: true
                }
            ],
            open: function (event, ui) {
                $('.ui-dialog-titlebar-close').hide();
            },
            close: function () {
            }
        });

        // Open Dialogue - View Details
        if ('starfish-reviews' === getQueryParams().page && 'details' === getQueryParams().action) {
            $('#srm-review-details-dialogue').dialog("option", "title", "Review Details").dialog("open");
        }

    });