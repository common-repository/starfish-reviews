<?php

add_action( 'rest_api_init', 'register_review_meta_fields' );
 
function register_review_meta_fields() {

    $args = array(
        'get_callback'  => 'rest_get_meta',
        'update_callback'   => null,
        'schema'            => null
    );
    register_rest_field( 'starfish_review', 'meta', $args );
    
}

function rest_get_meta( $post, $field_name, $request, $object_type ) {
    
    $fields = array(
        '_srm_tracking_id'      => 'tracking_id',
        '_srm_funnel_id'        => 'funnel_id',
        '_srm_reviewer_name'    => 'reviewer_name',
        '_srm_reviewer_email'   => 'reviewer_email',
        '_srm_reviewer_phone'   => 'reviewer_phone',
        '_srm_feedback'         => 'feedback',
        '_srm_desti_name'       => 'destination_name',
        '_srm_destination_url'  => 'destination_url',
        '_srm_desti_type'       => 'destination_type'
    );
    
    $meta = array();
    
    foreach( $fields as $field => $display_name ) {
        $meta[$display_name] = get_post_meta($post['id'], $field, true );
    }
    
    return $meta;
    
}