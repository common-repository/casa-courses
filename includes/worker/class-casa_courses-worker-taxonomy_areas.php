<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Class Casa_Courses_Worker_Taxonomy_Areas
 *
 * This class handles the synchronization of data with an external API for the taxonomy 'casa_courses_areas'.
 * It also provides methods to retrieve and update terms in the 'casa_courses_areas' taxonomy.
 */
class Casa_Courses_Worker_Taxonomy_Areas
{
    public static string $meta_order = 'casa_courses_areas_order';
    public static string $meta_id = 'casa_courses_areas_id';
    public static string $meta_image = 'casa_courses_areas_image';
    public static string $meta_icon = 'casa_courses_areas_icon';

    public static string $meta_text_box = 'casa_courses_areas_text_box';

    /**
     * Handle the updating of taxonomy casa_courses_areas
     *
     * @return string Returns a string indicating the status of the update
     * @since 1.0.0
     */
    public static function handle(): string
    {
        $areas = Casa_Api::handle( 'areas/?limit=null' );
        $data = $areas->message;

        if ( is_object($data) ) {
            if (property_exists($data, 'count') && $data->count > 0) {
                self::make($data->results);
            } else {
                return esc_attr__( 'No areas found', 'casa-courses' );
            }
        }


        return esc_attr__( 'Areas updated', 'casa-courses' );
    }

    /**
     * Get terms from the taxonomy casa_courses_areas based on the given ID
     *
     * @param string $casa_id The ID to search for in the meta_query
     * @return array|null The terms matching the given ID or null if no terms are found
     * @since 1.0.0
     */
    public static function get_taxonomy_areas( string $casa_id ): ?array
    {
        return get_terms( array (
            'hide_empty' => false,
            'meta_query' => array (
                array (
                    'key'     => self::$meta_id,
                    'value'   => $casa_id,
                    'compare' => 'LIKE'
                )
            ),
            'taxonomy'   => Casa_Courses_Custom_Taxonomy_Areas::$tax_type,
        ) );
    }

    /**
     * Perform operations to create or update areas in the custom taxonomy.
     *
     * @param array $areas An array of areas to be processed.
     *
     * @return void
     * @since 1.0.0
     */
    private static function make( array $areas ): void
    {
        $ids = [];

        foreach ($areas as $area) {
            $name = sanitize_text_field( $area->name );
            $slug = sanitize_text_field( $area->slug );
            $casa_id = sanitize_text_field( $area->id );
            $order = sanitize_text_field( $area->order );
            $term = self::get_taxonomy_areas( $casa_id );

            if ( empty( $term ) ) {
                $ids[] = self::create( $name, $slug, $casa_id, $order );
            } else {
                $ids[] = self::update( $term[ 0 ], $name, $slug, $casa_id, $order );
            }
        }

        $exclude_areas = get_terms( array (
            'taxonomy'   => Casa_Courses_Custom_Taxonomy_Areas::$tax_type,
            'exclude'    => $ids,
            'hide_empty' => false
        ) );

        if ( !empty( $exclude_areas ) ) {
            self::delete( $exclude_areas );
        }

        update_option( 'casa_courses_areas_loaded', gmdate( "c" ), 'no' );
    }

    /**
     * Creates a new term in a custom taxonomy area.
     *
     * @param string $name The name of the term.
     * @param string $slug The slug of the term.
     * @param string $casa_id The ID of the term.
     * @param int $order The order of the term.
     *
     * @return int The term ID of the created term.
     *
     * @throws RuntimeException If term creation fails with an error message.
     * @since 1.0.0
     */
    private static function create( string $name, string $slug, string $casa_id, int $order ): int
    {
        $tax = wp_insert_term( $name, Casa_Courses_Custom_Taxonomy_Areas::$tax_type, [
            'slug' => $slug,
        ] );

        if ( is_wp_error( $tax ) ) {
            $tax = term_exists( $slug, Casa_Courses_Custom_Taxonomy_Areas::$tax_type );

            if ( is_array( $tax ) ) {
                return $tax[ 'term_id' ];
            }

            throw new RuntimeException( esc_attr( $tax->get_error_message() ) );
        }

        self::update_meta( $tax, self::$meta_id, $casa_id );
        self::update_meta( $tax, self::$meta_order, $order );

        return $tax[ 'term_id' ];
    }

    /**
     * Updates an existing term in a custom taxonomy area.
     *
     * @param WP_Term $term The term object to update.
     * @param string $name The new name of the term.
     * @param string $slug The new slug of the term.
     * @param string $casa_id The new ID of the term.
     * @param int $order The new order of the term.
     *
     * @return int The term ID of the updated term.
     *
     * @since 1.0.0
     */
    private static function update( WP_Term $term, string $name, string $slug, string $casa_id, int $order ): int
    {
        $image = get_term_meta( $term->term_id, self::$meta_image, true );
        $icon = get_term_meta( $term->term_id, self::$meta_icon, true );
        $text_box = get_term_meta( $term->term_id, self::$meta_text_box, true );

        wp_update_term(
            $term->term_id,
            Casa_Courses_Custom_Taxonomy_Areas::$tax_type,
            [
                'name' => $name,
                'slug' => $slug,
            ]
        );

        self::update_meta( $term, self::$meta_id, $casa_id );
        self::update_meta( $term, self::$meta_order, $order );
        self::update_meta( $term, self::$meta_image, $image );
        self::update_meta( $term, self::$meta_icon, $icon );
        self::update_meta( $term, self::$meta_text_box, $text_box );

        return $term->term_id;
    }

    /**
     * Delete multiple course area terms.
     *
     * @param array $terms The array of terms to delete.
     * @return void
     * @since 1.0.0
     */
    private static function delete( array $terms ): void
    {
        foreach ( $terms as $term ) {
            // Don`t need clean meta fields. WP clean it automatically
            wp_delete_term( $term->term_id, Casa_Courses_Custom_Taxonomy_Areas::$tax_type );
        }
    }

    /**
     * Updates metadata for a given term.
     *
     * @param array|WP_Term $term The term to update the metadata for. Can be either the term array
     *                              with the 'term_id' key, or a term object.
     * @param string $key The metadata key to update.
     * @param mixed $value The new value for the metadata key.
     *
     * @return void
     * @since 1.0.0
     */
    private static function update_meta( $term, string $key, $value ): void
    {

        update_term_meta(
            is_object( $term ) ? $term->term_id : $term[ 'term_id' ],
            $key,
            $value
        );
    }
}
