<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function starfish_get_destination_icon($icon_type, $photo_id = '', $display = 'admin'){
  $icon_html = '';

  if($icon_type == 'Google'){
    $icon_html = '<span class="faicon_preview icon_google fab fa-google"></span>';
  }
  if($icon_type == 'Facebook'){
    $icon_html = '<span class="faicon_preview icon_facebook fab fa-facebook-f"></span>';
  }
  if($icon_type == 'Yelp'){
    $icon_html = '<span class="faicon_preview icon_yelp fab fa-yelp"></span>';
  }
  if($icon_type == 'Tripadvisor'){
    $icon_html = '<span class="faicon_preview icon_tripadvisor fab fa-tripadvisor"></span>';
  }
  if($icon_type == 'Amazon'){
    $icon_html = '<span class="faicon_preview icon_amazon fab fa-amazon"></span>';
  }
  if($icon_type == 'Audible'){
    $icon_html = '<span class="faicon_preview icon_audible fab fa-audible"></span>';
  }
  if($icon_type == 'iTunes'){
    $icon_html = '<span class="faicon_preview icon_itunes fab fa-itunes-note"></span>';
  }
  if($icon_type == 'AppleAppStore'){
    $icon_html = '<span class="faicon_preview icon_apple fab fa-apple"></span>';
  }
  if($icon_type == 'GooglePlay'){
    $icon_html = '<span class="faicon_preview icon_google fab fa-google-play"></span>';
  }
  if($icon_type == 'Foursquare'){
    $icon_html = '<span class="faicon_preview icon_foursquare fab fa-foursquare"></span>';
  }
  if($icon_type == 'WordPress'){
    $icon_html = '<span class="faicon_preview icon_wordpress fab fa-wordpress"></span>';
  }
  if($icon_type == 'Etsy'){
    $icon_html = '<span class="faicon_preview icon_etsy fab fa-etsy"></span>';
  }
  if($icon_type == 'YouTube'){
    $icon_html = '<span class="faicon_preview icon_youtube fab fa-youtube"></span>';
  }
  if($photo_id > 0){
      if($display == 'admin'){
        $img_thumb = wp_get_attachment_image_src( $photo_id, 'thumbnail' );
        if(isset($img_thumb[0])){
          $icon_html ='<img width="20" src="'.esc_url($img_thumb[0]).'" alt="" />';
        }
      }else{
        $img_thumb = wp_get_attachment_image_src( $photo_id, 'thumbnail' );
        if(isset($img_thumb[0])){
          $icon_html ='<img width="100" src="'.esc_url($img_thumb[0]).'" alt="" />';
        }
      }
  }

  return $icon_html;

}


function starfish_get_icon_color($icon_type){
  $icon_color = '';

  switch ($icon_type) {
    case "Google":
        $icon_color = '#dd4b39';
        break;
    case "Facebook":
        $icon_color = '#3b5998';
        break;
    case "Yelp":
        $icon_color = '#af0606';
        break;
    case "Tripadvisor":
        $icon_color = '#00af87';
        break;
    case "Amazon":
        $icon_color = '#ff9900';
        break;
    case "Audible":
        $icon_color = '#f7991c';
        break;
    case "iTunes":
        $icon_color = '#333333';
        break;
    case "AppleAppStore":
        $icon_color = '#34C5F9';
        break;
    case "GooglePlay":
        $icon_color = '#fbbc05';
        break;
    case "Foursquare":
        $icon_color = '#f94877';
        break;
    case "WordPress":
        $icon_color = '#21759b';
        break;
    case "Etsy":
        $icon_color = '#d5641c';
        break;
    case "YouTube":
        $icon_color = '#ff0000';
        break;
    default:
        $icon_color = '#000';
  }

  return $icon_color;

}