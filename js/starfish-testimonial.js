/**
 * Testimonial Processing
 *
 * @package WordPress
 * @subpackage starfish
 * @version 1.0
 */
$s = jQuery.noConflict();
$s(
	function ($) {

        // Star Rating Selection.
        $("#srm-testimonial-rating").rating({
            min: 0,
            max: 5,
            step: 1,
            size: 'md',
            stars: 5,
            showClear: false,
            showCaption: false,
        });

		$( document ).ready(
			function () {

                $('.filled-stars').on('click', function(e) {
                    if($('.rating-container').hasClass('srm-invalid-rating')) {
                        $('.rating-container')[0].classList.remove('srm-invalid-rating');
                        $('.rating-container')[0].classList.add('srm-valid-rating');
                    }
                })

                $('.srm-testimonial-form').on('submit', function (e) {

                    e.preventDefault();

                    const form = $(this);
                    const formDom = $(this)[0];
                    let postdata = form.serialize();
                    let rating = $("input[name='srm-testimonial-rating']",this).val();

                    if (!formDom.checkValidity() || !rating.trim()) {
                        e.stopPropagation();
                        formDom.classList.add('was-validated');
                        formDom.classList.add('failed-validation');
                        if(!rating.trim()) {
                            $('.rating-container')[0].classList.add('srm-invalid-rating');
                            $('.rating-container')[0].classList.remove('srm-valid-rating');
                        } else {
                            $('.rating-container')[0].classList.remove('srm-invalid-rating');
                            $('.rating-container')[0].classList.add('srm-valid-rating');
                        }
                    } else {
                        formDom.classList.add('was-validated');

                        const request = {
                            action: 'srm-testimonial-submit',
                            security: testimonial_vars.nonce,
                            data: postdata
                        };
                        console.info(request);
                        $.ajax({
                            action: 'srm-testimonial-submit',
                            type: 'POST',
                            dataType: 'json',
                            url: testimonial_vars.ajax_url,
                            data: request,
                            success: function (response) {
                                console.info(response);
                                if (response.success == true) {
                                    $('#srm-testimonial-submit-feedback').html('<span class="srm-testimonial-form-success">' + response.message + '</span>');
                                    $('.srm-testimonial-form')[0].classList.remove('was-validated');
                                    $('.srm-testimonial-form')[0].reset();
                                } else {
                                    $('#srm-testimonial-submit-feedback').html('<span class="srm-testimonial-form-error">' + response.message + '</span>');
                                }
                            },
                            error: function (response) {
                                console.error(response);
                                $('#srm-testimonial-submit-feedback').html(response.message);
                            }
                        })
                    }
                });

            });
	}
);
