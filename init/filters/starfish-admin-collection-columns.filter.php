<?php

// Add the custom columns to the collection post type:
add_filter( 'manage_collection_posts_columns', 'set_custom_edit_collection_columns' );
function set_custom_edit_collection_columns($columns) {
    $columns['visibility'] = __( 'Visibility', 'starfish' );
    $columns['shortcode'] = __( 'Shortcode', 'starfish' );
    return $columns;
}

// Add the data to the custom columns for the collection post type:
add_action( 'manage_collection_posts_custom_column' , 'custom_collection_column', 10, 2 );
function custom_collection_column( $column, $post_id ) {
    switch ( $column ) {

        case 'visibility' :
            $hidden = get_post_meta( $post_id, '_srm_collection_hidden', true );
            $checked = '';
            if ( $hidden ) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            echo '<input type="checkbox" ' . $checked . ' data-srm_collection_id="' . $post_id . '" data-style="srm-collection-hide-toggle" data-size="small" data-toggle="toggle" data-on="Hidden" data-off="Visible" data-onstyle="warning" data-offstyle="info">';
            break;
        case 'shortcode' :
            echo '<script>new ClipboardJS(\'#srm-copy-link-' . $post_id .'\');</script><code>[starfish collection="'. $post_id . '"]</code> <a id="srm-copy-link-' . $post_id .'" href="javascript:;" title="Copy to Clipboard" data-clipboard-text=\'[starfish collection="'. $post_id . '"]\'><i class="fa fa-copy"></i></a>';
            break;

    }

}