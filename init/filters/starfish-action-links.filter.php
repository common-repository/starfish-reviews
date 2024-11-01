<?php

use Starfish\Freemius as SRM_FREEMIUS;

/**
 * Add relevant links to plugins page
 *
 * @param array $links
 *
 * @return array
 * @throws Freemius_Exception
 */
function srm_plugin_action_links($links)
{
    $plugin_links  = array();
    $link_template = "<a href='%s' title='View Account'><span style='color:white; %s padding: 3px; border-radius:3px;'>%s</span></a>";
    switch (strtolower(SRM_PLAN_TITLE)) {
    case "free":
        $plan_badge = sprintf(
            $link_template,
            SRM_PLANS_URL,
            'background-color:#E91E63;',
            SRM_PLAN_TITLE
        );
        break;
    case ("webmaster" || "agency"):
        $plan_badge = sprintf(
            $link_template,
            SRM_FREEMIUS::starfish_fs()->get_account_url(),
            'background-color:#FFC200;',
            SRM_PLAN_TITLE
        );
        break;
    case ("marketer" || "entrepreneur"):
        $plan_badge = sprintf(
            $link_template,
            SRM_FREEMIUS::starfish_fs()->get_account_url(),
            'background-color:#7ED026;',
            SRM_PLAN_TITLE
        );
        break;
    case "business":
        $plan_badge = sprintf(
            $link_template,
            SRM_FREEMIUS::starfish_fs()->get_account_url(),
            'background-color:#00AEEF;',
            SRM_PLAN_TITLE
        );
        break;
    default:
        $plan_badge = sprintf(
            $link_template,
            SRM_PLANS_URL,
            'background-color:#E91E63;',
            'FREE'
        );
    }

    array_push($plugin_links, $plan_badge);

    array_push($plugin_links, '<a href="' . admin_url('admin.php?page=starfish-settings') . '">' . esc_html__('Settings', 'starfish') . '</a>');

    /**
     * Upgrade Links
    **/
    // Upgrade to Premium License
    if (! SRM_FREEMIUS::starfish_fs()->can_use_premium_code()) {
        array_push($plugin_links, SRM_GO_PREMIUM_LINK);
    }
    // Upgrade to Entrepreneur from Business
    if (SRM_FREEMIUS::starfish_fs()->is_plan_or_trial__premium_only(SRM_BUSINESS_PLAN, true) || SRM_FREEMIUS::starfish_fs()->is_plan_or_trial__premium_only(SRM_BUSINESS_PLAN_LEGACY, true)) {
        array_push($plugin_links, "<a href='" . SRM_PRICING_ADMIN_URL . "'>" . esc_html__('Upgrade to Entrepreneur', 'starfish') . "</a>");
    }
    // Upgrade to Agency from Entrepreneur or Marketer
    if (SRM_FREEMIUS::starfish_fs()->is_plan_or_trial__premium_only(SRM_MARKETER_PLAN_LEGACY, true) || SRM_FREEMIUS::starfish_fs()->is_plan_or_trial__premium_only(SRM_ENTREPRENEUR_PLAN, true)) {
        array_push($plugin_links, "<a href='" . SRM_PRICING_ADMIN_URL . "'>" . esc_html__('Upgrade to Agency', 'starfish') . "</a>");
    }

    return array_merge($plugin_links, $links);
}

add_filter('plugin_action_links_' . SRM_PLUGIN_BASENAME, 'srm_plugin_action_links');
