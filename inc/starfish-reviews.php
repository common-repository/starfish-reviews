<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
add_action( 'init', 'srm_register_reviews_cpt' );
/**
 * Register a Review post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function srm_register_reviews_cpt() {
	$labels = array(
		'name'               => _x( 'Reviews', 'post type general name', 'starfish' ),
		'singular_name'      => _x( 'Review', 'post type singular name', 'starfish' ),
		'menu_name'          => _x( 'Starfish Reviews', 'admin menu', 'starfish' ),
		'name_admin_bar'     => _x( 'Starfish Review', 'add new on admin bar', 'starfish' ),
		'add_new'            => _x( 'Add New', 'Review', 'starfish' ),
		'add_new_item'       => __( 'Add New Review', 'starfish' ),
		'new_item'           => __( 'New Review', 'starfish' ),
		'edit_item'          => __( 'Edit Review', 'starfish' ),
		'view_item'          => __( 'View Review', 'starfish' ),
		'all_items'          => __( 'All Reviews', 'starfish' ),
		'search_items'       => __( 'Search Reviews', 'starfish' ),
		'parent_item_colon'  => __( 'Parent Reviews:', 'starfish' ),
		'not_found'          => __( 'No Reviews found.', 'starfish' ),
		'not_found_in_trash' => __( 'No Reviews found in Trash.', 'starfish' )
	);

	$args = array(
		'labels'             => $labels,
    'description'        => __( 'Description.', 'starfish' ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'starfish-review' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 10,
		'menu_icon' => 'dashicons-star-filled',
		'supports'           => array( 'title', 'editor' )
	);

	register_post_type( 'starfish_review', $args );
}

function srm_disable_new_review_link() {
	// Hide sidebar link
	global $submenu;
	unset($submenu['edit.php?post_type=starfish_review'][10]);

}
add_action('admin_menu', 'srm_disable_new_review_link');


function srm_starfish_review_add_top_review_graph() {
    global $pagenow, $post;
				?>
				<style type="text/css">
					h1.wp-heading-inline{ display: none !important; }
			   .post-type-starfish_review .page-title-action { display:none; }
				</style>
				<?php
        echo '<div class="wrap">';
            echo '<h2><span class="dashicons dashicons_reviews dashicons-star-filled"></span>'. __('Reviews', 'starfish') .'</h2>';
        echo '</div>';
}

/**
 * Display HTML after the 'Posts' title
 * where we target the 'edit-post' screen
 */
add_filter( 'views_edit-starfish_review', function( $views ){
    srm_starfish_review_add_top_review_graph();
    return $views;
});

function srm_starfish_funnel_add_top() {
    global $pagenow, $post;
				?>
				<style type="text/css">
					h1.wp-heading-inline{ display: none !important; }
			   .post-type-funnel .page-title-action { display:none; }
				</style>
				<?php
}

/**
 * Display HTML after the 'Posts' title
 * where we target the 'edit-post' screen
 */
add_filter( 'views_edit-funnel', function( $views ){
    srm_starfish_funnel_add_top();
    return $views;
});


function srm_front_end_review_script() {
	global $post;
	wp_register_style(  'srm-review-front', SRM_LITE_PLUGIN_URL.'/css/reviews-frontend.css' );
	if( is_singular('funnel') ) {
			if ( ! wp_script_is( 'jquery', 'done' ) ) {
					wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_style('srm-review-front');
			wp_enqueue_style(  'fontawesome', SRM_LITE_PLUGIN_URL.'/css/fontawesome-all.min.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'srm_front_end_review_script');

function srm_admin_review_settings_script($hook) {
  // Load only on ?page=mypluginname
  if($hook != 'toplevel_page_starfish-reviews') {
    return;
  }
  wp_enqueue_style( 'srm_admin_css', SRM_LITE_PLUGIN_URL.'/css/reviews-admin.css' );
}
add_action( 'admin_enqueue_scripts', 'srm_admin_review_settings_script' );

// Add inline CSS in the admin head with the style tag
function srm_reviews_global_admin_head() {
	echo '<style>#adminmenu #menu-posts-starfish_review {display: none !important;}.srm-promotion-info{position:relative;}.srm_dismiss_link{padding: 2px; border-radius: 100%; background-color: #FA8072; color: #FFFFFF; position: absolute; top: 5px; right: 5px; text-decoration: none; }
	.srm_dismiss_link:hover{ background-color: #DC143C; color: #FFFFFF; }
	</style>';
}
add_action( 'admin_head', 'srm_reviews_global_admin_head' );
