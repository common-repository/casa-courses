<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Carbon\Carbon;

if ( !function_exists( 'casa_date_formatter' ) ) {
    /**
     * Formats the given start and end dates into a string.
     *
     * @param Carbon $start_date The start date.
     * @param Carbon $end_date The end date.
     *
     * @return string The formatted date string.
     * @since 1.0.0
     */
    function casa_date_formatter( Carbon $start_date, Carbon $end_date ): string
    {
        if ( $start_date->diffInDays( $end_date, false ) === 0 ) {
            $data = $start_date->isoFormat( 'D MMM HH:mm' ) . ' - ';
            $data .= $end_date->isoFormat( 'HH:mm' );
        } else {
            $data = $start_date->isoFormat( 'D MMM' ) . ' - ';
            $data .= $end_date->isoFormat( 'D MMM' );
        }

        return $data;
    }
}

if ( !function_exists( 'casa_next_date_available' ) ) {
    /**
     * Determines the next available date based on the given parameters.
     *
     * @param mixed $date The date to check availability.
     * @param string $timezone The timezone to use for date comparisons.
     * @param mixed $start_date The start date for availability.
     * @param int $id The ID of the event.
     * @param array $data The data array containing event details.
     *
     * @return array The updated data array with availability information.
     * @since 1.0.0
     */
    function casa_next_date_available( $date, string $timezone, $start_date, int $id, array $data ): array
    {
        if (
            !empty( $date ) &&
            Carbon::now()->timezone( $timezone )->diffInMinutes( $date, false ) < 0
        ) {
            return $data;
        }
        if (
            !empty( $start_date ) &&
            Carbon::parse( $date )->timezone( $timezone )->diffInMinutes( $start_date, false ) < 0
        ) {
            return $data;
        }

        $data[ 'start_date' ] = $date;
        $data[ 'tz' ] = $timezone;
        $data[ 'available_seats' ] = get_post_meta( $id, 'casa_events_metadata_available_seats', true );

        return $data;
    }
}

if ( !function_exists( 'casa_event_message_status' ) ) {
    /**
     * Determines the status message for an event based on the given data.
     *
     * @param mixed $data The data for the event.
     *
     * @return string The status message for the event. An empty string if no status message.
     * @since 1.0.0
     */
    function casa_event_message_status( $available_seats ): string
    {
        if ( !isset( $available_seats ) ) {
            return '';
        }

        $seats_rem = get_option( 'casa_courses_seats_remaining', false );
        $seats_full = get_option( 'casa_courses_seats_full', false );
        $limit = get_option( 'casa_courses_limited_available', false );

        if ( $available_seats > 0 && $available_seats <= $limit ) {
            return "<p class='few-seats-remaining m-0 mt-1'>" . esc_attr( $seats_rem ) . '</p>';
        }

        if ( $available_seats <= 0 ) {
            return "<p class='fully-booked m-0 mt-1'>" . esc_attr( $seats_full ) . '</p>';
        }

        return '';
    }
}

/**
 * Filtering next available course
 */
if ( !function_exists( 'casa_event_status' ) ) {
    /**
     * Returns the status of a CASA event based on the provided data.
     *
     * @param mixed $data The data for the CASA event.
     * @return string The status of the CASA event. Possible values are "limited", "sold" and "bookable".
     * @since 1.0.0
     */
    function casa_event_status( $data ): string
    {
        if ( is_array( $data ) && ( !isset( $data[ 'available_seats' ] ) ) ) {
            return '';
        }

        $available = (int)$data[ 'available_seats' ];
        $limit = get_option( 'casa_courses_limited_available', false );

        if ( $available > 0 && $available <= $limit ) {
            return esc_attr( 'limited' );
        }

        if ( $available <= 0 ) {
            return esc_attr( 'sold' );
        }

        return esc_attr( 'bookable' );
    }
}


if ( !function_exists( 'casa_404_page' ) ) {
    /**
     * Sets up a custom 404 page for CASA events.
     *
     * @return void
     * @global WP_Query $wp_query The global WordPress query object.
     * @since 1.0.0
     */
    function casa_404_page(): void
    {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 );
        exit();
    }
}
