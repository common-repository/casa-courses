<?php

use BooMeta\BooMeta;

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Casa_Courses_Custom_Posttype_Courses
 *
 * This class is responsible for setting up the custom post type 'casa-courses' for courses.
 */
class Casa_Courses_Custom_Posttype_Courses
{
    public static string $post_type = 'casa_courses';

    /**
     * Set up the Casa_Courses post type and register metadata fields.
     *
     * @return void
     * @since 1.0.0
     */
    public static function casa_courses_setup_post_type(): void
    {
        /**
         * Post Type: Casa_Courses.
         */
        $show_in_menu = false;
        $token = get_option( 'casa_courses_token' );
        $slug = get_option( 'casa_courses_courses_slug' );

        if ( is_admin() && current_user_can( 'manage_options' ) && !empty( $token ) ) {
            $show_in_menu = true;
        }
        $args = [
            "label"                 => __( "Courses", 'casa-courses' ),
            "labels"                => [
                "name"          => __( "Courses", 'casa-courses' ),
                "singular_name" => __( "Course", 'casa-courses' ),
                'menu_name'     => __( "Casa courses", 'casa-courses' ),
                'all_items'     => __( 'Courses', 'casa-courses' ),
            ],
            "description"           => "",
            "public"                => true,
            "publicly_queryable"    => true,
            "show_ui"               => true,
            "show_in_rest"          => false,
            "rest_base"             => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive"           => false,
            "show_in_menu"          => $show_in_menu,
            "show_in_nav_menus"     => true,
            "delete_with_user"      => false,
            "exclude_from_search"   => true,
            "capability_type"       => "post",
            "capabilities"          => array (
                'create_posts'           => 'do_not_allow',
                'delete_posts'           => 'do_not_allow',
                'delete_published_posts' => 'do_not_allow'
            ),
            "map_meta_cap"          => true,
            "hierarchical"          => false,
            "rewrite"               => [
                "slug"       => $slug . "/%area%",
                "with_front" => true
            ],
            "query_var"             => true,
            "menu_icon"             => "dashicons-book",
            "supports"              => [
                "title",
                "editor"
            ],
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
                        'attr'      => 'readonly',
                        'tab_class' => 'price_tab',
                    ),
                    array (
                        'slug'      => 'currency',
                        'label'     => esc_attr__( 'Currency', 'casa-courses' ),
                        'type'      => 'text',
                        'attr'      => 'readonly',
                        'tab_class' => 'price_tab',
                    ),
                    array (
                        'slug'      => 'invoice_type',
                        'label'     => esc_attr__( 'Invoice Type', 'casa-courses' ),
                        'type'      => 'text',
                        'attr'      => 'readonly',
                        'tab_class' => 'price_tab',
                    ),
                    array (
                        'slug'      => 'extra_costs_price',
                        'label'     => esc_attr__( 'Extra costs', 'casa-courses' ),
                        'type'      => 'json',
                        'attr'      => 'readonly',
                        'tab_class' => 'extra_costs_tab',
                    ),
                    array (
                        'slug'      => 'id',
                        'label'     => esc_attr__( 'ID', 'casa-courses' ),
                        'type'      => 'text',
                        'attr'      => 'readonly',
                        'tab_class' => 'description_tab',
                    ),
                    array (
                        'slug'      => 'number_of_days',
                        'label'     => esc_attr__( 'Number of days', 'casa-courses' ),
                        'type'      => 'text',
                        'tab_class' => 'description_tab',
                        'attr'      => 'readonly',
                    ),
                    array (
                        'slug'      => 'description',
                        'label'     => esc_attr__( 'Description', 'casa-courses' ),
                        'type'      => 'json',
                        'tab_class' => 'description_tab',
                        'attr'      => 'readonly',
                    ),
                ),
                'tabs'     => array (
                    array (
                        'key'   => 'description_tab',
                        'label' => esc_attr__( 'Description', 'casa-courses' ),
                    ),
                    array (
                        'key'   => 'price_tab',
                        'label' => esc_attr__( 'Price', 'casa-courses' ),
                    ),
                    array (
                        'key'   => 'extra_costs_tab',
                        'label' => esc_attr__( 'Extra costs', 'casa-courses' ),
                    ),
                ),
            ),
        ), self::$post_type );
    }

    /**
     * Register the post type template for Casa_Courses.
     *
     * This method adds filters to modify the post type link and single template.
     *
     * @return void
     * @since 1.0.0
     */
    public static function register_post_type_template(): void
    {

        add_filter( 'post_type_link', function ( $post_link, $id = 0 ) {
            $post = get_post( $id );

            if ( is_object( $post ) ) {
                $terms = wp_get_object_terms( $post->ID, 'casa_courses_areas' );

                if ( $terms ) {
                    return str_replace( '%area%', $terms[ 0 ]->slug, $post_link );
                }
            }
            return $post_link;
        }, 1, 3 );


        add_filter( 'single_template', function ( $single ) {
            if ( is_singular( self::$post_type ) ) {
                if ( get_option( 'casa_course_disable_caching' ) === 'true' ) {
                    header( 'Cache-Control: no-cache, no-store, must-revalidate' );
                    header( 'Pragma: no-cache' );
                    header( 'Expires: 0' );
                }

                if ( $template = locate_template( 'single-course.php' ) ) {
                    return $template;
                } else {
                    return plugin_dir_path( dirname( __FILE__ ) ) . '../templates/single-course.php';
                }
            }

            return $single;
        }, 20 );
    }
}
