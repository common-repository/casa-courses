<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Casa_courses
 * @subpackage Casa_courses/includes
 * @author     foretagsakademincasa
 */
class Casa_courses_Activator
{

    /**
     * Activate the plugin.
     *
     * This method is used to activate the plugin. It sets default values for various options if they are empty.
     *
     * @return void
     * @since 1.0.0
     */
    public static function activate()
    {    // Flag option for checking activation status and flush_rewrite_rules activate
        add_option( 'casa_courses_plugin_activation', 'activated' );

        !empty( get_option( 'casa_courses_token' ) ) ?: update_option( 'casa_courses_token', '' );
        !empty( get_option( 'casa_courses_domain' ) ) ?: update_option( 'casa_courses_domain', '' );
        !empty( get_option( 'casa_courses_project_id' ) ) ?: update_option( 'casa_courses_project_id', '' );
        !empty( get_option( 'casa_courses_token_error' ) ) ?: update_option( 'casa_courses_token_error', '' );
        !empty( get_option( 'casa_courses_courses_slug' ) ) ?: update_option( 'casa_courses_courses_slug', 'courses' );
        !empty( get_option( 'casa_courses_industry_json' ) ) ?: update_option( 'casa_courses_industry_json', '' );
        !empty( get_option( 'casa_courses_dietary_preferences_json' ) ) ?: update_option( 'casa_courses_dietary_preferences_json', '' );
        !empty( get_option( 'casa_courses_show_price' ) ) ?: update_option( 'casa_courses_show_price', 'true' );
        !empty( get_option( 'casa_course_disable_caching') ) ?: update_option( 'casa_course_disable_caching', 'true' );

        //Style Fields
        !empty( get_option( 'casa_courses_primary' ) ) ?: update_option( 'casa_courses_primary', '#008B86' );
        !empty( get_option( 'casa_courses_secondary' ) ) ?: update_option( 'casa_courses_secondary', '#30A8A8' );
        !empty( get_option( 'casa_courses_text_font' ) ) ?: update_option( 'casa_courses_text_font', 'Inherit' );
        !empty( get_option( 'casa_courses_area_box_bg_color' ) ) ?: update_option( 'casa_courses_area_box_bg_color', '#000' );
        !empty( get_option( 'casa_courses_area_list_color' ) ) ?: update_option( 'casa_courses_area_list_color', '#fff' );
        !empty( get_option( 'casa_courses_area_box_color' ) ) ?: update_option( 'casa_courses_area_box_color', '#fff' );
        !empty( get_option( 'casa_courses_area_box_font_size' ) ) ?: update_option( 'casa_courses_area_box_font_size', 14 );
        !empty( get_option( 'casa_courses_area_box_number_desktop' ) ) ?: update_option( 'casa_courses_area_box_number_desktop', 6 );
        !empty( get_option( 'casa_courses_price_text_position' ) ) ?: update_option( 'casa_courses_price_text_position', 'left' );
        !empty( get_option( 'casa_courses_detail_price_head_color' ) ) ?: update_option( 'casa_courses_detail_price_head_color', '#04f59f' );
        !empty( get_option( 'casa_courses_detail_price_head_bg_color' ) ) ?: update_option( 'casa_courses_detail_price_head_bg_color', '#024b54' );
        !empty( get_option( 'casa_courses_detail_price_head_border_color' ) ) ?: update_option( 'casa_courses_detail_price_head_border_color', '#024b54' );

        !empty( get_option( 'casa_courses_detail_event_color' ) ) ?: update_option( 'casa_courses_detail_event_color', '#fff' );
        !empty( get_option( 'casa_courses_detail_event_bg_color' ) ) ?: update_option( 'casa_courses_detail_event_bg_color', '#50a29e' );
        !empty( get_option( 'casa_courses_detail_event_btn_color' ) ) ?: update_option( 'casa_courses_detail_event_btn_color', '#fff' );
        !empty( get_option( 'casa_courses_detail_event_btn_bg_color' ) ) ?: update_option( 'casa_courses_detail_event_btn_bg_color', '#008B86' );
        !empty( get_option( 'casa_courses_few_seats_remaining_color' ) ) ?: update_option( 'casa_courses_few_seats_remaining_color', '#ffc107' );
        !empty( get_option( 'casa_courses_fully_booked_color' ) ) ?: update_option( 'casa_courses_fully_booked_color', '#dc3545' );

        !empty( get_option( 'casa_courses_booking_btn_color' ) ) ?: update_option( 'casa_courses_booking_btn_color', '#fff' );
        !empty( get_option( 'casa_courses_booking_btn_bg_color' ) ) ?: update_option( 'casa_courses_booking_btn_bg_color', '#008B86' );

        //Form Fields
        !empty( get_option( 'casa_courses_company_required' ) ) ?: update_option( 'casa_courses_company_required', 'true' );
        !empty( get_option( 'casa_courses_company_visible' ) ) ?: update_option( 'casa_courses_company_visible', 'true' );
        !empty( get_option( 'casa_courses_company_id_required' ) ) ?: update_option( 'casa_courses_company_id_required', 'true' );
        !empty( get_option( 'casa_courses_company_id_visible' ) ) ?: update_option( 'casa_courses_company_id_visible', 'true' );

        !empty( get_option( 'casa_courses_industry_required' ) ) ?: update_option( 'casa_courses_industry_required', 'true' );
        !empty( get_option( 'casa_courses_industry_visible' ) ) ?: update_option( 'casa_courses_industry_visible', 'true' );
        !empty( get_option( 'casa_courses_dietary_preferences_required' ) ) ?: update_option( 'casa_courses_dietary_preferences_required', 'true' );
        !empty( get_option( 'casa_courses_dietary_preferences_visible' ) ) ?: update_option( 'casa_courses_dietary_preferences_visible', 'true' );

        !empty( get_option( 'casa_courses_limited_available' ) ) ?: update_option( 'casa_courses_limited_available', '5' );
        !empty( get_option( 'casa_courses_scheduled_text_default' ) ) ?: update_option( 'casa_courses_scheduled_text_default', __( 'Contact us if interested', 'casa-courses' ) );
        !empty( get_option( 'casa_courses_seats_remaining' ) ) ?: update_option( 'casa_courses_seats_remaining', __( 'Few seats remaining', 'casa-courses' ) );
        !empty( get_option( 'casa_courses_seats_full' ) ) ?: update_option( 'casa_courses_seats_full', __( 'Fully booked', 'casa-courses' ) );
        !empty( get_option( 'casa_courses_title_separator' ) ) ?: update_option( 'casa_courses_title_separator', '|' );
        !empty( get_option( 'casa_courses_available_notify' ) ) ?: update_option( 'casa_courses_available_notify', 'true' );
        !empty( get_option( 'casa_courses_show_filter_home' ) ) ?: update_option( 'casa_courses_show_filter_home', 'true' );
        !empty( get_option( 'casa_courses_delay_days' ) ) ?: update_option( 'casa_courses_delay_days', 1 );


        !empty( get_option( 'casa_courses_terms_label' ) ) ?: update_option( 'casa_courses_terms_label', '' );
        !empty( get_option( 'casa_courses_privacy_label' ) ) ?: update_option( 'casa_courses_privacy_label', '' );
        !empty( get_option( 'casa_courses_privacy_message' ) ) ?: update_option( 'casa_courses_privacy_message', '' );
        !empty( get_option( 'casa_courses_terms_message' ) ) ?: update_option( 'casa_courses_terms_message', '' );

        //Homepage fields
        !empty( get_option( 'casa_courses_area_section_title' ) ) ?: update_option( 'casa_courses_area_section_title', __( 'Courses', 'casa-courses' ) );

        //Header/Footer fields
        !empty( get_option( 'casa_courses_header_template' ) ) ?: update_option( 'casa_courses_header_template', '<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->' );
        !empty( get_option( 'casa_courses_footer_template' ) ) ?: update_option( 'casa_courses_footer_template', '<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->' );

        //Calendar fields
        !empty( get_option( 'casa_courses_calendar_desc' ) ) ?: update_option( 'casa_courses_calendar_desc', '' );
        !empty( get_option( 'casa_courses_calendar_months' ) ) ?: update_option( 'casa_courses_calendar_months', 6 );
        !empty( get_option( 'casa_courses_calendar_title' ) ) ?: update_option( 'casa_courses_calendar_title', __( 'Course Calendar', 'casa-courses' ) );
        !empty( get_option( 'casa_courses_calendar_slug' ) ) ?: update_option( 'casa_courses_calendar_slug', 'calendar' );
        !empty( get_option( 'casa_courses_show_filter_calendar' ) ) ?: update_option( 'casa_courses_show_filter_calendar', 'false' );

        //Registration fields
        !empty( get_option( 'casa_courses_registration_title' ) ) ?: update_option( 'casa_courses_registration_title', __( 'Book', 'casa-courses' ) );
        !empty( get_option( 'casa_courses_registration_slug' ) ) ?: update_option( 'casa_courses_registration_slug', 'book' );
        !empty( get_option( 'casa_courses_registration_desc' ) ) ?: update_option( 'casa_courses_registration_desc', '' );
        !empty( get_option( 'casa_courses_submit_desc' ) ) ?: update_option( 'casa_courses_submit_desc', '' );

        //List View All
        !empty( get_option( 'casa_courses_list_all_section_desc' ) ) ?: update_option( 'casa_courses_list_all_section_desc', '' );

        // Add sync information
        !empty( get_option( 'casa_courses_industry_loaded' ) ) ?: update_option( 'casa_courses_industry_loaded', '' );
        !empty( get_option( 'casa_courses_dietary_preferences_loaded' ) ) ?: update_option( 'casa_courses_dietary_preferences_loaded', '' );
        !empty( get_option( 'casa_courses_areas_loaded' ) ) ?: update_option( 'casa_courses_areas_loaded', '' );
        !empty( get_option( 'casa_courses_courses_loaded' ) ) ?: update_option( 'casa_courses_courses_loaded', '' );
        !empty( get_option( 'casa_courses_events_loaded' ) ) ?: update_option( 'casa_courses_events_loaded', '' );

        // Check if casa_daily_sync cron job is scheduled
        if ( !wp_next_scheduled( 'casa_cron_sync' ) ) {
            // Schedule the event from now
            wp_schedule_event( time(), 'hourly', 'casa_cron_sync' );
        }
    }
}
