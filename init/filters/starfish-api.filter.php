<?php

/**
 * Change default number of reviews to return on API requests
 */
add_filter("rest_starfish_feedback_query", function ($args, $request) {
    if (!isset($_GET['per_page'])) {
        // new default to overwrite the default 10
        $args['posts_per_page'] = 1000000;
    }
    return $args;
}, 15, 2);
