/**
*Funnel review submission style choose option
***/
jQuery(document).ready(function($) {
  jQuery('#srm_button_style').change(function() {
     var option_val = jQuery(this).val();
     if(option_val == 'thumbs_outline'){
       jQuery('.button_style_preview').html('<span class="faicon iconyes far fa-thumbs-up"></span><span class="faicon iconno far fa-thumbs-down faicon_flip"></span>');
     }
     if(option_val == 'thumbs_solid'){
       jQuery('.button_style_preview').html('<span class="faicon iconyes fas fa-thumbs-up"></span><span class="faicon iconno fas fa-thumbs-down faicon_flip"></span>');
     }
     if(option_val == 'faces'){
       jQuery('.button_style_preview').html('<span class="faicon iconyes far fa-smile"></span><span class="faicon iconno far fa-frown"></span>');
     }
     if(option_val == 'scircle'){
       jQuery('.button_style_preview').html('<span class="faicon iconyes fas fa-check-circle"></span><span class="faicon iconno fas fa-times-circle"></span>');
     }
  });
});

jQuery(document).on('change', '#disable_review_gating', function() {
    if(this.checked) {
      jQuery('#srm_public_review_text_field').show();
    }else{
      jQuery('#srm_public_review_text_field').hide();
    }
});

jQuery('#srm_no_destination').live('change', function() {
   var option_val = jQuery(this).val();
   if(option_val == 'multiple'){
      jQuery('.multiple-destination-edit').show();
      jQuery('.single-destination-edit').hide();
   }else{
     jQuery('.multiple-destination-edit').hide();
     jQuery('.single-destination-edit').show();
   }
});



/*********Multiple Destination*********/
jQuery(document).ready(function($) {
  $(document).on('click', '.btn-multi-desti-add', function(e){
      e.preventDefault();
      var controlForm = $(this).parents('.mulit_desti_row_section'),
          currentEntry = $(this).parents('.mulit_desti_row_section .mulit_desti_row:first'),
          newEntry = $(currentEntry.clone()).appendTo(controlForm);

      newEntry.find('input').val('');
      newEntry.find('.mtd_icon_preview').html('');
      newEntry.find('.photo_upload_area').hide();
      controlForm.find('.mulit_desti_row:not(:last) .mr_add_remove')
          .removeClass('btn-multi-desti-add').addClass('btn-multi-desti-remove')
          .removeClass('btn-success').addClass('btn-danger')
          .html('<span class="fas fa-minus"></span>');
  }).on('click', '.btn-multi-desti-remove', function(e){
    $(this).parents('.mulit_desti_row:first').remove();
    e.preventDefault();
    return false;
  });

  $('.desti_icon').live('change', function() {
     var option_val = $(this).val();
     if(option_val == 'Google'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_google fab fa-google"></span>');
     }
     if(option_val == 'Facebook'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_facebook fab fa-facebook-f"></span>');
     }
     if(option_val == 'Yelp'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_yelp fab fa-yelp"></span>');
     }
     if(option_val == 'Tripadvisor'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_tripadvisor fab fa-tripadvisor"></span>');
     }
     if(option_val == 'Amazon'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_amazon fab fa-amazon"></span>');
     }
     if(option_val == 'Audible'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_audible fab fa-audible"></span>');
     }
     if(option_val == 'iTunes'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_itunes fab fa-itunes-note"></span>');
     }
     if(option_val == 'AppleAppStore'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_apple fab fa-apple"></span>');
     }
     if(option_val == 'GooglePlay'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_google fab fa-google-play"></span>');
     }
     if(option_val == 'Foursquare'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_foursquare fab fa-foursquare"></span>');
     }
     if(option_val == 'WordPress'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_wordpress fab fa-wordpress"></span>');
     }
     if(option_val == 'Etsy'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_etsy fab fa-etsy"></span>');
     }
     if(option_val == 'YouTube'){
       $(this).parents('.mulit_desti_row').find('.mtd_icon_preview').html('<span class="faicon_preview icon_youtube fab fa-youtube"></span>');
     }
     if(option_val == 'Uploadimage'){
       $(this).parents('.mulit_desti_row').find('.photo_upload_area').show();
     }else{
       $(this).parents('.mulit_desti_row').find('.photo_upload_area').hide();
     }
  });

  $(document).on('click', '.btn-upload-photo', function(e){
    e.preventDefault();
    var val_nonce = $(this).closest(".mulit_desti_row_section").find("input[name='image_upload_nonce']").val();
    //var $this = $(this),
      var $this = $(this).closest(".mulit_desti_row"),
      nonce = val_nonce,
      images_wrap = $this.find('.mtd_icon_preview'),
      set_url_value = $this.find('.icon_photo'),
      set_id_value = $this.find('.icon_photo_id'),
      status = $this.find('.status'),
      formdata = false;

    if ( $this.find('.photoupload').val() == '' ) {
      alert('Please select an image to upload');
      return;
    }

    status.fadeIn().text('Uploading...');

    if (window.FormData) {
      formdata = new FormData();
    }
    var files_data = $this.find('.photoupload');

    $.each($(files_data), function(i, obj) {
      $.each(obj.files, function(j, file) {
        formdata.append('files[' + j + ']', file);
      })
    });
    // our AJAX identifier
    formdata.append('action', 'starfish_upload_images');

    formdata.append('nonce', nonce);

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: formdata,
      dataType: 'json',
      processData: false,
      contentType: false,
      success: function(data) {
        if (data.status) {
          images_wrap.append(data.message);
          set_url_value.val(data.photo_url);
          set_id_value.val(data.attachment_id);
          status.fadeIn().text('Image uploaded').fadeOut(2000);
        } else {
          status.fadeIn().text(data.message);
        }
      }
    });

  });



});
