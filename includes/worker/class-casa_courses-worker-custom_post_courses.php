<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class Casa_Courses_Worker_Custom_Post_Courses
 *
 * This class is responsible for handling the courses custom post type.
 */
class Casa_Courses_Worker_Custom_Post_Courses
{
    /**
     * Handles the logic for updating courses.
     *
     * @return string The success message indicating the courses have been updated.
     * @since 1.0.0
     */
    public static function handle(): string
    {
        $templates = Casa_Api::handle( 'templates/?limit=null' );
        $data = $templates->message;

        if ( is_object( $data ) && property_exists( $data, 'count' ) && $data->count > 0 ) {
            self::make( $data->results );
        } else {
            return esc_attr__( 'No courses found', 'casa-courses' );
        }

        return esc_attr__( 'Courses updated', 'casa-courses' );
    }

    /**
     * Creates or updates courses meta data and taxonomy.
     *
     * @param array $courses The array of courses.
     *
     * @return void
     * @since 1.0.0
     */
    private static function make( array $courses ): void
    {
        $ids = [];
        $project_id = get_option( 'casa_courses_project_id', false );

        foreach ( $courses as $course ) {
            $project_information = null;

            if ( $project_id && !empty( $course->projects ) ) {
                $projects = array_values( array_filter( $course->projects, function ( $project ) use ( $project_id ) {
                    return $project->project_id === $project_id;
                } ) );

                if ( !empty ( $projects ) ) {
                    $project_information = $projects[ 0 ];
                    if ( $project_information->hide_event ) {
                        continue;
                    }
                }
            }

            $post = get_posts( array (
                'numberposts' => -1,
                'post_status' => 'any',
                'post_type'   => Casa_Courses_Custom_Posttype_Courses::$post_type,
                'meta_key'    => 'casa_courses_metadata_id',
                'meta_value'  => $course->id,
            ) );

            if ( count( $post ) ) {
                $id = self::update( $course, $post[ 0 ] );
            } else {
                $id = self::create( $course );
            }

            self::update_taxonomy_areas( $course, $id );

            isset( $course->invoice_type ) && update_post_meta( $id, 'casa_courses_metadata_invoice_type', $course->invoice_type );
            isset( $course->number_of_days ) && update_post_meta( $id, 'casa_courses_metadata_number_of_days', $course->number_of_days );

            if ( isset( $course->price ) ) {
                if ( isset( $project_information->project_price ) ) {
                    self::update_meta_project_price( $project_information->project_price, $id );
                }

                self::update_meta_price( $course->price, $id );
            }

            if ( isset( $course->extra_costs ) && is_array( $course->extra_costs ) ) {
                self::update_meta_extra_price( $course->extra_costs, $id );
            }

            if ( isset( $course->descriptions ) && count( $course->descriptions ) ) {
                self::update_meta_description( $course, $id );
            }

            if ( isset( $course->template_areas ) && is_array( $course->template_areas ) ) {
                self::update_meta_template_areas( $course->template_areas, $id );
            }

            $ids[] = $id;
        }

        self::delete_non_existent_courses_by_ids( $ids );

        update_option( 'casa_courses_courses_loaded', gmdate( "c" ), 'no' );
    }

    /**
     * Deletes courses that are not present in the list of ids.
     *
     * @param array $ids An array of course IDs to exclude.
     *
     * @return void
     * @since 1.0.0
     */
    private static function delete_non_existent_courses_by_ids( array $ids ): void
    {
        $exclude_courses = get_posts( array (
            'numberposts' => -1,
            'post_status' => 'any',
            'post_type'   => Casa_Courses_Custom_Posttype_Courses::$post_type,
            'exclude'     => $ids,
            'hide_empty'  => false
        ) );

        if ( !empty( $exclude_courses ) ) {
            self::delete( $exclude_courses );
        }
    }

    /**
     * Deletes the given courses.
     *
     * @param array $courses The array of courses to delete.
     *
     * @return void
     * @since 1.0.0
     */
    private static function delete( array $courses ): void
    {
        foreach ( $courses as $course ) {
            wp_delete_post( $course->ID );
        }
    }

    /**
     * Updates the taxonomy areas for a given course.
     *
     * @param object $course The object containing the course information.
     * @param int $id The ID of the course to update the taxonomy areas for.
     *
     * @return void
     * @since 1.0.0
     */
    private static function update_taxonomy_areas( object $course, int $id ): void
    {
        $append = false;

        if ( !empty( $course->areas ) ) {
            foreach ( $course->areas as $key => $area ) {
                if ( $key !== array_key_first( $course->areas ) ) {
                    $append = true;
                }

                $term = Casa_Courses_Worker_Taxonomy_Areas::get_taxonomy_areas( $area->id );

                if ( !empty( $term ) && is_array( $term ) ) {
                    wp_set_object_terms( $id, $term[ 0 ]->term_id, Casa_Courses_Custom_Taxonomy_Areas::$tax_type, $append );
                }
            }
        }
    }

    /**
     * Updates the post information and returns the ID of the updated post.
     *
     * @param object $course The object containing the course information.
     * @param object $post The object representing the post to update.
     *
     * @return int The ID of the updated post.
     * @since 1.0.0
     */
    private static function update( object $course, object $post ): int
    {

        $post->post_title = $course->name;
        $post->post_date = $course->date_created;
        $post->post_status = $course->is_active ? 'publish' : 'draft';

        wp_update_post( $post );

        return $post->ID;
    }

    /**
     * Creates a new course post and returns its ID.
     *
     * @param object $course The object containing the course information.
     *
     * @return int The ID of the newly created course post.
     * @since 1.0.0
     */
    private static function create( object $course ): int
    {
        $args = array (
            'post_type'   => Casa_Courses_Custom_Posttype_Courses::$post_type,
            'post_title'  => $course->name,
            'post_date'   => $course->date_created,
            'post_status' => $course->is_active ? 'publish' : 'draft',
        );

        $post_id = wp_insert_post( $args );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        update_post_meta( $post_id, 'casa_courses_metadata_id', $course->id );

        return $post_id;
    }


    /**
     * Updates the meta price and currency for a given post.
     *
     * @param object $price The object containing the price and currency information.
     * @param int $post_id The ID of the post to update the meta for.
     *
     * @return void
     * @since 1.0.0
     */
    private static function update_meta_price( object $price, int $post_id ): void
    {
        if ( isset( $price->price ) ) {
            isset( $price->price ) && update_post_meta( $post_id, 'casa_courses_metadata_price', $price->price );
            isset( $price->currency ) && update_post_meta( $post_id, 'casa_courses_metadata_currency', $price->currency );
        }
    }

    private static function update_meta_project_price( object $price, int $post_id ): void
    {
        if ( isset( $price->price ) ) {
            isset( $price->price ) && update_post_meta( $post_id, 'casa_courses_metadata_project_price', $price->price );
            isset( $price->currency ) && update_post_meta( $post_id, 'casa_courses_metadata_project_currency', $price->currency );
        }
    }

    /**
     * Updates the meta price and currency for extra costs of a given post.
     *
     * @param array $extra_costs An array of objects containing the extra costs information.
     * @param int $post_id The ID of the post to update the meta for.
     *
     * @return void
     * @since 1.0.0
     */
    private static function update_meta_extra_price( array $extra_costs, int $post_id ): void
    {
        $project_id = get_option( 'casa_courses_project_id', false );
        $extra_cost = [];
        foreach ( $extra_costs as $key => $cost ) {
            if ( $cost->is_default && ( ( $project_id && $cost->project === $project_id ) || ( !$project_id && !$cost->project ) ) ) {
                $extra_cost[ $key ][ 'description' ] = $cost->description;
                $extra_cost[ $key ][ 'price' ] = $cost->price->price;
                $extra_cost[ $key ][ 'currency' ] = $cost->price->currency;
                $extra_cost[ $key ][ 'is_default' ] = $cost->is_default;
                $extra_cost[ $key ][ 'is_discountable' ] = $cost->is_discountable;
            }
        }

        update_post_meta( $post_id, 'casa_courses_metadata_extra_costs_price', wp_json_encode( $extra_cost, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
    }

    /**
     * Updates the meta description for a given post.
     *
     * @param object $course The object containing the course information.
     * @param int $post_id The ID of the post to update the meta for.
     *
     * @return void
     * @since 1.0.0
     */
    private static function update_meta_description( object $course, int $post_id ): void
    {
        $desc = [];
        foreach ( $course->descriptions as $key => $info ) {
            $desc[ $key ][ 'subject' ] = $info->subject;
            $desc[ $key ][ 'information' ] = wp_kses_post( str_replace( "\"", "'", $info->information ) );
            $desc[ $key ][ 'order' ] = $info->order;
            $desc[ $key ][ 'is_subject_visible' ] = $info->is_subject_visible;
        }

        update_post_meta( $post_id, 'casa_courses_metadata_description', wp_json_encode( $desc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
    }


    /**
     * Updates the meta template areas for a given post.
     *
     * @param array $template_areas An array of objects representing the template areas and their order.
     * @param int $post_id The ID of the post to update the meta for.
     *
     * @return void
     * @since 1.0.0
     */
    private static function update_meta_template_areas( array $template_areas, int $post_id ): void
    {
        foreach ( $template_areas as $info ) {
            update_post_meta( $post_id, 'casa_courses_metadata_template_areas_' . $info->area_slug, $info->order );
        }
    }
}
