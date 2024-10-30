<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class Casa_Courses_Worker_Dietary_Preferences
 *
 * This class handles the dietary preferences for Casa Courses workers.
 *
 * @package Casa_Courses
 */
class Casa_Courses_Worker_Dietary_Preferences
{

    /**
     * Handle Dietary Preferences
     *
     * Retrieves and handles dietary preferences data from the Casa API.
     *
     * @return string Returns a string indicating the status of the dietary preferences update.
     * @since 1.0.0
     */
    public static function handle(): string
    {
        $events = Casa_Api::handle( 'dietary-preferences/?limit=null' );
        $data = $events->message;

        if ( is_object( $data ) ) {
            if ( property_exists( $data, 'count' ) && $data->count > 0 ) {
                self::make( $data->results );
            } else {
                return esc_attr__( 'No dietary preferences found', 'casa-courses' );
            }
        }

        return esc_attr__( 'Dietary preferences updated', 'casa-courses' );
    }

    /**
     * Updates the dietary preferences JSON in the option table.
     *
     * @param mixed $data The data to be encoded as JSON.
     * @return void
     * @since 1.0.0
     */
    private static function make( $data ): void
    {
        update_option( 'casa_courses_dietary_preferences_json', wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
        update_option( 'casa_courses_dietary_preferences_loaded', gmdate( "c" ), 'no' );
    }
}
