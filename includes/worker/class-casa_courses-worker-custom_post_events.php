<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


use Carbon\Carbon;

/**
 * Casa_Courses_Worker_Custom_Post_Events class
 */
class Casa_Courses_Worker_Custom_Post_Events
{
    public static string $status_booked = 'booked';
    public static string $status_reserved = 'reserved';

    /**
     * Handle method to update events
     *
     * @return string Returns the result message after updating events
     * @since 1.0.0
     */
    public static function handle(): string
    {
        $date = Carbon::now();
        $days = intval( get_option( 'casa_courses_delay_days', '0' ) );

        if ( $days > 0 ) {
            $date->addDays( $days );
        }

        $events = Casa_Api::handle( 'events/?limit=null&start_date=' . $date->format( 'Y-m-d' ) );
        $data = $events->message;

        if ( is_object( $data ) && property_exists( $data, 'count' ) && $data->count > 0 ) {
            self::make( $data->results );
        } else {
            return esc_attr__( 'No events found', 'casa-courses' );
        }

        return esc_attr__( 'Events updated', 'casa-courses' );
    }

    /**
     * Make events
     *
     * @param array $events An array of events
     * @return void
     * @since 1.0.0
     */
    private static function make( array $events ): void
    {
        $ids = [];
        $project_id = get_option( 'casa_courses_project_id', false );

        foreach ( $events as $event ) {
            if ( $project_id && !empty( $event->projects ) ) {
                $hideProjects = array_filter( $event->projects, function ( $project ) use ( $project_id ) {
                    return $project->project_id === $project_id && $project->hide_event;
                } );

                if ( !empty( $hideProjects ) ) {
                    continue;
                }
            }

            $post = get_posts( array (
                'numberposts' => -1,
                'post_status' => 'any',
                'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
                'meta_key'    => 'casa_events_metadata_id',
                'meta_value'  => $event->id,
            ) );

            if ( count( $post ) ) {
                $id = self::update( $event, $post[ 0 ] );
            } else {
                $id = self::create( $event );
            }

            isset( $event->invoice_type ) && update_post_meta( $id, 'casa_events_metadata_invoice_type', $event->invoice_type );
            isset( $event->template_id ) && update_post_meta( $id, 'casa_events_metadata_template_id', $event->template_id );

            isset( $event->start_date ) && update_post_meta( $id, 'casa_events_metadata_start_date', $event->start_date );
            isset( $event->end_date ) && update_post_meta( $id, 'casa_events_metadata_end_date', $event->end_date );

            update_post_meta( $id, 'casa_events_metadata_available_seats', self::get_available_seats( $event, $project_id ) );
            update_post_meta( $id, 'casa_events_metadata_max_participant_count', self::get_event_max_participant( $event, $project_id ) );

            isset( $event->venue_city ) && update_post_meta( $id, 'casa_events_metadata_venue_city', $event->venue_city );
            isset( $event->venue_name ) && update_post_meta( $id, 'casa_events_metadata_venue_name', $event->venue_name );

            isset( $event->name ) && update_post_meta( $id, 'casa_events_metadata_event_name', $event->name );

            isset( $event->order ) && update_post_meta( $id, 'casa_events_metadata_order', $event->order );

            if ( isset( $event->price ) ) {
                self::update_meta_price( $event->price, $id );
            }

            if ( isset( $event->sessions ) && count( $event->sessions ) > 0 ) {
                self::update_next_available_date( $event->sessions, $id );
                self::update_meta_sessions( $event->sessions, $id );
            }

            $ids[] = $id;
        }

        self::exclude_events_by_ids( $ids );

        update_option( 'casa_courses_events_loaded', gmdate( "c" ), 'no' );
    }

    /**
     * Update the next available date and corresponding timezone in post meta
     *
     * @param array $sessions An array of sessions
     * @param int $id The post ID
     * @return void
     * @since 1.0.0
     */
    public static function update_next_available_date( array $sessions, int $id ): void
    {
        $date = null;
        $timezone = null;
        foreach ( $sessions as $key => $session ) {
            if ( is_array( $session->days ) && $key == 0 ) {
                $date = $session->days[ 0 ]->start_date;
                $timezone = $session->timezone;
            } else if ( is_array( $session->days ) ) {
                if ( Carbon::parse( $session->days[ 0 ]->start_date )->timezone( $session->timezone )->diffInMinutes( $date, false ) > 0 ) {
                    $date = $session->days[ 0 ]->start_date;
                    $timezone = $session->timezone;
                }
            }
        }

        !empty( $date ) && update_post_meta( $id, 'casa_events_metadata_next_available_date', $date );
        !empty( $timezone ) && update_post_meta( $id, 'casa_events_metadata_available_date_timezone', $timezone );
    }

    /**
     * Exclude events by IDs
     *
     * Fetches the events by excluding the specified IDs and deletes them if found.
     *
     * @param array $ids The IDs of the events to be excluded
     * @return void
     * @since 1.0.0
     */
    private static function exclude_events_by_ids( array $ids ): void
    {

        $exclude_events = get_posts( array (
            'numberposts' => -1,
            'post_status' => 'any',
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'exclude'     => $ids,
            'hide_empty'  => false
        ) );

        if ( !empty( $exclude_events ) ) {
            self::delete( $exclude_events );
        }
    }

    /**
     * Delete events and their associated data
     *
     * @param array $events An array of events to be deleted
     * @return void
     * @since 1.0.0
     */
    private static function delete( array $events ): void
    {
        foreach ( $events as $event ) {
            // Don`t need clean meta fields. WP clean it automatically
            wp_delete_post( $event->ID );
        }
    }

    /**
     * Update event post
     *
     * @param object $event The event object containing the updated information
     * @param object $post The post object to be updated
     * @return int The ID of the updated post
     * @since 1.0.0
     */
    private static function update( object $event, object $post ): int
    {
        $post->post_title = $event->name;
        $post->post_date = $event->date_created;
        $post->post_status = $event->is_active ? 'publish' : 'draft';

        wp_update_post( $post );

        return $post->ID;
    }

    /**
     * Create a new event post
     *
     * @param object $event The event object containing the necessary information
     * @return int|false The ID of the created post or false if an error occurred
     * @since 1.0.0
     */
    private static function create( object $event ): int
    {
        $args = array (
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'post_title'  => $event->name,
            'post_date'   => $event->date_created,
            'post_status' => $event->is_active ? 'publish' : 'draft',
        );

        $post_id = wp_insert_post( $args );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        update_post_meta( $post_id, 'casa_events_metadata_id', $event->id );

        return $post_id;
    }

    /**
     * Update the meta price and currency for a given post.
     *
     * @param object $price The price object that contains the price and currency.
     * @param int $post_id The ID of the post to update the meta for.
     * @return void
     * @since
     */
    private static function update_meta_price( object $price, int $post_id ): void
    {
        if ( isset( $price->price ) ) {
            isset( $price->price ) && update_post_meta( $post_id, 'casa_events_metadata_price', $price->price );
            isset( $price->currency ) && update_post_meta( $post_id, 'casa_events_metadata_currency', $price->currency );
        }
    }

    /**
     * Update the meta sessions for a given post.
     *
     * @param array $sessions The sessions data to be updated.
     * @param int $post_id The ID of the post to update the meta for.
     * @since 1.0.0
     */
    private static function update_meta_sessions( array $sessions, int $post_id ): void
    {
        update_post_meta( $post_id, 'casa_events_metadata_sessions', wp_json_encode( $sessions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
    }

    private static function get_available_seats( object $event, string $project_id ): int
    {
        $max_participant_count = $event->max_participant_count;
        $booked_participants = $event->booked_participants;
        $available_seats = $max_participant_count - $booked_participants;

        if ( $available_seats > 0 && !empty( $project_id ) ) {
            $project_found = false;

            foreach ( $event->project_participants as $project_participants ) {
                if ( $project_participants->project_id === $project_id ) {
                    $project_found = true;
                    $available_seats = min( $available_seats, $project_participants->max_participant_count - $project_participants->participant_count );
                    break;
                }
            }

            if ( !$project_found ) {
                $available_seats = 0;
            }
        }

        if ( $available_seats < 0 ) {
            $available_seats = 0;
        }

        return (int)$available_seats;
    }

    /**
     * Get the maximum participant count for an event.
     *
     * @param object $event The event object.
     * @return int The maximum participant count.
     * @since 1.0.0
     */
    private static function get_event_max_participant( object $event, string $project_id ): int
    {
        $max_participant = $event->max_participant_count;

        if ( !empty( $project_id ) && isset( $event->project_participants ) && count( $event->project_participants ) > 0 ) {
            foreach ( $event->project_participants as $participants ) {
                if ( $participants->project_id === $project_id ) {
                    if ( $max_participant > $participants->max_participant_count ) {
                        $max_participant = $participants->max_participant_count;
                    }
                    break;
                }
            }
        }

        return (int)$max_participant;
    }

    /**
     * Get the event with the given Casa ID.
     *
     * @param string $casa_id The ID of the event to retrieve.
     * @return array|null The event data as an array, or null if the event was not found.
     * @since 1.0.0
     */
    public static function get_event( string $casa_id ): ?array
    {
        return get_posts( array (
            'numberposts' => 1,
            'post_status' => 'publish',
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'meta_key'    => 'casa_events_metadata_id',
            'meta_value'  => $casa_id,
        ) );
    }

    /**
     * Sync the participants of a set of coming events.
     *
     * @param string $casa_id The ID of the template to retrieve coming events from.
     * @return void
     * @since 1.0.0
     */
    public static function sync_events_participants( string $casa_id ): void
    {
        $events = Casa_Api::handle( 'templates/' . $casa_id . '/coming-events/?limit=null' );

        if ( isset( $events->message ) && $events->success == "true" && is_object( $events->message ) ) {
            foreach ( $events->message->results as $event ) {
                self::sync_event_participants( $event );
            }
        }
    }

    /**
     * Synchronize event participants with the corresponding post meta.
     *
     * @param object $event The event object.
     * @return void
     * @since 1.0.0
     */
    private static function sync_event_participants( object $event ): void
    {
        $post = get_posts( array (
            'numberposts' => 1,
            'post_status' => 'any',
            'post_type'   => Casa_Courses_Custom_Posttype_Events::$post_type,
            'meta_key'    => 'casa_events_metadata_id',
            'meta_value'  => $event->id,
        ) );

        if ( count( $post ) ) {
            $project_id = get_option( 'casa_courses_project_id' );

            update_post_meta( $post[ 0 ]->ID, 'casa_events_metadata_available_seats', self::get_available_seats( $event, $project_id ) );
            update_post_meta( $post[ 0 ]->ID, 'casa_events_metadata_max_participant_count', self::get_event_max_participant( $event, $project_id ) );
        }
    }

    /**
     * Connect a participant to an event.
     *
     * @param array $data The data of the participant to connect.
     * @param mixed $token The recaptcha token.
     * @return stdClass The response message from the API.
     * @since 1.0.0
     */
    public static function connect_participant( array $data, $token ): stdClass
    {
        $status = $data[ 'status' ];
        unset( $data[ 'status' ] );
        $id = $data[ 'event_id' ];
        unset( $data[ 'event_id' ] );
        $company = self::get_company_data( $data );
        $participants = self::get_participants_data( $data );
        $contact_data = self::get_contact_data( $data );

        $connect_data = new stdClass();
        $connect_data->event = $id;
        $connect_data->contact_person = $contact_data;
        $connect_data->status = $status;
        $connect_data->book_as_reserve = true;

        if ( count( (array)$company ) > 0 ) {
            $connect_data->company = $company;
        }

        if ( count( $participants ) > 0 ) {
            $connect_data->participants = $participants;
        }

        if ( !empty( $token ) ) {
            $connect_data->token = $token;
        }

        $message = Casa_Api::handle( 'events/' . $id . '/connect/', 'POST', wp_json_encode( $connect_data, JSON_UNESCAPED_UNICODE ) );

        if ( $message->success == "true" ) {
            $current_event = Casa_Api::handle( 'events/' . $id . '/' );
            self::sync_event_participants( $current_event->message );
        }

        return $message;
    }

    /**
     * Create Casa post data from form data.
     *
     * @param mixed &$data The data object containing participants information.
     * @return array An array containing participant objects.
     */
    private static function get_participants_data( &$data ): array
    {
        $participants = [];

        foreach ($data[ 'participants' ] as $array_participant ) {
            $participant = new stdClass();
            empty( $array_participant[ 'dietary_preferences' ] ) ?: $participant->dietary_preference = $array_participant[ 'dietary_preferences' ];
            empty( $array_participant[ 'dietary_preference_custom' ] ) ?: $participant->dietary_preference = $array_participant[ 'dietary_preference_custom' ];
            $participant->participant = new stdClass();
            empty( $array_participant[ 'cell_phone_number' ] ) ?: $participant->participant->cell_phone_number = $array_participant[ 'cell_phone_number' ];
            empty( $array_participant[ 'email' ] ) ?: $participant->participant->email = $array_participant[ 'email' ];
            $participant->participant->first_name = $array_participant[ 'first_name' ];
            $participant->participant->last_name = $array_participant[ 'last_name' ];

            $participants[] = $participant;
        }

        unset( $data[ 'participants' ] );
        return $participants;
    }


    /**
     * Get the contact data from the provided form data.
     *
     * @param mixed $data The data array that contains the contact data.
     * @return array The contact data and associated ID.
     * @since
     */
    private static function get_contact_data( $data ): array
    {
        $payload = [];

        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {
                if ( $key !== 'event_id' && !empty( $value ) ) {
                    $payload[ $key ] = $value;
                }
            }
        }

        return $payload;
    }

    /**
     * Get the company data from the form data array and return it as an object.
     *
     * @param mixed $data The data array containing the company data.
     * @return stdClass The object containing the company data.
     */
    private static function get_company_data( &$data ): stdClass
    {
        $company_data = new stdClass();
        $address = new stdClass();

        $company_fields = [
            'company_id'            => 'company_id',
            'company_name'          => 'name',
            'company_corporate_id'  => 'corporate_id',
            'company_sector'        => 'sector',
            'address_zip_code'      => 'zip_code',
            'address_city'          => 'city',
            'address_email'         => 'email',
            'address_address_row_1' => 'address_row_1',
        ];

        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value ) {

                if ( array_key_exists( $key, $company_fields ) && !empty( $value ) ) {
                    if (
                        str_starts_with( $key, 'company' )
                    ) {
                        $company_data->{$company_fields[ $key ]} = $value;
                    } else {
                        $address->{$company_fields[ $key ]} = $value;
                    }
                }

                if ( array_key_exists( $key, $company_fields ) ) {
                    unset( $data[ $key ] );
                }
            }
        }

        if ( count( (array)$address ) > 0 ) {
            $address->country = "SE";
            $address->type = "invoicing";
            $company_data->addresses = [ $address ];
        }

        return $company_data;
    }
}
