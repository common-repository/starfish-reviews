<?php

use Starfish\Feedback as SRM_FEEDBACK;
use Starfish\Freemius as SRM_FREEMIUS;
use Starfish\Twig as SRM_TWIG;

add_action('init', 'srm_register_feedback_cpt');

/**
 * Register a Feedback post type.
 *
 * @throws Freemius_Exception
 */
function srm_register_feedback_cpt()
{
    $labels = array(
        'name'               => esc_html_x('Feedback', 'post type general name', 'starfish'),
        'singular_name'      => esc_html_x('Feedback', 'post type singular name', 'starfish'),
        'menu_name'          => esc_html_x('Feedback', 'admin menu', 'starfish'),
        'add_new'            => esc_html_x('Add New', 'Review', 'starfish'),
        'add_new_item'       => esc_html__('Add New Feedback', 'starfish'),
        'new_item'           => esc_html__('New Feedback', 'starfish'),
        'edit_item'          => esc_html__('Edit Feedback', 'starfish'),
        'view_item'          => esc_html__('View Feedback', 'starfish'),
        'all_items'          => esc_html__('Feedback', 'starfish'),
        'search_items'       => esc_html__('Search Feedback', 'starfish'),
        'parent_item_colon'  => esc_html__('Parent Feedback:', 'starfish'),
        'not_found'          => esc_html__('No Feedback found.', 'starfish'),
        'not_found_in_trash' => esc_html__('No Feedback found in Trash.', 'starfish'),
    );

    $args = array(
        'labels'             => $labels,
        'description'        => esc_html__('Description.', 'starfish'),
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => 'starfish-parent',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'starfish-feedback' ),
        'capability_type'    => 'post',
        'capabilities'       => array(
            'create_posts' => false,
            'delete_posts' => true,
        ),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 2,
        'supports'           => array( 'title', 'editor' ),
        'show_in_rest'       => true,
    );

    // Set Restrictions Based on Current License/Plan.
    if (SRM_FEEDBACK::srm_restrict_control(SRM_PLAN_TITLE)) {
        $args['capabilities']['delete_posts'] = false;
        $args['map_meta_cap']                 = false;
        $url                                  = $_SERVER['REQUEST_URI'];
        if (strpos($url, 'post_type=starfish_feedback') !== false && strpos($url, 'page=') === false) {
            print SRM_FEEDBACK::srm_admin_plan_restriction_notice(SRM_PLAN_TITLE);
        }
    }

    register_post_type('starfish_feedback', $args);
}

/**
 * Reorder Feedback admin column
 *
 * @param array $post_columns The existing post's columns.
 *
 * @return array
 */
function srm_manage_starfish_feedback_posts_columns($post_columns)
{
    return array(
        'cb'          => $post_columns['cb'],
        'title'       => esc_html__('Date & Time', 'starfish'),
        'track_id'    => esc_html__('ID', 'starfish'),
        'reviewer'    => esc_html__('Reviewer', 'starfish'),
        'feedback'    => esc_html__('Feedback', 'starfish'),
        'message'     => esc_html__('Message', 'starfish'),
        'funnel'      => esc_html__('Funnel', 'starfish'),
        'destination' => esc_html__('Destination', 'starfish'),
    );
}

add_action('manage_starfish_feedback_posts_columns', 'srm_manage_starfish_feedback_posts_columns');

/**
 * Add order column to admin listing screen for feedback
 *
 * @param array $feedback_columns The existing post columns.
 *
 * @return mixed
 */
function srm_add_new_starfish_feedback_column($feedback_columns)
{
    $feedback_columns['feedback']    = esc_html__('Feedback', 'starfish');
	$feedback_columns['track_id']    = esc_html__('ID', 'starfish');
	$feedback_columns['reviewer']    = esc_html__('Reviewer', 'starfish');
    $feedback_columns['message']     = esc_html__('Message', 'starfish');
    $feedback_columns['funnel']      = esc_html__('Funnel', 'starfish');
    $feedback_columns['destination'] = esc_html__('Destination', 'starfish');

    return $feedback_columns;
}

add_action('manage_edit-starfish_feedback_columns', 'srm_add_new_starfish_feedback_column');

/**
 * Customize column order
 *
 * @param string $name Column name.
 *
 * @throws Freemius_Exception The Freemius exception.
 */
function srm_starfish_feedback_show_order_column($name)
{
    global $post;
    $premium_feature_link = '<a href="' . SRM_PLANS_URL . '" title="' . __('Premium Feature, Upgrade Now!', 'starfish') . '"><span class="premium-only-badge fas fa-star"></span></a>';
    switch ($name) {
        case 'track_id':
            $tracking_id = get_post_meta($post->ID, SRM_META_TRACKING_ID, true);
            if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                echo esc_html($tracking_id);
            } else {
                echo $premium_feature_link;
            }
            break;
        case 'reviewer':
            if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
				$feedback = get_post_meta($post->ID, SRM_META_REVIEWER_FEEDBACK, true);
                if("false" === $feedback || "no" === strtolower($feedback)) {
					echo '<i class="fa fa-user" aria-hidden="true" title="Name"></i> ' . esc_html(get_post_meta($post->ID, SRM_META_REVIEWER_NAME, true));
					echo '<br/><i class="fa fa-envelope" aria-hidden="true" title="Email"></i> ' . esc_html(get_post_meta($post->ID, SRM_META_REVIEWER_EMAIL, true));
					echo '<br/><i class="fa fa-phone" aria-hidden="true" title="Phone"></i> ' . esc_html(get_post_meta($post->ID, SRM_META_REVIEWER_PHONE, true));
				}
            } else {
                echo $premium_feature_link;
            }
            break;
        case 'feedback':
            $feedback = get_post_meta($post->ID, SRM_META_REVIEWER_FEEDBACK, true);
            if ("true" === $feedback || "yes" === strtolower($feedback)) {
                echo '<i class="faicon iconyes far fa-thumbs-up" aria-hidden="true" title="' . esc_html__('Positive', 'starfish') . '"></i>';
            } else {
                echo '<i class="faicon iconno faicon_flip far fa-thumbs-up" aria-hidden="true" title="' . esc_html__('Negative', 'starfish') . '"></i>';
            }
            break;
        case 'message':
            $content = wp_trim_words(get_the_content($post->ID), 10, '...');
            if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                echo esc_html($content);
            } else {
                echo $premium_feature_link;
            }
            break;
        case 'funnel':
            $srm_funnel_id    = get_post_meta($post->ID, SRM_META_FUNNEL_ID, true);
            $srm_funnel_title = get_the_title($srm_funnel_id);
            if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                echo esc_html($srm_funnel_title);
            } else {
                echo $premium_feature_link;
            }
            break;
        case 'destination':
            if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
                echo esc_html(get_post_meta($post->ID, SRM_META_DESTI_NAME, true));
            } else {
                echo $premium_feature_link;
            }
            break;
        default:
            break;
    }
}

add_action('manage_starfish_feedback_posts_custom_column', 'srm_starfish_feedback_show_order_column');

/**
 * Make columns sortable
 *
 * @param array $columns Post's columns.
 *
 * @return mixed
 * @throws Freemius_Exception The Freemius exception.
 */
function srm_starfish_feedback_column_register_sortable($columns)
{
    if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
        $columns['track_id']    = 'tracking_id';
        $columns['feedback']    = 'feedback';
        $columns['funnel']      = 'funnel';
        $columns['destination'] = 'destination';
    }

    return $columns;
}

add_filter('manage_edit-starfish_feedback_sortable_columns', 'srm_starfish_feedback_column_register_sortable');
add_action('pre_get_posts', 'starfish_feedback_custom_orderby');

function starfish_feedback_custom_orderby($query)
{
    if (! is_admin()) {
        return;
    }
    $orderby = $query->get('orderby');

    if ('feedback' === $orderby) {
        $query->set('meta_key', SRM_META_REVIEWER_FEEDBACK);
        $query->set('orderby', 'meta_value');
    }
    if ('tracking_id' === $orderby) {
        $query->set('meta_key', SRM_META_TRACKING_ID);
        $query->set('orderby', 'meta_value');
    }
}

/**
 * Add meta_keys to CPT search field
 *
 * @param array $query Search Query.

 */
function starfish_feedback_custom_search_query($query)
{

    // use your post type
    $post_type = 'starfish_feedback';

    if (!is_admin()) {
        return;
    }
    if (!isset($query->query['post_type']) || (isset($query->query['post_type']) && $query->query['post_type'] != $post_type)) {
        return;
    }

    // Use your Custom fields/column name to search for
    $custom_fields = array(
        // put all the meta fields you want to search for here
        SRM_META_REVIEWER_FEEDBACK,
        SRM_META_TRACKING_ID,
        SRM_META_DESTI_NAME,
        SRM_META_REVIEWER_NAME,
        SRM_META_REVIEWER_EMAIL,
        SRM_META_REVIEWER_PHONE,
    );
    $search_term = $query->query_vars['s'];

    // we have to remove the "s" parameter from the query, because it will prevent the posts from being found
    $query->query_vars['s'] = '';

    if ($search_term != '') {
        if (strtolower($search_term) === 'positive') {
            $search_term = 'true';
        } elseif (strtolower($search_term) === 'negative') {
            $search_term = 'false';
        }
        $meta_query = array('relation' => 'OR');
        foreach ($custom_fields as $custom_field) {
            array_push($meta_query, array(
                'key'     => $custom_field,
                'value'   => $search_term,
                'compare' => 'LIKE'
            ));
        }
        $query->set("meta_query", $meta_query);

        // To allow the search to also return "OR" results on the post title
        $query->set('_meta_or_title', $search_term);
        // To allow the search to also return "OR" results on the post content
        $query->set('_post_body', $search_term);
    }
}
add_filter("pre_get_posts", "starfish_feedback_custom_search_query");

/**
 * Add filter by funnel to Admin
 *
 * @param $post_type
 * @param $which
 *
 * @throws Freemius_Exception The Freemius exception.
 */
function srm_add_filter_by_funnel_feedback_admin($post_type)
{
    global $typenow;
    if (SRM_FREEMIUS::starfish_fs()->can_use_premium_code() && 'starfish_feedback' === $typenow) {
        $current_funnel = '';
        $funnel_arr     = get_option('srm_all_funnels');
        if ($post_type === 'starfish_feedback' && isset($_GET['funnel_id'])) {
            $current_funnel = esc_html($_GET['funnel_id']);
        } ?>
        <select name="funnel_id" id="funnel_id">
            <option value="all" <?php selected('all', $current_funnel); ?>><?php _e('All Funnels', 'starfish'); ?></option>
            <?php
            if (is_array($funnel_arr) && (count($funnel_arr) > 0)) {
                foreach ($funnel_arr as $funnel_id => $funnel_name) {
                    ?>
                    <option
                    value="<?php echo esc_attr($funnel_id); ?>" <?php selected($funnel_id, $current_funnel); ?>><?php echo esc_attr($funnel_name); ?></option><?php
                }
            } ?>
        </select>
        <?php
    }
}

add_action('restrict_manage_posts', 'srm_add_filter_by_funnel_feedback_admin');

/**
 * @param $which
 *
 * @throws Freemius_Exception
 * @throws \Twig\Error\LoaderError
 * @throws \Twig\Error\RuntimeError
 * @throws \Twig\Error\SyntaxError
 */
function srm_feedback_export($which)
{
    global $typenow;
    global $current_screen;
    if ('starfish_feedback' != $current_screen->post_type) {
        return;
    }
    if ((SRM_FREEMIUS::starfish_fs()->can_use_premium_code() && (! isset($_GET['page'])) && ('starfish_feedback' === $typenow))) {
        $total_feedback = SRM_FEEDBACK::srm_get_total_feedback();
        $template_variables = array(
            'count' => $total_feedback,
            'files' => SRM_FEEDBACK::srm_admin_get_feedback_exports()
        );
        $twig    = SRM_TWIG::get_twig();
        $actions = $twig->render('starfish-admin-feedback-actions.html.twig', $template_variables);
        if ($total_feedback > 0) { ?>
    <script type="text/javascript">
        jQuery(document).ready( function($)
        {
            $($(".alignleft.actions")[1]).append('<?php echo str_replace(array("\n", "\t", "\r"), '', $actions); ?>');
        });
    </script>
        <?php }
    }
}

add_action('admin_head-edit.php', 'srm_feedback_export');
