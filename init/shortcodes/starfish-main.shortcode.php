<?php

use  Starfish\Funnels as SRM_FUNNELS ;
use  Starfish\Twig as SRM_TWIG ;
use  Starfish\Premium\Collections as SRM_COLLECTIONS ;
use  Starfish\Testimonials as SRM_TESTIMONIALS ;
use  Starfish\Freemius as SRM_FREEMIUS ;
use  Twig\Error\LoaderError ;
use  Twig\Error\RuntimeError ;
use  Twig\Error\SyntaxError ;
use  Twig\TwigFilter ;
/**
 * Starfish Main Shortcode
 *
 * @param array $atts The shortcode's attributes.
 *
 * @return string
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 * @throws Freemius_Exception
 */
function srm_starfish_shortcode( $atts )
{
    $args = shortcode_atts( array(
        'funnel'      => '',
        'id'          => '',
        'collection'  => '',
        'testimonial' => '',
    ), $atts );
    $testimonial = $args['testimonial'];
    // Category.
    $funnel_id = (int) $args['funnel'];
    $collection_id = (int) $args['collection'];
    $tracking_id = $args['id'];
    $content = null;
    if ( !empty($funnel_id) ) {
        $content .= srm_get_funnel_content( $funnel_id, $tracking_id );
    }
    if ( !empty($collection_id) && SRM_FREEMIUS::starfish_fs()->is__premium_only() ) {
        $content .= srm_get_collection_content__premium_only( $collection_id, $atts );
    }
    if ( !empty($testimonial) ) {
        $content .= srm_get_testimonial_form_content( $atts );
    }
    return $content;
}

add_shortcode( 'starfish', 'srm_starfish_shortcode' );
/**
 * Get the funnel contents from template.
 *
 * @param integer $funnel_id The funnel ID.
 * @param string  $tracking_id The tracking ID.
 *
 * @return string
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_get_funnel_content( $funnel_id, $tracking_id )
{
    $post_status = get_post_status( $funnel_id );
    
    if ( !$post_status || $post_status !== 'publish' ) {
        return SRM_FUNNELS::srm_invalid_funnel_notice( $funnel_id );
    } else {
        $template_variables = SRM_FUNNELS::srm_get_funnel_variables( $funnel_id, $tracking_id );
        $template_variables['shortcode'] = true;
        $filter = new TwigFilter( 'attachmentUrl', function ( $icon_id ) {
            return SRM_FUNNELS::get_icon_url( $icon_id );
        } );
        $twig = SRM_TWIG::get_twig();
        $twig->addFilter( $filter );
        return $twig->render( 'starfish-funnel-stepper.html.twig', $template_variables );
    }

}

/**
 * Get the testimonial form contents from template.
 *
 * @param array  $atts The shortcode attributes.
 *
 * @return string
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function srm_get_testimonial_form_content( $atts )
{
    $twig = SRM_TWIG::get_twig();
    $usage = $atts['testimonial'] ?? null;
    // "form" = (default) return the testimonial form. "display" = return the given testimonials within the "posts" attribute
    
    if ( 'display' === $usage ) {
        $template_variables = SRM_TESTIMONIALS::srm_get_testimonial_display_variables( $atts );
        return $twig->render( 'starfish-testimonial-display.html.twig', $template_variables );
    } elseif ( 'form' === $usage || empty($usage) || 'display' != $usage ) {
        $template_variables = SRM_TESTIMONIALS::srm_get_testimonial_form_variables( $atts );
        return $twig->render( 'starfish-testimonial-form.html.twig', $template_variables );
    }

}
