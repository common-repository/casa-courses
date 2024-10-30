<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class Class_Casa_Routes
 *
 * This class handles the routing logic for the Casa Courses plugin.
 */
class Casa_Courses_Routes
{

    private string $title = '';

    /**
     * Initializes the method by adding action hooks and filters for WordPress initialization.
     *
     * This method adds the following action hooks and filters:
     * - 'init' action hook to call the 'rewrite_rules' method of the current object.
     * - 'template_include' filter hook to call the 'include_template' method of the current object with priority 999 and 1 argument.
     * - 'query_vars' filter hook to add a custom query variable called "current_page".
     *
     * @return void
     * @since 1.0.0
     */
    public function init(): void
    {
        add_action( 'init', array (
            $this,
            'rewrite_rules'
        ) );
        add_filter( 'template_include', array (
            $this,
            'include_template'
        ), 999, 1 );
        add_filter( 'query_vars', function ( $vars ) {
            $vars[] = "current_page";
            return $vars;
        } );
        add_filter( 'pre_get_document_title', array (
            $this,
            'set_courses_title'
        ) );
        add_filter( 'document_title_separator', array (
            $this,
            'set_title_separator'
        ) );

        add_action( 'send_headers', array (
            $this,
            'add_no_caching'
        ) );
    }

    public function add_no_caching(): void
    {
        $page_name = get_query_var('pagename');
        global $post;

        if ( ( $page_name === 'courses' || ($post && $post->post_type === Casa_Courses_Custom_Posttype_Courses::$post_type ) ) && get_option( 'casa_course_disable_caching' ) === 'true' ) {
            header( 'Cache-Control: no-cache, no-store, must-revalidate' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );
        }
    }

    /**
     * Includes the appropriate template file based on the current page and query variables.
     *
     * This method is called by the 'template_include' filter hook and is used to determine
     * which template file should be loaded based on the current page and query variables.
     * It checks the query variables 'pagename' and 'current_page' to determine the correct
     * page template to use. If no specific page template is found, it falls back to a default
     * template based on the current theme or plugin.
     *
     * @param string $template The default template file.
     * @return string The template file to include.
     * @since 1.0.0
     */
    public function include_template( string $template ): string
    {
        $page_name = get_query_var( 'pagename' );

        if ( $page_name === 'courses' ) {
            global $wp_query;

            $page = get_query_var( 'current_page' );
            $theme_path = get_template_directory();
            $calendar_slug = get_option( 'casa_courses_calendar_slug' );
            $registration_slug = get_option( 'casa_courses_registration_slug' );

            status_header( 200 );
            $wp_query->is_404 = false;

            if ( $page === $calendar_slug ) {
                $this->title = get_option( 'casa_courses_calendar_title', '' );

                if ( file_exists( $theme_path . '/page-templates/course-calendar.php' ) ) {
                    return $theme_path . '/page-templates/course-calendar.php';
                }

                return plugin_dir_path( __FILE__ ) . '../templates/course-calendar.php';
            }

            if ( $page === $registration_slug ) {
                $this->title = __( 'Book', 'casa-courses' );

                if ( file_exists( $theme_path . '/page-templates/course-registration.php' ) ) {
                    return $theme_path . '/page-templates/course-registration.php';
                }

                return plugin_dir_path( __FILE__ ) . '../templates/course-registration.php';
            }

            if ( $page === '' ) {
                $this->title = get_option( 'casa_courses_area_section_title', '' );

                if ( file_exists( $theme_path . '/page-templates/course-home.php' ) ) {
                    return $theme_path . '/page-templates/course-home.php';
                }

                return plugin_dir_path( __FILE__ ) . '../templates/course-home.php';
            } else {
                $term = get_term_by( 'slug', sanitize_text_field( get_query_var( 'current_page' ) ), Casa_Courses_Custom_Taxonomy_Areas::$tax_type );

                if ( $term && !is_wp_error( $term ) ) {
                    $this->title = $term->name;
                }

                if ( file_exists( $theme_path . '/page-templates/course-area.php' ) ) {
                    return $theme_path . '/page-templates/course-area.php';
                }

                return plugin_dir_path( __FILE__ ) . '../templates/course-area.php';
            }
        }

        return $template;
    }

    /**
     * Flushes the rewrite rules and updates them in the database.
     *
     * This method calls the 'rewrite_rules' method of the current object to update the rewrite rules.
     * It then calls 'flush_rewrite_rules()' function to flush and save the updated rules in the database.
     *
     * @return void
     * @since 1.0.0
     */
    public function flush_rules(): void
    {
        $this->rewrite_rules();
        flush_rewrite_rules();
    }

    /**
     * Adds rewrite rules for the custom post type "courses".
     *
     * This method adds the following rewrite rules:
     * - A rewrite rule that matches URLs in the format "{slug}/(.*)/(.*)/?" and rewrites them to "index.php?post_type={post_type}&name=$matches[2]".
     * - A rewrite rule that matches URLs in the format "{slug}/([^/]+)/?" and rewrites them to "index.php?pagename=courses&current_page=$matches[1]".
     * - A rewrite rule that matches URLs in the format "{slug}/?" and rewrites them to "index.php?pagename=courses".
     *
     * @return void
     * @since 1.0.0
     */
    public function rewrite_rules(): void
    {
        $slug = get_option( 'casa_courses_courses_slug' );
        $post_type = Casa_Courses_Custom_Posttype_Courses::$post_type;

        add_rewrite_rule( '^' . $slug . '/(.*)/(.*)/?$', 'index.php?post_type=' . $post_type . '&name=$matches[2]', 'top' );
        add_rewrite_rule( '^' . $slug . '/([^/]+)/?', 'index.php?pagename=courses&current_page=$matches[1]', 'top' );
        add_rewrite_rule( '^' . $slug . '/?', 'index.php?pagename=courses', 'top' );
    }

    /**
     * Sets the title of the document.
     *
     * This method takes the default title as an argument and returns
     * the custom title if it is not empty, otherwise it returns the default title.
     *
     * @param string $default_title The default title of the document.
     * @return string The title of the document.
     * @since 1.0.0
     */
    public function set_courses_title( string $default_title ): string
    {
        $casa_separator = get_option( 'casa_courses_title_separator', '|' );
        return !empty( $this->title ) ? $this->title . ' ' . $casa_separator . ' ' . get_bloginfo('name') : $default_title;
    }

    /**
     * Sets the title separator for the document.
     *
     * This method retrieves the custom separator specified in the WordPress options and returns it as the new title separator.
     * If the current page is a singular post of the custom post type "Casa_Courses_Custom_Posttype_Courses", the custom separator is added
     * to the title separator.
     *
     * @param string $default_separator The default title separator.
     * @return string The new title separator.
     * @since 1.0.0
     */
    public function set_title_separator( string $default_separator ): string
    {
        $casa_separator = get_option( 'casa_courses_title_separator', '|' );
        return is_singular( Casa_Courses_Custom_Posttype_Courses::$post_type ) ? ' ' . $casa_separator . ' ' : $default_separator;
    }
}
