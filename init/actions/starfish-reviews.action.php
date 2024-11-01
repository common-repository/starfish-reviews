<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'init', 'srm_register_reviews_cpt' );

/**
 * Register a Review post type.
 *
 */
function srm_register_reviews_cpt() {
	$labels = array(
		'name'               => esc_html_x( 'Reviews', 'post type general name', 'starfish' ),
		'singular_name'      => esc_html_x( 'Review', 'post type singular name', 'starfish' ),
		'menu_name'          => esc_html_x( 'Starfish Reviews', 'admin menu', 'starfish' ),
		'name_admin_bar'     => esc_html_x( 'Starfish Review', 'add new on admin bar', 'starfish' ),
		'add_new'            => esc_html_x( 'Add New', 'Review', 'starfish' ),
		'add_new_item'       => esc_html__( 'Add New Review', 'starfish' ),
		'new_item'           => esc_html__( 'New Review', 'starfish' ),
		'edit_item'          => esc_html__( 'Edit Review', 'starfish' ),
		'view_item'          => esc_html__( 'View Review', 'starfish' ),
		'all_items'          => esc_html__( 'All Reviews', 'starfish' ),
		'search_items'       => esc_html__( 'Search Reviews', 'starfish' ),
		'parent_item_colon'  => esc_html__( 'Parent Reviews:', 'starfish' ),
		'not_found'          => esc_html__( 'No Reviews found.', 'starfish' ),
		'not_found_in_trash' => esc_html__( 'No Reviews found in Trash.', 'starfish' )
	);

	$args = array(
		'labels'             => $labels,
                'description'        => esc_html__( 'Description.', 'starfish' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'starfish-review' ),
		'capability_type'    => 'post',
                'capabilities'      =>  array( 
                    'create_posts'  => false,
                    'delete_posts'  => true
                    ),
                'map_meta_cap'      => true,
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 10,
		'menu_icon'          => 'dashicons-star-filled',
		'supports'           => array( 'title', 'editor' ),
                'show_in_rest'       => true
	);
        
        // Set Restrictions Based on Current License/Plan
        $current_plan = SRM_FREEMIUS::starfish_fs()->get_plan_title();
        if( SRM_REVIEWS::srm_restrict_control( $current_plan ) ) {
            $args['capabilities']['delete_posts'] = false;
            $args['map_meta_cap'] = false;
            $url = $_SERVER['REQUEST_URI'];  
            if( strpos( $url, 'post_type=starfish_review' ) !== false && strpos( $url, 'page=' ) == false ) {
               print SRM_REVIEWS::srm_admin_plan_restriction_notice( $current_plan );
            }        
        }

	register_post_type( 'starfish_review', $args );
}

function srm_disable_new_review_link() {
    // Hide sidebar link
    global $submenu;
    unset($submenu['edit.php?post_type=starfish_review'][10]);

}
add_action('admin_menu', 'srm_disable_new_review_link');

/**
* Reorder Reveiws admin column
*/
function srm_manage_starfish_review_posts_columns($post_columns) {
    $post_columns = array(
        'cb'            => $post_columns['cb'],
        'title'         => esc_html__( 'Date & Time', 'starfish' ),
	    'track_id'      => esc_html__( 'ID', 'starfish' ),
	    'reviewer'      => esc_html__( 'Reviewer', 'starfish' ),
	    'feedback'      => esc_html__( 'Feedback', 'starfish' ),
	    'message'       => esc_html__( 'Message', 'starfish' ),
        'funnel'        => esc_html__( 'Funnel', 'starfish' ),
	    'destination'   => esc_html__( 'Destination', 'starfish' )
    );
    return $post_columns;
}
add_action('manage_starfish_review_posts_columns', 'srm_manage_starfish_review_posts_columns');

/**
* add order column to admin listing screen for reviews
*/
function srm_add_new_starfish_review_column($review_columns) {
	$review_columns['track_id']     = esc_html__( 'ID', 'starfish' );
	$review_columns['reviewer']     = esc_html__( 'Reviewer', 'starfish' );
	$review_columns['feedback']     = esc_html__( 'Feedback', 'starfish' );
	$review_columns['message']      = esc_html__( 'Message', 'starfish' );
	$review_columns['funnel']       = esc_html__( 'Funnel', 'starfish' );
	$review_columns['destination']  = esc_html__( 'Destination', 'starfish' );
  return $review_columns;
}
add_action('manage_edit-starfish_review_columns', 'srm_add_new_starfish_review_column');

/**
* show custom order column values
*/
function srm_starfish_review_show_order_column($name){
  global $post;

  switch ($name) {
        case 'track_id':
            $tracking_id = get_post_meta($post->ID, '_srm_tracking_id', true);
            if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
               echo esc_html($tracking_id); 
            }
            else {
                echo '<a href="' . SRM_PLANS_URL . '" title="Premium Feature, Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            }
            break;
        case 'reviewer':
            if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                if(get_post_meta($post->ID, '_srm_reviewer_name', true) != ''){
                    echo 'Name: '. esc_html(get_post_meta($post->ID, '_srm_reviewer_name', true));
                }
                if(get_post_meta($post->ID, '_srm_reviewer_email', true) != ''){
                    echo '<br />Email: '. esc_html(get_post_meta($post->ID, '_srm_reviewer_email', true));
                }
                if(get_post_meta($post->ID, '_srm_reviewer_phone', true) != ''){
                    echo '<br />Phone: '. esc_html(get_post_meta($post->ID, '_srm_reviewer_phone', true));
                }
            } else {
                echo '<a href="' . SRM_PLANS_URL . '" title="Premium Feature, Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            }
            break;
        case 'feedback':
            $feedback = get_post_meta($post->ID, '_srm_feedback', true);
            $feedback = esc_html($feedback);
            if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                if($feedback == 'Yes'){
                        echo esc_html__( 'Positive', 'starfish' );
                }else{
                        echo esc_html__( 'Negative', 'starfish' );
                }
            } else {
                echo '<a href="' . SRM_PLANS_URL . '" title="Premium Feature, Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            }
            break;
        case 'message':
            $content = wp_trim_words( get_the_content($post->ID), 10, '...' );
            if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                echo $content;
            } else {
                echo '<a href="' . SRM_PLANS_URL . '" title="Premium Feature, Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            }
            break;
        case 'funnel':
            $srm_funnel_id = get_post_meta($post->ID, '_srm_funnel_id', true);
            $srm_funnel_title = get_the_title($srm_funnel_id);
            if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                echo esc_html($srm_funnel_title);
            } else {
                echo '<a href="' . SRM_PLANS_URL . '" title="Premium Feature, Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            }   
            break;
        case 'destination':
            if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                if(get_post_meta($post->ID, '_srm_desti_name', true) != ''){
                    $desti_name = esc_html(get_post_meta($post->ID, '_srm_desti_name', true));
                    echo $desti_name;
                }
            } else {
                echo '<a href="' . SRM_PLANS_URL . '" title="Premium Feature, Upgrade Now!"><span class="premium-only-badge fas fa-star"></span></a>';
            } 
            break;
   default:
      break;
   }
}
add_action('manage_starfish_review_posts_custom_column','srm_starfish_review_show_order_column');

/**
* make column sortable
*/
function srm_starfish_review_column_register_sortable($columns){
    if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
        $columns['track_id'] = '_srm_tracking_id';
        $columns['feedback'] = '_srm_feedback';
        $columns['funnel'] = '_srm_funnel_id';
        $columns['destination'] = '_srm_desti_name';
    }
  return $columns;
}
add_filter('manage_edit-starfish_review_sortable_columns','srm_starfish_review_column_register_sortable');

/**
 * @throws Freemius_Exception Freemius exception.
 */
function srm_add_filter_by_funnel_reveiw_admin() {
  global $typenow;
  global $wp_query;
  if(SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
    if ( $typenow == 'starfish_review' ) {
        $current_funnel = '';
        $funnel_arr = get_option('srm_all_funnels');
        if( isset( $_GET['funnel_id'] ) ) {
          $current_funnel = $_GET['funnel_id'];
        } ?>
        <select name="funnel_id" id="funnel_id">
              <option value="all" <?php selected( 'all', $current_funnel ); ?>><?php _e( 'All Funnels', 'starfish' ); ?></option>
              <?php
                  if(isset($funnel_arr) && (count($funnel_arr) > 0)){
                      foreach ($funnel_arr as $funnel_id => $funnel_name) {
                          ?><option value="<?php echo esc_attr( $funnel_id ); ?>" <?php selected( $funnel_id, $current_funnel ); ?>><?php echo esc_attr( $funnel_name ); ?></option><?php
                      }
                  }
              ?>
        </select>
  <?php }}
}
add_action( 'restrict_manage_posts', 'srm_add_filter_by_funnel_reveiw_admin' );


/**
 * Update query
 * @since 1.0
 * @return void
 */
function srm_admin_sort_reviews_by_funnel( $query ) {
  global $pagenow;
  // Get the post type
  $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
  if ( is_admin() && $pagenow=='edit.php' && $post_type == 'starfish_review' && isset( $_GET['funnel_id'] ) && $_GET['funnel_id'] !='all' ) {
    $query->query_vars['meta_key'] = '_srm_funnel_id';
    $query->query_vars['meta_value'] = intval($_GET['funnel_id']);
    $query->query_vars['meta_compare'] = '=';
  }
}
add_filter( 'parse_query', 'srm_admin_sort_reviews_by_funnel' );