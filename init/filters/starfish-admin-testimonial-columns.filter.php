<?php


// Add the custom columns to the testimonial post type.
add_filter( 'manage_edit-starfish_testimonial_columns', 'srm_set_custom_edit_testimonial_columns' );
function srm_set_custom_edit_testimonial_columns( $columns ) {
	$columns['hide']      = __( 'Visibility', 'starfish' );
	$columns['name']      = __( 'Name', 'starfish' );
	$columns['referrer']  = __( 'Referrer', 'starfish' );
	$columns['rating']    = __( 'Rating', 'starfish' );
	$columns['shortcode'] = __( 'Shortcode', 'starfish' );
	$columns['date'] 	  = __( 'Date', 'starfish' );
	return $columns;
}

// Add the data to the custom columns for the testimonial post type.
add_action( 'manage_starfish_testimonial_posts_custom_column', 'srm_custom_testimonial_column', 10, 2 );
function srm_custom_testimonial_column( $column, $post_id ) {
	if ( 'starfish_testimonial' !== get_post_type($post_id)) {
		return;
	}
	switch ( $column ) {
		case 'hide':
			$hide  = esc_html(get_post_meta( $post_id, SRM_META_TESTIMONIAL_HIDDEN, true ));
			$checked = '';
			if ( $hide ) {
				$checked = 'checked'; }
			echo '<div class="bootstrap-srm"><input type="checkbox" ' . $checked . ' data-srm_testimonial_id="' . $post_id . '" data-style="srm-testimonial-hide-toggle" data-size="small" data-toggle="toggle" data-on="Hidden" data-off="Visible" data-onstyle="warning" data-offstyle="info"></div>';
			break;
		case 'shortcode':
			echo '<script>new ClipboardJS(\'#srm-copy-link-' . $post_id . '\');</script><code>[starfish testimonial="display" posts="' . $post_id . '"]</code> <a id="srm-copy-link-' . $post_id . '" href="javascript:;" title="Copy to Clipboard" data-clipboard-text=\'[starfish testimonial="' . $post_id . '"]\'><i class="fa fa-copy"></i></a>';
			break;
		case 'rating':
			$rating = esc_html(get_post_meta( $post_id, SRM_META_TESTIMONIAL_RATING, true ));
			$value  = '<div class="srm-testimonials-rating" title="' . $rating . ' Star Rating">';
			for ( $s = 1; $s <= $rating; $s++ ) {
				$value .= '<span class="fa fa-star srm-testimonials-star"></span>';
			}
			echo $value . '</div>';
			break;
		case 'referrer':
			$referrer_url = esc_url(get_post_meta( $post_id, SRM_META_TESTIMONIAL_REFERRER, true ));
			$referrer_title = get_the_title( url_to_postid( $referrer_url ) );
			echo '<a href="' . $referrer_url . '" target="_blank">' . $referrer_title . '</a>';
			break;
		case 'name':
			$name = esc_html(get_post_meta( $post_id, SRM_META_TESTIMONIAL_NAME, true ));
			$avatar = empty( get_post_meta($post_id, SRM_META_TESTIMONIAL_AVATAR, true)) ? SRM_PLUGIN_URL . '/assets/default_profile_image.png' : esc_url(get_post_meta($post_id, SRM_META_TESTIMONIAL_AVATAR), true);
			echo '<div class="srm-testimonials-avatar"><img title="' . $name . '" alt="Testimonial Avatar" src="' . $avatar . '"/></div>';
			echo $name;
			echo '<br/><i class="fa fa-envelope" aria-hidden="true" title="Email"></i> ' . esc_html(get_post_meta( $post_id, SRM_META_TESTIMONIAL_EMAIL, true ));
			echo '<br/><i class="fa fa-phone" aria-hidden="true" title="Phone"></i> ' . esc_html(get_post_meta( $post_id, SRM_META_TESTIMONIAL_PHONE, true ));
			break;
		default:
			echo "Not Available";
			break;
	}
}
add_filter( 'manage_edit-starfish_testimonial_sortable_columns', 'srm_starfish_testimonial_column_register_sortable' );
function srm_starfish_testimonial_column_register_sortable( $columns ) {
	$columns['name']     = 'name';
	$columns['hide']     = 'hide';
	$columns['referrer'] = 'referrer';
	$columns['rating']   = 'rating';
	return $columns;
}

add_filter('manage_starfish_testimonial_posts_columns', 'srm_starfish_testimonial_column_order');
function srm_starfish_testimonial_column_order($columns) {
	$n_columns = array();
	$before = 'categories'; // move before this

	foreach($columns as $key => $value) {
		if ($key==$before){
			$n_columns['name'] = '';
			$n_columns['rating'] = '';
			$n_columns['hide'] = '';
			$n_columns['referrer'] = '';
		}
		$n_columns[$key] = $value;
	}
	return $n_columns;
}