<?php

use Starfish\Settings as SRM_SETTINGS;
use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Funnels as SRM_FUNNELS;
use Starfish\Twig as SRM_TWIG;
use Twig\TwigFilter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'init', 'srm_register_funnel_cpt' );
add_action( 'add_meta_boxes_funnel', 'srm_funnel_meta_boxes' );
add_action( 'save_post_funnel', 'srm_funnel_toolbox_save_meta_box_data' );
add_action( 'save_post_funnel', 'srm_funnel_settings_save_meta_box_data' );
add_action( 'save_post_funnel', 'srm_funnel_yes_result_save_meta_box_data' );
add_action( 'admin_notices', 'funnel_validation_admin_notice' );
add_action( 'save_post_funnel', 'srm_no_result_save_meta_box_data' );
add_action( 'save_post_funnel', 'srm_save_all_funnels_to_options' );
add_action( 'wp_ajax_starfish_upload_images', 'starfish_upload_images_callback' );
add_action( 'wp_ajax_nopriv_starfish_upload_images', 'starfish_upload_images_callback' );

/**
 * Register a Funnel post type.
 */
function srm_register_funnel_cpt() {
	$labels = array(
		'name'                  => esc_html_x( 'Funnels', 'post type general name', 'starfish' ),
		'singular_name'         => esc_html_x( 'Funnel', 'post type singular name', 'starfish' ),
		'menu_name'             => esc_html_x( 'Funnels', 'admin menu', 'starfish' ),
		'name_admin_bar'        => esc_html_x( 'Funnel', 'add new on admin bar', 'starfish' ),
		'add_new'               => esc_html_x( 'Add New', 'Funnel', 'starfish' ),
		'add_new_item'          => esc_html__( 'Add New Funnel', 'starfish' ),
		'new_item'              => esc_html__( 'New Funnel', 'starfish' ),
		'edit_item'             => esc_html__( 'Edit Funnel', 'starfish' ),
		'featured_image'        => esc_html__( 'Funnel Logo', 'starfish' ),
		'set_featured_image'    => esc_html__( 'Set Funnel Logo', 'starfish' ),
		'remove_featured_image' => esc_html__( 'Remove Funnel Logo', 'starfish' ),
		'use_featured_image'    => esc_html__( 'Use as Funnel Logo', 'starfish' ),
		'view_item'             => esc_html__( 'View Funnel', 'starfish' ),
		'all_items'             => esc_html__( 'Funnels', 'starfish' ),
		'search_items'          => esc_html__( 'Search Funnels', 'starfish' ),
		'parent_item_colon'     => esc_html__( 'Parent Funnels:', 'starfish' ),
		'not_found'             => esc_html__( 'No Funnels found.', 'starfish' ),
		'not_found_in_trash'    => esc_html__( 'No Funnels found in Trash.', 'starfish' ),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => esc_html__( 'Description.', 'starfish' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => 'starfish-parent',
		'menu_position'      => 1,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => esc_html( get_option( SRM_OPTION_FUNNEL_SLUG ) ) ),
		'capability_type'    => 'post',
		'capabilities'       => array( 'create_pages' => true ),
		'map_meta_cap'       => true,
		'has_archive'        => ( get_option( SRM_OPTION_FUNNEL_ARCHIVES ) === 'yes' ? false : true ),
		'hierarchical'       => false,
		'supports'           => array( 'title', 'thumbnail' ),
		'show_in_rest'       => true,
	);

	// Set Restrictions Based on Current Site's License/Plan.
	if ( ! is_network_admin() && SRM_FUNNELS::srm_restrict_add_new() ) {
		$args['capabilities']['create_posts'] = false;
	}
	register_post_type( 'funnel', $args );
}

/**
 * Add meta boxes
 *
 * @param post $post The post object
 */
function srm_funnel_meta_boxes( $post ) {
	// Normal
	add_meta_box( 'funnel_settings_meta_box', esc_html__( 'Question', 'starfish' ), 'srm_funnel_settings_build_meta_box', 'funnel', 'normal', 'default' );
	add_meta_box( 'funnel_yes_meta_box', esc_html__( 'Positive Response', 'starfish' ), 'srm_yes_result_build_meta_box', 'funnel', 'normal', 'default' );
	add_meta_box( 'funnel_no_meta_box', esc_html__( 'Negative Response', 'starfish' ), 'srm_no_result_build_meta_box', 'funnel', 'normal', 'default' );
	// Side
	add_meta_box( 'funnel_toolbox_meta_box', esc_html__( 'Funnel Toolbox', 'starfish' ), 'srm_funnel_toolbox_build_meta_box', 'funnel', 'side', 'default' );
}


/**
 * Build Funnel Toolbox meta box
 *
 * @param post $post The post object
 */
function srm_funnel_toolbox_build_meta_box( $post ) {
	// Premium Only Styling
	$premium_only      = SRM_FREEMIUS::starfish_premium_only_styles();
	$premium_container = $premium_only['container_class'];
	$premium_badge     = $premium_only['premium_only_badge'];
	$premium_link      = $premium_only['premium_only_link'];

	$toolbox_funnel_id = get_the_ID();

	$toolbox_restore_button_text = _x( 'Restore Defaults', 'Button Text to restore default funnel settings', 'starfish' );
	$toolbox_restore_description = __( sprintf( 'Replace existing values with the <a href="%1$s" title="View Default Funnel Options">current defaults</a>.', '/wp-admin/admin.php?page=starfish-settings&tab=defaults_options' ), 'starfish' );

	$toolbox_embed_description = __( 'Copy and Paste the below code snippet into any HTML webpage to show this funnel', 'starfish' );
	$toolbox_embed_subtext     = __( 'The Embed Code requires destinations to open in a new window.', 'starfish' );
	$toolbox_embed_url         = get_permalink();
    $toolbox_embed_torigin     = esc_url( get_post_meta( $post->ID, '_srm_funnel_embed_target_origin', true ) );
	$toolbox_embed_dyn_sizing  = esc_html( get_post_meta( $post->ID, '_srm_funnel_embed_dynamic_sizing', true ) );
    $toolbox_embed_url_domain  = parse_url( $toolbox_embed_url, PHP_URL_SCHEME ) . '://' . parse_url( $toolbox_embed_url, PHP_URL_HOST );

    $toolbox_embed_dyn_sizing_checked = checked( $toolbox_embed_dyn_sizing, 'true', false );

    $toolbox_embed_torigin_description      = __('The domain where the above embed code will be placed (i.e., <code>https://mydomain.com</code>). Dynamic Sizing will not function without this.', 'starfish');
	$toolbox_embed_dyn_sizing_description   = __('Automatically calculate the iframe height at the Target Domain. If not checked, the <code>&lt;script/&gt;</code> section of the snippet can be removed', 'starfish');

    wp_nonce_field( basename( __FILE__ ), 'funnel_toolbox_meta_box_nonce' );
	print <<<TOOLS
	<div id="srm-funnel-tool-restore" class="srm-funnel-tool">
            <button id="srm-defaults-restore" class="srm-settings-caution-button srm-settings-button">$toolbox_restore_button_text</button>
            <p class="description">$toolbox_restore_description</p>
    </div>
    <div id="srm-funnel-shortcode" class="srm-funnel-tool $premium_container">
        <script>new ClipboardJS('#srm-copy-shortcode-link-$toolbox_funnel_id');</script>
        <div class="srm-sidebar-subtitle">
           $premium_badge Shortcode $premium_link
        </div>
        <code>[starfish funnel="$toolbox_funnel_id"]</code>
        <a id="srm-copy-shortcode-link-$toolbox_funnel_id" href="javascript:;" title="Copy to Clipboard" data-clipboard-text='[starfish funnel="$toolbox_funnel_id"]'>
            <i class="fa fa-copy"></i>
        </a>
    </div>
    <div id="srm-funnel-embed-snippet" class="srm-funnel-tool">
        <div class="srm-sidebar-subtitle">Embed Code</div>
        <p class="description">$toolbox_embed_description</p>
        <script>new ClipboardJS('#srm-copy-embedcode-link-$toolbox_funnel_id');</script>
        <a id="srm-copy-embedcode-link-$toolbox_funnel_id" href="javascript:;" title="Copy to Clipboard" data-clipboard-text='&lt;iframe frameborder=&quot;0&quot; scrolling=&quot;no&quot; id=&quot;starfish-reviews-funnel-$toolbox_funnel_id&quot; width=&quot;640&quot; height=&quot;475&quot; src=&quot;$toolbox_embed_url&quot;&gt;&lt;/iframe&gt;
&lt;script&gt;
const iframe = document.getElementById(&apos;starfish-reviews-funnel-$toolbox_funnel_id&apos;)
window.addEventListener(&apos;message&apos;, ({ origin, data }) =&gt; {
  if (origin === &apos;$toolbox_embed_url_domain&apos; &amp;&amp; data.height) {
    iframe.height = data.height
  } else {
      console.log(&quot;STARFISH: Dynamic iFrame Failure; invalid Origin &quot; + origin)
  }
});
&lt;/script&gt;'>
            <i class="fa fa-copy"></i>
        </a>
        <pre>
            <code class="html">
&lt;iframe frameborder=&quot;0&quot; scrolling=&quot;no&quot; id=&quot;starfish-reviews-funnel-$toolbox_funnel_id&quot; width=&quot;640&quot; height=&quot;475&quot; src=&quot;$toolbox_embed_url&quot;&gt;&lt;/iframe&gt;
&lt;script&gt;
const iframe = document.getElementById(&apos;starfish-reviews-funnel-$toolbox_funnel_id&apos;)
window.addEventListener(&apos;message&apos;, ({ origin, data }) =&gt; {
  if (origin === &apos;$toolbox_embed_url&apos; &amp;&amp; data.height) {
    iframe.height = data.height
  } else {
      console.log(&quot;STARFISH: Dynamic iFrame Failure; invalid Origin &quot; + origin)
  }
});
&lt;/script&gt;
            </code>
        </pre>
        <div class="srm-sidebar-alert"><i class="fa fa-exclamation"></i> $toolbox_embed_subtext</div>
        <p>
            <div id="srm-funnel-embed-settings">
                <input type="checkbox" name="srm-funnel-embed-dynamic-sizing" id="srm-funnel-embed-dynamic-sizing" value="true" $toolbox_embed_dyn_sizing_checked> Dynamic Sizing?
                <p class="description" id="srm-funnel-embed-dynamic-sizing-description">$toolbox_embed_dyn_sizing_description</p>
                <br/>
                <input type="text" size="30" name="srm-funnel-embed-target-origin" id="srm-funnel-embed-target-origin" value="$toolbox_embed_torigin" placeholder="Target Domain">
                <p class="description" id="srm-funnel-embed-target-origin-description">$toolbox_embed_torigin_description</p>
            </div>
         </p>
    </div>
TOOLS;
}

/**
 * Build Funnel Settings meta box
 *
 * @param post $post The post object
 *
 * @throws Freemius_Exception
 */
function srm_funnel_settings_build_meta_box( $post ) {
	// Premium Only Styling
	$premium_only = SRM_FREEMIUS::starfish_premium_only_styles();

	// make sure the form request comes from WordPress
	wp_nonce_field( basename( __FILE__ ), 'funnel_settings_meta_box_nonce' );
	if ( isset( $_GET['post'] ) && ( $_GET['action'] == 'edit' ) ) {
		$srm_yn_question = esc_html( get_post_meta( $post->ID, '_srm_yn_question', true ) );
		if ( empty( $srm_yn_question ) ) {
			$srm_yn_question = esc_html( get_option( SRM_OPTION_FUNNEL_YN_QUESTION, SRM_SETTINGS::srm_get_options( 'defaults' )[ SRM_OPTION_FUNNEL_YN_QUESTION ]['default'] ) );
		}

		$srm_button_style = esc_html( get_post_meta( $post->ID, '_srm_button_style', true ) );
		if ( empty( $srm_button_style ) ) {
			$srm_button_style = 'thumbs_outline';
		}
	} else {
		$srm_yn_question  = esc_html( get_option( SRM_OPTION_FUNNEL_YN_QUESTION, SRM_SETTINGS::srm_get_options( 'defaults' )[ SRM_OPTION_FUNNEL_YN_QUESTION ]['default'] ) );
		$srm_button_style = 'thumbs_outline';
	} ?>

	<div id="srm-funnel-settings" class='inside'>
		<table class="form-table" aria-describedby="Starfish Reviews Funnel Settings">
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_yn_question"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Yes/No Question', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
				</th>
				<td>
					<input <?php echo $premium_only['readonly']; ?> type="text" class="regular-text"
																	name="srm_yn_question" id="srm_yn_question"
																	value="<?php echo $srm_yn_question; ?>"
																	placeholder="<?php echo esc_html__( 'Would you recommend {WP site name here}?', 'starfish' ); ?>">
					<p class="description"
					   id="srm_yn_question-description"><?php echo __( 'Shortcode <code>{site-name}</code> uses the site name as set in WP Settings -> General.', 'starfish' ); ?></p>
				</td>
			</tr>
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_button_style"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Answer Buttons Style', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
				</th>
				<td>
					<select name="srm_button_style" id="srm_button_style">
						<option value="thumbs_outline" <?php selected( $srm_button_style, 'thumbs_outline' ); ?> ><?php echo esc_html__( 'Thumbs outline', 'starfish' ); ?></option>
						<option <?php echo $premium_only['disabled']; ?>
								value="thumbs_solid" <?php selected( $srm_button_style, 'thumbs_solid' ); ?> ><?php echo esc_html__( 'Thumbs solid', 'starfish' ); ?></option>
						<option <?php echo $premium_only['disabled']; ?>
								value="faces" <?php selected( $srm_button_style, 'faces' ); ?> ><?php echo esc_html__( 'Faces', 'starfish' ); ?></option>
						<option <?php echo $premium_only['disabled']; ?>
								value="scircle" <?php selected( $srm_button_style, 'scircle' ); ?> ><?php echo esc_html__( 'Symbol circles', 'starfish' ); ?></option>
					</select>
					<div class="button_style_preview">
						<?php if ( $srm_button_style == 'thumbs_outline' ) { ?>
							<span class="faicon iconyes far fa-thumbs-up"></span><span
									class="faicon iconno far fa-thumbs-down faicon_flip"></span>
						<?php } ?>
						<?php if ( $srm_button_style == 'thumbs_solid' ) { ?>
							<span class="faicon iconyes fas fa-thumbs-up"></span><span
									class="faicon iconno fas fa-thumbs-down faicon_flip"></span>
						<?php } ?>
						<?php if ( $srm_button_style == 'faces' ) { ?>
							<span class="faicon iconyes far fa-smile"></span><span
									class="faicon iconno far fa-frown"></span>
						<?php } ?>
						<?php if ( $srm_button_style == 'scircle' ) { ?>
							<span class="faicon iconyes fas fa-check-circle"></span><span
									class="faicon iconno fas fa-times-circle"></span>
						<?php } ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

function srm_yes_result_build_meta_box( $post ) {
	// Premium Only Styling
	$premium_only = SRM_FREEMIUS::starfish_premium_only_styles();

	// make sure the form request comes from WordPress
	wp_nonce_field( basename( __FILE__ ), 'funnel_yes_result_meta_box_nonce' );

	if ( isset( $_GET['post'] ) && ( $_GET['action'] == 'edit' ) ) {

		// retrieve funnel's current metadata, otherwise use the defaults
		$srm_yes_review_prompt = esc_html( get_post_meta( $post->ID, '_srm_yes_review_prompt', true ) );
		if ( empty( $srm_yes_review_prompt ) ) {
			$srm_yes_review_prompt = esc_html( get_option( SRM_OPTION_FUNNEL_REVIEW_PROMPT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_REVIEW_PROMPT ]['default'] ) );
		}

		$srm_no_destination = esc_html( get_post_meta( $post->ID, '_srm_no_destination', true ) );
		// ** DEFAULT set by selection auto-selecting "single" ** //

		$srm_destination_layout = esc_html( get_post_meta( $post->ID, '_srm_multiple_destination_layout', true ) );
		// ** DEFAULT set by selection auto-selecting "horizontal" ** //

		$srm_review_destination = esc_html( get_post_meta( $post->ID, '_srm_review_destination', true ) );
		// ** NO DEFAULT, required field on submit ** //

		$srm_review_auto_redirect = esc_html( get_post_meta( $post->ID, '_srm_review_auto_redirect', true ) );
		if ( empty( $srm_review_auto_redirect ) ) {
			$srm_review_auto_redirect = esc_html( get_option( SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS ]['default'] ) );
		}

		$srm_button_text = esc_html( get_post_meta( $post->ID, '_srm_button_text', true ) );
		if ( empty( $srm_button_text ) ) {
			$srm_button_text = esc_html( get_option( SRM_OPTION_FUNNEL_SUBMIT_BUTTON, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_SUBMIT_BUTTON ]['default'] ) );
		}

		$srm_multi_desti     = get_post_meta( $post->ID, '_srm_multi_desti', true );
		$srm_open_new_window = esc_html( get_post_meta( $post->ID, '_srm_open_new_window', true ) );
	} else {
		$srm_yes_review_prompt    = esc_html( get_option( SRM_OPTION_FUNNEL_REVIEW_PROMPT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_REVIEW_PROMPT ]['default'] ) );
		$srm_review_destination   = '';
		$srm_no_destination       = 'single';
		$srm_destination_layout   = 'horizontal';
		$srm_multi_desti          = array();
		$srm_review_auto_redirect = esc_html( get_option( SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_YES_REDIRECT_SECONDS ]['default'] ) );
		$srm_button_text          = esc_html( get_option( SRM_OPTION_FUNNEL_SUBMIT_BUTTON, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_SUBMIT_BUTTON ]['default'] ) );
		$srm_open_new_window      = false;
	}

	if ( $srm_no_destination == 'multiple' ) {
		$multi_desti_row_style  = 'display: table-row;';
		$single_desti_row_style = 'display: none;';
	} else {
		$multi_desti_row_style  = 'display: none;';
		$single_desti_row_style = 'display: table-row;';
	}
	?>
	<div id="srm-funnel-yes-settings" class='inside'>
		<table class="form-table" aria-describedby="Starfish Reviews Funnel Positive Settings">
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_yes_review_prompt"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Review Prompt', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
				</th>
				<td>
					<textarea <?php echo $premium_only['readonly']; ?> name="srm_yes_review_prompt"
																	   id="srm_yes_review_prompt" rows="5"
																	   cols="60"><?php echo $srm_yes_review_prompt; ?></textarea>
					<p class="description"
					   id="srm_yes_review_prompt-description"><?php echo __( 'Accepts HTML', 'starfish' ); ?></p>
				</td>
			</tr>
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_no_destination"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Number of Destinations', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
				</th>
				<td>
					<select name="srm_no_destination" id="srm_no_destination">
						<option value="single" <?php selected( $srm_no_destination, 'single' ); ?> ><?php echo esc_html__( 'Single', 'starfish' ); ?></option>
						<option <?php echo $premium_only['disabled']; ?>
								value="multiple" <?php selected( $srm_no_destination, 'multiple' ); ?> ><?php echo esc_html__( 'Multiple', 'starfish' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="multiple-destination-layout <?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_multiple_destination_layout"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Destination Layout', 'starfish' ); ?>
																			<?php
																			echo $premium_only['premium_only_link'];
																			?>
						</label>
				</th>
				<td>
					<select name="srm_multiple_destination_layout" id="srm_multiple_destination_layout">
						<option <?php echo $premium_only['disabled']; ?>
								value="horizontal" <?php selected( $srm_destination_layout, 'horizontal' ); ?> ><?php echo esc_html__( 'Horizontal', 'starfish' ); ?></option>
						<option value="vertical" <?php selected( $srm_destination_layout, 'vertical' ); ?> ><?php echo esc_html__( 'Vertical', 'starfish' ); ?></option>
					</select>
				</td>
			</tr>
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_open_new_window"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'New Window?', 'starfish' ); ?>
																<?php
																echo $premium_only['premium_only_link'];
																?>
						</label>
				</th>
				<td>
					<input <?php echo $premium_only['readonly']; ?> type="checkbox" name="srm_open_new_window"
																	id="srm_open_new_window"
																	value="true" <?php checked( $srm_open_new_window, 'true' ); ?>>
					<p class="description"
					   id="srm_open_new_window-description">
					   <?php
						echo __(
							'Open Destinations in a new browser window.',
							'starfish'
						);
						?>
						</p>
				</td>
			</tr>
			<tr class="multiple-destination-edit" style="<?php echo $multi_desti_row_style; ?>">
				<td class="nopadding multi_destination" colspan="2">
					<strong><?php echo esc_html__( 'Set Destinations', 'starfish' ); ?></strong>
					<table id="srm-multi-destinations" class="mulit_desti_row_section"
						   aria-describedby="Starfish Reviews Funnel Multiple Destinations">
						<thead>
						<tr>
							<th scope="col"><?php echo esc_html__( 'Icon', 'starfish' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Preview', 'starfish' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Icon Colors', 'starfish' ); ?></th>
							<th scope="col"><?php echo esc_html__( 'Name', 'starfish' ); ?><sup>*</sup></th>
							<th scope="col"><?php echo esc_html__( 'Destination', 'starfish' ); ?><sup>*</sup></th>
							<th scope="col"></th>
						</tr>
						</thead>
						<tbody>
						<?php
						// Sort array by index.
						$template_variables = array(
							'icons'                        => SRM_FUNNELS::srm_get_icons(),
							'upload_label'                 => esc_html__( 'Upload New Icon', 'starfish' ),
							'select_icon_label'            => esc_html__( 'Select Icon', 'starfish' ),
							'icon_color_label'             => esc_html__( 'Color', 'starfish' ),
							'icon_bgcolor_label'           => esc_html__( 'Background', 'starfish' ),
							'destination_name_placeholder' => esc_html__( 'Name', 'starfish' ),
							'destination_url_placeholder'  => esc_html__( 'Review URL', 'starfish' ),
						);
						if ( isset( $srm_multi_desti ) && is_array( $srm_multi_desti ) && ( count( $srm_multi_desti ) > 0 ) ) {
							foreach ( $srm_multi_desti as $key => $multi_desti ) {
								$current_variables['selected']              = esc_html( $multi_desti['desti_icon'] );
								$current_variables['icon_image_id']         = intval( $multi_desti['icon_photo_id'] );
								$current_variables['icon_image_url']        = wp_get_attachment_url( intval( $multi_desti['icon_photo_id'] ) );
								$current_variables['icon_color']            = esc_html( $multi_desti['srm_desti_color'] );
								$current_variables['icon_bg_color']         = esc_html( $multi_desti['srm_desti_bg_color'] );
								$current_variables['destination_name']      = esc_html( $multi_desti['srm_desti_name'] );
								$current_variables['destination_name_hide'] = esc_html( $multi_desti['srm_desti_name_hide'] );
								$current_variables['destination_url']       = esc_html( $multi_desti['srm_desti_url'] );
								// Render a Multi-Destination Row from Template
								$filter = new TwigFilter( 'attachmentUrl', 'SRM_FUNNELS::get_icon_url' );
								$twig   = SRM_TWIG::get_twig();
								$twig->addFilter( $filter );
								echo $twig->render( 'starfish-funnel-admin-destination.html.twig', $template_variables + $current_variables );
							}
						}
						// Render an empty Destination Row Template
						$template_variables['empty_row'] = true;
						$filter                          = new TwigFilter( 'attachmentUrl', 'SRM_FUNNELS::get_icon_url' );
						$twig                            = SRM_TWIG::get_twig();
						$twig->addFilter( $filter );
						echo $twig->render( 'starfish-funnel-admin-destination.html.twig', $template_variables );
						?>
						</tbody>
					</table>
				</td>
			</tr>
			<tr class="single-destination-edit" style="<?php echo $single_desti_row_style; ?>">
				<th scope="row"><label
							for="srm_review_destination"><?php echo __( 'Review Destination', 'starfish' ); ?><span style="color: red; font-size: 18px">*</span></label>
				</th>
				<td><input type="text" class="regular-text" name="srm_review_destination" id="srm_review_destination"
						   value="<?php echo $srm_review_destination; ?>"
						   placeholder="<?php echo esc_html__( 'Review URL', 'starfish' ); ?>">
					<p class="description"
					   id="srm_yn_question-description"><?php echo __( 'Destination URL is required (i.e "http://google.com"). Use for single and default destination.', 'starfish' ); ?></p>
				</td>
			</tr>
			<tr class="srm-review-auto-redirect-edit <?php echo $premium_only['container_class']; ?>"
				style="<?php echo $single_desti_row_style; ?>">
				<th scope="row">
					<label for="srm_review_auto_redirect"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Auto Redirect (seconds)', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
				</th>
				<td>
					<input <?php echo $premium_only['readonly']; ?> type="text" class="regular-text"
																	name="srm_review_auto_redirect"
																	id="srm_review_auto_redirect"
																	value="<?php echo $srm_review_auto_redirect; ?>"
																	placeholder="10">
					<p class="description"
					   id="srm_review_auto_redirect-description"><?php echo esc_html__( 'Auto-forward to destination URL after X number of seconds (0 = disabled; "imm" = immediately).', 'starfish' ); ?></p>
				</td>
			</tr>
			<tr class="srm-button-text-edit" style="<?php echo $single_desti_row_style; ?>">
				<th scope="row">
					<label for="srm_button_text"><?php echo esc_html__( 'Submit Button (Positive)', 'starfish' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" name="srm_button_text" id="srm_button_text"
						   value="<?php echo $srm_button_text; ?>"
						   placeholder="<?php echo esc_html__( 'Submit Review', 'starfish' ); ?>">
				</td>
			</tr>
		</table>
	</div>
	<?php
}

function srm_no_result_build_meta_box( $post ) {
	// Premium Only Styling
	$premium_only = SRM_FREEMIUS::starfish_premium_only_styles();

	// make sure the form request comes from WordPress
	wp_nonce_field( basename( __FILE__ ), 'funnel_no_result_meta_box_nonce' );

	if ( isset( $_GET['post'] ) && ( $_GET['action'] == 'edit' ) ) {

		// retrieve funnel's current metadata, otherwise use the defaults
		$srm_no_review_prompt = esc_html( get_post_meta( $post->ID, '_srm_no_review_prompt', true ) );
		if ( empty( $srm_no_review_prompt ) ) {
			$srm_no_review_prompt = esc_html( get_option( SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT ]['default'] ) );
		}

		$srm_email_feedback = esc_html( get_post_meta( $post->ID, '_srm_email_feedback', true ) );
		if ( empty( $srm_email_feedback ) ) {
			$srm_email_feedback = esc_html( get_option( SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT, SRM_SETTINGS::srm_get_options( 'funnel_email' )[ SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT ]['default'] ) );
		}

		$srm_no_thank_you_msg = esc_html( get_post_meta( $post->ID, '_srm_no_thank_you_msg', true ) );
		if ( empty( $srm_no_thank_you_msg ) ) {
			$srm_no_thank_you_msg = esc_html( get_option( SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU ]['default'] ) );
		}

		$srm_ask_name = esc_html( get_post_meta( $post->ID, '_srm_ask_name', true ) );

		$srm_ask_name_required = esc_html( get_post_meta( $post->ID, '_srm_ask_name_required', true ) );

		$srm_ask_email = esc_html( get_post_meta( $post->ID, '_srm_ask_email', true ) );

		$srm_ask_email_required = esc_html( get_post_meta( $post->ID, '_srm_ask_email_required', true ) );

		$srm_ask_phone = esc_html( get_post_meta( $post->ID, '_srm_ask_phone', true ) );

		$srm_ask_phone_required = esc_html( get_post_meta( $post->ID, '_srm_ask_phone_required', true ) );

		$srm_button_text_no = esc_html( get_post_meta( $post->ID, '_srm_button_text_no', true ) );
		if ( empty( $srm_button_text_no ) ) {
			$srm_button_text_no = esc_html( get_option( SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO ]['default'] ) );
		}

		$disable_review_gating = esc_html( get_post_meta( $post->ID, '_srm_disable_review_gating', true ) );

		$disable_review_gating_skip_feedback = esc_html( get_post_meta( $post->ID, '_srm_skip_feedback_text', true ) );
		if ( empty( $disable_review_gating_skip_feedback ) ) {
			$disable_review_gating_skip_feedback = esc_html( get_option( SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT ]['default'] ) );
		}

		$srm_public_review_text = esc_html( get_post_meta( $post->ID, '_srm_public_review_text', true ) );
		if ( empty( $srm_public_review_text ) ) {
			$srm_public_review_text = esc_html( get_option( SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT ]['default'] ) );
		}
	} else {
		$srm_no_review_prompt                = esc_html( get_option( SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_NO_FEEDBACK_PROMPT ]['default'] ) );
		$srm_email_feedback                  = esc_html( get_option( SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT, SRM_SETTINGS::srm_get_options( 'funnel_email' )[ SRM_OPTION_FUNNEL_EMAIL_NOTIFICATION_RECIPIENT ]['default'] ) );
		$srm_no_thank_you_msg                = esc_html( get_option( SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_MSG_NO_THANK_YOU ]['default'] ) );
		$srm_button_text_no                  = esc_html( get_option( SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_SUBMIT_BUTTON_NO ]['default'] ) );
		$srm_ask_name                        = '';
		$srm_ask_name_required               = '';
		$srm_ask_email                       = '';
		$srm_ask_email_required              = '';
		$srm_ask_phone                       = '';
		$srm_ask_phone_required              = '';
		$disable_review_gating               = 'yes'; // Disable by default.
		$disable_review_gating_skip_feedback = esc_html( get_option( SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_SKIP_FEEDBACK_TEXT ]['default'] ) );

		$srm_public_review_text = esc_html( get_option( SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT, SRM_SETTINGS::srm_get_options( 'funnel_general' )[ SRM_OPTION_FUNNEL_PUBLIC_REVIEW_TEXT ]['default'] ) );
	}
	?>
	<div id="srm-funnel-no-settings" class='inside'>
		<table class="form-table" aria-describedby="Starfish Reviews Funnel Negative Settings">
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row"><label
							for="srm_no_review_prompt"><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Review Prompt', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?> </label>
				</th>
				<td><textarea <?php echo $premium_only['readonly']; ?> name="srm_no_review_prompt"
																	   id="srm_no_review_prompt" rows="5" cols="60"
																	   placeholder="<?php echo esc_html__( 'We\'re sorry we didn\'t meet expectations. How can we do better in the future?', 'starfish' ); ?>"><?php echo $srm_no_review_prompt; ?></textarea>
				</td>
			</tr>

			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label><?php echo $premium_only['premium_only_badge']; ?><?php esc_html_e( 'Additional Fields', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
				</th>
				<td>
					<div class="srm-admin-funnel-field-group">
						<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox" type="checkbox"
																		name="srm_ask_name" id="srm_ask_name"
																		value="yes" <?php checked( $srm_ask_name, 'yes' ); ?> /><label
								class="ask_checkbox_label"
								for="srm_ask_name"><?php esc_html_e( 'Name', 'starfish' ); ?></label>
						<div class="srm-admin-funnel-field-group-secondary">
							<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox srm-ask-required"
																			type="checkbox" name="srm_ask_name_required"
																			id="srm_ask_name_required"
																			value="yes" <?php checked( $srm_ask_name_required, 'yes' ); ?> /><label
									class="ask_checkbox_label srm-ask-required"
									for="srm_ask_name_required"><?php esc_html_e( 'Required', 'starfish' ); ?></label>
						</div>
					</div>
					<div class="srm-admin-funnel-field-group">
						<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox" type="checkbox"
																		name="srm_ask_email" id="srm_ask_email"
																		value="yes" <?php checked( $srm_ask_email, 'yes' ); ?> /><label
								class="ask_checkbox_label"
								for="srm_ask_email"><?php esc_html_e( 'Email', 'starfish' ); ?></label>
						<div class="srm-admin-funnel-field-group-secondary">
							<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox srm-ask-required"
																			type="checkbox"
																			name="srm_ask_email_required"
																			id="srm_ask_email_required"
																			value="yes" <?php checked( $srm_ask_email_required, 'yes' ); ?> /><label
									class="ask_checkbox_label srm-ask-required"
									for="srm_ask_email_required"><?php esc_html_e( 'Required', 'starfish' ); ?></label>
						</div>
					</div>
					<div class="srm-admin-funnel-field-group">
						<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox" type="checkbox"
																		name="srm_ask_phone" id="srm_ask_phone"
																		value="yes" <?php checked( $srm_ask_phone, 'yes' ); ?> /><label
								class="ask_checkbox_label"
								for="srm_ask_phone"><?php echo esc_html__( 'Phone', 'starfish' ); ?></label>
						<div class="srm-admin-funnel-field-group-secondary">
							<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox srm-ask-required"
																			type="checkbox"
																			name="srm_ask_phone_required"
																			id="srm_ask_phone_required"
																			value="yes" <?php checked( $srm_ask_phone_required, 'yes' ); ?> /><label
									class="ask_checkbox_label srm-ask-required"
									for="srm_ask_phone_required"><?php esc_html_e( 'Required', 'starfish' ); ?></label>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row"><label
							for="srm_email_feedback"><?php esc_html_e( 'Email Feedback', 'starfish' ); ?></label></th>
				<td><input type="text" class="regular-text" name="srm_email_feedback" id="srm_email_feedback"
						   value="<?php echo $srm_email_feedback; ?>"
						   placeholder="<?php echo esc_html__( 'email address(es)', 'starfish' ); ?>">
					<p class="description"
					   id="srm_email_feedback-description"><?php echo __( 'email address(es) to send negative feedback to. You can add comma separated multiple email addresses. Shortcode <code>{admin-email}</code> defaults to the email set in WP Settings -> Funnel -> Email.', 'starfish' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label
							for="srm_button_text_no"><?php esc_html_e( 'Submit Button (Negative)', 'starfish' ); ?></label>
				</th>
				<td><input type="text" class="regular-text" name="srm_button_text_no" id="srm_button_text_no"
						   value="<?php echo $srm_button_text_no; ?>"
						   placeholder="<?php echo esc_html__( 'Send Feedback', 'starfish' ); ?>">
				</td>
			</tr>
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row"><label
							for="srm_no_thank_you_msg"><?php echo $premium_only['premium_only_badge']; ?><?php esc_html_e( 'Thank You Message', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?> </label>
				</th>
				<td><textarea <?php echo $premium_only['readonly']; ?> name="srm_no_thank_you_msg"
																	   id="srm_no_thank_you_msg" rows="5" cols="60"
																	   placeholder="<?php esc_html_e( 'Thank you!', 'starfish' ); ?>"><?php echo $srm_no_thank_you_msg; ?></textarea>
				</td>
			</tr>
			<tr class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label><?php echo $premium_only['premium_only_badge']; ?><?php echo esc_html__( 'Disable Review Gating', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?></label>
					<a class="srm-help-inline" href="javascript:void(0)" onclick="Beacon('article', '6261f22ba535c33d541a1126', { type: 'modal' })" title="Starfish Help"><i class="fa fa-question-circle srm-help-icon"></i></a>
				</th>
				<td>
					<input <?php echo $premium_only['disabled']; ?> class="ask_checkbox" type="checkbox"
																	name="disable_review_gating"
																	id="disable_review_gating"
																	value="yes" <?php checked( $disable_review_gating, 'yes' ); ?> />
					<label class="ask_checkbox_label" for="disable_review_gating"><?php esc_html_e( 'Allow negative responses to proceed to review destination.', 'starfish' ); ?></label>
					<p class="description" id="disable_review_gating-description">
						<div class="srm-inline-notice warning">
							<?php esc_html_e( 'Review gating is usually in violation of review site\'s terms and conditions (e.g. Google, TrustPilot, Yelp, etc.) in order to comply with their policy of not selectively asking for reviews only from people with positive feedback be sure and leave Review Gating disabled.', 'starfish' ); ?>
						</div>
					</p>
				</td>
			</tr>
			<tr id="srm_skip_feedback_text_field" class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label for="srm_skip_feedback_text"><?php echo $premium_only['premium_only_badge']; ?><?php esc_html_e( 'Skip Feedback Text', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?> </label>
				</th>
				<td>
					<input <?php echo $premium_only['readonly']; ?> type="text" class="regular-text"
																	name="srm_skip_feedback_text"
																	id="srm_skip_feedback_text"
																	value="<?php echo $disable_review_gating_skip_feedback; ?>">
					<p class="description" id="srm_skip_feedback_text-description">
						<?php
						esc_html_e(
							'Link text to skip the Feedback form
                        and leave a public review',
							'starfish'
						);
						?>
					</p>
				</td>
			</tr>
			<tr id="srm_public_review_text_field" class="<?php echo $premium_only['container_class']; ?>">
				<th scope="row">
					<label
						for="srm_public_review_text"><?php echo $premium_only['premium_only_badge']; ?><?php esc_html_e( 'Public Review Text', 'starfish' ); ?><?php echo $premium_only['premium_only_link']; ?> </label>
				</th>
				<td>
					<input <?php echo $premium_only['readonly']; ?> type="text" class="regular-text"
																	name="srm_public_review_text"
																	id="srm_public_review_text"
																	value="<?php echo $srm_public_review_text; ?>">
					<p class="description"
					   id="srm_public_review_text-description"><?php esc_html_e( 'Header text presented above the destination option(s) if feedback skipped', 'starfish' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<?php
	include SRM_DIALOGUE_HELP_FUNNEL;
}

/**
 * Store custom field meta box data for Toolbox
 *
 * @param int $post_id The post ID.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
 */
function srm_funnel_toolbox_save_meta_box_data( $post_id ) {
	// verify meta box nonce
	if ( ! isset( $_POST['funnel_toolbox_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['funnel_toolbox_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( array_key_exists( 'srm-funnel-embed-target-origin', $_POST ) ) {
		update_post_meta( $post_id, '_srm_funnel_embed_target_origin', sanitize_url( $_POST['srm-funnel-embed-target-origin'] ) );
	}

    update_post_meta( $post_id, '_srm_funnel_embed_dynamic_sizing', sanitize_text_field( wp_unslash( ( isset( $_POST['srm-funnel-embed-dynamic-sizing'] ) ) ? $_POST['srm-funnel-embed-dynamic-sizing'] : false ) ) );

}

/**
 * Store custom field meta box data
 *
 * @param int $post_id The post ID.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
 */
function srm_funnel_settings_save_meta_box_data( $post_id ) {
	// verify meta box nonce
	if ( ! isset( $_POST['funnel_settings_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['funnel_settings_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( array_key_exists( 'srm_yn_question', $_POST ) ) {
		update_post_meta( $post_id, '_srm_yn_question', sanitize_text_field( $_POST['srm_yn_question'] ) );
	}
	if ( array_key_exists( 'srm_button_style', $_POST ) ) {
		update_post_meta( $post_id, '_srm_button_style', sanitize_text_field( $_POST['srm_button_style'] ) );
	}
}

function srm_funnel_yes_result_save_meta_box_data( $post_id ) {
	// verify meta box nonce
	if ( ! isset( $_POST['funnel_yes_result_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['funnel_yes_result_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( array_key_exists( 'srm_yes_review_prompt', $_POST ) ) {
		update_post_meta( $post_id, '_srm_yes_review_prompt', wp_kses_post( $_POST['srm_yes_review_prompt'] ) );
	}

	if ( array_key_exists( 'srm_review_destination', $_POST ) ) {
		update_post_meta( $post_id, '_srm_review_destination', sanitize_url( $_POST['srm_review_destination'] ) );
	}

	if ( array_key_exists( 'srm_review_auto_redirect', $_POST ) ) {
		update_post_meta( $post_id, '_srm_review_auto_redirect', sanitize_text_field( $_POST['srm_review_auto_redirect'] ) );
	}

	if ( array_key_exists( 'srm_button_text', $_POST ) ) {
		update_post_meta( $post_id, '_srm_button_text', sanitize_text_field( $_POST['srm_button_text'] ) );
	}

	if ( array_key_exists( 'srm_no_destination', $_POST ) ) {
		update_post_meta( $post_id, '_srm_no_destination', sanitize_text_field( $_POST['srm_no_destination'] ) );
	}

	if ( array_key_exists( 'srm_multiple_destination_layout', $_POST ) ) {
		update_post_meta( $post_id, '_srm_multiple_destination_layout', sanitize_text_field( $_POST['srm_multiple_destination_layout'] ) );
	}

	update_post_meta( $post_id, '_srm_open_new_window', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_open_new_window'] ) ) ? $_POST['srm_open_new_window'] : false ) ) );

	if ( array_key_exists( 'desti_icon', $_POST ) && SRM_FREEMIUS::starfish_fs()->can_use_premium_code() ) {
		$desti_icon_arr          = ( $_POST['desti_icon'] ) ?? null;
		$icon_photo_id_arr       = ( $_POST['icon_photo_id'] ) ?? null;
		$srm_desti_color_arr     = ( $_POST['srm_desti_color'] ) ?? null;
		$srm_desti_bg_color_arr  = ( $_POST['srm_desti_bg_color'] ) ?? null;
		$srm_desti_name_arr      = ( $_POST['srm_desti_name'] ) ?? null;
		$srm_desti_name_hide_arr = ( $_POST['srm_desti_name_hide'] ) ?? null;
		$srm_desti_url_arr       = ( $_POST['srm_desti_url'] ) ?? null;
		$srm_desti_index_arr     = ( $_POST['srm_desti_index'] ) ?? null;
		$multi_desti_icon_set    = array();
		if ( count( $desti_icon_arr ) > 0 ) {
			foreach ( $desti_icon_arr as $key_icon => $value_icon ) {
				if ( ( isset( $srm_desti_name_arr[ $key_icon ] ) && ( $srm_desti_name_arr[ $key_icon ] != '' ) ) && ( isset( $srm_desti_url_arr[ $key_icon ] ) && ( $srm_desti_url_arr[ $key_icon ] != '' ) ) ) {
					// Clear any associated custom icon uploaded if a predefined icon is selected
					if ( ! empty( $value_icon ) ) {
						$icon_photo_id = 0;
					} else {
						$icon_photo_id = isset( $icon_photo_id_arr[ $key_icon ] ) ? intval( $icon_photo_id_arr[ $key_icon ] ) : null;
					}
					$multi_desti_icon_set[] = array(
						'desti_icon'          => $value_icon,
						'icon_photo_id'       => $icon_photo_id,
						'srm_desti_color'     => ( esc_html( $srm_desti_color_arr[ $key_icon ] ) ) ?? null,
						'srm_desti_bg_color'  => ( esc_html( $srm_desti_bg_color_arr[ $key_icon ] ) ) ?? null,
						'srm_desti_name'      => ( trim( esc_html( $srm_desti_name_arr[ $key_icon ] ) ) ) ?? null,
						'srm_desti_name_hide' => ( trim( esc_html( $srm_desti_name_hide_arr[ $key_icon ] ) ) ) ?? null,
						'srm_desti_url'       => ( trim( sanitize_url( $srm_desti_url_arr[ $key_icon ] ) ) ) ?? null,
						'srm_desti_index'     => ( esc_html( $srm_desti_index_arr[ $key_icon ] ) ) ?? null,
					);
				}
			}
		}

		if ( ! empty( $multi_desti_icon_set ) ) {
			update_post_meta( $post_id, '_srm_multi_desti', $multi_desti_icon_set );
			$multi_desti_required_value = true;
		} else {
			$multi_desti_required_value = false;
		}
	}

	// Destination URL & Name is required, cannot be empty
	if ( ( array_key_exists( 'srm_no_destination', $_POST ) && ( $_POST['srm_no_destination'] == 'single' ) ) && ( ! isset( $_REQUEST['srm_review_destination'] ) || ( $_REQUEST['srm_review_destination'] == '' ) ) ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
		add_filter( 'redirect_post_location', 'srm_funnel_destination_required_query_var', 99 );
	}

	if ( ( array_key_exists( 'srm_no_destination', $_POST ) && ( $_POST['srm_no_destination'] == 'multiple' ) ) && $multi_desti_required_value ) {
		foreach ( $multi_desti_icon_set as $destination ) {
			if ( $destination['srm_desti_name'] == '' || $destination['srm_desti_url'] == '' ) {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
				add_filter( 'redirect_post_location', 'srm_funnel_destination_required_query_var', 99 );
				break;
			}
		}
	}

	// Destination URL must be a valid URL
	if ( ( array_key_exists( 'srm_no_destination', $_POST ) && ( $_POST['srm_no_destination'] == 'single' ) ) && ( isset( $_REQUEST['srm_review_destination'] ) && ! filter_var( $_REQUEST['srm_review_destination'], FILTER_VALIDATE_URL ) ) ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
		add_filter( 'redirect_post_location', 'srm_funnel_destination_validurl_query_var', 99 );
	}

	if ( ( array_key_exists( 'srm_no_destination', $_POST ) && ( $_POST['srm_no_destination'] == 'multiple' ) ) && $multi_desti_required_value ) {
		foreach ( $multi_desti_icon_set as $destination ) {
			if ( isset( $destination['srm_desti_url'] ) && ! filter_var( $destination['srm_desti_url'], FILTER_VALIDATE_URL ) ) {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
				add_filter( 'redirect_post_location', 'srm_funnel_destination_validurl_query_var', 99 );
				break;
			}
		}
	}
}

function srm_funnel_inactive_restriction_notice_query_var( $location ) {
	return add_query_arg( array( 'inactive_api' => 'yes' ), $location );
}

function srm_funnel_destination_required_query_var( $location ) {
	return add_query_arg( array( 'funnel_destination_required' => 'yes' ), $location );
}

function srm_funnel_destination_validurl_query_var( $location ) {
	return add_query_arg( array( 'funnel_destination_valid' => 'no' ), $location );
}

function funnel_validation_admin_notice() {
	if ( isset( $_GET['funnel_destination_required'] ) && ( $_GET['funnel_destination_required'] == 'yes' ) ) {
		?>
		<div class="error">
			<p><?php _e( 'Review Destination Name and URL are required', 'starfish' ); ?></p>
		</div>
		<?php
	}
	if ( isset( $_GET['funnel_destination_valid'] ) && ( $_GET['funnel_destination_valid'] == 'no' ) ) {
		?>
		<div class="error">
			<p><?php _e( 'Review Destination URL must be a valid URL (i.e. http://google.com or fb://page/?id=1728724967421282)', 'starfish' ); ?></p>
		</div>
		<?php
	}
}

function srm_no_result_save_meta_box_data( $post_id ) {
	// verify meta box nonce
	if ( ! isset( $_POST['funnel_no_result_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['funnel_no_result_meta_box_nonce'] ) ), basename( __FILE__ ) ) ) {
		return;
	}
	// return if autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Check the user's permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// store custom fields values
	if ( array_key_exists( 'srm_no_review_prompt', $_POST ) ) {
		update_post_meta( $post_id, '_srm_no_review_prompt', wp_kses_post( wp_unslash( ( isset( $_POST['srm_no_review_prompt'] ) ) ? $_POST['srm_no_review_prompt'] : '' ) ) );
	}
	// store custom fields values
	if ( array_key_exists( 'srm_email_feedback', $_POST ) ) {
		update_post_meta( $post_id, '_srm_email_feedback', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_email_feedback'] ) ) ? $_POST['srm_email_feedback'] : '' ) ) );
	}
	// store custom fields values
	if ( array_key_exists( 'srm_no_thank_you_msg', $_POST ) ) {
		update_post_meta( $post_id, '_srm_no_thank_you_msg', wp_kses_post( wp_unslash( ( isset( $_POST['srm_no_thank_you_msg'] ) ) ? $_POST['srm_no_thank_you_msg'] : '' ) ) );
	}

	if ( array_key_exists( 'srm_button_text_no', $_POST ) ) {
		update_post_meta( $post_id, '_srm_button_text_no', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_button_text_no'] ) ) ? $_POST['srm_button_text_no'] : '' ) ) );
	}

	update_post_meta( $post_id, '_srm_ask_name', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_ask_name'] ) ) ? $_POST['srm_ask_name'] : 'false' ) ) );

	update_post_meta( $post_id, '_srm_ask_name_required', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_ask_name_required'] ) ) ? $_POST['srm_ask_name_required'] : 'false' ) ) );

	update_post_meta( $post_id, '_srm_ask_email', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_ask_email'] ) ) ? $_POST['srm_ask_email'] : 'false' ) ) );

	update_post_meta( $post_id, '_srm_ask_email_required', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_ask_email_required'] ) ) ? $_POST['srm_ask_email_required'] : 'false' ) ) );

	update_post_meta( $post_id, '_srm_ask_phone', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_ask_phone'] ) ) ? $_POST['srm_ask_phone'] : 'false' ) ) );

	update_post_meta( $post_id, '_srm_ask_phone_required', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_ask_phone_required'] ) ) ? $_POST['srm_ask_phone_required'] : 'false' ) ) );

	update_post_meta( $post_id, '_srm_disable_review_gating', sanitize_text_field( wp_unslash( ( isset( $_POST['disable_review_gating'] ) ) ? $_POST['disable_review_gating'] : 'false' ) ) );

	if ( array_key_exists( 'srm_public_review_text', $_POST ) ) {
		update_post_meta( $post_id, '_srm_public_review_text', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_public_review_text'] ) ) ? $_POST['srm_public_review_text'] : '' ) ) );
	}

	if ( array_key_exists( 'srm_skip_feedback_text', $_POST ) ) {
		update_post_meta( $post_id, '_srm_skip_feedback_text', sanitize_text_field( wp_unslash( ( isset( $_POST['srm_skip_feedback_text'] ) ) ? $_POST['srm_skip_feedback_text'] : '' ) ) );
	}
}


function srm_save_all_funnels_to_options() {
	$result_arr       = array();
	$srm_funnel_args  = array(
		'post_type'      => 'funnel',
		'posts_per_page' => '-1',
	);
	$srm_funnel_query = new WP_Query( $srm_funnel_args );
	$total_funnel     = 0;
	if ( $srm_funnel_query->have_posts() ) {
		while ( $srm_funnel_query->have_posts() ) {
			$srm_funnel_query->the_post();
			$funnel_id                = get_the_ID();
			$funnel_name              = get_the_title();
			$result_arr[ $funnel_id ] = $funnel_name;
			$total_funnel            += 1;
		}
		wp_reset_postdata();
	}
	update_option( SRM_OPTION_ALL_FUNNELS, $result_arr );
}
