<?php

/**
 * Filter the Feedback results according to selected Funnel
 *
 * @param $query
 *
 * @return mixed
 */
function srm_parse_filter_by_funnel_feedback_admin($query)
{
    //modify the query only if it admin and main query.
    if (!(is_admin() && $query->is_main_query())) {
        return $query;
    }
    //we want to modify the query for the targeted custom post and filter option
    if (isset($query->query['post_type']) && !('starfish_feedback' === $query->query['post_type'] && isset($_REQUEST['funnel_id']))) {
        return $query;
    }
    //for the default value of our filter no modification is required
    if ('all' === esc_html($_REQUEST['funnel_id'])) {
        return $query;
    }
    //modify the query_vars.
    $query->query_vars['meta_key'] = SRM_META_FUNNEL_ID;
    $query->query_vars['meta_value'] = esc_html($_REQUEST['funnel_id']);
    return $query;
}

add_filter('parse_query', 'srm_parse_filter_by_funnel_feedback_admin', 10);
