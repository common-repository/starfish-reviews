<?php

use Starfish\Funnels as SRM_FUNNELS;
use Starfish\Premium\Collections as SRM_COLLECTIONS;

function srm_load_starfish_templates( $template ) {

	global $post;

	/*
	 * Global Scripts/Styles
	 */
	if (
		( 'funnel' === $post->post_type && empty( locate_template( array( 'single-funnel.php' ) ) ) ) ||
		( 'collection' === $post->post_type && empty( locate_template( array( 'single-collection.php' ) ) ) )
	) {
		srm_main_scripts();
	}

	/*
	 * Specific Post Type Scripts/Styles
	 */
	if ( 'funnel' === $post->post_type && empty( locate_template( array( 'single-funnel.php' ) ) ) ) {
		// Enqueue Scripts.
		$funnel_id = get_the_ID();
		SRM_FUNNELS::srm_enqueue_public_funnel_scripts( $funnel_id );
		SRM_FUNNELS::srm_localize_funnel_scripts( $funnel_id );
		return SRM_PLUGIN_PATH . 'template/starfish-funnel.template.php';
	}

	if ( 'collection' === $post->post_type && empty( locate_template( array( 'single-collection.php' ) ) ) ) {
		// Enqueue Scripts.
		SRM_COLLECTIONS::srm_enqueue_collection_scripts( get_the_ID() );
		return SRM_PLUGIN_PATH . 'template/starfish-collection.template__premium_only.php';
	}

	return $template;
}

add_filter( 'single_template', 'srm_load_starfish_templates' );
