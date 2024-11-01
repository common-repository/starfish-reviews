<?php

/*Change menu-order*/

add_filter('custom_menu_order', 'srm_submenu_order');

function srm_submenu_order($menu_order)
{
    global $submenu;

    $srm_parent_menu = 'edit.php?post_type=starfish_feedback';
    $reviews_key     = null;
    $profiles_key    = null;
    $collections_key = null;
    $feedback_key    = null;
    $funnels_key     = null;
    $reports_key     = null;
    $settings_key    = null;
    $upgrade_key     = null;
    $new_order       = array();

    foreach ($submenu[$srm_parent_menu] as $key => $menu) {
        if (in_array('Reviews', $menu, true)) {
            $reviews_key = $key;
        }
        if (in_array('Profiles', $menu, true)) {
            $profiles_key = $key;
        }
        if (in_array('Collections', $menu, true)) {
            $collections_key = $key;
        }
        if (in_array('Feedback', $menu, true)) {
            $feedback_key = $key;
        }
        if (in_array('Funnels', $menu, true)) {
            $funnels_key = $key;
        }
        if (in_array('Reports', $menu, true)) {
            $reports_key = $key;
        }
        if (in_array('Settings', $menu, true)) {
            $settings_key = $key;
        }
        if (in_array('Upgrade', $menu, true)) {
            $upgrade_key = $key;
        }
    }

    if (!empty($reviews_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$reviews_key];
    }
    if (!empty($profiles_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$profiles_key];
    }
    if (!empty($collections_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$collections_key];
    }
    if (!empty($feedback_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$feedback_key];
    }
    if (!empty($funnels_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$funnels_key];
    }
    if (!empty($reports_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$reports_key];
    }
    if (!empty($settings_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$settings_key];
    }
    if (!empty($upgrade_key)) {
        $new_order[] = $submenu[$srm_parent_menu][$upgrade_key];
    }

    $submenu[$srm_parent_menu] = $new_order;
    return $menu_order;
}
