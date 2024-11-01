<?php

namespace Starfish;

use DOMDocument;
use Starfish\Logging as SRM_LOGGING;

/**
 * General Starfish Utilities
 *
 * @category   Reviews
 * @package    WordPress
 * @subpackage Starfish
 *
 * @author  silvercolt45 <webmaster@silvercolt.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @version Release: 1.0
 * @since   Release: 2.4.0
 */
class Utils
{
    public const EDIT_ROOT = 'edit.php';
    public const POST_ROOT = 'post.php';
    public const POST_NEW  = 'post-new.php';

    /**
     * Check if current page is a Starfish page
     *
     * @return bool
     */
    public static function is_starfish_page($post, $pagenow, $get)
    {
        if ((isset($post) && ('funnel' == $post->post_type) && ((self::POST_ROOT === $pagenow) || (self::POST_NEW === $pagenow) || (self::EDIT_ROOT === $pagenow)))
            || (isset($post) && ('collection' == $post->post_type) && ((self::POST_ROOT === $pagenow) || (self::POST_NEW === $pagenow) || (self::EDIT_ROOT === $pagenow)))
            || (isset($post) && ('funnel' == $post->post_type || 'collection' == $post->post_type))
            || (isset($get['page']) && 'starfish-settings' === $get['page'])
            || (isset($get['post_type']) && 'feedback' === $get['post_type'])
            || (isset($get['page']) && ('starfish-reviews-profiles' === $get['page'] || 'starfish-reviews' === $get['page']))
            || (isset($get['post_type']) && 'collection' === $get['post_type'])
            || (isset($get['post_type']) && 'starfish_testimonial' === $get['post_type'])
            || (isset($get['action']) && 'edit' === $get['action'] && self::POST_ROOT === $pagenow && isset($post)
            && (esc_html(get_option(SRM_OPTION_FUNNEL_SLUG)) == get_post_type($get['post']) || 'collection' == get_post_type($get['post']) || 'feedback' == get_post_type($get['post']) || 'starfish_testimonial' == get_post_type($get['post'])))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if current page is a Starfish Funnel
     *
     * @param $post
     * @param $pagenow
     *
     * @return bool
     */
    public static function is_starfish_funnel($post, $pagenow)
    {
        if (isset($post) && ('funnel' == $post->post_type) && ((self::POST_ROOT === $pagenow) || (self::POST_NEW === $pagenow))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if current page is a Starfish Testimonial
     *
     * @param $post
     * @param $pagenow
     *
     * @return bool
     */
    public static function is_starfish_testimonial($post, $pagenow)
    {
        if (isset($post) && (esc_html('starfish_testimonial') === $post->post_type) && ((self::POST_ROOT === $pagenow) || (self::POST_NEW === $pagenow) || (self::EDIT_ROOT === $pagenow))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if current page is a Starfish Feedback
     *
     * @param $post
     * @param $pagenow
     *
     * @return bool
     */
    public static function is_starfish_feedback($post, $pagenow)
    {
        if (isset($post) && ('starfish_feedback' === $post->post_type) && ((self::EDIT_ROOT === $pagenow))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if current page is a Starfish Collection
     *
     * @param $post
     * @param $pagenow
     *
     * @return bool
     */
    public static function is_starfish_collection($post, $pagenow, $get)
    {
        if ((isset($post) && ('collection' === $post->post_type) && (('post.php' === $pagenow) || (self::POST_NEW === $pagenow)))
            || ((self::EDIT_ROOT === $pagenow) && (isset($get['post_type']) && 'collection' === $get['post_type']))
            || (isset($get['post_type']) && 'collection' === $get['post_type'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if current page is a Starfish Reviews Profile
     *
     * @param $post
     * @param $pagenow
     *
     * @return bool
     */
    public static function is_starfish_reviews_profile($post, $pagenow, $get)
    {
        if (isset($get['page']) && ('starfish-reviews-profiles' === $get['page'] || 'starfish-reviews' === $get['page'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if current page is a Starfish Settings
     *
     * @param $post
     * @param $pagenow
     *
     * @return bool
     */
    public static function is_starfish_settings($post, $pagenow, $get)
    {
        if ((isset($get['page']) && 'starfish-settings' === $get['page'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Date Compare for use in usort() function
     *
     * @param $a
     * @param $b
     *
     * @return false|int
     */
    public static function date_compare($a, $b)
    {
        $t1 = strtotime($a['created']);
        $t2  = strtotime($b['created']);
        return $t1 - $t2;
    }

    /**
     * @param  $shortcode_key
     * @param  $shortcode_tag
     * @param  $post
     *
     * @return array|false
     */
    public static function get_shortcode_attributes($shortcode_key, $shortcode_tag, $post)
    {
        $query         = get_post($post->ID);
        $post_content  = $query->post_content;
        $has_shortcode = has_shortcode($post_content, $shortcode_tag);

        SRM_LOGGING::addEntry(
            array(
                'level'   => SRM_LOGGING::SRM_INFO,
                'action'  => 'has_shortcode',
                'message' => 'Shortcode: ' . $shortcode_key . ', Post ID: ' . $post->ID . ' = ' . var_export($has_shortcode, true),
                'code'    => 'SRM-C-GSA-X',
            )
        );
        if ($has_shortcode) {
            $output = array();
            // get shortcode regex pattern WordPress function
            $pattern = get_shortcode_regex(array( $shortcode_tag ));
            if (preg_match_all('/' . $pattern . '/s', $post_content, $matches)) {
                $out = array();
                if (isset($matches[2])) {
                    foreach ((array) $matches[2] as $key => $value) {
                        if ($shortcode_tag === $value) {
                            $out[] = shortcode_parse_atts($matches[3][ $key ]);
                        }
                    }
                }
                return $out;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check if page has given shortcode present.
     *
     * @return integer|bool Shortcode ID (where applicable) or True if found otherwise false
     */
    public static function is_shortcode_present($post, $shortcode_key)
    {

        if (!empty($post)) {
            $query        	 = get_post($post->ID);
            $post_content    = $query->post_content;
            $shortcode_attrs = self::get_shortcode_attributes($shortcode_key, 'starfish', $post);
            SRM_LOGGING::addEntry(
                array(
                    'level'   => SRM_LOGGING::SRM_INFO,
                    'action'  => 'shortcode_attributes',
                    'message' => 'Shortcode: ' . $shortcode_key . ', Post ID: ' . $post->ID . ' Attributes = ' . var_export($shortcode_attrs, true),
                    'code'    => 'SRM-C-ISP-X',
                )
            );
            if (!empty($shortcode_attrs)) {
                foreach ($shortcode_attrs as $shortcode) {
                    if (isset($shortcode[$shortcode_key])) {
						return $shortcode[$shortcode_key];
                    }
                }
            // Check for any signs of HTML.
            } elseif (!empty($post_content)) {
                $page_html = apply_filters('the_content', $post_content);
                if(!empty($page_html)) {
                    $domdoc    = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $domdoc->loadHTML($page_html);
                    if (strpos($page_html, 'srm-' . $shortcode_key) !== false) {
                        $divs = $domdoc->getElementsByTagName('div');
                        foreach ($divs as $div) {
                            if (!empty($div->getAttribute('data-'.$shortcode_key.'_id'))) {
                                return $div->getAttribute('data-'.$shortcode_key.'_id');
                            } else {
                                return true;
                            }
                        }
                    }
                    libxml_clear_errors();
                }
            }
        }
        return false;
    }
}
