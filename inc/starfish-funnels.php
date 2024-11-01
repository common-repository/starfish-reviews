<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
add_action( 'init', 'srm_register_funnel_cpt' );
/**
 * Register a Funnel post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function srm_register_funnel_cpt() {
	$labels = array(
		'name'               => _x( 'Funnels', 'post type general name', 'starfish' ),
		'singular_name'      => _x( 'Funnel', 'post type singular name', 'starfish' ),
		'menu_name'          => _x( 'Funnels', 'admin menu', 'starfish' ),
		'name_admin_bar'     => _x( 'Funnel', 'add new on admin bar', 'starfish' ),
		'add_new'            => _x( 'Add New', 'Funnel', 'starfish' ),
		'add_new_item'       => __( 'Add New Funnel', 'starfish' ),
		'new_item'           => __( 'New Funnel', 'starfish' ),
		'edit_item'          => __( 'Edit Funnel', 'starfish' ),
		'view_item'          => __( 'View Funnel', 'starfish' ),
		'all_items'          => __( 'All Funnels', 'starfish' ),
		'search_items'       => __( 'Search Funnels', 'starfish' ),
		'parent_item_colon'  => __( 'Parent Funnels:', 'starfish' ),
		'not_found'          => __( 'No Funnels found.', 'starfish' ),
		'not_found_in_trash' => __( 'No Funnels found in Trash.', 'starfish' )
	);
	$reveiw_page_slug = esc_html(get_option('srm_review_slug'));
	$args = array(
		'labels'             => $labels,
    'description'        => __( 'Description.', 'starfish' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'edit.php?post_type=starfish_review',
		'query_var'          => true,
		'rewrite'            => array( 'slug' => $reveiw_page_slug ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 100,
		'supports'           => array( 'title' )
	);

	register_post_type( 'funnel', $args );
}

/**
 * Store custom field meta box data
 *
 * @param int $post_id The post ID.
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
 */
function srm_funnel_sttings_save_meta_box_data( $post_id ){
	// verify meta box nonce
	if ( !isset( $_POST['funnel_settings_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['funnel_settings_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
  // Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}

}
add_action( 'save_post_funnel', 'srm_funnel_sttings_save_meta_box_data' );

function srm_funnel_yes_result_save_meta_box_data( $post_id ){
	// verify meta box nonce
	if ( !isset( $_POST['funnel_yes_result_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['funnel_yes_result_meta_box_nonce'], basename( __FILE__ ) ) ){
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
		return;
	}
  // Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ){
		return;
	}

	$total_funnel = get_total_funnel();
	$srm_plan_id = get_option( 'starfish_reviews_plan_id' );
	if($total_funnel > 1){
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
		add_filter( 'redirect_post_location', 'srm_funnel_restriction_notice_query_var', 99 );
	}

}
add_action( 'save_post_funnel', 'srm_funnel_yes_result_save_meta_box_data' );

function srm_funnel_restriction_notice_query_var( $location ) {
	return add_query_arg( array( 'funnel_limitation' => 'yes' ), $location );
}

function srm_funnel_add_notice_query_var( $location ) {
	return add_query_arg( array( 'funnel_destination_required' => 'yes' ), $location );
}

add_action( 'admin_notices', 'funnel_validation_admin_notice' );
function funnel_validation_admin_notice(){
	if(isset($_GET['funnel_destination_required']) && ($_GET['funnel_destination_required'] =='yes')){
		?>
		<div class="error">
		  <p><?php _e( 'Review Destination URL Is Required', 'starfish' ); ?></p>
		</div>
		<?php
	}

	if(isset($_GET['funnel_limitation']) && ($_GET['funnel_limitation'] =='yes')){
		?>
		<div class="error">
			<p><?php _e( 'You have the lite version of Starfish Reviews, which limits you to 1 funnel. Please <a href="https://starfishwp.com/shop/" target="_blank">upgrade to</a> the Webmaster license if you need more.', 'starfish' ); ?></p>
		</div>
		<?php
	}
}


function srm_get_last_funnel_id(){
	global $wpdb;
	$lastrowId = $wpdb->get_col( "SELECT ID FROM $wpdb->posts where post_type='funnel' and post_status='publish' ORDER BY post_date DESC " );
	if(isset($lastrowId[0])){
		$lastFunnelId = $lastrowId[0];
	}else{
		$lastFunnelId = '';
	}

	return $lastFunnelId;
}

function srm_save_all_funnels_to_options(){
	$result_arr = array();
	$srm_funnel_args = array( 'post_type' => 'funnel', 'posts_per_page' => '-1');
	$srm_funnel_query = new WP_Query( $srm_funnel_args );
	$total_funnel = 0;
	if ( $srm_funnel_query->have_posts() ) {
			while ( $srm_funnel_query->have_posts() ) {
				$srm_funnel_query->the_post();
				$funnel_id = get_the_ID();
				$funnel_name = get_the_title();
				$result_arr[$funnel_id] = $funnel_name;
				$total_funnel += 1;
			}
			wp_reset_postdata();
	}
	update_option('srm_all_funnels', $result_arr);
}
add_action( 'save_post_funnel', 'srm_save_all_funnels_to_options' );

function get_total_funnel(){
	$count_funnels = wp_count_posts('funnel');
	$published_funnels = $count_funnels->publish;

	return $published_funnels;
}
