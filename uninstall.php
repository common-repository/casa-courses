<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://-
 * @since      1.0.0
 *
 * @package    Casa_courses
 */

// If uninstall not called from WordPress, then exit.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-casa_courses-custom-taxonomy_areas.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-casa_courses-custom-posttype_courses.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-casa_courses-custom-posttype_events.php';

delete_option( 'casa_courses_token' );
delete_option( 'casa_courses_domain' );
delete_option( 'casa_courses_project_id' );
delete_option( 'casa_courses_token_error' );
delete_option( 'casa_courses_courses_slug' );
delete_option( 'casa_courses_company_required' );
delete_option( 'casa_courses_company_visible' );
delete_option( 'casa_courses_company_id_required' );
delete_option( 'casa_courses_company_id_visible' );
delete_option( 'casa_courses_show_price' );
delete_option( 'casa_course_disable_caching' );
delete_option( 'casa_courses_limited_available' );

//Style fields
delete_option( 'casa_courses_primary' );
delete_option( 'casa_courses_secondary' );
delete_option( 'casa_courses_text_font' );
delete_option( 'casa_courses_area_box_font_size' );
delete_option( 'casa_courses_area_box_number_desktop' );
delete_option( 'casa_courses_price_text_position' );
delete_option( 'casa_courses_detail_price_head_color' );
delete_option( 'casa_courses_detail_price_head_bg_color' );
delete_option( 'casa_courses_detail_price_head_border_color' );

delete_option( 'casa_courses_area_box_color' );
delete_option( 'casa_courses_area_box_bg_color' );
delete_option( 'casa_courses_area_list_color' );


delete_option( 'casa_courses_detail_event_color' );
delete_option( 'casa_courses_detail_event_bg_color' );
delete_option( 'casa_courses_detail_event_btn_color' );
delete_option( 'casa_courses_detail_event_btn_bg_color' );

delete_option( 'casa_courses_booking_btn_color' );
delete_option( 'casa_courses_booking_btn_bg_color' );

delete_option( 'casa_courses_industry_json' );
delete_option( 'casa_courses_dietary_preferences_visible' );
delete_option( 'casa_courses_dietary_preferences_json' );

delete_option( 'casa_courses_available_notify' );
delete_option( 'casa_courses_show_filter_home' );
delete_option( 'casa_courses_few_seats_left_color' );
delete_option( 'casa_courses_fully_booked_color' );

//Header/Footer fields
delete_option( 'casa_courses_header_template' );
delete_option( 'casa_courses_footer_template' );

delete_option( 'casa_courses_scheduled_text_default' );
delete_option( 'casa_courses_seats_remaining' );
delete_option( 'casa_courses_seats_full' );
delete_option( 'casa_courses_title_separator' );

//Registration fields
delete_option( 'casa_courses_submit_desc' );
delete_option( 'casa_courses_registration_title' );
delete_option( 'casa_courses_registration_slug' );
delete_option( 'casa_courses_registration_desc' );

delete_option( 'casa_courses_terms_label' );
delete_option( 'casa_courses_privacy_label' );
delete_option( 'casa_courses_privacy_message' );
delete_option( 'casa_courses_terms_message' );

//Homepage fields
delete_option( 'casa_courses_area_section_title' );
delete_option( 'casa_courses_list_all_section_desc' );

// Calendar fields 
delete_option( 'casa_courses_courses_calendar_slug' );
delete_option( 'casa_courses_calendar_desc' );
delete_option( 'casa_courses_calendar_months' );
delete_option( 'casa_courses_calendar_title' );
delete_option( 'casa_courses_show_filter_calendar' );

// Delete sync information dates
delete_option( 'casa_courses_industry_loaded' );
delete_option( 'casa_courses_dietary_preferences_loaded' );
delete_option( 'casa_courses_areas_loaded' );
delete_option( 'casa_courses_courses_loaded' );
delete_option( 'casa_courses_events_loaded' );

// Remove casa_cron_sync cron job
wp_clear_scheduled_hook( 'casa_cron_sync' );

// delete custom post type casa_courses
$casa_course_args = array (
    'post_type'      => Casa_Courses_Custom_Posttype_Courses::$post_type,
    'posts_per_page' => -1
);
$casa_course_posts = get_posts( $casa_course_args );
foreach ( $casa_course_posts as $post ) {
    wp_delete_post( $post->ID );
}

// delete custom post type casa_events
$casa_event_args = array (
    'post_type'      => Casa_Courses_Custom_Posttype_Events::$post_type,
    'posts_per_page' => -1
);
$casa_event_posts = get_posts( $casa_event_args );
foreach ( $casa_event_posts as $post ) {
    wp_delete_post( $post->ID );
}

// delete custom post tax casa_events
$terms = get_terms( array (
    'taxonomy'   => Casa_Courses_Custom_Taxonomy_Areas::$tax_type,
    'hide_empty' => false
) );
foreach ( $terms as $term ) {
    wp_delete_term( $term->term_id, Casa_Courses_Custom_Taxonomy_Areas::$tax_type );
}
