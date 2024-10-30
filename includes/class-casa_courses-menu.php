<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Sub menu class
 *
 *
 */
class Casa_Courses_Menu
{

    /**
     * The constructor method for the class.
     *
     * @return void
     */
    public function __construct()
    {
        add_action( 'admin_menu', array (
            $this,
            'register_menu'
        ) );

        add_action( 'admin_init', function () {
            add_settings_section(
                'casa_courses_section_style',
                '',
                '',
                'casa-courses-plugin-style'
            );

            add_settings_section(
                'casa_courses_section_style_listing',
                '',
                '',
                'casa-courses-plugin-style-listing'
            );

            add_settings_section(
                'casa_courses_section_style_detail',
                '',
                '',
                'casa-courses-plugin-style-detail',
            );

            add_settings_section(
                'casa_courses_section_style_booking',
                '',
                '',
                'casa-courses-plugin-style-booking',
            );

            add_settings_section(
                'casa_courses_section_synchronization',
                '',
                '',
                'casa-courses-plugin-synchronization'
            );

            add_settings_section(
                'casa_courses__section_settings',
                '',
                '',
                'casa-courses-plugin-information'
            );

            add_settings_section(
                'casa_courses__section_home',
                '',
                '',
                'casa-courses-plugin-home'
            );

            add_settings_section(
                'casa_courses__section_calendar',
                '',
                '',
                'casa-courses-plugin-calendar'
            );

            add_settings_section(
                'casa_courses__section_registration',
                '',
                '',
                'casa-courses-plugin-registration'
            );

            add_settings_section(
                'casa_courses__section_header_footer',
                '',
                '',
                'casa-courses-plugin-header-footer'
            );
        } );

        add_action( 'admin_init', function () {
            $fields = array (
                array (
                    'uid'         => 'casa_courses_primary',
                    'label'       => esc_attr__( 'Primary color', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style',
                    'group'       => 'casa-courses-plugin-style',
                    's'           => 'casa_courses_style',
                    'type'        => 'color',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_primary' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => '',
                ),

                array (
                    'uid'         => 'casa_courses_secondary',
                    'label'       => esc_attr__( 'Secondary color', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style',
                    'group'       => 'casa-courses-plugin-style',
                    's'           => 'casa_courses_style',
                    'type'        => 'color',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_secondary' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => '',
                ),

                array (
                    'uid'      => 'casa_courses_text_font',
                    'label'    => esc_attr__( 'Font', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style',
                    'group'    => 'casa-courses-plugin-style',
                    's'        => 'casa_courses_style',
                    'type'     => 'select',
                    'options'  => array (
                        'Inherit' => __( 'Inherit', 'casa-courses' ),
                        'Roboto'  => __( 'Roboto', 'casa-courses' ),
                        'DM+Sans' => __( 'DM Sans', 'casa-courses' ),
                    ),
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_text_font' ),
                    'default'  => 'Inherit',
                ),

                array (
                    'uid'         => 'casa_courses_area_box_font_size',
                    'label'       => esc_attr__( 'Area box font size', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style',
                    'group'       => 'casa-courses-plugin-style',
                    's'           => 'casa_courses_style',
                    'type'        => 'number',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_area_box_font_size' ),
                    'options'     => false,
                    'placeholder' => '',
                    'required'    => true,
                    'default'     => 14,
                ),

                array (
                    'uid'         => 'casa_courses_area_box_number_desktop',
                    'label'       => esc_attr__( 'Number of area boxes on the desktop', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style',
                    'group'       => 'casa-courses-plugin-style',
                    's'           => 'casa_courses_style',
                    'type'        => 'number',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_area_box_number_desktop' ),
                    'options'     => false,
                    'placeholder' => '',
                    'required'    => true,
                    'default'     => 6
                ),

                array (
                    'uid'      => 'casa_courses_price_text_position',
                    'label'    => esc_attr__( 'Price text position', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style',
                    'group'    => 'casa-courses-plugin-style',
                    's'        => 'casa_courses_style',
                    'type'     => 'select',
                    'options'  => array (
                        'left'   => __( 'Left', 'casa-courses' ),
                        'center' => __( 'Center', 'casa-courses' ),
                    ),
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_price_text_position' ),
                    'default'  => 'left',
                    'required' => true,
                ),

                array (
                    'uid'         => 'casa_courses_area_box_color',
                    'label'       => esc_attr__( 'Area boxes text color', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style_listing',
                    'group'       => 'casa-courses-plugin-style-listing',
                    's'           => 'casa_courses_style_listing',
                    'type'        => 'color',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_area_box_color' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => '',
                    'required'    => true,
                ),

                array (
                    'uid'         => 'casa_courses_area_box_bg_color',
                    'label'       => esc_attr__( 'Area boxes background color', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style_listing',
                    'group'       => 'casa-courses-plugin-style-listing',
                    's'           => 'casa_courses_style_listing',
                    'type'        => 'color',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_area_box_bg_color' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => '',
                    'required'    => true,
                ),

                array (
                    'uid'         => 'casa_courses_area_list_color',
                    'label'       => esc_attr__( 'Area list text color', 'casa-courses' ),
                    'section'     => 'casa_courses_section_style_listing',
                    'group'       => 'casa-courses-plugin-style-listing',
                    's'           => 'casa_courses_style_listing',
                    'type'        => 'color',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_area_list_color' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => '',
                    'required'    => true,
                ),

                array (
                    'uid'      => 'casa_courses_detail_price_head_color',
                    'label'    => esc_attr__( '"Price and duration" text color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_price_head_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_detail_price_head_bg_color',
                    'label'    => esc_attr__( '"Price and duration" background color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_price_head_bg_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_detail_price_head_border_color',
                    'label'    => esc_attr__( '"Price and duration" border color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_price_head_border_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_detail_event_color',
                    'label'    => esc_attr__( 'Event boxes text color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_event_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_detail_event_bg_color',
                    'label'    => esc_attr__( 'Event boxes background color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_event_bg_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_detail_event_btn_color',
                    'label'    => esc_attr__( 'Event boxes button text color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_event_btn_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_detail_event_btn_bg_color',
                    'label'    => esc_attr__( 'Event boxes button background color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_detail_event_btn_bg_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_few_seats_remaining_color',
                    'label'    => esc_attr__( 'Few seats remaining text color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_few_seats_remaining_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_fully_booked_color',
                    'label'    => esc_attr__( 'Fully booked text color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_detail',
                    'group'    => 'casa-courses-plugin-style-detail',
                    's'        => 'casa_courses_style_detail',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_fully_booked_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_booking_btn_color',
                    'label'    => esc_attr__( 'Course booking button text color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_booking',
                    'group'    => 'casa-courses-plugin-style-booking',
                    's'        => 'casa_courses_style_booking',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_booking_btn_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_booking_btn_bg_color',
                    'label'    => esc_attr__( 'Course booking button background color', 'casa-courses' ),
                    'section'  => 'casa_courses_section_style_booking',
                    'group'    => 'casa-courses-plugin-style-booking',
                    's'        => 'casa_courses_style_booking',
                    'type'     => 'color',
                    'callback' => 'field_callback',
                    'value'    => get_option( 'casa_courses_booking_btn_bg_color' ),
                    'options'  => false,
                    'required' => true,
                    'default'  => '',
                ),

                array (
                    'uid'      => 'casa_courses_company_visible',
                    'label'    => esc_attr__( 'Show corporate fields', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_company_visible' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_courses_company_required',
                    'label'    => esc_attr__( 'Corporate fields are required', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_company_required' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_courses_company_id_visible',
                    'label'    => esc_attr__( 'Show corporate ID field', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_company_id_visible' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_courses_company_id_required',
                    'label'    => esc_attr__( 'Corporate ID field is required', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_company_id_required' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_courses_industry_visible',
                    'label'    => esc_attr__( 'Show industry field', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_industry_visible' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_courses_industry_required',
                    'label'    => esc_attr__( 'Industry field is required', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_industry_required' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_courses_dietary_preferences_visible',
                    'label'    => esc_attr__( 'Show dietary preferences field', 'casa-courses' ),
                    'section'  => 'casa_courses__section_registration',
                    'group'    => 'casa-courses-plugin-registration',
                    's'        => 'casa_courses_registration',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_dietary_preferences_visible' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'      => 'casa_course_disable_caching',
                    'label'    => esc_attr__( 'Disable caching', 'casa-courses' ),
                    'section'  => 'casa_courses__section_settings',
                    'group'    => 'casa-courses-plugin-information',
                    's'        => 'casa_courses_settings',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_course_disable_caching' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                    'helper'   => esc_attr__( 'Disable browser caching on all pages created by plugin.', 'casa-courses' ),
                ),

                array (
                    'uid'      => 'casa_courses_available_notify',
                    'label'    => esc_attr__( 'Allow waiting list', 'casa-courses' ),
                    'section'  => 'casa_courses__section_settings',
                    'group'    => 'casa-courses-plugin-information',
                    's'        => 'casa_courses_settings',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_available_notify' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                    'helper'   => esc_attr__( 'If the event is fully booked, a waiting list is created in CASA.', 'casa-courses' ),
                ),

                array (
                    'uid'      => 'casa_courses_show_price',
                    'label'    => esc_attr__( 'Show price', 'casa-courses' ),
                    'section'  => 'casa_courses__section_settings',
                    'group'    => 'casa-courses-plugin-information',
                    's'        => 'casa_courses_settings',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_show_price' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'true',
                ),

                array (
                    'uid'         => 'casa_courses_limited_available',
                    'label'       => esc_attr__( 'Remaining seats when "limited availability" is activated', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'number',
                    'value'       => get_option( 'casa_courses_limited_available' ),
                    'default'     => '5',
                    'placeholder' => '',
                    'helper'      => esc_attr__( 'Enter the amount of seats that should be available when the "limited availability" warning should be shown.', 'casa-courses' ),
                ),


                array (
                    'uid'         => 'casa_courses_delay_days',
                    'label'       => esc_attr__( 'Hide course date before course start', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'number',
                    'value'       => get_option( 'casa_courses_delay_days' ),
                    'placeholder' => '',
                    'default'     => '1',
                    'helper'      => esc_attr__( 'This setting hides course starts that are less than the set number of days away. The lowest setting is 1 (i.e. tomorrow).', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_courses_slug',
                    'label'       => esc_attr__( 'Courses root slug', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_courses_slug' ),
                    'required'    => true,
                    'default'     => '',
                    'placeholder' => '',
                    'helper'      => esc_attr__( 'Enter the slug used as a prefix to the course detail pages. For example with "courses" a course detail page would be shown on "example.com/courses/course-detail"', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_scheduled_text_default',
                    'label'       => esc_attr__( 'Text when no events are scheduled', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_scheduled_text_default' ),
                    'placeholder' => '',
                    'default'     => '',
                    'required'    => true,
                    'helper'      => esc_attr__( 'Enter the text that should be shown when there are no (upcoming) events scheduled.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_seats_remaining',
                    'label'       => esc_attr__( 'Text when few seats are remaining on the event', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'required'    => true,
                    'value'       => get_option( 'casa_courses_seats_remaining' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the text that should be shown when there are few seats remaining on an event.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_seats_full',
                    'label'       => esc_attr__( 'Text when the event is sold out', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'required'    => true,
                    'value'       => get_option( 'casa_courses_seats_full' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the text that should be shown when there are no seats remaining on an event.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_title_separator',
                    'label'       => esc_attr__( 'Separator for title', 'casa-courses' ),
                    'section'     => 'casa_courses__section_settings',
                    'group'       => 'casa-courses-plugin-information',
                    's'           => 'casa_courses_settings',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'required'    => true,
                    'value'       => get_option( 'casa_courses_title_separator' ),
                    'placeholder' => '',
                    'default'     => '|',
                    'helper'      => esc_attr__( 'Enter the separator that should be used between the title name and site name.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_terms_label',
                    'label'       => esc_attr__( 'Booking policy link', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'url',
                    'value'       => get_option( 'casa_courses_terms_label' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the link to a page that shows your sites booking policy. If this is empty the person booking won\'t see a checkbox for accepting a booking policy.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_terms_message',
                    'label'       => esc_attr__( 'Booking policy text', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_terms_message' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the text that should be shown beside the checkbox for accepting the booking policy.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_privacy_label',
                    'label'       => esc_attr__( 'Privacy policy link', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'url',
                    'value'       => get_option( 'casa_courses_privacy_label' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the link to a page that shows your sites privacy policy. If this is empty the person booking won\'t see a checkbox for accepting a privacy policy.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_privacy_message',
                    'label'       => esc_attr__( 'Privacy policy text', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_privacy_message' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the text that should be shown beside the checkbox for accepting the privacy policy.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_google_recaptcha',
                    'label'       => esc_attr__( 'Google reCAPTCHA V3 site key', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_google_recaptcha' ),
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => wp_kses_post( __( 'Enter a Google reCAPTCHA key to be used on the booking page (optional). The admin console can be found <a href="https://www.google.com/recaptcha/admin/" target="_blank">here</a>.', 'casa-courses' ) ),
                ),

                array (
                    'uid'         => 'casa_courses_synchronization',
                    'label'       => esc_attr__( 'Synchronize data:', 'casa-courses' ),
                    'section'     => 'casa_courses_section_synchronization',
                    'group'       => 'casa-courses-plugin-synchronization',
                    's'           => 'casa_courses_synchronization',
                    'type'        => 'button',
                    'callback'    => 'field_callback',
                    'value'       => 'Start',
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => '',
                    'helper'      => esc_attr__( 'Synchronize data with CASA. Regular synchronization with CASA takes place automatically.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_industry_loaded',
                    'label'       => esc_attr__( 'Industry last loaded:', 'casa-courses' ),
                    'section'     => 'casa_courses_section_synchronization',
                    'group'       => 'casa-courses-plugin-synchronization',
                    's'           => 'casa_courses_industry_loaded',
                    'type'        => 'datetime',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_industry_loaded' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => esc_attr__( 'Not synchronized', 'casa-courses' ),
                    'helper'      => esc_attr__( 'Last time industry was synchronized from CASA.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_dietary_preferences_loaded',
                    'label'       => esc_attr__( 'Dietary preferences last loaded:', 'casa-courses' ),
                    'section'     => 'casa_courses_section_synchronization',
                    'group'       => 'casa-courses-plugin-synchronization',
                    's'           => 'casa_courses_dietary_preferences_loaded',
                    'type'        => 'datetime',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_dietary_preferences_loaded' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => esc_attr__( 'Not synchronized', 'casa-courses' ),
                    'helper'      => esc_attr__( 'Last time dietary preferences were synchronized from CASA.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_areas_loaded',
                    'label'       => esc_attr__( 'Areas last loaded:', 'casa-courses' ),
                    'section'     => 'casa_courses_section_synchronization',
                    'group'       => 'casa-courses-plugin-synchronization',
                    's'           => 'casa_courses_areas_loaded',
                    'type'        => 'datetime',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_areas_loaded' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => esc_attr__( 'Not synchronized', 'casa-courses' ),
                    'helper'      => esc_attr__( 'Last time areas were synchronized from CASA.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_courses_loaded',
                    'label'       => esc_attr__( 'Templates last loaded:', 'casa-courses' ),
                    'section'     => 'casa_courses_section_synchronization',
                    'group'       => 'casa-courses-plugin-synchronization',
                    's'           => 'casa_courses_courses_loaded',
                    'type'        => 'datetime',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_courses_loaded' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => esc_attr__( 'Not synchronized', 'casa-courses' ),
                    'helper'      => esc_attr__( 'Last time templates were synchronized from CASA.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_events_loaded',
                    'label'       => esc_attr__( 'Events last loaded:', 'casa-courses' ),
                    'section'     => 'casa_courses_section_synchronization',
                    'group'       => 'casa-courses-plugin-synchronization',
                    's'           => 'casa_courses_events_loaded',
                    'type'        => 'datetime',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_events_loaded' ),
                    'options'     => false,
                    'placeholder' => '',
                    'default'     => esc_attr__( 'Not synchronized', 'casa-courses' ),
                    'helper'      => esc_attr__( 'Last time events were synchronized from CASA.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_area_section_title',
                    'label'       => esc_attr__( 'Title', 'casa-courses' ),
                    'section'     => 'casa_courses__section_home',
                    'group'       => 'casa-courses-plugin-home',
                    's'           => 'casa_courses_home',
                    'rows'        => '8',
                    'type'        => 'text',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_area_section_title' ),
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the title for the course listing page.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_list_all_section_desc',
                    'label'       => esc_attr__( 'Description', 'casa-courses' ),
                    'section'     => 'casa_courses__section_home',
                    'group'       => 'casa-courses-plugin-home',
                    's'           => 'casa_courses_home',
                    'rows'        => '8',
                    'type'        => 'wp_editor',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_list_all_section_desc' ),
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the description for the course listing page.', 'casa-courses' ),
                ),

                array (
                    'uid'      => 'casa_courses_show_filter_home',
                    'label'    => esc_attr__( 'Show course area boxes', 'casa-courses' ),
                    'section'  => 'casa_courses__section_home',
                    'group'    => 'casa-courses-plugin-home',
                    's'        => 'casa_courses_home',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_show_filter_home' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'false',
                ),

                array (
                    'uid'         => 'casa_courses_registration_slug',
                    'label'       => esc_attr__( 'Booking page slug', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_registration_slug' ),
                    'required'    => true,
                    'default'     => 'book',
                    'placeholder' => '',
                ),

                array (
                    'uid'         => 'casa_courses_registration_title',
                    'label'       => esc_attr__( 'Title', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_registration_title' ),
                    'default'     => esc_attr__( 'Book', 'casa-courses' ),
                    'placeholder' => '',
                    'helper'      => esc_attr__( 'Enter the title for the booking page.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_registration_desc',
                    'label'       => esc_attr__( 'Description', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'rows'        => '8',
                    'type'        => 'wp_editor',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_registration_desc' ),
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the description for the booking page.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_submit_desc',
                    'label'       => esc_attr__( 'Booking confirmation text', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'rows'        => '8',
                    'type'        => 'wp_editor',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_submit_desc' ),
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the text that should be shown for a successful booking.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_error_desc',
                    'label'       => esc_attr__( 'Unsuccessful booking text', 'casa-courses' ),
                    'section'     => 'casa_courses__section_registration',
                    'group'       => 'casa-courses-plugin-registration',
                    's'           => 'casa_courses_registration',
                    'rows'        => '8',
                    'type'        => 'wp_editor',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_error_desc' ),
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the tex that should be shown for an unsuccessful booking.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_calendar_slug',
                    'label'       => esc_attr__( 'Courses calendar root slug', 'casa-courses' ),
                    'section'     => 'casa_courses__section_calendar',
                    'group'       => 'casa-courses-plugin-calendar',
                    's'           => 'casa_courses_calendar',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_calendar_slug' ),
                    'required'    => true,
                    'default'     => 'calendar',
                    'placeholder' => '',
                ),

                array (
                    'uid'         => 'casa_courses_calendar_title',
                    'label'       => esc_attr__( 'Title', 'casa-courses' ),
                    'section'     => 'casa_courses__section_calendar',
                    'group'       => 'casa-courses-plugin-calendar',
                    's'           => 'casa_courses_calendar',
                    'type'        => 'text',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_calendar_title' ),
                    'default'     => esc_attr__( 'Course Calendar', 'casa-courses' ),
                    'helper'      => esc_attr__( 'Enter the title for the course calendar page.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_calendar_desc',
                    'label'       => esc_attr__( 'Description', 'casa-courses' ),
                    'section'     => 'casa_courses__section_calendar',
                    'group'       => 'casa-courses-plugin-calendar',
                    's'           => 'casa_courses_calendar',
                    'rows'        => '8',
                    'type'        => 'wp_editor',
                    'placeholder' => '',
                    'callback'    => 'field_callback',
                    'value'       => get_option( 'casa_courses_calendar_desc' ),
                    'default'     => '',
                    'helper'      => esc_attr__( 'Enter the description for the course calendar page.', 'casa-courses' ),
                ),

                array (
                    'uid'         => 'casa_courses_calendar_months',
                    'label'       => esc_attr__( 'Number of months to display in calendar view', 'casa-courses' ),
                    'section'     => 'casa_courses__section_calendar',
                    'group'       => 'casa-courses-plugin-calendar',
                    's'           => 'casa_courses_calendar',
                    'callback'    => 'field_callback',
                    'type'        => 'number',
                    'value'       => get_option( 'casa_courses_calendar_months' ),
                    'placeholder' => '',
                    'default'     => 6,
                ),

                array (
                    'uid'      => 'casa_courses_show_filter_calendar',
                    'label'    => esc_attr__( 'Show course area boxes', 'casa-courses' ),
                    'section'  => 'casa_courses__section_calendar',
                    'group'    => 'casa-courses-plugin-calendar',
                    's'        => 'casa_courses_calendar',
                    'callback' => 'field_callback',
                    'type'     => 'toggle',
                    'value'    => get_option( 'casa_courses_show_filter_calendar' ),
                    'options'  => array (
                        esc_attr__( 'yes', 'casa-courses' ) => 'true',
                        esc_attr__( 'no', 'casa-courses' )  => 'false',
                    ),
                    'default'  => 'false',
                ),

                array (
                    'uid'         => 'casa_courses_header_template',
                    'label'       => esc_attr__( 'Header template', 'casa-courses' ),
                    'section'     => 'casa_courses__section_header_footer',
                    'group'       => 'casa-courses-plugin-header-footer',
                    's'           => 'casa_courses_section_header_footer',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'value'       => get_option( 'casa_courses_header_template' ),
                    'default'     => '',
                    'helper'      => 'Block themes use the header template, which is specified in this field',
                    'required'    => true,
                    'placeholder' => '',
                ),

                array (
                    'uid'         => 'casa_courses_footer_template',
                    'label'       => esc_attr__( 'Footer template', 'casa-courses' ),
                    'section'     => 'casa_courses__section_header_footer',
                    'group'       => 'casa-courses-plugin-header-footer',
                    's'           => 'casa_courses_section_header_footer',
                    'callback'    => 'field_callback',
                    'type'        => 'text',
                    'required'    => true,
                    'helper'      => 'Block themes use the footer template, which is specified in this field',
                    'value'       => get_option( 'casa_courses_footer_template' ),
                    'default'     => '',
                    'placeholder' => '',
                ),
            );

            foreach ( $fields as $field ) {
                register_setting( $field[ 's' ], $field[ 'uid' ] );

                add_settings_field(
                    $field[ 'uid' ],
                    $field[ 'label' ],
                    array (
                        $this,
                        $field[ 'callback' ]
                    ),
                    $field[ 'group' ],
                    $field[ 'section' ],
                    $field
                );
            }
        } );
    }

    /**
     * Register Menu
     * @return void
     */
    public function register_menu(): void
    {

        $token = get_option( 'casa_courses_token' );

        add_menu_page(
            'Casa Page',
            esc_attr__( 'Casa settings', 'casa-courses' ),
            'manage_options',
            'casa-courses-plugin'
        );
        if ( $token ) {
            add_submenu_page(
                'casa-courses-plugin',
                esc_attr__( 'Settings', 'casa-courses' ),
                esc_attr__( 'Settings', 'casa-courses' ),
                'manage_options',
                'casa-courses-plugin',
                [
                    $this,
                    'submenu_page_setting_callback'
                ]
            );

            add_submenu_page(
                'casa-courses-plugin',
                esc_attr__( 'Styling', 'casa-courses' ),
                esc_attr__( 'Styling', 'casa-courses' ),
                'manage_options',
                'casa-courses-plugin-styling',
                array (
                    $this,
                    'submenu_page_style_callback'
                )
            );

            add_submenu_page(
                'casa-courses-plugin',
                esc_attr__( 'Token', 'casa-courses' ),
                esc_attr__( 'Token', 'casa-courses' ),
                'manage_options',
                'casa-courses-plugin-token',
                array (
                    $this,
                    'menu_page_token_callback'
                )
            );
        } else {
            add_submenu_page(
                'casa-courses-plugin',
                esc_attr__( 'Token', 'casa-courses' ),
                esc_attr__( 'Token', 'casa-courses' ),
                'manage_options',
                'casa-courses-plugin',
                array (
                    $this,
                    'menu_page_token_callback'
                )
            );
        }
    }

    /**
     * Render Menu Callback
     * @return void
     */
    public function menu_page_token_callback(): void
    {
        /**
         * The form to be loaded on the plugin's admin page
         */
        if ( current_user_can( 'manage_options' ) ) :
            $token = get_option( 'casa_courses_token' );
            $error = get_option( 'casa_courses_token_error' );
            // Generate a custom nonce value.
            $meta_nonce = wp_create_nonce( 'casa_courses_general_form_nonce' );
            $allow_a_tag = [
                'a' => [
                    'href' => [],
                    'title' => []
                ]
            ];

            echo '<div class="wrap casa__general">
				<h2>' . esc_attr__( 'Token settings', 'casa-courses' ) . '</h2><div class="casa__general_token">
				<h3 class="casa__general_token-title">' . esc_attr__( 'Status', 'casa-courses' ) . ': ';
            if ( !empty( $token ) ) :
                echo '<span class="sp__status casa__status-active">';
                esc_attr_e( 'Active', 'casa-courses' );
            else :
                echo '<span class="sp__status casa__status-inactive">';
                esc_attr_e( 'Inactive', 'casa-courses' );
            endif;
            echo '</span></h3><form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" id="casa__general_form" >
				<input type="hidden" name="action" value="casa_courses_general_form_response">
				<input type="hidden" name="casa_courses_general_nonce" value="' . esc_attr( $meta_nonce ) . '" />';
            $this->casa_courses_token_field_html();
            if ( empty( $token ) ) {
                submit_button( esc_attr__( 'Add Token', 'casa-courses' ) );
            } else {
                submit_button( esc_attr__( 'Remove Token', 'casa-courses' ), 'ps-remove__token' );
            }

            if ( !empty( $error ) ) :
                echo '<br><span class="ps-token-error">' . esc_attr( $error ) . '</span>';
                update_option( 'casa_courses_token_error', '' );
            endif;

            echo '</form></div>';

            echo '<div class="casa_token_info">';
            echo wp_kses( __( 'Your unique Token is generated by the Casa Team. Please contact <a href="mailto:support@casaclient.se">support@casaclient.se</a> if you need any assistance.', 'casa-courses' ), $allow_a_tag);
            echo '</div>';
        else :
            echo '<p>' . esc_attr__( "You are not authorized to perform this operation.", 'casa-courses' ) . '</p>';
        endif;
    }

    /** token field */
    public function casa_courses_token_field_html(): void
    {
        $token = get_option( 'casa_courses_token' );
        $domain = get_option( 'casa_courses_domain' );
        $project_id = get_option( 'casa_courses_project_id' );

        echo '<div class="ps-field-group">';
        echo sprintf(
            '<label class="ps-field-label" for="casa_courses_domain">' . esc_attr__( 'Domain:', 'casa-courses' ) . ' <input required class="regular-text" type="text" id="casa_courses_domain" name="casa_courses_domain" ' . ( $domain ? 'placeholder="%1$s" readonly' : 'placeholder="' . esc_attr__( 'Insert API Domain', 'casa-courses' ) . '"' ) . ' />
            </label><label class="ps-field-label" for="casa_courses_domain">' . esc_attr__( 'Project ID:', 'casa-courses' ) . ' <input class="regular-text" type="text" id="casa_courses_project_id" name="casa_courses_project_id" ' . ( $project_id ? 'placeholder="%2$s" readonly' : 'placeholder="' . esc_attr__( 'Insert Project ID', 'casa-courses' ) . '"' ) . ' />
            </label><label class="ps-field-label" for="casa_courses_token">' . esc_attr__( 'Token:', 'casa-courses' ) . ' <input required class="regular-text" type="text" id="casa_courses_token" name="casa_courses_token" ' . ( $token ? 'placeholder="%3$s" readonly' : 'placeholder="' . esc_attr__( 'Insert API Token', 'casa-courses' ) . '"' ) . ' /></label>',
            esc_attr( $domain ),
            esc_attr( $project_id ),
            esc_attr( $token )
        );
        echo '</div>';
    }

    public function field_callback( $arguments ): void
    {
        $required = '';
        $value = $arguments[ 'value' ]; // Get the current value, if there is one
        if ( !$value ) { // If no value exists
            $value = $arguments[ 'default' ]; // Set to our default
        }

        if ( isset( $arguments[ 'required' ] ) && $arguments[ 'required' ] ) {
            $required = 'required';
        }

        switch ( $arguments[ 'type' ] ) {
            case 'shortcode':
                echo '<div class="casa_integration-shortcode-wrap">';
                printf( '<input style="color: #ACACAC;border: 1px solid #ACACAC;" readonly name="%1$s" id="%1$s" type="%2$s" value="%3$s" />', esc_attr( $arguments[ 'uid' ] ), 'text', esc_attr( $value ) );
                printf( '<input type="button" name="button" class="button casa_shortcode-btn casa_input" value="Copy">' );
                echo '</div>';
                break;
            case 'datetime':
                printf( '<p>%1$s</p><br>', esc_attr( self::format_date_time ( $value ) ) );
                break;
            case 'helper':
                printf( '<p>%1$s</p>', esc_attr( $arguments[ 'title' ] ) );
                printf( '<p>%1$s</p>', esc_attr( $value ) );
                break;
            case 'number': // If it is a number field
                printf( '<input step="1" min="0" name="%1$s" class="casa_input" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', esc_attr( $arguments[ 'uid' ] ), esc_attr( $arguments[ 'type' ] ), esc_attr( $arguments[ 'placeholder' ] ), esc_attr( $value ) );
                break;
            case 'url': // If it is a number field
                printf( '<input name="%1$s" class="casa_input" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', esc_attr( $arguments[ 'uid' ] ), esc_attr( $arguments[ 'type' ] ), esc_attr( $arguments[ 'placeholder' ] ), esc_attr( $value ) );
                break;
            case 'text': // If it is a text field
                printf( '<input name="%1$s" class="casa_input" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" %5$s />', esc_attr( $arguments[ 'uid' ] ), esc_attr( $arguments[ 'type' ] ), esc_attr( $arguments[ 'placeholder' ] ), esc_attr( $value ), esc_attr( $required ) );
                break;
            case 'textarea': // If it is a textarea
                echo '<div class="casa_textarea_wrap">';
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="%4$s" cols="50">%3$s</textarea>', esc_attr( $arguments[ 'uid' ] ), esc_attr( $arguments[ 'placeholder' ] ), esc_attr( $value ), esc_attr( $arguments[ 'rows' ] ) );
                echo '</div>';
                break;
            case 'wp_editor':
                ob_start();
                wp_editor( $value, $arguments[ 'uid' ], array (
                    'textarea_name' => $arguments[ 'uid' ],
                    'textarea_rows' => $arguments[ 'rows' ],
                    'tinymce'       => array (
                        'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,spellchecker,wp_adv',
                        'toolbar2' => 'formatselect,fontsizeselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                    ),
                ) );

                echo wp_kses_post( ob_get_clean() );

                break;
            case 'image':
                if ( $value ) {
                    $att_image = wp_get_attachment_image_src( $value, 'medium' );
                    $image = $att_image[ 0 ];
                } ?>
                <div class="casa_image_wrap <?php echo esc_attr( $arguments[ 'uid' ] ) ?>-wrap">
                    <input type="hidden" name="<?php echo esc_attr( $arguments[ 'uid' ] ) ?>"
                           value="<?php echo esc_attr( $value ) ?>" id="<?php echo esc_attr( $arguments[ 'uid' ] ) ?>"
                           class="casa-courses-image">
                    <div class="img-wrap">
                        <img src="<?php echo !empty( $image ) ? esc_attr( $image ) : ''; ?>" id="casa_courses_image-view">
                    </div>
                    <div class="casa_courses-file-action">
                        <input class="upload_image_button button casa-add-image"
                               name="_add_image_<?php echo esc_attr( $arguments[ 'uid' ] ) ?>"
                               id="_add_image_<?php echo esc_attr( $arguments[ 'uid' ] ) ?>" type="button"
                               value="<?php esc_attr_e( 'Select Image', 'casa-courses' ); ?>"/>
                        <span class="casa__courses-link" <?php if ( empty( $value ) ) echo 'style="display: none;"'; ?>>
                            <a class="delete casa-remove-image" id="_remove_image_<?php echo esc_attr( $arguments[ 'uid' ] ) ?>"
                               type="button"
                               title="<?php esc_attr_e( 'Remove Image', 'casa-courses' ); ?>">
                                <?php esc_attr_e( 'Remove Image', 'casa-courses' ); ?>
                            </a>
                        </span>
                    </div>
                </div>
                <?php
                break;
            case 'toggle': // If it is a textarea
                echo '<div class="casa_textarea_wrap">';
                $html = '<toggle>';

                foreach ( $arguments[ 'options' ] as $key => $option ) {
                    $html .= '<label><input data-tabclass="" ' .
                        'required' . ' type="radio" name="' . esc_attr( $arguments[ 'uid' ] ) . '"' . ( $option == $value ? ' checked="checked"' : '' ) . ' value="' . esc_attr( $option ) . '" /><div>' . $key . '</div></label>';
                }

                $html .= '</toggle>';
                $allowed_html = array(
                    'toggle' => array(),
                    'label' => array(),
                    'input' => array(
                         'data-tabclass' => true,
                         'name' => true,
                         'type' => true,
                         'checked' => true,
                         'value' => true,
                         'required' => true,
                    ),
                    'div' => array(),    // Allow <div>
                );

                echo wp_kses( $html, $allowed_html ) . '</div>';
                break;
            case 'select': // If it is a select dropdown
                if ( !empty( $arguments[ 'options' ] ) && is_array( $arguments[ 'options' ] ) ) {
                    $options_markup = '';
                    foreach ( $arguments[ 'options' ] as $key => $label ) {
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_attr( $label ) );
                    }
                    $allowed_html = array(
                        'option' => array(
                            'value' => true,
                            'selected' => true
                        )
                    );
                    printf( '<select name="%1$s" class="casa_input" id="%1$s">%2$s</select>', esc_attr( $arguments[ 'uid' ] ), wp_kses( $options_markup, $allowed_html ) );
                }
                break;
            case 'color':
                printf(
                    '<input type="text" name="%1$s" id="%1$s" value="%2$s" class="widefat color_field" />',
                    esc_attr( $arguments[ 'uid' ] ),
                    esc_attr( $value )
                );
                break;
            case 'button':
                printf(
                    '<button name="%1$s" id="%1$s" class="widefat button button-primary">%2$s</button>',
                    esc_attr( $arguments[ 'uid' ] ),
                    esc_attr( $value )
                );
                break;
        }

        // If there is help text
        if ( isset( $arguments[ 'helper' ] ) ) {
            printf( '<div class="helper"> %s</div>', wp_kses_post( $arguments[ 'helper' ] ) ); // Show it
        }

        // If there is supplemental text
        if ( isset( $arguments[ 'supplemental' ] ) ) {
            printf( '<p class="description">%s</p>', esc_attr( $arguments[ 'supplemental' ] ) ); // Show it
        }
    }

    /**
     * Render submenu Styling
     * @return void
     */
    public function submenu_page_style_callback(): void
    {
        $metabox = [
            [
                'key'   => 'tab_course_style_general',
                'label' => esc_attr__( 'General', 'casa-courses' )
            ],
            [
                'key'   => 'tab_course_style_listing',
                'label' => esc_attr__( 'Course listing', 'casa-courses' )
            ],
            [
                'key'   => 'tab_course_style_detail',
                'label' => esc_attr__( 'Course detail', 'casa-courses' )
            ],
            [
                'key'   => 'tab_course_style_booking',
                'label' => esc_attr__( 'Course booking', 'casa-courses' )
            ],
        ];

        $nonce = wp_create_nonce( 'ajax-nonce' );
        echo '<div class="wrap casa_integration_wrap" data-nonce="' . esc_attr( $nonce ) . '">';
        echo '<h2>' . esc_attr__( 'Styling', 'casa-courses' ) . '</h2>';
        echo '<div class="casa_integration_flex">';
        echo '<ul class="com-tab-group">';
        foreach ( $metabox as $key => $element ) :
            echo '<li class="' . ( $key === 0 ? 'active' : '' ) . ' ' . esc_attr( $element[ 'key' ] ) . '">
                <a href="" class="com-tab-button" data-endpoint="0" data-key="' . esc_attr( $element[ 'key' ] ) . '">' . esc_attr( $element[ 'label' ] ) . '</a>
                </li>';
        endforeach;
        echo '</ul>';

        $this->get_box_html(
            __( 'General', 'casa-courses' ),
            'casa_courses_style',
            'casa-courses-plugin-style',
            'tab_course_style_general',
            true
        );

        $this->get_box_html(
            __( 'Course listing', 'casa-courses' ),
            'casa_courses_style_listing',
            'casa-courses-plugin-style-listing',
            'tab_course_style_listing',
            true
        );

        $this->get_box_html(
            __( 'Course detail', 'casa-courses' ),
            'casa_courses_style_detail',
            'casa-courses-plugin-style-detail',
            'tab_course_style_detail',
            true
        );

        $this->get_box_html(
            __( 'Course booking', 'casa-courses' ),
            'casa_courses_style_booking',
            'casa-courses-plugin-style-booking',
            'tab_course_style_booking',
            true
        );

        echo '</div></div>';
    }

    /**
     * Render submenu Settings
     * @return void
     */
    public function submenu_page_setting_callback(): void
    {
        $meta_box = [
            [
                'key'   => 'tab_info_page',
                'label' => __( 'General', 'casa-courses' )
            ],
            [
                'key'   => 'tab_home_page',
                'label' => __( 'Course list Page', 'casa-courses' )
            ],
            [
                'key'   => 'tab_calendar_page',
                'label' => __( 'Calendar Page', 'casa-courses' )
            ],
            [
                'key'   => 'tab_reg_page',
                'label' => __( 'Booking page', 'casa-courses' )
            ],
            [
                'key'   => 'tab_sync',
                'label' => __( 'Sync', 'casa-courses' )
            ],
            [
                'key'   => 'tab_header_footer',
                'label' => __( 'Header/Footer', 'casa-courses' )
            ],
        ];
        $nonce = wp_create_nonce( 'ajax-nonce' );
        echo '<div class="wrap casa_integration_wrap" data-nonce="' . esc_attr( $nonce ) . '">';
        echo '<h2>' . esc_attr__( 'Settings', 'casa-courses' ) . '</h2>';
        echo '<div class="casa_integration_flex">';
        echo '<ul class="com-tab-group">';

        foreach ( $meta_box as $key => $element ) :
            echo '<li class="' . ( $key === 0 ? 'active' : '' ) . ' ' . esc_attr( $element[ 'key' ] ) . '">
                <a href="" class="com-tab-button" data-endpoint="0" data-key="' . esc_attr( $element[ 'key' ] ) . '">' . esc_attr( $element[ 'label' ] ) . '</a>
                </li>';
        endforeach;
        echo '</ul>';

        $this->get_box_html(
            __( 'Settings', 'casa-courses' ),
            'casa_courses_settings',
            'casa-courses-plugin-information',
            'tab_info_page',
            true
        );

        $this->get_box_html(
            __( 'Course list Page Settings', 'casa-courses' ),
            'casa_courses_home',
            'casa-courses-plugin-home',
            'tab_home_page',
            true
        );

        $this->get_box_html(
            __( 'Calendar Page Settings', 'casa-courses' ),
            'casa_courses_calendar',
            'casa-courses-plugin-calendar',
            'tab_calendar_page',
            true
        );

        $this->get_box_html(
            __( 'Registration Page Settings', 'casa-courses' ),
            'casa_courses_registration',
            'casa-courses-plugin-registration',
            'tab_reg_page',
            true
        );

        $this->get_box_html(
            __( 'Synchronization', 'casa-courses' ),
            'casa_courses_synchronization',
            'casa-courses-plugin-synchronization',
            'tab_sync',
            false
        );

        $this->get_box_html(
            __( 'Header/Footer', 'casa-courses' ),
            'casa_courses_section_header_footer',
            'casa-courses-plugin-header-footer',
            'tab_header_footer',
            true
        );

        echo '</div></div>';
    }

    public function get_box_html( $title, $fields, $section, $tab, $submit = true ): void
    {
        if ( $tab ) :
            echo '<div class="metadata-wrap ' . esc_attr( $tab ) . ' ' . ( ( $tab === 'tab_info_page' || $tab === 'tab_course_style_general' ) ? 'active' : 'hidden' ) . '">';
        endif;
        echo '<div class="casa_integration_wrap-box">';
        echo '<div class="casa_integration-box-header" data-option="' . esc_attr( $fields ) . '_status' . '">';
        echo '<h2>' . esc_attr( $title ) . '</h2>';
        echo '</div>';
        echo '<div class="casa_integration-box-body">';
        echo '<form method="post" action="options.php" class="form_' . esc_attr( $fields ) . '">';
        if ( $submit ) :
            settings_fields( $fields ); // settings group name
            do_settings_sections( $section ); // just a page slug
            submit_button();
        else :
            do_settings_sections( $section );
        endif;
        echo '</form></div></div>';
        if ( $tab ) :
            echo '</div>';
        endif;
    }

    /**
     * Formats a given UTC time to the format specified in WordPress options.
     *
     * @param string|int $utc_time The UTC time to format.
     *
     * @return string The formatted date/time string.
     * @since 1.0.0
     */
    static function format_date_time( $utc_time ): string
    {
        try {
            // Retrieve the timezone string from WordPress options
            $wp_timezone = get_option( 'timezone_string' );

            // Create DateTimeZone object for WordPress timezone
            $timezone = new DateTimeZone( $wp_timezone );

            // Create DateTime object for last sync, correct it to WordPress timezone
            $date_time_utc = new DateTime( $utc_time, new DateTimeZone( 'UTC' ) );
            $date_time_utc->setTimezone( $timezone );

            // Format the date/time string according to your requirements
            $formatted_time = $date_time_utc->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

        } catch ( Exception $e ) {
            $formatted_time = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $utc_time );
        }

        return $formatted_time;
    }
}
