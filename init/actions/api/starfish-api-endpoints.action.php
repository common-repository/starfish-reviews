<?php

/**
 * Register new API Endpoints:
 */

use Starfish\Logging as SRM_LOGGING;
use Starfish\Support as SRM_SUPPORT;

add_action(
    'rest_api_init',
    function () {
        // DataShake Callback for posting back to Starfish the status of a Profile Job, including the scraped reviews
        register_rest_route(
            'starfish',
            '/reviews/summary',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => 'init_profile_callback',
                'permission_callback' => '__return_true',
            ]
        );
        // Starfish endpoint for retrieving all reviews captured
        register_rest_route(
            'starfish',
            '/reviews',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => 'init_reviews_callback',
                'permission_callback' => function ($request) {
                    return current_user_can('manage_options');
                },
            ]
        );
        // Starfish endpoint for retrieving last review scrape summaries
        register_rest_route(
            'starfish',
            '/reviews/summary',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => 'init_reviews_summary_callback',
                'permission_callback' => function ($request) {
                    return current_user_can('manage_options');
                },
            ]
        );
    }
);


/**
 * @param WP_REST_Request $request The callback payload from DataShake
 *                                 Example Request Payload:
 *                                 <pre>
 *                                 {"success":true,"status":200,"job_id":68365254,"source_url":"https://facebook.com/pg/StarfishWP/reviews","place_id":null,"external_identifier":null,"meta_data":null,"unique_id":null,"review_count":0,"average_rating":0.0,"last_crawl":"2019-12-16","crawl_status":"complete","percentage_complete":100,"result_count":0,"credits_used":10,"from_date":"2019-11-01","blocks":null,"reviews":[]}
 *                                 </pre>
 *
 * @return WP_Error|WP_REST_Response
 */
function init_profile_callback(WP_REST_Request $request)
{
    $srm_error_cya = '01';
    $body          = $request->get_body();
    $response      = json_decode($body);
    if (function_exists('srm_profile_callback')) {
        // Verify the scrape status is 'complete' or 'pending' and not a failure of some kind
        if ('complete' === $response->crawl_status) {
            $summary_id = srm_profile_callback($response);
            if ($summary_id !== false) {
                $srm_error_cya = '02';
                SRM_LOGGING::addEntry(
                    [
                        'level' => SRM_LOGGING::SRM_INFO,
                        'action' => 'Datashake Callback Successful',
                        'message' => 'summary_id=' . print_r($summary_id, true),
                        'code' => 'SRM_R_PCB_X_' . $srm_error_cya,
                    ]
                );
                return new WP_REST_Response(true, 200);
            } else {
                $srm_error_cya = '03';
                SRM_LOGGING::addEntry(
                    [
                        'level' => SRM_LOGGING::SRM_ERROR,
                        'action' => 'Datashake Callback Failed',
                        'message' => 'status=500,message="Failed to process callback",summary_id=' . print_r($summary_id, true) . ', 
                                      rs_response=' . print_r($body, true),
                        'code' => 'SRM_R_PCB_X_' . $srm_error_cya,
                    ]
                );
                return new WP_Error('srm-rs-callback', __('Failed to process callback', 'starfish'), ['status' => 500, 'payload' => $body]);
            }
        } else {
            SRM_LOGGING::addEntry(
                [
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'Datashake Scrape Failed',
                    'message' => 'job_id=' . $response->job_id . ', crawl_status=' . $response->crawl_status,
                    'code'    => 'SRM_R_PCB_X_' . $srm_error_cya,
                ]
            );
            SRM_SUPPORT::srm_post_message(
                [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'Datashake is experiencing issues scraping reviews',
                        ],
                    ],
                    ['type' => 'divider'],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'Site: ' . SRM_PLUGIN_URL . '\n\nJob ID: <https://app.datashake.com/profiles|' . $response->job_id . '>\n\nCrawl Status: ' . $response->crawl_status,
                        ],
                    ],
                ],
                'Datashake Callback Failure'
            );
            return new WP_Error('srm-rs-callback', __('Profile Scrape Failed', 'starfish'), [ 'status' => 201, 'job_id=' . $response->job_id . ', crawl_status' => $response->crawl_status ]);
        }
    }

    SRM_LOGGING::addEntry(
        [
            'level'   => SRM_LOGGING::SRM_ERROR,
            'action'  => 'Datashake Callback Failed (hard)',
            'message' => 'status=500,message="Callback function not found",rs_response=' . print_r($body, true),
            'code'    => 'SRM_R_PCB_X_' . $srm_error_cya,
        ]
    );
    return new WP_Error('srm-rs-callback', __('Callback function not found', 'starfish'), [ 'status' => 500 ]);
}

/**
 * Request all reviews from Starfish tables
 *
 * @return WP_REST_Response|WP_Error
 */
function init_reviews_callback()
{
    if (function_exists('srm_reviews_callback')) {
        $reviews = srm_reviews_callback();
        if ($reviews) {
            return new WP_REST_Response($reviews, 200);
        } else {
            return new WP_Error('srm-reviews-callback', __('Failed to process callback', 'starfish'), [ 'status' => 500 ]);
        }
    }

    return new WP_Error('srm-reviews-callback', __('Callback function not found', 'starfish'), [ 'status' => 500 ]);
}

/**
 * Request reviews last summary statistics
 *
 * @return WP_REST_Response|WP_Error
 */
function init_reviews_summary_callback()
{
    if (function_exists('srm_reviews_summary_callback')) {
        $reviews = srm_reviews_summary_callback();
        if ($reviews) {
            return new WP_REST_Response($reviews, 200);
        } else {
            return new WP_Error('srm-reviews-summary-callback', __('Failed to process callback', 'starfish'), [ 'status' => 500 ]);
        }
    }

    return new WP_Error('srm-reviews-summary-callback', __('Callback function not found', 'starfish'), [ 'status' => 500 ]);
}