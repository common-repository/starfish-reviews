<?php

add_filter('manage_edit-funnel_columns', 'srm_funnel_add_new_custom_columns');
function srm_funnel_add_new_custom_columns( $columns ) {
	$columns['shortcode'] = __('Shortcode', 'starfish');
	return $columns;
}

add_action('manage_posts_custom_column', 'srm_custom_funnel_column', 10, 2);
function srm_custom_funnel_column($column, $post_id)
{
	if ( 'funnel' !== get_post_type($post_id)) {
		return;
	}
	if ($column === 'shortcode') {
		echo '<script>new ClipboardJS(\'#srm-copy-link-' . $post_id . '\');</script><code>[starfish funnel="' . $post_id . '"]</code> <a id="srm-copy-link-' . $post_id . '" href="javascript:;" title="Copy to Clipboard" data-clipboard-text=\'[starfish funnel="' . $post_id . '"]\'><i class="fa fa-copy"></i></a>';
	}
}