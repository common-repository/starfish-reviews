<?php

add_filter( 'views_edit-starfish_testimonial', 'srm_modified_publish_status' );
function srm_modified_publish_status( $views ) {

	if ( isset( $views['publish'] ) ) {
		$views['publish'] = str_replace( 'Published ', 'Approved ', $views['publish'] );
	}

	if ( isset( $views['draft'] ) ) {
		unset( $views['draft'] );
	}

	return array_values( $views );
}

add_filter( 'post_date_column_status' , 'my_post_date_column_time' , 10 , 4 );

function my_post_date_column_time( $status, $post, $column_name, $mode ) {
	if($post->post_type == 'starfish_testimonial') {
		if($status == 'Published') {
			$status = 'Approved';
		}
	}
	return $status;
}

add_filter( 'wp_insert_post_data', 'srm_force_new_pending', 20, 2 );

function srm_force_new_pending( $data, $postarr ) {
	if ( $data['post_type'] !== 'starfish_testimonial' ) return $data;
	if ( $data['post_status'] === 'auto-draft' || $data['post_status'] === 'draft' ) {
		$data['post_status'] = 'pending';
	}
	return $data;
}

add_filter( 'wp_untrash_post_status', 'wp_untrash_post_set_previous_status', 20, 3 );

/**
 * ========================================
 * Custom bulk actions for Testimonials
 * ========================================
 */
add_filter('bulk_actions-edit-starfish_testimonial', function($bulk_actions) {
	$bulk_actions['approve'] = __('Approve', 'starfish');
	$bulk_actions['unapprove'] = __('Un-Approve', 'starfish');
	return $bulk_actions;
});

add_filter('handle_bulk_actions-edit-starfish_testimonial', function($redirect_url, $action, $post_ids) {
	if ($action == 'approve') {
		foreach ($post_ids as $post_id) {
			wp_update_post([
				'ID' => $post_id,
				'post_status' => 'publish'
			]);
		}
		$redirect_url = add_query_arg('approve', count($post_ids), $redirect_url);
	} elseif ($action == 'unapprove') {
		foreach ($post_ids as $post_id) {
			wp_update_post([
				'ID' => $post_id,
				'post_status' => 'pending'
			]);
		}
		$redirect_url = add_query_arg('unapprove', count($post_ids), $redirect_url);
	}
	return $redirect_url;
}, 10, 3);