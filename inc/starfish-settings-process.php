<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//notice-error, notice-warning, notice-success, or notice-info  updated is-dismissible
function starfish_notice_data_successfully_saved(){
  $class = 'notice updated is-dismissible';
	$message = __( 'Settings saved.', 'starfish' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

function starfish_notice_data_nonce_verify_required(){
  $class = 'notice notice-error is-dismissible';
	$message = __( 'Nonce unverified! Try again.', 'starfish' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

function starfish_notice_data_review_destination_url_required(){
  $class = 'notice notice-error is-dismissible';
	$message = __( 'Destination URL Required', 'starfish' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

add_action( 'admin_init', 'starfish_notice_for_permium_promotion');
function starfish_notice_for_permium_promotion(){

  if(isset($_REQUEST['srm-alart-plugin-review']) && ($_REQUEST['srm-alart-plugin-review'] == 'hide')){
    update_option('srm_alart_plugin_review', 'hide');
  }
  if(isset($_REQUEST['srm-alart-pro-promotion']) && ($_REQUEST['srm-alart-pro-promotion'] == 'hide')){
    update_option('srm_alart_pro_promotion', 'hide');
  }

  if(get_option('srm_alart_plugin_review') != 'hide'){
    if(srm_get_total_reviews() >= 4){
      add_action( 'admin_notices', 'starfish_notice_for_getting_funnel');
    }
  }

  if(get_option('srm_alart_pro_promotion') != 'hide'){
    $today = time();
    $activation_date = get_option('srm_active_date');
    $days_between = ceil(abs($today - $activation_date) / 86400);
    if($days_between >= 15){
        add_action( 'admin_notices', 'starfish_notice_for_promoting_more_features');
    }
  }

}

function starfish_notice_for_getting_funnel(){
  $class = 'notice notice-info srm-promotion-info';
  $wp_review_url = 'https://wordpress.org/support/plugin/starfish-reviews/reviews/#new-post';
  $wp_review_site = __( 'WordPress.org', 'starfish' );
	$message = __( 'Excellent! You\'ve gotten some nice feedback from your Starfish Reviews funnel. Could you give Starfish Reviews an honest rating and review on ', 'starfish' );
	printf( '<div class="%1$s"><p>%2$s <a href="%3$s" target="_blank">%4$s</a>?</p><a class="srm_dismiss_link" href="admin.php?page=starfish-reviews&srm-alart-plugin-review=hide"><span class="dashicons dashicons-no-alt"></span></a></div>', esc_attr( $class ), esc_html( $message ), esc_url( $wp_review_url ), esc_html( $wp_review_site ) );
}

function starfish_notice_for_promoting_more_features(){
  $class = 'notice notice-info srm-promotion-info';
  $wp_promo_url = 'https://starfishwp.com/';
	$message1 = __( 'We notice you\'ve been using Starfish Reviews Lite a while now. Are you interested in more features and control? Check out our ', 'starfish' );
  $message2 = __( 'Starfish Reviews Pro ', 'starfish' );
  $message3 = __( 'editions of the plugin.', 'starfish' );
	printf( '<div class="%1$s"><p>%2$s <a href="%3$s" target="_blank">%4$s</a> %5$s</p><a class="srm_dismiss_link" href="admin.php?page=starfish-reviews&srm-alart-pro-promotion=hide"><span class="dashicons dashicons-no-alt"></span></a></div>', esc_attr( $class ), esc_html( $message1 ), esc_url( $wp_promo_url ), esc_html( $message2 ), esc_html( $message3 ) );
}


function srm_get_total_reviews(){
  $total_review = $negative_review = $positive_review = 0;
  $srm_review_args = array( 'post_type' => 'starfish_review', 'posts_per_page' => '-1');
  $srm_review_query = new WP_Query( $srm_review_args );
  if ( $srm_review_query->have_posts() ) {
      while ( $srm_review_query->have_posts() ) {
        $srm_review_query->the_post();
        $review_id = get_the_ID();
        $feedback = get_post_meta($review_id, '_srm_feedback', true);
        if($feedback == 'Yes'){
            $positive_review += 1;
        }else{
          $negative_review += 1;
        }
        $total_review += 1;
      }
      wp_reset_postdata();
  }
  return $total_review;
}
