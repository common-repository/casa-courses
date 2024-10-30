<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Carbon\Carbon;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://-
 * @since      1.0.0
 *
 * @package    Casa_courses
 * @subpackage Casa_courses/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Casa_courses
 * @subpackage Casa_courses/public
 * @author     -
 */
class Casa_courses_Public
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
     * Footer template
     *
     * @since    1.0.0
     * @access   private
     * @var      string $footer
     */
    private static $footer;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct( string $plugin_name, string $version )
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        $current_post_type = get_post_type();
        $page_name = get_query_var( 'pagename' );

        if ( $page_name === 'courses' || $current_post_type === 'casa_courses' ) {
            $font = get_option( 'casa_courses_text_font' );

            wp_enqueue_style( $this->plugin_name . '-variables', plugin_dir_url( __FILE__ ) . 'css/casa-courses-variables.css', array(), $this->version );
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/casa-courses-public.css', array (), $this->version );

            if ( $font !== 'Inherit' ) {
                wp_enqueue_style( 'casa-google-font', 'https://fonts.googleapis.com/css?family=' . esc_attr( $font ) . '::wght@400;500;600&display=swap', array (), $this->version );
            }
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $current_post_type = get_post_type();
        $page_name = get_query_var( 'pagename' );

        if ( $page_name === 'courses' || $current_post_type === 'casa_courses' ) {
            $google_recaptcha = get_option( 'casa_courses_google_recaptcha' );
            $dependencies = array (
                'jquery'
            );

            if ( !empty( $google_recaptcha ) ) {
                $dependencies[] = 'casa_google_api';
                $this->enqueue_recaptcha( $google_recaptcha );
            }

            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/casa-courses-public.js', $dependencies, $this->version, false );

            wp_localize_script( $this->plugin_name, 'casa_courses_config', [
                'restUrl'                  => get_rest_url() . 'casa-courses/v1/',
                'ajax_nonce'               => wp_create_nonce( 'wp_rest' ),
                'google_recaptcha_enabled' => !empty( $google_recaptcha ),
                'google_recaptcha'         => $google_recaptcha
            ] );
        }
    }

    /**
     * Enqueues the reCAPTCHA script if the conditions are met.
     *
     * @param string $google_recaptcha The reCAPTCHA site key.
     *
     * @return void
     * @since 1.0.0
     */
    public function enqueue_recaptcha( string $google_recaptcha ): void
    {
        global $wp_query;

        $page_name = get_query_var( 'pagename' );
        $reg_page_slug = get_option( "casa_courses_registration_slug" );

        if ( !empty( $google_recaptcha )
            && $page_name === 'courses'
            && array_key_exists( 'current_page', $wp_query->query )
            && $wp_query->query[ 'current_page' ] === $reg_page_slug
            && !empty( $_GET[ 'id' ] )
        ) {
            wp_register_script(
                'casa_google_api',
                'https://www.google.com/recaptcha/api.js?render=' . $google_recaptcha,
                array (),
                $this->version,
                true
            );
        }
    }

    /**
     * Displays the areas section with a list of areas and their links.
     *
     * @param mixed $data The data to customize the section. If provided, it should contain the 'slug' key to highlight the current area.
     *
     * @return void
     * @since 1.0.0
     */
    public function show_areas_section( $data )
    {
        $current_slug = !empty( $data ) ? $data[ 'slug' ] : null;

        $terms = $this->get_ordered_course_areas();

        if ( $terms ) :
            $slug = get_option( 'casa_courses_courses_slug' );
            echo '<div class="casa__area-section"><ul class="casa__area-list">';
            foreach ( $terms as $term ) :
                $image_id = get_term_meta( $term->term_id, 'casa_courses_areas_image', true );
                $image = wp_get_attachment_image( $image_id, 'large', "", array ( "class" => "alignright casa__image large" ) );
                $link = get_bloginfo( 'url' );

                if ( $current_slug === $term->slug ) :
                    echo '<li class="casa__area-item active">';
                    echo '<a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '">';
                else :
                    echo '<li class="casa__area-item">';
                    echo '<a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $term->slug ) . '">';
                endif;

                if ( $image ) :
                    echo wp_kses_post( $image );
                endif;

                echo '<span>' . esc_attr( $term->name ) . '</span>';
                echo '</a>';

                echo '</li>';
            endforeach;
            echo '</ul></div>';
        endif;
    }


    /**
     * Shows the hero section on a course listing page.
     *
     * @return void
     */
    public function show_hero_section()
    {
        $desc = get_option( 'casa_courses_list_all_section_desc', 'false' );
        $image_id = get_option( 'casa_courses_list_all_image', 'false' );
        $show_filter = get_option( 'casa_courses_show_filter_home' );
        $title = get_option( 'casa_courses_area_section_title', 'false' );

        $image = wp_get_attachment_image( $image_id, 'large', "", array ( "class" => "casa__view-image casa__image" ) );

        if ( !empty( $title ) ) :
            echo '<h1>' . esc_attr( $title ) . '</h1>';
        endif;

        if ( $desc ) :
            if ( $image ) :
                echo wp_kses_post( $image );
            endif;
            echo '<div class="casa__hero-desc">' . wp_kses_post( wpautop ( $desc ) ) . '</div>';
        endif;

        if ( $show_filter === 'true' ) :
            do_action( 'casa_courses_areas_section' );
        endif;
    }

    /**
     * Action casa_courses_calendar_section
     *
     * @return void
     */
    public function show_calendar_section()
    {
        $title = get_option( 'casa_courses_calendar_title' );
        $desc = get_option( 'casa_courses_calendar_desc', 'false' );
        $show_filter = get_option( 'casa_courses_show_filter_calendar' );

        if ( $title ) :
            echo '<h1>' . esc_attr( $title ) . '</h1>';
        endif;
        if ( $desc ) :
            echo '<div class="casa-desc">' . wp_kses_post( wpautop ( $desc ) ) . '</div>';
        endif;

        if ( $show_filter === 'true' ) :
            do_action( 'casa_courses_areas_section' );
        endif;
    }

    /**
     * Action casa_courses_registration_section
     *
     * @return void
     */
    public function show_registration_section( $event )
    {
        $title = get_option( 'casa_courses_registration_title' );
        $desc = get_option( 'casa_courses_registration_desc', 'false' );
        $event_name = get_post_meta( $event->ID, 'casa_events_metadata_event_name', true );
        $available_date = get_post_meta( $event->ID, 'casa_events_metadata_next_available_date', true );
        $tz = get_post_meta( $event->ID, 'casa_events_metadata_available_date_timezone', true );

        $desc = str_replace( "{event_name}", '<span class="js-event__date-title">' . $event_name . '</span>', $desc );
        $desc = str_replace( "{event_date_start}", '<span class="js-event__date-start">' . Carbon::parse( $available_date )->timezone( $tz )->isoFormat( 'D MMM' ) . '</span>', $desc );

        if ( $title ) :
            echo '<h1>' . esc_attr( $title ) . '</h1>';
        endif;
        if ( $desc ) :
            echo '<div class="casa-desc">' . wp_kses_post( wpautop ( $desc ) ) . '</div>';
        endif;
    }

    /**
     * Action casa_courses_area_soon_course
     *
     * @return void
     */
    public function show_area_soon_course( $term )
    {
        $link = get_bloginfo( 'url' );
        $slug = get_option( 'casa_courses_courses_slug' );
        $scheduled_message = get_option( 'casa_courses_scheduled_text_default', false );

        $courses = $this->get_ordered_courses_for_area( $term );

        echo '<table class="casa-area__courses-table" cellspacing="0">
		<thead>
			<tr  class="area-name">
				<th>' . esc_attr__( 'Course Name', 'casa-courses' ) . '</th>
				<th>' . esc_attr__( 'Next available date', 'casa-courses' ) . '</th>
			</tr>
		</thead>
		<tbody>';

        if ( $courses ) :
            foreach ( $courses as $course ) :
                $events = $this->get_ordered_events_for_course( $course->ID );

                $available_date = '';
                $data = [ 'start_date' => '' ];
                foreach ( $events as $event ) :
                    $date = get_post_meta( $event->ID, 'casa_events_metadata_next_available_date', true );
                    $timezone = get_post_meta( $event->ID, 'casa_events_metadata_available_date_timezone', true );

                    $data = casa_next_date_available( $date, $timezone, $data[ 'start_date' ], $event->ID, $data );

                endforeach;

                $event_status_class = casa_event_status( $data );

                if ( !empty( $data[ 'tz' ] ) && !empty( $data[ 'start_date' ] ) ) {
                    $available_date = Carbon::parse( $data[ 'start_date' ] )->timezone( $data[ 'tz' ] )->isoFormat( 'D MMM' );
                }

                echo '<tr><td><a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $term->slug ) . '/' . esc_attr( $course->post_name ) . '">' . esc_attr( $course->post_title ) . '</a></td>';

                if ( $available_date ) :
                    echo '<td class="' . esc_attr( $event_status_class ) . '">' . esc_attr( $available_date ) . '</td>';
                else :
                    echo '<td>' . esc_attr( $scheduled_message ) . '</td>';
                endif;
                echo '</tr>';

            endforeach;
        endif;
        echo '</tbody></table>';
    }

    /**
     * Action casa_courses_area_soon_course
     *
     * @return void
     */
    public function show_area_soon_courses( $terms )
    {
        $link = get_bloginfo( 'url' );
        $slug = get_option( 'casa_courses_courses_slug' );
        $scheduled_message = get_option( 'casa_courses_scheduled_text_default', false );
        $html = '';

        foreach ( $terms as $term ) :

            $courses = $this->get_ordered_courses_for_area( $term );

            $course_count = 0;
            ob_start();
            if ( $courses ) :
                echo '<tr class="area-name"><td colspan="2">' . esc_attr( $term->name ) . '</td></tr>';

                foreach ( $courses as $course ) :
                    $events = $this->get_ordered_events_for_course( $course->ID );

                    $available_date = '';
                    $data = [ 'start_date' => '' ];
                    foreach ( $events as $event ) :
                        $date = get_post_meta( $event->ID, 'casa_events_metadata_next_available_date', true );
                        $timezone = get_post_meta( $event->ID, 'casa_events_metadata_available_date_timezone', true );

                        $data = casa_next_date_available( $date, $timezone, $data[ 'start_date' ], $event->ID, $data );

                    endforeach;

                    $event_status_class = casa_event_status( $data );

                    if ( !empty( $data[ 'tz' ] ) && !empty( $data[ 'start_date' ] ) ) {
                        $available_date = Carbon::parse( $data[ 'start_date' ] )->timezone( $data[ 'tz' ] )->isoFormat( 'D MMM' );
                    }

                    $course_count = 1;
                    echo '<tr><td><a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $term->slug ) . '/' . esc_attr( $course->post_name ) . '">' . esc_attr( $course->post_title ) . '</a></td>';

                    if ( $available_date ) :
                        echo '<td class="' . esc_attr( $event_status_class ) . '">' . esc_attr( $available_date ) . '</td>';
                    else :
                        echo '<td>' . esc_attr( $scheduled_message ) . '</td>';
                    endif;

                    echo '</tr>';

                endforeach;
            endif;

            if ( $course_count ) :
                $html .= ob_get_contents();
            endif;
            ob_end_clean();
        endforeach;

        if ( !empty( $html ) ) :
            echo '<table class="casa-area__courses-table courses__table" cellspacing="0">
		<thead>
			<tr>
				<th>' . esc_attr__( 'Course Name', 'casa-courses' ) . '</th>
				<th>' . esc_attr__( 'Next available date', 'casa-courses' ) . '</th>
			</tr>
		</thead><tbody>';
            echo wp_kses_post( $html );
            echo '</tbody></table>';
        endif;
    }


    /**
     * Action casa_courses_calendar_table_section
     *
     * @return void
     */
    public function show_calendar_table_section( $terms )
    {
        $link = get_bloginfo( 'url' );
        $slug = get_option( 'casa_courses_courses_slug' );
        $months = intval( get_option( 'casa_courses_calendar_months', 6 ) );

        $terms = $this->get_ordered_course_areas();

        $html = '';

        foreach ( $terms as $term ) :

            $courses = $this->get_ordered_courses_for_area( $term );

            $courses_present = false;
            ob_start();

            if ( $courses ) :
                echo '<tr class="area-name"><td colspan="' . esc_attr( $months + 1 ) . '">' . esc_attr( $term->name ) . '</td></tr>';

                foreach ( $courses as $course ) :
                    $events_row = '';

                    $event_posts = $this->get_ordered_events_for_course( $course->ID );

                    $events = [];
                    foreach ( $event_posts as $key => $event ) :
                        $events[ $key ][ 'date' ] = get_post_meta( $event->ID, 'casa_events_metadata_next_available_date', true );
                        $events[ $key ][ 'tz' ] = get_post_meta( $event->ID, 'casa_events_metadata_available_date_timezone', true );
                        $events[ $key ][ 'available_seats' ] = get_post_meta( $event->ID, 'casa_events_metadata_available_seats', true );
                        $events[ $key ][ 'status' ] = casa_event_status( $events[ $key ] );
                    endforeach;

                    $row = '<tr><td><a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $term->slug ) . '/' . esc_attr( $course->post_name ) . '">' . esc_attr( $course->post_title ) . '</a></td>';

                    for ( $i = 0; $i < $months; $i++ ) :
                        $month = Carbon::now()->addMonths( $i );
                        $sep_symbol = '';
                        $events_row .= '<td data-label="' . Carbon::now()->addMonths( $i )->isoFormat( 'MMM' ) . '">';
                        $data = '';
                        foreach ( $events as $event ) :
                            if ( $month->isSameMonth( $event[ 'date' ] ) ) :
                                $data .= $sep_symbol . '<a class="' . esc_attr( $event[ 'status' ] ) . '" href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $term->slug ) . '/' . esc_attr( $course->post_name ) . '">' . esc_attr( Carbon::parse( $event[ 'date' ] )->timezone( $event[ 'tz' ] )->isoFormat( 'D' ) ) . '</a>';
                                $sep_symbol = ', ';
                            endif;
                        endforeach;

                        $events_row .= ( empty( $data ) ? '&nbsp;' : $data ) . '</td>';
                    endfor;

                    $row .= $events_row . '</tr>';

                    $allowed_html = array(
                        'tr' => array(),
                        'td' => array(
                            'data-label' => true,
                        ),
                        'a' => array(
                            'href' => true,
                            'class' => true,
                        ),
                    );

                    echo wp_kses( $row, $allowed_html );
                    $courses_present = true;
                endforeach;
            endif;

            if ( $courses_present ) :
                $html .= ob_get_contents();
            endif;
            ob_end_clean();
        endforeach;

        if ( $html ) :
            echo '<table class="casa-area__courses-table courses__calendar" cellspacing="0">
			<thead><tr><th>' . esc_attr__( 'Course Name', 'casa-courses' ) . '</th>';
            for ( $i = 0; $i < $months; $i++ ) :
                $month_name = Carbon::now()->addMonths( $i )->isoFormat( 'MMM' );
                echo '<th>' . esc_attr( $month_name ) . '</th>';
            endfor;
            echo '</tr></thead><tbody>';
            echo wp_kses_post( $html );
            echo '</tbody></table>';
        else :
            echo '<div class="casa__no-results">';
            esc_attr_e( 'There are no active events available', 'casa-courses' );
            echo '</div>';
        endif;
    }

    /**
     * Action casa_courses_breadcrumb
     *
     * @return void
     */
    public function show_casa_breadcrumb( $param )
    {
        $link = get_bloginfo( 'url' );
        $slug = get_option( 'casa_courses_courses_slug' );
        if ( isset( $param[ 'title' ] ) ) :
            echo '<div class="row casa-course__breadcrumb">';
            echo '<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/">' . esc_attr__( 'Courses', 'casa-courses' ) . '</a></li>';
            if ( isset( $param[ 'terms' ] ) && is_object( $param[ 'terms' ][ 0 ] ) ) :
                echo '<li class="breadcrumb-item"><a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $param[ 'terms' ][ 0 ]->slug ) . '">' . esc_attr( $param[ 'terms' ][ 0 ]->name ) . '</a></li>';
            endif;
            if ( isset( $param[ 'template' ] ) && is_object( $param[ 'template' ] ) ) :
                echo '<li class="breadcrumb-item"><a href="' . esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $param[ 'terms' ][ 0 ]->slug ) . '/' . esc_attr( $param[ 'template' ]->post_name ) . '">' . esc_attr( $param[ 'template' ]->post_title ) . '</a></li>';
            endif;
            echo '<li class="breadcrumb-item active">' . esc_attr( $param[ 'title' ] ) . '</li>
				</ol>
			</nav>';
            echo '</div>';
        endif;
    }

    /**
     * Action casa_courses_breadcrumb
     *
     * @return void
     */
    public function show_list_view_all()
    {
        $terms = $this->get_ordered_course_areas();

        echo '<div class="casa__view-section">';

        if ( $terms ) :
            do_action( 'casa_courses_area_soon_courses', $terms );
        endif;

        echo '</div>';
    }

    /**
     * Action casa_courses_events
     *
     * @return void
     */
    public function show_courses_events( $id )
    {
        $template_id = get_post_meta( $id, 'casa_courses_metadata_id', true );
        $link = get_bloginfo( 'url' );
        $slug = get_option( 'casa_courses_courses_slug' );
        $booked_slug = get_option( 'casa_courses_registration_slug' );
        $available_notify = get_option( 'casa_courses_available_notify' );

        $template_id && $posts = get_posts( array (
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'meta_key'    => 'casa_events_metadata_start_date',
            'orderby'     => 'meta_value',
            'order'       => 'ASC',
            'meta_query'  => array (
                array (
                    'key'   => 'casa_events_metadata_template_id',
                    'value' => $template_id,
                )
            )

        ) );

        $event_status_allowed_html = array (
            'p' => array (
                'class' => true
            )
        );

        if ( isset( $posts ) && count( $posts ) ) : ?>
            <h3 class="upcoming__events"><?php esc_attr_e( 'Upcoming dates', 'casa-courses' ) ?></h3>
            <?php foreach ( $posts as $p ) :
                $available_seats = (int)get_post_meta( $p->ID, 'casa_events_metadata_available_seats', true );
                $max_participant_count = (int)get_post_meta( $p->ID, 'casa_events_metadata_max_participant_count', true );
                $event_id = get_post_meta( $p->ID, 'casa_events_metadata_id', true );

                $venue_city = get_post_meta( $p->ID, 'casa_events_metadata_venue_city', true );
                $venue_name = get_post_meta( $p->ID, 'casa_events_metadata_venue_name', true );
                $sessions = json_decode( get_post_meta( $p->ID, 'casa_events_metadata_sessions', true ) );

                $venue = esc_attr__( 'Venue', 'casa-courses' ) . ': ' . $venue_name;
                if ( $venue_city ) {
                    $venue .= ' / ' . $venue_city;
                }

                $is_notify = $available_seats <= 0 || $max_participant_count === 0;

                if ( $available_notify !== 'true' && $is_notify ) {
                    continue;
                }

                $event_status_message = casa_event_message_status( $available_seats );

                if ( $sessions && is_array( $sessions ) ) :
                    ?>
                    <div class="event">
                        <div class="dates">
                            <?php foreach ( $sessions as $session ) :
                                $time_now = Carbon::now()->timezone( $session->timezone ); ?>
                                <?php if ( $time_now->diffInSeconds( $session->days[ 0 ]->start_date, false ) >= 0 ) : ?>
                                <?php foreach ( $session->days as $days ) :
                                    $start_date = Carbon::parse( $days->start_date )->timezone( $session->timezone );
                                    $end_date = Carbon::parse( $days->end_date )->timezone( $session->timezone );
                                    ?>
                                    <?php echo esc_attr( casa_date_formatter( $start_date, $end_date ) ); ?>
                                    <br/>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ( $venue_name ) : ?>
                                <div class="venue"><?php echo esc_attr( $venue ) ?></div>
                            <?php endif; ?>
                            <?php if ( !empty( $event_status_message ) ) : ?>
                                <?php echo wp_kses( $event_status_message, $event_status_allowed_html ) ?>
                            <?php endif; ?>
                        </div>
                        <div class="book">
                            <?php $button_text = ( $is_notify ? __( 'Notify me', 'casa-courses' ) : __( 'Book', 'casa-courses' ) ) ?>
                            <a href="<?php echo esc_attr( $link ) . '/' . esc_attr( $slug ) . '/' . esc_attr( $booked_slug ) . '/?id=' . esc_attr( $event_id ) ?>"
                               class="book-btn"
                               title="<?php echo esc_attr( $button_text ) ?>">
                                <?php echo esc_attr( $button_text ) ?>
                            </a>
                        </div>
                    </div>
                <?php
                endif;
            endforeach;
        endif;
    }


    /**
     * Action casa_courses_message_sections
     *
     * @return void
     */
    public function show_courses_form_message_sections( $params = array () )
    {
        $submit_desc = get_option( 'casa_courses_submit_desc', 'false' );
        $error_desc = get_option( 'casa_courses_error_desc', 'false' );
        $event_name = get_post_meta( $params[ 'event' ][ 0 ]->ID, 'casa_events_metadata_event_name', true );
        $available_date = get_post_meta( $params[ 'event' ][ 0 ]->ID, 'casa_events_metadata_next_available_date', true );
        $tz = get_post_meta( $params[ 'event' ][ 0 ]->ID, 'casa_events_metadata_available_date_timezone', true );

        $submit_desc = str_replace( "{event_name}", '<span class="js-event__date-title">' . esc_attr( $event_name ) . '</span>', $submit_desc );
        $submit_desc = str_replace( "{event_date_start}", '<span class="js-event__date-start">' . Carbon::parse( $available_date )->timezone( $tz )->isoFormat( 'D MMM' ) . '</span>', $submit_desc );


        if ( $submit_desc ) :
            echo '<div class="casa-form__submit-section" style="display:none">';
            echo '<div class="casa-desc casa__submit-desc">' . esc_attr( $submit_desc ) . '</div>';
            echo '<div id="js-booked__participants" class="row" style="display:none">
				<h4>' . esc_attr__( 'Booked:', 'casa-courses' ) . '</h4></div>';
            echo '</div>';
            echo '<div id="js-not_booked__participants" class="row" style="display:none">
			<h4>' . esc_attr__( 'Not Booked:', 'casa-courses' ) . '</h4></div>';
            echo '</div>';
        endif;


        if ( $error_desc ) :
            echo '<div class="casa-form__error-section" style="display:none">';
            echo '<div class="casa-desc casa__error-desc">' . esc_attr( $error_desc ) . '</div>';
        endif;

        echo '<template id="template-book__participants">
				<div class="col-12 col-md-6 col-lg-4">
					<ul class="list-booked__participant-info">{%items%}</ul>
				</div>
			</template>';
    }

    /**
     * Action casa_courses_form
     *
     * @return void
     */
    public function show_courses_form( $params = array () )
    {
        $template_id = get_post_meta( $params[ 'event' ][ 0 ]->ID, 'casa_events_metadata_template_id', true );
        $current_event = $events = [];

        $company_required = get_option( 'casa_courses_company_required', false );
        $company_visible = get_option( 'casa_courses_company_visible', false );

        $company_id_required = get_option( 'casa_courses_company_id_required', false );
        $company_id_visible = get_option( 'casa_courses_company_id_visible', false );

        $industry_required = get_option( 'casa_courses_industry_required', false );
        $industry_visible = get_option( 'casa_courses_industry_visible', false );
        $industry_json = get_option( 'casa_courses_industry_json', false );

        $dietary_preferences_visible = get_option( 'casa_courses_dietary_preferences_visible', false );
        $dietary_preferences_json = get_option( 'casa_courses_dietary_preferences_json', false );

        $privacy_link = get_option( 'casa_courses_privacy_label', false );
        $terms_link = get_option( 'casa_courses_terms_label', false );

        $privacy_message = get_option( 'casa_courses_privacy_message', false );
        $terms_message = get_option( 'casa_courses_terms_message', false );

        $event_status_allowed_html = array (
            'p' => array (
                'class' => true
            )
        );

        $template_id && $posts = get_posts( array (
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'meta_key'    => 'casa_events_metadata_start_date',
            'orderby'     => 'meta_value',
            'order'       => 'ASC',
            'meta_query'  => array (
                array (
                    'key'   => 'casa_events_metadata_template_id',
                    'value' => $template_id,
                )
            )
        ) );

        if ( count( $posts ) ) :
            foreach ( $posts as $key => $p ) :
                $event_end_date = get_post_meta( $p->ID, 'casa_events_metadata_end_date', true );
                $tz = get_post_meta( $p->ID, 'casa_events_metadata_available_date_timezone', true );

                $event_name = get_post_meta( $p->ID, 'casa_events_metadata_event_name', true );
                $available_date = get_post_meta( $p->ID, 'casa_events_metadata_next_available_date', true );

                $available_seats = (int)get_post_meta( $p->ID, 'casa_events_metadata_available_seats', true );
                $max_participant_count = (int)get_post_meta( $p->ID, 'casa_events_metadata_max_participant_count', true );
                $event_id = get_post_meta( $p->ID, 'casa_events_metadata_id', true );

                $is_notify = $available_seats <= 0 || $max_participant_count === 0;

                $event_status_message = casa_event_message_status( $available_seats );

                $event_date_formatted = casa_date_formatter(
                    Carbon::parse( $available_date )->timezone( $tz ),
                    Carbon::parse( $event_end_date )->timezone( $tz )
                );

                $events[ $key ] = [
                    'id'               => $event_id,
                    'status_message'   => $event_status_message,
                    'start_date'       => Carbon::parse( $available_date )->timezone( $tz )->isoFormat( 'D MMM' ),
                    'date'             => $event_date_formatted,
                    'available_seats'  => $available_seats,
                    'max_participants' => $max_participant_count,
                    'status'           => $is_notify ? Casa_Courses_Worker_Custom_Post_Events::$status_reserved : Casa_Courses_Worker_Custom_Post_Events::$status_booked,
                    'btn_title'        => $is_notify ? esc_attr__( 'Notify me', 'casa-courses' ) : esc_attr__( 'Book', 'casa-courses' ),
                    'name'             => $event_name,
                ];

                if ( $params[ 'event' ][ 0 ]->ID === $p->ID ) :
                    $current_event = $events[ $key ];
                endif;

            endforeach;
        endif;

        ?>
        <section class="courses__registration mt-5">
            <form class="fa-book__form g-3 needs-validation" id="fa-book__form">
                <?php if ( count( $events ) > 1 ): ?>
                <div class="col col-md-6 col-xl-4 mt-2 pe-md-3">
                    <label for="booking_event_date"
                           class="form-label"><?php esc_attr_e( 'Choose the event you want to book *', 'casa-courses' ) ?></label>
                    <select class="form-select booking_event_date" id="booking_event_date" name="event_id" required>
                        <?php foreach ( $events as $event ) : ?>
                            <option <?php echo $current_event[ 'id' ] === $event[ 'id' ] ? "selected" : "" ?>
                                    data-name="<?php echo esc_attr( $event[ 'name' ] ) ?>"
                                    data-date-start="<?php echo esc_attr( $event[ 'start_date' ] ) ?>"
                                    data-status="<?php echo esc_attr( $event[ 'status' ] ) ?>"
                                    data-status-message="<?php echo esc_attr( $event[ 'status_message' ] ) ?>"
                                    data-title="<?php echo esc_attr( $event[ 'btn_title' ] ) ?>"
                                    data-available-seats="<?php echo esc_attr( $event[ 'available_seats' ] ) ?>"
                                    data-max-participants="<?php echo esc_attr( $event[ 'max_participants' ] ) ?>"
                                    data-id="<?php echo esc_attr( $event[ 'id' ] ) ?>"
                                    value="<?php echo esc_attr( $event[ 'id' ] ) ?>"><?php echo esc_attr( $event[ 'date' ] ) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">
                        <?php esc_attr_e( 'Please choose event date', 'casa-courses' ) ?>
                    </div>
                </div>
                <?php endif; ?>

                <div id="event_status_message" class="col-12">
                    <?php if ( !empty( $current_event[ 'status_message' ] ) ) : ?>
                        <?php echo wp_kses( $current_event[ 'status_message' ], $event_status_allowed_html ) ?>
                    <?php endif; ?>
                </div>

                <?php if ( $company_visible === "true" ) : ?>
                    <div class="row">
                        <div class="col-12 mt-4">
                            <h4 class="courses__book-title"><?php esc_attr_e( 'Corporate Information', 'casa-courses' ) ?></h4>
                        </div>
                        <div class="col-md-6 col-xl-4">
                            <input type="hidden" id="company_id" name="company_id">
                            <label for="corporate_name" class="col-form-label">
                                <?php esc_attr_e( 'Corporate Name', 'casa-courses' ); ?>:
                            </label>
                            <input type="text" <?php echo( $company_required === "true" ? "required" : "" ); ?>
                                   class="form-control" id="corporate_name" name="company_name"
                                   placeholder="<?php esc_attr_e( 'Corporate Name', 'casa-courses' ); ?><?php echo( $company_required === "true" ? "*" : "" ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please fill a corporate name', 'casa-courses' ) ?>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <label for="invoice_address" class="col-form-label">
                                <?php esc_attr_e( 'Invoice Address', 'casa-courses' ); ?>:
                            </label>
                            <input type="text" <?php echo( $company_required === "true" ? "required" : "" ); ?>
                                   class="form-control" id="invoice_address" name="address_address_row_1"
                                   placeholder="<?php esc_attr_e( 'Invoice Address', 'casa-courses' ); ?><?php echo( $company_required === "true" ? "*" : "" ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please provide a valid invoice address', 'casa-courses' ) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xl-4 mt-2">
                            <label for="zip_code" class="col-form-label">
                                <?php esc_attr_e( 'Zip Code', 'casa-courses' ); ?>:
                            </label>
                            <input type="text" <?php echo( $company_required === "true" ? "required" : "" ); ?>
                                   class="form-control" id="zip_code" name="address_zip_code"
                                   placeholder="<?php esc_attr_e( 'Zip Code', 'casa-courses' ); ?><?php echo( $company_required === "true" ? "*" : "" ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please provide a valid zip.', 'casa-courses' ) ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 mt-2">
                            <label for="city" class="col-form-label">
                                <?php esc_attr_e( 'City', 'casa-courses' ); ?>:
                            </label>
                            <input type="text" <?php echo( $company_required === "true" ? "required" : "" ); ?>
                                   class="form-control" id="city" name="address_city"
                                   placeholder="<?php esc_attr_e( 'City', 'casa-courses' ); ?><?php echo( $company_required === "true" ? "*" : "" ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please provide a valid city.', 'casa-courses' ) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xl-4 mt-2">
                            <label for="e_invoice_address" class="col-form-label">
                                <?php esc_attr_e( 'E-Invoice Address', 'casa-courses' ); ?>:
                            </label>
                            <input type="email" class="form-control" id="e_invoice_address" name="address_email"
                                   placeholder="<?php esc_attr_e( 'E-Invoice Address', 'casa-courses' ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please provide a valid e-invoice address', 'casa-courses' ) ?>
                            </div>
                        </div>
                        <?php if ( $company_id_visible === "true" ) : ?>
                            <div class="col-md-6 col-xl-4 mt-2">
                                <label for="corporate_id" class="col-form-label">
                                    <?php esc_attr_e( 'Corporate ID', 'casa-courses' ); ?>:
                                </label>
                                <input type="text" <?php echo( $company_id_required === "true" ? "required" : "" ); ?>
                                       class="form-control" id="corporate_id" name="company_corporate_id"
                                       placeholder="<?php esc_attr_e( 'Corporate ID', 'casa-courses' ); ?><?php echo( $company_id_required === "true" ? "*" : "" ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please provide a valid corporate ID', 'casa-courses' ) ?>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>

                    <?php if ( $industry_visible === "true" && !empty( $industry_json ) ) : ?>
                        <?php $industry = json_decode( $industry_json );
                        if ( !empty( $industry ) && is_array( $industry ) ) : ?>
                            <div class="row">
                                <div class="col col-md-6 col-xl-4 mt-2 pe-md-3">
                                    <label for="industry" class="col-form-label">
                                        <?php esc_attr_e( 'Industry', 'casa-courses' ); ?>:
                                    </label>
                                    <select class="form-select" id="industry"
                                            name="company_sector" <?php echo( $industry_required === "true" ? "required" : "" ); ?>>
                                        <option value=""><?php esc_attr_e( 'Select Industry', 'casa-courses' ); ?><?php echo( $industry_required === "true" ? "*" : "" ); ?></option>
                                        <?php foreach ( $industry as $value ) : ?>
                                            <option value="<?php echo esc_attr( $value->id ) ?>"><?php echo esc_attr( $value->name ) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?php esc_attr_e( 'Please select a valid industry.', 'casa-courses' ) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    <?php endif ?>
                <?php endif ?>

                <div class="row">
                    <div class="col-12 mt-4">
                        <h4 class="courses__book-title"><?php esc_attr_e( 'Contact Information', 'casa-courses' ) ?></h4>
                    </div>
                    <div class="col-md-6 col-xl-4">
                        <label for="first-name" class="col-form-label">
                            <?php esc_attr_e( 'First name', 'casa-courses' ) ?>:
                        </label>
                        <input type="text" class="form-control" id="first-name" name="first_name"
                               placeholder="<?php esc_attr_e( 'First name', 'casa-courses' ); ?>">
                        <div class="invalid-feedback">
                            <?php esc_attr_e( 'Please fill a first name', 'casa-courses' ) ?>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label for="last-name" class="col-form-label">
                            <?php esc_attr_e( 'Last name', 'casa-courses' ); ?>:
                        </label>
                        <input type="text" class="form-control" id="last-name" name="last_name"
                               placeholder="<?php esc_attr_e( 'Last name', 'casa-courses' ); ?>">
                        <div class="invalid-feedback">
                            <?php esc_attr_e( 'Please fill a last name', 'casa-courses' ) ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-xl-4 mt-2">
                        <label for="email" class="col-form-label">
                            <?php esc_attr_e( 'E-mail', 'casa-courses' ); ?>:
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="<?php esc_attr_e( 'E-mail', 'casa-courses' ); ?>">
                        <div class="invalid-feedback">
                            <?php esc_attr_e( 'Please fill a email', 'casa-courses' ) ?>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-4 mt-2">
                        <label for="phone" class="col-form-label">
                            <?php esc_attr_e( 'Phone nr', 'casa-courses' ); ?>:
                        </label>
                        <input type="tel" class="form-control" id="phone" name="cell_phone_number"
                               placeholder="<?php esc_attr_e( 'Phone nr', 'casa-courses' ); ?>">
                        <div class="invalid-feedback">
                            <?php esc_attr_e( 'Please fill a phone', 'casa-courses' ) ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-xl-4 mt-2">
                        <label for="contact_is_participant" class="form-label">
                            <input type="checkbox" class="form-check-input checkbox-lg me-1" id="contact_is_participant"
                                   name="contact_is_participant">
                            <?php esc_attr_e( 'I will also participate', 'casa-courses' ); ?>
                        </label>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <h4 class="courses__book-title"><?php esc_attr_e( 'Participant Information', 'casa-courses' ) ?></h4>
                </div>

                <template id="contact_participant_template">
                    <div id="contact_participant" class="mb-4 js-contact_participant">
                        <div class="row">
                            <div class="col-12 mt-2">
                                <h5 class="courses__book-title"><?php esc_attr_e( 'Participant', 'casa-courses' ) ?></h5>
                            </div>
                            <div class="col-md-6 col-xl-4">
                                <label for="p-first-name0" class="col-form-label">
                                    <?php esc_attr_e( 'First name', 'casa-courses' ) ?>:
                                </label>
                                <input readonly type="text" class="form-control" id="p-first-name0"
                                       name="participant_first_name[]"
                                       placeholder="<?php esc_attr_e( 'First name', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a first name', 'casa-courses' ) ?>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-4">
                                <label for="p-last-name0" class="col-form-label">
                                    <?php esc_attr_e( 'Last name', 'casa-courses' ); ?>:
                                </label>
                                <input readonly type="text" required class="form-control" id="p-last-name0"
                                       name="participant_last_name[]"
                                       placeholder="<?php esc_attr_e( 'Last name', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a last name', 'casa-courses' ) ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xl-4">
                                <label for="p-email0" class="col-form-label">
                                    <?php esc_attr_e( 'Email', 'casa-courses' ) ?>:
                                </label>
                                <input readonly type="email" class="form-control" id="p-email0"
                                       name="participant_email[]"
                                       placeholder="<?php esc_attr_e( 'Email', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a email', 'casa-courses' ) ?>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-4">
                                <label for="p-phone0" class="col-form-label">
                                    <?php esc_attr_e( 'Phone', 'casa-courses' ); ?>:
                                </label>
                                <input readonly type="tel" class="form-control" id="p-phone0"
                                       name="participant_cell_phone_number[]"
                                       placeholder="<?php esc_attr_e( 'Phone', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a phone', 'casa-courses' ) ?>
                                </div>
                            </div>
                        </div>

                        <?php if ( $dietary_preferences_visible === "true" ) : ?>
                            <?php $dietary_preferences = json_decode( $dietary_preferences_json );
                            if ( !empty( $dietary_preferences ) && is_array( $dietary_preferences ) ) : ?>
                                <div class="row">
                                    <div class="col-md-6 col-xl-4">
                                        <label for="dietary_preferences" class="col-form-label">
                                            <?php esc_attr_e( 'Dietary Preferences', 'casa-courses' ); ?>:
                                        </label>
                                        <select class="form-select" id="dietary_preferences"
                                                name="dietary_preference[]">
                                            <option value=""><?php esc_attr_e( 'Select Dietary preferences', 'casa-courses' ); ?></option>
                                            <?php foreach ( $dietary_preferences as $value ) : ?>
                                                <option value="<?php echo esc_attr( $value->name ) ?>"><?php echo esc_attr( $value->name ) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?php esc_attr_e( 'Please select a valid dietary preference', 'casa-courses' ) ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-xl-4 js-dietary_preferences_custom">
                                        <label for="dietary_preferences_custom" class="col-form-label">
                                            <?php esc_attr_e( 'Custom Dietary Preferences', 'casa-courses' ); ?>:
                                        </label>
                                        <input type="text" class="form-control" id="dietary_preferences_custom"
                                               name="dietary_preferences_custom[]"
                                               placeholder="<?php esc_attr_e( 'Custom Dietary Preferences', 'casa-courses' ); ?>">
                                        <div class="invalid-feedback">
                                            <?php esc_attr_e( 'Please provide a valid text', 'casa-courses' ) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        <?php endif ?>
                    </div>
                </template>

                <div class="contact_participant-template js-contact_participant active mb-4">
                    <div class="row">
                        <div class="col-md-6 col-xl-4 mt-2">
                            <h5 class="courses__book-title"><?php esc_attr_e( 'Participant', 'casa-courses' ) ?></h5>
                        </div>
                        <div class="col-md-6 col-xl-4 mt-2 d-flex justify-content-end">
                            <button type="button" class="btn-close js-participant-remove" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xl-4">
                            <label for="p-first-name1" class="col-form-label">
                                <?php esc_attr_e( 'First name', 'casa-courses' ) ?>:
                            </label>
                            <input type="text" class="form-control" id="p-first-name1" name="participant_first_name[]"
                                   placeholder="<?php esc_attr_e( 'First name', 'casa-courses' ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please fill a first name', 'casa-courses' ) ?>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <label for="p-last-name1" class="col-form-label">
                                <?php esc_attr_e( 'Last name', 'casa-courses' ); ?>:
                            </label>
                            <input type="text" class="form-control" id="p-last-name1" name="participant_last_name[]"
                                   placeholder="<?php esc_attr_e( 'Last name', 'casa-courses' ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please fill a last name', 'casa-courses' ) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-xl-4">
                            <label for="p-email1" class="col-form-label">
                                <?php esc_attr_e( 'Email', 'casa-courses' ) ?>:
                            </label>
                            <input type="email" required class="form-control" id="p-email1" name="participant_email[]"
                                   placeholder="<?php esc_attr_e( 'Email', 'casa-courses' ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please fill a email', 'casa-courses' ) ?>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <label for="p-phone1" class="col-form-label">
                                <?php esc_attr_e( 'Phone', 'casa-courses' ); ?>:
                            </label>
                            <input type="tel" class="form-control" id="p-phone1"
                                   name="participant_cell_phone_number[]"
                                   placeholder="<?php esc_attr_e( 'Phone', 'casa-courses' ); ?>">
                            <div class="invalid-feedback">
                                <?php esc_attr_e( 'Please fill a phone', 'casa-courses' ) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ( $dietary_preferences_visible === "true" ) : ?>
                        <?php $dietary_preferences = json_decode( $dietary_preferences_json );
                        if ( !empty( $dietary_preferences ) && is_array( $dietary_preferences ) ) : ?>
                            <div class="row">
                                <div class="col-md-6 col-xl-4">
                                    <label for="dietary_preferences{%number%}" class="col-form-label">
                                        <?php esc_attr_e( 'Dietary Preferences', 'casa-courses' ); ?>:
                                    </label>
                                    <select class="form-select" id="dietary_preferences{%number%}"
                                            name="dietary_preference[]">
                                        <option value=""><?php esc_attr_e( 'Select Dietary preferences', 'casa-courses' ); ?></option>
                                        <?php foreach ( $dietary_preferences as $value ) : ?>
                                            <option value="<?php echo esc_attr( $value->name ) ?>"><?php echo esc_attr( $value->name ) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?php esc_attr_e( 'Please select a valid dietary preference', 'casa-courses' ) ?>
                                    </div>
                                </div>

                                <div class="col-md-6 col-xl-4 js-dietary_preferences_custom">
                                    <label for="dietary_preferences_custom{%number%}" class="col-form-label">
                                        <?php esc_attr_e( 'Custom Dietary Preferences', 'casa-courses' ); ?>:
                                    </label>
                                    <input type="text" class="form-control" id="dietary_preferences_custom{%number%}"
                                           name="dietary_preferences_custom[]"
                                           placeholder="<?php esc_attr_e( 'Custom Dietary Preferences', 'casa-courses' ); ?>">
                                    <div class="invalid-feedback">
                                        <?php esc_attr_e( 'Please provide a valid text', 'casa-courses' ) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    <?php endif ?>
                </div>
                <template id="participant_template">
                    <div class="contact_participant-template js-contact_participant mb-4">
                        <div class="row">
                            <div class="col-md-6 col-xl-4 mt-2">
                                <h5 class="courses__book-title"><?php esc_attr_e( 'Participant', 'casa-courses' ) ?></h5>
                            </div>
                            <div class="col-md-6 col-xl-4 mt-2 d-flex justify-content-end">
                                <button type="button" class="btn-close js-participant-remove"
                                        aria-label="Close"></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xl-4">
                                <label for="p-first-name{%number%}" class="col-form-label">
                                    <?php esc_attr_e( 'First name', 'casa-courses' ) ?>:
                                </label>
                                <input type="text" class="form-control" id="p-first-name{%number%}"
                                       name="participant_first_name[]"
                                       placeholder="<?php esc_attr_e( 'First name', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a first name', 'casa-courses' ) ?>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-4">
                                <label for="p-last-name{%number%}" class="col-form-label">
                                    <?php esc_attr_e( 'Last name', 'casa-courses' ); ?>:
                                </label>
                                <input type="text" class="form-control" id="p-last-name{%number%}"
                                       name="participant_last_name[]"
                                       placeholder="<?php esc_attr_e( 'Last name', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a last name', 'casa-courses' ) ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-xl-4">
                                <label for="p-email{%number%}" class="col-form-label">
                                    <?php esc_attr_e( 'Email', 'casa-courses' ) ?>:
                                </label>
                                <input type="email" required class="form-control" id="p-email{%number%}"
                                       name="participant_email[]"
                                       placeholder="<?php esc_attr_e( 'Email', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a email', 'casa-courses' ) ?>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-4">
                                <label for="p-phone{%number%}" class="col-form-label">
                                    <?php esc_attr_e( 'Phone', 'casa-courses' ); ?>:
                                </label>
                                <input type="tel" class="form-control" id="p-phone{%number%}"
                                       name="participant_cell_phone_number[]"
                                       placeholder="<?php esc_attr_e( 'Phone', 'casa-courses' ); ?>">
                                <div class="invalid-feedback">
                                    <?php esc_attr_e( 'Please fill a phone', 'casa-courses' ) ?>
                                </div>
                            </div>
                        </div>

                        <?php if ( $dietary_preferences_visible === "true" ) : ?>
                            <?php $dietary_preferences = json_decode( $dietary_preferences_json );
                            if ( !empty( $dietary_preferences ) && is_array( $dietary_preferences ) ) : ?>
                                <div class="row">
                                    <div class="col-md-6 col-xl-4">
                                        <label for="dietary_preferences{%number%}" class="col-form-label">
                                            <?php esc_attr_e( 'Dietary Preferences', 'casa-courses' ); ?>:
                                        </label>
                                        <select class="form-select" id="dietary_preferences{%number%}"
                                                name="dietary_preference[]">
                                            <option value=""><?php esc_attr_e( 'Select Dietary preferences', 'casa-courses' ); ?></option>
                                            <?php foreach ( $dietary_preferences as $value ) : ?>
                                                <option value="<?php echo esc_attr( $value->name ) ?>"><?php echo esc_attr( $value->name ) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            <?php esc_attr_e( 'Please select a valid dietary preference', 'casa-courses' ) ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-xl-4 js-dietary_preferences_custom">
                                        <label for="dietary_preferences_custom{%number%}" class="col-form-label">
                                            <?php esc_attr_e( 'Custom Dietary Preferences', 'casa-courses' ); ?>:
                                        </label>
                                        <input type="text" class="form-control"
                                               id="dietary_preferences_custom{%number%}"
                                               name="dietary_preferences_custom[]"
                                               placeholder="<?php esc_attr_e( 'Custom Dietary Preferences', 'casa-courses' ); ?>">
                                        <div class="invalid-feedback">
                                            <?php esc_attr_e( 'Please provide a valid text', 'casa-courses' ) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        <?php endif ?>
                    </div>
                </template>

                <div class="row mt-3">
                    <div class="col-md-6 col-xl-4">
                        <button type="button" id="add_new_participant"
                                data-available-seats="<?php echo esc_attr( $current_event[ 'available_seats' ] ) ?>"
                                data-max-participants="<?php echo esc_attr( $current_event[ 'max_participants' ] ) ?>"
                                data-status="<?php echo esc_attr( $current_event[ 'status' ] ) ?>" <?php echo( ( $current_event[ 'available_seats' ] > 0 ) ? '' : 'disabled="disabled"' ) ?>
                                class="btn btn-outline-secondary"><?php esc_attr_e( '+ Add participants', 'casa-courses' ); ?></button>
                    </div>
                </div>

                <?php if ( !empty( $terms_link ) && !empty( $terms_message ) ) : ?>
                    <div class="row mt-4">
                        <div class="col-12 mt-2">
                            <label for="terms_policy" class="form-label">
                                <input type="checkbox" required class="form-check-input checkbox-lg me-1"
                                       id="terms_policy" name="terms_policy">
                                <?php echo esc_attr( $terms_message ) ?> <a href="<?php echo esc_attr( $terms_link ) ?>"
                                                                            target="_blank"
                                                                            title="<?php esc_attr_e( 'Booking Policy', 'casa-courses' ); ?>">
                                    <?php echo esc_attr_e( 'Read More', 'casa-courses' ); ?>
                                </a>
                            </label>
                        </div>
                    </div>
                <?php endif ?>


                <?php if ( !empty( $privacy_link ) && !empty( $privacy_message ) ) : ?>
                    <div class="row">
                        <div class="col-12 mt-2">
                            <label for="privacy_policy" class="form-label">
                                <input type="checkbox" required class="form-check-input checkbox-lg me-1"
                                       id="privacy_policy" name="privacy_policy">
                                <?php echo esc_attr( $privacy_message ) ?> <a
                                        href="<?php echo esc_attr( $privacy_link ) ?>" target="_blank"
                                        title="<?php esc_attr_e( 'Privacy Policy', 'casa-courses' ); ?>">
                                    <?php esc_attr_e( 'Read More', 'casa-courses' ); ?>
                                </a>
                            </label>
                        </div>
                    </div>
                <?php endif ?>

                <input type="hidden" id="event-id" name="event_id"
                       value="<?php echo esc_attr( $current_event[ 'id' ] ) ?>">
                <input type="hidden" id="event-status" name="status"
                       value="<?php echo esc_attr( $current_event[ 'status' ] ) ?>">
                <div class="row">
                    <div class="col-md-6 col-xl-4 mt-2">
                        <span class="alert-message"></span>
                    </div>
                    <div class="col-md-6 col-xl-4 mt-2 mt-2 d-flex justify-content-end">
                        <button type="submit" class="book-btn submit js-modal__submit">
                            <span class="spinner-border spinner-border-sm js-modal__submit-spinner"
                                  style="display: none;" aria-hidden="true"></span>
                            <span class="btn_title"><?php echo esc_attr( $current_event[ 'btn_title' ] ) ?></span>
                        </button>
                    </div>
                </div>
            </form>
        </section>
        <?php
    }

    /**
     * Action casa_courses_header
     *
     * @return void
     */
    public function show_courses_header()
    {

        if ( wp_is_block_theme() ) :
            $header_option = get_option( 'casa_courses_header_template', true );
            $footer_option = get_option( 'casa_courses_footer_template', true );

            $header = do_blocks( $header_option );
            self::$footer = do_blocks( $footer_option );
        endif;
        ?>
        <!DOCTYPE html>
    <html lang="<?php echo esc_attr( get_locale() ) ?>">
        <?php wp_head(); ?>

    <body <?php body_class(); ?>>
        <?php wp_body_open(); ?>
        <a class="skip-link screen-reader-text"
           href="#wp--skip-link--target"><?php esc_attr_e( 'Skip to content', 'casa-courses' ) ?></a>
        <?php if ( wp_is_block_theme() ) : ?>
        <header class="wp-block-template-part site-header">
            <?php echo wp_kses_post( $header ); ?>
        </header>
    <?php else :
        get_header();
    endif;
    }

    /**
     * Action casa_courses_footer
     *
     * @return void
     */
    public function show_courses_footer()
    {
        if ( !wp_is_block_theme() ) :
            get_footer();
        else :

            echo wp_kses_post( self::$footer );
            echo "</body></html>";
        endif;
    }

    private function get_ordered_course_areas()
    {
        return get_terms( [
            'taxonomy'   => Casa_Courses_Custom_Taxonomy_Areas::$tax_type,
            'hide_empty' => false,
            'meta_key'   => 'casa_courses_areas_order',
            'orderby'    => 'meta_value_num',
            'order'      => 'ASC',
        ] );
    }

    private function get_ordered_courses_for_area( WP_Term $term ): array
    {
        return get_posts( [
            'post_type'   => Casa_Courses_Custom_Posttype_Courses::$post_type,
            'order'       => 'ASC',
            'meta_key'    => 'casa_courses_metadata_template_areas_' . $term->slug,
            'orderby'     => 'meta_value_num',
            'numberposts' => -1,
            'tax_query'   => array (
                array (
                    'taxonomy' => Casa_Courses_Custom_Taxonomy_Areas::$tax_type,
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                )
            )
        ] );
    }

    private function get_ordered_events_for_course( int $coursePostId ): array
    {
        $id = get_post_meta( $coursePostId, 'casa_courses_metadata_id' );

        return get_posts( [
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'meta_key'    => 'casa_events_metadata_next_available_date',
            'orderby'     => 'meta_value_num',
            'order'       => 'ASC',
            'numberposts' => -1,
            'meta_query'  => array (
                array (
                    'key'     => 'casa_events_metadata_template_id',
                    'value'   => $id,
                    'compare' => '=',
                )
            )
        ] );
    }
}
