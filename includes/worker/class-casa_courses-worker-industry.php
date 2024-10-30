<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class Casa_Courses_Worker_Industry
 *
 * This class is responsible for updating industries
 *
 * @package Casa_Courses_Worker
 */
class Casa_Courses_Worker_Industry
{

    /**
     * Handle industries data
     *
     * @return string Returns a string message indicating whether the industries were updated or not
     * @since 1.0.0
     */
    public static function handle(): string
    {
        $events = Casa_Api::handle( 'sectors/?limit=null' );
        $data = $events->message;

        if ( is_object( $data ) ) {
            if ( property_exists( $data, 'count' ) && $data->count > 0 ) {
                self::make( $data->results );
            } else {
                return esc_attr__( 'No industries found', 'casa-courses' );
            }
        }

        return esc_attr__( 'Industries updated', 'casa-courses' );
    }

    /**
     * Updates the value of the option 'casa_courses_industry_json' with the JSON-encoded data.
     *
     * @param mixed $data The data to be encoded and stored in the option.
     * @return void
     * @since 1.0.0
     */
    private static function make( $data ): void
    {
        update_option( 'casa_courses_industry_json', wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
        update_option( 'casa_courses_industry_loaded', gmdate( "c" ), 'no' );
    }
}
