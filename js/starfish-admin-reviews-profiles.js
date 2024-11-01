/**
 * Reviews Profiles processing
 *
 * @package WordPress
 * @subpackage starfish
 * @version 2.4
 */

jQuery(function ($) {

    // Local Constants

    const SRM_SS_IS_PROFILE_URL_VALIDATED = 'srm_reviews_profile_url_is_validated';

    // Reviews Profile Quick Actions
    $(document).ready(function () {

        // Reviews Profile Visibility
        // =============================

        // Update hide status to VISIBLE
        $('.srm-reviews-profiles-hide-toggle .toggle-on').click(function () {
            let profile_id = $(this).parent().prev('input').data("srm_reviews_profile_id");
            srm_toggle_hidden(false, profile_id);
        });
        //Update hide status to HIDDEN
        $('.srm-reviews-profiles-hide-toggle .toggle-off').click(function () {
            let profile_id = $(this).parent().prev('input').data("srm_reviews_profile_id");
            srm_toggle_hidden(true, profile_id);
        });

        // Reviews Profile Renew Profile
        // =============================
        $('.srm-reviews-profile-renew').click(function () {
            let profile_id = $(this).data("srm_profile_id");
            // Set Profile Status to Progress Indicator
            $('#srm-reviews-profile-' + profile_id).addClass('processing').html('<i class=\'fa fa-cog fa-spin fa-fw\'></i>');
            srm_renew_profile(profile_id);
        });

        $('input[name="srm-reviews-profile-scrape-now"]').click(function(){
            if($(this).is(":checked")) {
                $('input[name="srm-reviews-profile-diff-last"]').show();
                $('label[for="srm-reviews-profile-diff-last"]').show();
            }
            else {
                $('input[name="srm-reviews-profile-diff-last"]').hide();
                $('label[for="srm-reviews-profile-diff-last"]').hide();
            }
        });

        // Reviews Profile Validate URL
        // =============================
        $('#srm-reviews-profile-site-test').click(function(event) {
            event.preventDefault();
            srm_validate_profile_url();
        });

    });

    // Reviews Profile Form Validator Listener
    window.addEventListener('load', function () {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        Array.prototype.filter.call(forms, function (form) {
            form.addEventListener('submit', function (event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    // REVIEWS PROFILES DIALOGUES - Add Profile
    $("#srm-reviews-profiles-dialogue").dialog(
        srm_reviews_profiles_dialogue($("#srm-reviews-profile-form"), "Profile", "srm-reviews-profile-form", "srm-create-reviews-profile")
    );

    // Open Dialogue - Add New
    $('#srm-reviews-profiles-add-new').click(function () {
        $('#srm-reviews-profiles-dialogue').dialog("option", "title", "Add New Profile").dialog("open");
    });

    // Open Dialogue - View Details
    if ('starfish-reviews-profiles' === getQueryParams().page && 'details' === getQueryParams().action) {
        const site = getQueryParams().site;
        $('#srm-reviews-profiles-dialogue').dialog("option", "title", "Profile Details").dialog("open");
        $(document).ready(function () {
            $('a.dropdown-item[title="' + site + '"]').click();
            // If URL has already been validated, mark it.
            if ('starfish-reviews-profiles' === getQueryParams().page && 'details' === getQueryParams().action && site.toLowerCase() != 'google') {
                if (localStorage.getItem(getQueryParams().id + "_" + SRM_SS_IS_PROFILE_URL_VALIDATED)) {
                    srm_completed_url_validation();
                }
                $('#srm-reviews-profiles-sites').attr("disabled", true);
                $('#srm-reviews-profiles-sites').parent().next().html("");
            }
        });
    }

    // Handle Selection of Profile Review Site
    $("a.dropdown-item").click(function () {
        srm_require_url_validation()
        const selectedSite = $(this).attr('title').toUpperCase().replace(' ', '_');
        $('#srm-reviews-profiles-sites').text($(this).text());
        const sites = profile_vars.review_sites;
        if ('starfish-reviews-profiles' === getQueryParams().page && 'details' !== getQueryParams().action) {
            $('#srm-reviews-profile-site-slug').val('');
            $('#srm-reviews-profile-site-url').val(sites[selectedSite].url);
        }
        $('#srm-reviews-profile-site-logo img').attr('src', sites[selectedSite].logo);
        $('#srm-reviews-profile-site-logo img').attr('title', sites[selectedSite].name + ' Reviews');
        $('#srm-reviews-profile-site-logo img').attr('alt', sites[selectedSite].name + ' Reviews');
        $('#srm-reviews-profile-slug-group').show();
        if (selectedSite == 'GOOGLE') {
            $('#srm-reviews-profile-slug-group').hide();
            $('#srm-reviews-profile-google-instructions').show();
            $('#srm-reviews-profile-placeid-group').show();
            $('#srm-reviews-profile-query-group').show();
            $('#srm-reviews-profile-submit').attr("disabled", false);
        } else {
            $('#srm-reviews-profile-slug-group').show();
            $('#srm-reviews-profile-google-instructions').hide();
            $('#srm-reviews-profile-placeid-group').hide();
            $('#srm-reviews-profile-query-group').hide();
        }
        let url_template = sites[selectedSite].url;
        $("#srm-reviews-profile-site-slug").keyup(function() {
            if(url_template !== null) {
                $(document).ready(function () {
                    srm_require_url_validation();
                    srm_on_profile_slug_change(url_template)
                });
            }
        });
    });

    function srm_on_profile_slug_change(url_template) {
        let slug_value = $("#srm-reviews-profile-site-slug").val();
        let new_value = url_template.format(slug_value);
        $("#srm-reviews-profile-site-url").val(new_value);
        $("#srm-reviews-profile-site-open").attr("href", new_value);
    }

    // Dialog Controls
    function srm_reviews_profiles_dialogue(form_element, dialogue_title, form_name, form_action) {
        return {
            autoOpen: false,
            width: "600",
            minHeight: "600",
            height: "auto",
            title: dialogue_title,
            modal: true,
            position: {my: "center", at: "center", of: window, collision: "fit", within: "#wpbody"},
            closeOnEscape: false,
            buttons: [
                {
                    id: "srm-reviews-profile-cancel",
                    text: "Cancel",
                    icon: "",
                    click: function () {
                        $(this).dialog("close");
                        window.location = "?post_type=starfish_feedback&page=starfish-reviews-profiles";
                    },
                    "class": 'button button-secondary srm-dialogue-cancel-button',
                    showText: true
                },
                {
                    id: "srm-reviews-profile-submit",
                    text: "Submit",
                    type: "submit",
                    disabled: true,
                    form: form_name,
                    icon: "",
                    "class": 'button button-primary srm-dialogue-submit-button',
                    click: function () {
                        // Clear any existing errors
                        $("#srm-reviews-profile-error").remove();
                        // Profile validation
                        $('.srm-dialogue-submit-button').html('validating... <i class=\'fa fa-cog fa-spin fa-fw\'></i>').attr("disabled", true);
                        let site = $('#srm-reviews-profiles-sites').text().toLowerCase().trim();
                        if(site != 'google') {
                            srm_validate_profile_url();
                        }
                        // Submit new Profile form
                        $('.srm-dialogue-submit-button').html('working... <i class=\'fa fa-cog fa-spin fa-fw\'></i>').attr("disabled", true);
                        let request = {
                            action: form_action,
                            data: form_element.serialize(),
                            security: profile_vars.profile_nonce
                        };
                        $.ajax(
                            {
                                action: form_action,
                                type: 'POST',
                                dataType: 'json',
                                url: profile_vars.ajax_url,
                                data: request,
                                success: function (data) {
                                    if (data.success) {
                                        localStorage.setItem(data.id + "_" + SRM_SS_IS_PROFILE_URL_VALIDATED, true);
                                        $('.srm-dialogue-submit-button').html("Saved!");
                                        $("#srm-reviews-profiles-dialogue").dialog("close");
                                        // Refresh page
                                        window.location = "?post_type=starfish_feedback&page=starfish-reviews-profiles";
                                    } else {
                                        $('.srm-dialogue-submit-button').html("Re-Submit").attr("disabled", false);
                                        if (!$("#srm-reviews-profile-error").length) {
                                            $(".ui-dialog-buttonset").after("<div id='srm-reviews-profile-error'>" + data.message + "</div>");
                                        }
                                    }
                                },
                                error: function (data) {
                                    $('.srm-dialogue-submit-button').html("Re-Submit").attr("disabled", false);
                                    console.log("SRM: response=error, request=" + JSON.stringify(request) + ", data=" + JSON.stringify(data));
                                    if (!$("#srm-reviews-profile-error").length) {
                                        $(".ui-dialog-buttonset").after("<div id='srm-reviews-profile-error'>" + JSON.stringify(data) + "</div>");
                                    }
                                }
                            }
                        )
                    },
                    showText: true
                }
            ],
            open: function (event, ui) {
                $('.ui-dialog-titlebar-close').hide();

                // Hide the following groups until a site selection is made
                $('#srm-reviews-profile-slug-group').hide();
                $('#srm-reviews-profile-placeid-group').hide();
                $('#srm-reviews-profile-query-group').hide();
                // Disable Site selection if viewing the details of existing profile
                // ========================================
                let site = $('#srm-reviews-profiles-sites').text().toLowerCase().trim();
                if ('starfish-reviews-profiles' === getQueryParams().page && 'details' === getQueryParams().action && site != 'google') {
                    $('#srm-reviews-profiles-sites').attr("disabled", true);
                    $('#srm-reviews-profiles-sites').parent().next().html("");
                }
            },
            close: function () {
            }
        }
    }

    // Form validations: Only allow one of the three methods of retrieving reviews (URL, Place ID, or Query)
    $("#srm-reviews-profile-placeid").focus(function () {
        srm_disable_others($(this), true)
    }).focusout(function () {
        if ( $("#srm-reviews-profile-placeid").val() != '' ) {
            srm_disable_others($(this), true)
        } else {
            srm_disable_others($(this), false)
        }
    });
    $("#srm-reviews-profile-query").focus(function () {
        srm_disable_others($(this), true)
    }).focusout(function () {
        if ( $("#srm-reviews-profile-query").val() != '' ) {
            srm_disable_others($(this), true)
        } else {
            srm_disable_others($(this), false)
        }
    });

    function srm_disable_others(ele, disable) {
        switch (ele.attr('id')) {
            case 'srm-reviews-profile-site-path':
                $("#srm-reviews-profile-placeid").prop("disabled", disable).prop("required", false);
                $("#srm-reviews-profile-query").prop("disabled", disable).prop("required", false);
                break;
            case 'srm-reviews-profile-placeid':
                $("#srm-reviews-profile-placeid").prop("disabled", false).prop("required", true);
                $("#srm-reviews-profile-query").prop("disabled", disable).prop("required", false);
                break;
            case 'srm-reviews-profile-query':
                $("#srm-reviews-profile-query").prop("disabled", false).prop("required", true);
                $("#srm-reviews-profile-placeid").prop("disabled", disable).prop("required", false);
                break;
            default:
                $("#srm-reviews-profile-placeid").prop("disabled", false).prop("required", true);
                $("#srm-reviews-profile-query").prop("disabled", false).prop("required", true);
        }
    }

    function srm_toggle_hidden(is_hidden, profile_id) {
        let request = {
            action: 'srm-reviews-profile-hide',
            is_hidden: is_hidden,
            profile_id: profile_id,
            security: profile_vars.profile_nonce
        };
        $.ajax(
            {
                action: 'srm-reviews-profile-hide',
                type: 'POST',
                dataType: 'json',
                url: profile_vars.ajax_url,
                data: request,
                success: function (data) {
                    console.info("Successfully requested profile hidden status be updated. Response = " + data.message);
                },
                error: function (data) {
                    console.error("Failed to update profile hidden status. Response = " + data.message);
                }
            }
        )
    }

    function srm_renew_profile(profile_id) {
        let request = {
            action: 'srm-reviews-profile-renew',
            profile_id: profile_id,
            security: profile_vars.profile_nonce
        };
        $.ajax(
            {
                action: 'srm-reviews-profile-renew',
                type: 'POST',
                dataType: 'json',
                url: profile_vars.ajax_url,
                data: request,
                success: function (data) {
                    console.info("Successfully renewed profile. Response = " + data.message);
                    // Remove Progress Indicator
                    $('#srm-reviews-profile-' + profile_id).removeClass('processing').html('');
                    // Refresh page
                    window.location = "?post_type=starfish_feedback&page=starfish-reviews-profiles";
                },
                error: function (data) {
                    console.error("Failed to renew profile. Response = " + data.message);
                    // Remove Progress Indicator
                    $('#srm-reviews-profile-' + profile_id).removeClass('processing').html('Error');
                }
            }
        )
    }

    /**
     * Main Validation before officially submitting a new Profile
     */
    function srm_validate_profile_url() {

        let is_valid = (!localStorage.getItem(getQueryParams().id + "_" + SRM_SS_IS_PROFILE_URL_VALIDATED) || !sessionStorage.getItem(SRM_SS_IS_PROFILE_URL_VALIDATED)) ? true : false;
        let site     = $('#srm-reviews-profiles-sites').text().toLowerCase().trim();
        let message  = "The Profile URL is invalid, please make any necessary corrections.";
        let slug     = $("#srm-reviews-profile-site-slug").val();

        if(slug != '') {
            is_valid = srm_validate_profile_slug(slug, site);
            if (is_valid) {
                // Check if URL is a working URL
                if(site != "google") {
                    let request = {
                        action: 'srm-reviews-profile-validate-url',
                        profile_url: $('#srm-reviews-profile-site-url').val(),
                        security: profile_vars.profile_nonce
                    };
                    $.ajax({
                        action: 'srm-reviews-profile-validate-url',
                        url: profile_vars.ajax_url,
                        data: request,
                        dataType: 'json',
                        type: "POST",
                        success: function (data) {
                            if (data.success) {
                                srm_completed_url_validation();
                            } else {
                                srm_require_url_validation();
                                $(".ui-dialog-buttonset").after("<div id='srm-reviews-profile-error'>" + data.message + "</div>");
                                throw new Error(data.message);
                            }
                        },
                        error: function (data) {
                            srm_require_url_validation();
                            $(".ui-dialog-buttonset").after("<div id='srm-reviews-profile-error'>" + data.message + "</div>");
                            throw new Error(data.message);
                        }
                    });
                }
            } else {
                $("#srm-reviews-profile-error").remove();
                $(".ui-dialog-buttonset").after("<div id='srm-reviews-profile-error'>" + message + "</div>");
                $('#srm-reviews-profile-site-test').parent().removeClass("srm-profile-site-tested").addClass("srm-profile-site-untested");
                throw new Error(message);
            }
        }
        sessionStorage.setItem(SRM_SS_IS_PROFILE_URL_VALIDATED, is_valid);
        return is_valid;

    }

    /**
     * Profile Slug Validation
     */
    function srm_validate_profile_slug(slug, site) {
        // Ensure the slug value does not contain URL formatting (e.g., http)
        const regex    = new RegExp(/^((?!http|\/).)*$/, "ms");
        return regex.test(slug);
    }

    /**
     * Helper functions to set URL Validation State: REQUIRED
     */
    function srm_require_url_validation() {
        sessionStorage.setItem(SRM_SS_IS_PROFILE_URL_VALIDATED, false);
        $("#srm-reviews-profile-error").remove();
        $('#srm-reviews-profile-site-test').parent().removeClass("srm-profile-site-tested").addClass("srm-profile-site-untested");
        $('#srm-reviews-profile-site-test').html("<i class=\"fas fa-rocket\" title=\"Validate URL\"></i> Validate");
        $('.srm-dialogue-submit-button').attr("disabled", true);
    }

    /**
     * Helper functions to set URL Validation State: COMPLETED
     */
    function srm_completed_url_validation() {
        sessionStorage.setItem(SRM_SS_IS_PROFILE_URL_VALIDATED, true);
        $("#srm-reviews-profile-error").remove();
        $('#srm-reviews-profile-site-test').parent().removeClass("srm-profile-site-untested").addClass("srm-profile-site-tested");
        $('#srm-reviews-profile-site-test').html("<i class=\"fas fa-check-circle\" title=\"Successfully Validated URL\"></i>");
        $('.srm-dialogue-submit-button').attr("disabled", false);
    }

});