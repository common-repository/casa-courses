<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Casa_courses
 * @subpackage Casa_courses/includes
 * @author     foretagsakademincasa
 */
class Casa_Courses_Init
{
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected string $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected string $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if ( defined( 'CASA_COURSES_VERSION' ) ) {
            $this->version = CASA_COURSES_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'casa-courses';
    }

    /**
     * Register custom post types for CASA courses
     *
     * This method registers the necessary custom post types and their settings
     * for CASA courses.
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_courses_custom_posttype()
    {
        Casa_Courses_Custom_Posttype_Courses::register_post_type_template();
        Casa_Courses_Custom_Posttype_Courses::casa_courses_setup_post_type();
        Casa_Courses_Custom_Posttype_Events::casa_courses_setup_post_type();
    }

    /**
     * Create Casa Courses Taxonomy Areas
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_courses_taxonomy_areas()
    {
        Casa_Courses_Custom_Taxonomy_Areas::casa_courses_setup_area_taxonomy();
    }

    /**
     * Changes the default behavior of taxonomy checkboxes to radio buttons
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_courses_taxonomy_radio_buttons()
    {
        add_filter( 'wp_terms_checklist_args', function ( $args ) {
            if ( !empty( $args[ 'taxonomy' ] ) && $args[ 'taxonomy' ] === Casa_Courses_Custom_Taxonomy_Areas::$tax_type ) {
                if ( empty( $args[ 'walker' ] ) || is_a( $args[ 'walker' ], 'Walker' ) ) { // Don't override 3rd party walkers.

                    $args[ 'walker' ] = new class extends Walker_Category_Checklist {
                        function walk( $elements, $max_depth, ...$args )
                        {
                            return str_replace(
                                array (
                                    'type="checkbox"',
                                    "type='checkbox'"
                                ),
                                array (
                                    'type="radio" disabled',
                                    "type='radio' disabled"
                                ),
                                parent::walk( $elements, $max_depth, ...$args )
                            );
                        }
                    };
                }
            }
            return $args;
        } );
    }

    /**
     * Register REST API endpoints for casa-courses/v1 route
     *
     * This method registers three REST API endpoints for the casa-courses/v1 route: /companies, /sync, and /connect-event.
     * It uses the WordPress rest_api_init action hook to register the endpoints with the specified methods, permission callbacks, and callback functions.
     * The permission callbacks verify the AJAX nonce and user authentication. The callback functions invoke other methods to handle the requests.
     *
     * @return void
     * @since 1.0.0
     */
    public function rest_api_end_point()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'casa-courses/v1', '/companies', array (
                'methods'             => WP_REST_Server::EDITABLE,
                'permission_callback' => function ( $res ) {
                    return !!wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['ajax_nonce'] ?? '' ) ), 'wp_rest' );
                },
                'args'                => array (
                    'company_name' => array (
                        'validate_callback' => function ( $param, $request, $key ) {
                            return is_string( $param );
                        }
                    ),
                ),
                'callback'            => fn ( $req ) => $this->get_company( $req ),
            ) );
            register_rest_route( 'casa-courses/v1', '/sync', array (
                'methods'             => WP_REST_Server::EDITABLE,
                'permission_callback' => function ( $res ) {
                    return !!wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['ajax_nonce'] ?? '' ) ), 'wp_rest' ) && ( is_user_logged_in() || current_user_can( 'manage_options' ) );
                },
                'callback'            => fn ( $req ) => $this->get_sync( $req ),
            ) );
            register_rest_route( 'casa-courses/v1', '/connect-event', array (
                'methods'             => WP_REST_Server::EDITABLE,
                'permission_callback' => function ( $res ) {
                    return !!wp_verify_nonce( sanitize_text_field( wp_unslash ( $_REQUEST[ 'ajax_nonce' ] ?? '' ) ), 'wp_rest' );
                },
                'callback'            => fn ( $req ) => $this->connect_event( $req ),
            ) );
        } );
    }

    /**
     * Connect Participant Event and Send Status Message
     *
     * @param mixed $request The request object.
     * @return void
     * @since 1.0.0
     */
    public function connect_event( $request ): void
    {
        if ( isset( $_POST[ 'ajax_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'ajax_nonce' ] ) ), 'wp_rest' ) ) {
            $sanitized_form_data = array();
            $main_properties = [
                'address_address_row_1',
                'address_city',
                'address_email',
                'address_zip_code',
                'cell_phone_number',
                'company_corporate_id',
                'event_id',
                'company_id',
                'company_name',
                'company_sector',
                'email',
                'first_name',
                'last_name',
                'status',
            ];

            $participant_properties = [
                'cell_phone_number',
                'dietary_preferences',
                'dietary_preferences_custom',
                'email',
                'first_name',
                'last_name'
            ];

            foreach ( $main_properties as $property ) {
                $sanitized_form_data[ $property ] = sanitize_text_field( wp_unslash( $_POST[ 'form_data' ][ $property ] ) );
            }

            if ( isset ( $_POST[ 'form_data' ][ 'participants' ] ) && is_array( $_POST[ 'form_data' ][ 'participants' ] ) ) {
                foreach ( $_POST[ 'form_data' ][ 'participants' ] as $participant ) {
                    $sanitized_participant = array ();
                    foreach ( $participant_properties as $property ) {
                        $sanitized_participant[ $property ] = sanitize_text_field( wp_unslash( $participant[ $property ] ) );
                    }
                    $sanitized_form_data[ 'participants' ][] = $sanitized_participant;
                }
            }
            $token = $request->get_header( 'x_recaptcha_response' );

            if ( !empty( $sanitized_form_data ) ) {
                try {
                    $res = Casa_Courses_Worker_Custom_Post_Events::connect_participant( $sanitized_form_data, $token );
                } catch ( Exception $e ) {
                    $message = $e->getMessage();

                    wp_send_json_error( array (
                        'message' => $message,
                    ), 400 );
                    return;
                }

                if ( $res->success === true ) {
                    wp_send_json_success( array (
                        'labels' => array (
                            'phone'     => esc_attr__( 'Phone', 'casa-courses' ),
                            'name'      => esc_attr__( 'Name', 'casa-courses' ),
                            'email'     => esc_attr__( 'E-mailaddress', 'casa-courses' ),
                            'status'    => esc_attr__( 'Status', 'casa-courses' ),
                            'booked'    => esc_attr__( 'Booked', 'casa-courses' ),
                            'cancelled' => esc_attr__( 'Not booked', 'casa-courses' ),
                            'reserved'  => esc_attr__( 'Waiting list', 'casa-courses' ),
                        ),
                        'message'      => esc_attr__( 'Successfully booked participants', 'casa-courses' ),
                        'participants' => $res->message,
                        'status'       => @$res->status,
                    ) );
                } else {
                    wp_send_json_error( array (
                        'message' => $res->message,
                        'status'  => @$res->message,
                    ), @$res->status );
                }
            } else {
                wp_send_json_error( array (
                    'message' => esc_attr__( "There was an error with the form data.", 'casa-courses' ),
                    'status'  => 400,
                ), 400 );
            }
        } else {
            wp_send_json_error( array (
                'message' => esc_attr__( "Invalid nonce specified.", 'casa-courses' ),
                'status'  => 400,
            ), 400 );
        }
    }

    /**
     * Get Company Information
     *
     * This method retrieves information about a company using the Casa API.
     *
     * @param mixed $request The request containing the company name to retrieve information for.
     * @return void
     * @since 1.0.0
     */
    public function get_company( $request ): void
    {
        $company_name = sanitize_text_field( $request->get_param( 'company_name' ) );

        $response = Casa_Api::get_company( $company_name );

        if ( $response->status === 'success' ) {
            wp_send_json_success( array (
                'message' => $response->message,
                'result'  => $response->result
            ), 200 );
        }

        wp_send_json_error( array (
            'message' => $response->message,
        ) );
    }

    /**
     * Retrieve sync data from the server.
     *
     * @param mixed $request The request data.
     * @return void
     * @since 1.0.0
     */
    public function get_sync( $request ): void
    {
        $sync = $this->sync_data();

        if ( $sync->status === 'success' ) {
            wp_send_json_success( array (
                'message' => wp_json_encode( $sync->message ),
            ), 200 );
        }

        wp_send_json_error( array (
            'message' => wp_json_encode( $sync->message ),
        ) );
    }

    /**
     * Retrieve sync data from the server.
     *
     * This method retrieves sync data from the server by executing several handlers for different components.
     * If all the handlers execute successfully, the method sets the status to 'success' and returns the sync data.
     * If any handler throws a \RuntimeException, the method sets the status to 'error' and returns the error message.
     *
     * @return stdClass The sync data object.
     *   - string $status The sync status. Possible values: 'success', 'error'.
     *   - string $message The sync message.
     * @since 1.0.0
     */
    function sync_data(): stdClass
    {
        $response = new stdClass();
        $response->status = 'error';
        $message = '';

        try {
            $message .= "<br>" . Casa_Courses_Worker_Taxonomy_Areas::handle();
            $message .= "<br>" . Casa_Courses_Worker_Custom_Post_Courses::handle();
            $message .= "<br>" . Casa_Courses_Worker_Custom_Post_Events::handle();
            $message .= "<br>" . Casa_Courses_Worker_Industry::handle();
            $message .= "<br>" . Casa_Courses_Worker_Dietary_Preferences::handle();
            $response->status = 'success';
        } catch ( RuntimeException $e ) {
            $message = $e->getMessage();
        }

        $response->message = $message;

        return $response;
    }

    /**
     * Enqueue scripts and styles.
     *
     * This function is hooked into the 'wp_enqueue_scripts' action.
     * It adds actions to the 'wp_head' and 'wp_enqueue_scripts' hooks depending
     * on certain conditions.
     *
     * @return void
     * @since 1.0.0
     */
    public function wp_enqueue_scripts(): void
    {
        add_action( 'wp_enqueue_scripts', function () {
            global $post, $wp;
            
            if ( ( is_a( $post, 'WP_Post' ) && is_single() && Casa_Courses_Custom_Posttype_Courses::$post_type == get_post_type() ) || ( array_key_exists( 'pagename', $wp->query_vars ) && $wp->query_vars[ 'pagename' ] === 'courses' ) ) {
                add_action('wp_print_styles', function() {
                    $primary_color = get_option( 'casa_courses_primary' );
                    $secondary_color = get_option( 'casa_courses_secondary' );
                    $font = get_option( 'casa_courses_text_font' );

                    $price_head_color = get_option( 'casa_courses_detail_price_head_color' );
                    $price_head_bg_color = get_option( 'casa_courses_detail_price_head_bg_color' );
                    $price_head_border_color = get_option( 'casa_courses_detail_price_head_border_color' );

                    $event_box_color = get_option( 'casa_courses_detail_event_color' );
                    $event_box_bg_color = get_option( 'casa_courses_detail_event_bg_color' );
                    $event_box_btn_color = get_option( 'casa_courses_detail_event_btn_color' );
                    $event_box_btn_bg_color = get_option( 'casa_courses_detail_event_btn_bg_color' );

                    $booking_btn_color = get_option( 'casa_courses_booking_btn_color' );
                    $booking_btn_bg_color = get_option( 'casa_courses_booking_btn_bg_color' );

                    $area_box_bg_color = get_option( 'casa_courses_area_box_bg_color' );
                    $area_list_color = get_option( 'casa_courses_area_list_color' );
                    $area_box_color = get_option( 'casa_courses_area_box_color' );
                    $area_box_font_size = get_option( 'casa_courses_area_box_font_size' );
                    $area_box_number = get_option( 'casa_courses_area_box_number_desktop' );

                    $price_position = get_option( 'casa_courses_price_text_position' );

                    $few_seats_left_color = get_option( 'casa_courses_few_seats_remaining_color' );
                    $fully_booked_color = get_option( 'casa_courses_fully_booked_color' );

                    $css = ':root {';

                    $css .= $font ? '--casa-font-family: ' . esc_attr( str_replace( "+", " ", $font ) ) . ', sans-serif;' : '';
                    $css .= $primary_color ? '--casa-primary-color: ' . esc_attr( $primary_color ) . ';' : '';
                    $css .= $secondary_color ? '--casa-secondary-color: ' . esc_attr( $secondary_color ) . ';' : '';

                    $css .= $price_head_color ? '--casa-price-head-color: ' . esc_attr( $price_head_color ) . ';' : '';
                    $css .= $price_head_bg_color ? '--casa-price-head-bg-color: ' . esc_attr( $price_head_bg_color ) . ';' : '';
                    $css .= $price_head_border_color ? '--casa-price-head-border-color: ' . esc_attr( $price_head_border_color ) . ';' : '';

                    $css .= $event_box_color ? '--casa-event-box-color: ' . esc_attr( $event_box_color ) . ';' : '';
                    $css .= $event_box_bg_color ? '--casa-event-box-bg-color: ' . esc_attr( $event_box_bg_color ) . ';' : '';
                    $css .= $event_box_btn_color ? '--casa-event-box-btn-color: ' . esc_attr( $event_box_btn_color ) . ';' : '';

                    $css .= $event_box_btn_bg_color ? '--casa-event-box-btn-bg-color: ' . esc_attr( $event_box_btn_bg_color ) . ';' : '';
                    $css .= $booking_btn_color ? '--casa-booking-btn-color: ' . esc_attr( $booking_btn_color ) . ';' : '';
                    $css .= $booking_btn_bg_color ? '--casa-booking-btn-bg-color: ' . esc_attr( $booking_btn_bg_color ) . ';' : '';

                    $css .= $area_box_bg_color ? '--casa-area-box-bg-color: ' . esc_attr( $area_box_bg_color ) . ';' : '';
                    $css .= $area_list_color ? '--casa-area-list-color: ' . esc_attr( $area_list_color ) . ';' : '';
                    $css .= $area_box_color ? '--casa-area-box-color: ' . esc_attr( $area_box_color ) . ';' : '';
                    $css .= $area_box_font_size ? '--casa-area-box-font-size: ' . esc_attr( $area_box_font_size ) . 'px;' : '';
                    $css .= $area_box_number ? '--casa-area-box-number: ' . esc_attr( $area_box_number ) . ';' : '';
                    $css .= $price_position ? '--casa-price-position: ' . esc_attr( $price_position ) . ';' : '';
                    $css .= $few_seats_left_color ? '--casa-few-seats-remaining-color: ' . esc_attr( $few_seats_left_color ) . ';' : '';
                    $css .= $fully_booked_color ? '--casa-fully-booked-color: ' . esc_attr( $fully_booked_color ) . ';' : '';

                    $css .= '}';

                    $successful = wp_add_inline_style( $this->plugin_name . '-variables', $css );
                });

                add_action( 'wp_head', function() {
                    $font = get_option( 'casa_courses_text_font' );

                    if ( $font !== 'Inherit' ) {
                        echo '<link rel="preconnect" href="https://fonts.googleapis.com" />';
                        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />';
                    }
                });
            }
        } );
    }

    /**
     * Executes the casa_courses_cron_job method.
     *
     * This method is responsible for performing the casa update cron job by adding an action hook.
     * If the casa courses token option is set, it will call the sync_data method.
     *
     * @return void
     * @since 1.0.0
     */
    public function casa_courses_cron_job()
    {
        // Do casa update
        add_action( 'casa_cron_sync', function () {
            if ( get_option( 'casa_courses_token' ) ) {
                $this->sync_data();
            }
        } );
    }
}
