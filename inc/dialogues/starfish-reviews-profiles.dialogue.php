<?php

use Starfish\Premium\ReviewsProfiles as SRM_REVIEWS_PROFILES;
use Starfish\Premium\UtilsReviews as SRM_UTILS_REVIEWS;

if (current_user_can('manage_options')) {
    $sites        = SRM_UTILS_REVIEWS::get_review_sites();
    $last_scrape  = null;
    $profile_id   = null;
    $job_id       = null;
    $profile_data = null;
    $profile_host = null;
    $profile_slug = null;
    $profile_url  = null;
    if (isset($_GET[ 'action' ]) && sanitize_text_field($_GET[ 'action' ]) === 'details' && isset($_GET[ 'id' ]) && ! empty($_GET[ 'id' ])) {
        $profile_id   = sanitize_text_field($_GET['id']);
        $profile_data = SRM_REVIEWS_PROFILES::get_profile($profile_id);
        $profile_host = wp_parse_url($profile_data->url, PHP_URL_HOST);
        foreach ($sites as $site) {
            if (wp_parse_url($site->url, PHP_URL_HOST) === $profile_host) {
                $site_url_format = $site->url;
            }
        }
        $url_parts         = explode("/", strtolower($profile_data->url));
        $format_parts      = explode("/", strtolower($site_url_format));
        $format_slug_index = array_search("%s", $format_parts, true);
        $profile_slug      = $url_parts[$format_slug_index];
        $profile_url       = $profile_data->url;
        $job_id            = $profile_data->job_id;
    }
    ?>
    <div id="srm-reviews-profiles-dialogue" class="srm-dialogue srm-help-dialogue" style="display:none">
        <?php if (isset($profile_data->id)) {
            if (! empty($profile_data->last_crawl)) {
                $last_scrape = gmdate('m/d/Y', strtotime($profile_data->last_crawl));
            }
            ?>
            <div id="srm-reviews-profile-summary" class="table-responsive">
                <table class="table table-sm table-hover" aria-describedby="Profile Summary">
                    <thead>
                    <tr>
                        <th scope="col" colspan="4" class="table-info">Last Scrape Summary</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">Profile ID</th>
                        <td><?php print $profile_data->id; ?></td>
                        <th scope="row">Average Rating</th>
                        <td>
                            <?php
                            if (empty($profile_data->average_rating)) {
                                print '0';
                            } else {
                                for ($s = 1; $s <= $profile_data->average_rating; $s++) {
                                    print '<span class="fa fa-star srm-reviews-star"></span>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Total Reviews</th>
                        <td><?php (empty($profile_data->review_count)) ? print '0' : $profile_data->review_count; ?></td>
                        <th scope="row">Reviews Last Scrape</th>
                        <td><?php (empty($profile_data->result_count)) ? print '0' : $profile_data->result_count; ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Last Scrape</th>
                        <td><?php if (empty($last_scrape)) {
                            print 'Not Available';
                            } else {
                                print $last_scrape;
                            } ?></td>
                        <th scope="row">Scrape Status</th>
                        <td><?php print SRM_REVIEWS_PROFILES::column_crawl_status((array) $profile_data); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <form id="srm-reviews-profile-form" class="needs-validation" novalidate onsubmit="return false">
            <input id="srm-reviews-profile-id" name="srm-reviews-profile-id" type="hidden"
                   value="<?php print $profile_id; ?>"/>
            <input id="srm-reviews-profile-job-id" name="srm-reviews-profile-job-id" type="hidden"
                   value="<?php print $job_id; ?>"/>
            <div class="form-group">
                <label class="form-control-label" for="srm-reviews-profile-name">Profile Name</label>
                <input id="srm-reviews-profile-name" name="srm-reviews-profile-name"
                       class="form-control form-control-sm" type="text"
                       required="true" value="<?php (!empty($profile_data)) ? print $profile_data->name : print '' ?>"/>
                <div class="invalid-feedback">This field is required.</div>
                <p class="description">The Name of this profile.</p>
            </div>
            <div class="srm-form-instructions"></div>
            <div class="form-group">
                <div class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" id="srm-reviews-profiles-sites"
                            data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        Choose a Reviews Site
                    </button>
                    <div class="dropdown-menu" aria-labelledby="srm-reviews-profiles-sites">
                        <?php
                        foreach ($sites as $site) {
                            ( wp_parse_url($site->url, PHP_URL_HOST) === $profile_host ) ? $selected = $site->name : $selected = '';
                            echo '<a class="dropdown-item" title="' . esc_html($site->name) . '" ><img src="' . esc_html($site->logo) . '" alt="' . esc_html($site->name) . '" 
                            width="25px"/>&nbsp;&nbsp;' . esc_html($site->name) . '</a>';
                        }
                        ?>
                    </div>
                </div>
                <p class="description">Select a site from the supported options</p>
            </div>
            <div id="srm-reviews-profile-slug-group" class="form-group">
                <label class="form-control-label" for="srm-reviews-profile-slug">Profile SLUG</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend w-100">
                        <span class="input-group-text" id="srm-reviews-profile-site-logo">
                            <img src="" alt="Profile Site Logo"/></span>
                        <input id="srm-reviews-profile-site-slug" name="srm-reviews-profile-site-slug"
                               class="form-control"
                               type="text" required="true" value="<?php print $profile_slug; ?>"/>
                        <div class="invalid-feedback">This field is required.</div>
                    </div>
                </div>
                <p class="description">The unique identifier for your business found within the review URL. [<a href="https://docs.starfish.reviews/article/136-what-is-a-profile-slug" title="What is a Slug?">Learn More</a>]</p>
                <label class="form-control-label" for="srm-reviews-profile-site-url">Review URL Preview</label>
                <div class="input-group mb-3">
                    <div class="input-group-append w-100">
                        <input id="srm-reviews-profile-site-url" name="srm-reviews-profile-site-url"
                       class="input-group-text form-control" value="<?php print $profile_url; ?>" type="text" readonly/>
                        <span class="input-group-text srm-profile-site-untested">
                            <a id="srm-reviews-profile-site-test" href="#" rel="noopener" title="Validate the URL is correct"><i class="fas fa-rocket" title="Validate URL"></i> Validate</a>
                        </span>
                        <span class="input-group-text srm-profile-site-open">
                            <a id="srm-reviews-profile-site-open" href="<?php print $profile_url; ?>" target="_blank" rel="noopener" title="Open Review URL"><i class="fas fa-external-link-alt" title="Test it out yourself!"></i></a>
                        </span>
                    </div>
                </div>
                <p class="description srm-description-highlighted-info"><strong>Required</strong>: Click "Validate" to ensure the above URL is accurate.</p>
            </div>
            <div id="srm-reviews-profile-google-instructions" style="display: none;">
                <p><i class="fas fa-exclamation-triangle"></i> Choose one of the following options to identify your Google Businesss</p>
            </div>
            <div id="srm-reviews-profile-placeid-group" class="form-group">
                <label class="form-control-label" for="srm-reviews-profile-placeid">Place ID</label>
                <input id="srm-reviews-profile-placeid" name="srm-reviews-profile-placeid"
                       class="form-control form-control-sm" type="text"
                       required="true" value="<?php  (!empty($profile_data)) ? print $profile_data->place_id : print '' ?>"/>
                <div class="invalid-feedback">Invalid Place ID.</div>
                <p class="description">You can provide a Google Place ID (eg. <code>ChIJr5dFzfKAhYARj99B34yuZOw</code>)
                    to be more specific about the
                    Google Business you need reviews from.</p>
            </div>
            <div id="srm-reviews-profile-query-group" class="form-group">
                <label class="form-control-label" for="srm-reviews-profile-query">Query</label>
                <input id="srm-reviews-profile-query" name="srm-reviews-profile-query"
                       class="form-control form-control-sm" type="text"
                       required="true" value="<?php (!empty($profile_data)) ? print $profile_data->query : print '' ?>"/>
                <div class="invalid-feedback">Please ensure to enter a valid Search Query (i.e. )</div>
                <p class="description">Google search query that shows the Google My Business listing in the search
                    results.</p>
            </div>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-success btn-sm <?php ( empty($profile_data->active) || $profile_data->active === '1' ) ? print 'active' : print ''; ?>">
                    <input type="radio" name="srm-reviews-profile-active"
                           value="1" <?php ( empty($profile_data->active) || $profile_data->active === '1' ) ? print 'checked' : print ''; ?>>
                    Active
                </label>
                <label
                        class="btn btn-outline-secondary btn-sm <?php ( !empty($profile_data) && ($profile_data->active === '0' || $profile_data->active === '3') ) ? print 'active' : print ''; ?>">
                    <input type="radio" name="srm-reviews-profile-active"
                           value="0" <?php ( !empty($profile_data) && ($profile_data->active === '0' || $profile_data->active === '3') ) ? print 'checked' : print ''; ?>>
                    Disabled
                </label>
            </div>
            <p class="description">Create the new profile in a "disabled" state, default is "active".</p>
            <div id="srm-reviews-profile-scrape-decision-group" class="form-check-inline">
                <input class="form-check-input" type="checkbox" value="1" name="srm-reviews-profile-scrape-now">
                <label class="form-check-label" for="srm-reviews-profile-scrape-now"><?php print __('Scrape for Reviews Now?', 'starfish'); ?></label>
                &nbsp;&nbsp;
                <?php  if (! empty($last_scrape)) {
                    print '<input style="display: none" class="form-check-input" type="checkbox" value="1" name="srm-reviews-profile-diff-last" checked>';
                    print '<label style="display: none" class="form-check-label" for="srm-reviews-profile-diff-last">' . __('Scrape from last scrape date?', 'starfish') . ' (' . $last_scrape . ')</label>';
                } ?>
            </div>
        </form>
    </div>
    <?php
}
