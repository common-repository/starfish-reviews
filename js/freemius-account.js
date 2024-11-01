/**
 * 
 * Change Freemius Account element references
 * 
 */
jQuery(document).ready(function($) {
    $("a[href*='/wp-admin/edit.php?post_type=starfish_review&billing_cycle=annual&page=starfish-settings-pricing']").attr("href", "https://starfish.reviews/plans-pricing/").attr("target", "_blank");
});