<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Represents the Casa Courses Admin class. Provides functionality for managing the admin area of the Casa Courses plugin.
 */
class Casa_courses_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private string $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private string $version;

    /**
     * Constructor method for the class.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of the plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct( string $plugin_name, string $version )
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Add the plugin settings link to the plugins page.
     *
     * This function adds a link to the plugin settings page in the
     * WordPress admin panel. The link is appended to the existing
     * array of links and returned.
     *
     * @param array $links Existing array of plugin action links.
     *
     * @return    array              Updated array of plugin action links,
     *                               including the plugin settings link.
     * @since     1.0.0
     *
     */
    public function add_plugin_settings_link( array $links ): array
    {
        $settings_link = '<a href="admin.php?page=casa-courses-plugin">' . esc_attr__( 'Settings' ) . '</a>';
        $links[] = $settings_link;
        return $links;
    }

    /**
     * Enqueues the styles for the plugin.
     *
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Casa_courses_Loader as all the hooks are defined
     * in that particular class.
     *
     * The Casa_courses_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_styles(): void
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Casa_courses_Loader as all the hooks are defined
         * in that particular class.
         *
         * The Casa_courses_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/casa-courses-admin.css', array (), $this->version, 'all' );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_editor();
    }

    /**
     * Initializes the plugin.
     *
     * This function is responsible for performing the necessary initialization steps
     * when the plugin is activated or when certain settings are updated in the admin area.
     *
     * If the plugin has been activated and is in the admin area, it deletes the
     * "casa_courses_plugin_activation" option and flushes the rewrite rules.
     *
     * If the plugin is in the admin area and the "option_page" is one of the specified pages
     * ("casa_courses_settings", "casa_courses_calendar", "casa_courses_registration")
     * and the "action" is "update", it updates the "casa_courses_plugin_activation" option
     * to "activated".
     *
     * @return void
     * @since 1.0.0
     */
    public function plugin_initialize(): void
    {
        $option_page = isset ( $_POST[ 'option_page' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'option_page' ] ) ) : '';

        if ( is_admin() && get_option( 'casa_courses_plugin_activation' ) == 'activated' ) {
            delete_option( 'casa_courses_plugin_activation' );
            flush_rewrite_rules();
        }

        if (
            is_admin() && isset( $option_page )
            && in_array(
                $option_page,
                ['casa_courses_settings', 'casa_courses_calendar', 'casa_courses_registration'],
                true
            )
            && sanitize_text_field( isset ( $_POST[ 'action' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'action' ] ) ) : '' ) === 'update'
        ) {
            update_option( 'casa_courses_plugin_activation', 'activated' );
        }
    }

    /**
     * Enqueues the scripts for the plugin.
     *
     * An instance of this class should be passed to the run() function
     * defined in Casa_courses_Loader as all the hooks are defined
     * in that particular class.
     *
     * The Casa_courses_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_scripts(): void
    {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/casa-courses-admin.js', array ( 'jquery' ), $this->version, false );
        wp_enqueue_script( 'jquery-validation', plugin_dir_url( __FILE__ ) . 'js/jquery-validate-min.js', array (
            'jquery',
            $this->plugin_name
        ), $this->version, false );
        wp_enqueue_script( 'wp-color-picker' );
        wp_localize_script( $this->plugin_name, 'casa_courses_config', [
            'restUrl'    => get_rest_url() . 'casa-courses/v1/',
            'ajax_nonce' => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    /**
     * Handles the form response for the general settings of the Casa Courses plugin.
     *
     * This function processes the form submission and updates the relevant options
     * based on the submitted data. It also performs necessary validations and
     * redirects the user back to the admin page after processing.
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_courses_general_form_response(): void
    {
        if ( isset( $_POST[ 'casa_courses_general_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'casa_courses_general_nonce' ] ) ), 'casa_courses_general_form_nonce' ) ) {
            $token = sanitize_text_field( isset ( $_POST[ 'casa_courses_token' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'casa_courses_token' ] ) ) : '' );
            $domain = sanitize_text_field( isset ( $_POST[ 'casa_courses_domain' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'casa_courses_domain' ] ) ) : '' );
            $project_id = sanitize_text_field( isset ( $_POST[ 'casa_courses_project_id' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'casa_courses_project_id' ] ) ) : '' );

            if ( !empty( $token ) && !empty( $domain ) ) {
                $success = Casa_Api::verify_settings( $domain, $token, $project_id );

                if ( $success === true ) {
                    update_option( 'casa_courses_token_error', '' );
                    update_option( 'casa_courses_token', $token );
                    update_option( 'casa_courses_project_id', $project_id );
                    update_option( 'casa_courses_domain', $domain );

                    $plugin_init = new Casa_Courses_Init();
                    $plugin_init->sync_data();

                    flush_rewrite_rules();
                } else {
                    update_option( 'casa_courses_token_error', __( 'Error: Invalid Token', 'casa-courses' ) );
                }
            } else {
                update_option( 'casa_courses_token_error', '' );
                update_option( 'casa_courses_token', '' );
                update_option( 'casa_courses_project_id', '' );
                update_option( 'casa_courses_domain', '' );
            }

            wp_redirect( admin_url( 'admin.php?page=casa-courses-plugin' ) );
        } else {
            wp_die( esc_attr__( 'Invalid nonce specified', 'casa-courses' ), esc_attr__( 'Error', 'casa-courses' ), array (
                'response'  => 403,
                'back_link' => 'admin.php?page=casa-courses-plugin',
            ) );
        }
    }

    /**
     * Adds the Casa dashboard widget.
     *
     * This method adds the Casa dashboard widget to the WordPress admin dashboard.
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_dashboard_widget(): void
    {
        wp_add_dashboard_widget( 'casa_dashboard_widget', esc_attr__( 'Casa courses', 'casa-courses'  ), [ $this, 'casa_dashboard_widget_render' ] );
    }

    /**
     * Renders the Casa dashboard widget.
     *
     * This method is responsible for rendering the Casa dashboard widget on the WordPress admin dashboard.
     * It displays the counts of course areas, courses, and events loaded from Casa.
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_dashboard_widget_render(): void
    {
        $courses_count = wp_count_posts( Casa_Courses_Custom_Posttype_Courses::$post_type )->publish;
        $events_count = wp_count_posts( Casa_Courses_Custom_Posttype_Events::$post_type )->publish;
        $areas_count = wp_count_terms( Casa_Courses_Custom_Taxonomy_Areas::$tax_type );
        $allow_strong = array( "strong" => array() );

        /* translators: 1: number of course areas loaded */
        echo wp_kses( sprintf( __( 'There is currently <strong>%1$s</strong> course areas loaded from Casa.', 'casa-courses' ), esc_attr( $areas_count ) ), $allow_strong );
        echo '<br>';
        /* translators: 1: number of courses loaded */
        echo wp_kses( sprintf( __( 'There is currently <strong>%1$s</strong> courses loaded from Casa.', 'casa-courses' ), esc_attr( $courses_count ) ), $allow_strong );
        echo '<br>';
        /* translators: 1: number of events loaded */
        echo wp_kses( sprintf( __( 'There is currently <strong>%1$s</strong> events loaded from Casa.', 'casa-courses' ), esc_attr( $events_count ) ), $allow_strong );
    }
}
