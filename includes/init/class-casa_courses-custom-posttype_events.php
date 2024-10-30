<?php

use BooMeta\BooMeta;

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Casa_Courses_Custom_Posttype_Events class.
 */
class Casa_Courses_Custom_Posttype_Events
{

    public static string $post_type = "casa_events";

    /**
     * Sets up the "Casa_Courses" post type and registers metadata fields.
     *
     * @return void
     * @since 1.0.0
     */
    public static function casa_courses_setup_post_type(): void
    {
        /**
         * Post Type: Casa_Courses.
         */
        $args = [
            "label"                 => __( "Events", 'casa-courses' ),
            "labels"                => [
                "name"          => __( "Event", 'casa-courses' ),
                "singular_name" => __( "Events", 'casa-courses' ),
                'menu_name'     => __( "Casa Events", 'casa-courses' ),
                'all_items'     => 'Events',
            ],
            "description"           => "",
            "public"                => false,
            "publicly_queryable"    => false,
            "show_ui"               => true,
            "show_in_rest"          => false,
            "rest_base"             => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive"           => false,
            "show_in_menu"          => false,
            "show_in_nav_menus"     => true,
            "delete_with_user"      => false,
            "exclude_from_search"   => true,
            "capability_type"       => "post",
            "map_meta_cap"          => true,
            "hierarchical"          => false,
            "query_var"             => true,
            "menu_icon"             => "dashicons-format-image",
            "supports"              => [ "title" ],
            "show_in_graphql"       => false,
            "register_meta_box_cb"  => function () {
                ( BooMeta::get_instance_meta_box( self::$post_type ) )->add_meta_boxes();
            },
        ];

        register_post_type( self::$post_type, $args );

        global $casa_boo_meta;

        $casa_boo_meta->fields( array (
            array (
                'slug'     => 'details',
                'label'    => esc_attr__( 'Details', 'casa-courses' ),
                'position' => 'normal',
                'metadata' => array (

                    array (
                        'slug'      => 'price',
                        'label'     => esc_attr__( 'Price', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'price_tab',
                    ),
                    array (
                        'slug'      => 'currency',
                        'label'     => esc_attr__( 'Currency', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'price_tab',
                    ),
                    array (
                        'slug'      => 'invoice_type',
                        'label'     => esc_attr__( 'Invoice Type', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'price_tab',
                    ),
                    array (
                        'slug'      => 'start_date',
                        'label'     => esc_attr__( 'Start Date', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'dates_tab',
                    ),
                    array (
                        'slug'      => 'end_date',
                        'label'     => esc_attr__( 'End Date', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'dates_tab',
                    ),
                    array (
                        'slug'      => 'next_available_date',
                        'label'     => esc_attr__( 'Next available date', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'dates_tab',
                    ),
                    array (
                        'slug'      => 'available_date_timezone',
                        'label'     => esc_attr__( 'Timezone', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'dates_tab',
                    ),
                    array (
                        'slug'      => 'id',
                        'label'     => esc_attr__( 'ID', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'template_id',
                        'label'     => esc_attr__( 'Template ID', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'event_name',
                        'label'     => esc_attr__( 'Event Name', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'max_participant_count',
                        'label'     => esc_attr__( 'Max Participant Count', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'available_seats',
                        'label'     => esc_attr__( 'Available seats', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'venue_city',
                        'label'     => esc_attr__( 'Venue City', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'venue_name',
                        'label'     => esc_attr__( 'Venue Name', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'order',
                        'label'     => esc_attr__( 'Order', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'information_tab',
                    ),
                    array (
                        'slug'      => 'sessions',
                        'label'     => esc_attr__( 'Sessions', 'casa-courses' ),
                        'type'      => 'json',
                        'tab_class' => 'sessions_tab',
                    ),
                ),
                'tabs'     => array (
                    array (
                        'key'   => 'price_tab',
                        'label' => esc_attr__( 'Price', 'casa-courses' ),
                    ),
                    array (
                        'key'   => 'dates_tab',
                        'label' => esc_attr__( 'Dates', 'casa-courses' ),
                    ),
                    array (
                        'key'   => 'information_tab',
                        'label' => esc_attr__( 'Information', 'casa-courses' ),
                    ),
                    array (
                        'key'   => 'sessions_tab',
                        'label' => esc_attr__( 'Sessions', 'casa-courses' ),
                    ),
                ),
            ),
        ), self::$post_type );
    }
}
