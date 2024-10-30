<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Casa_Courses_Custom_Taxonomy_Areas
 */
class Casa_Courses_Custom_Taxonomy_Areas
{


    public static string $tax_type = 'casa_courses_areas';

    /**
     * Sets up the "Areas" taxonomy for the 'casa-courses' post type.
     *
     * @param string $name The name of the post type. Default is 'casa-courses'.
     * @since 1.0.0
     */
    public static function casa_courses_setup_area_taxonomy( string $name = 'casa_courses' ): void
    {
        /**
         * Taxonomy: Areas.
         */
        $args = [
            'label'                 => __( 'Course areas', 'casa-courses' ),
            'labels'                => [
                'name'          => __( 'Course areas', 'casa-courses' ),
                'singular_name' => __( 'Course area', 'casa-courses' ),
            ],
            'public'                => false,
            'publicly_queryable'    => false,
            'hierarchical'          => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'query_var'             => true,
            'rewrite'               => [
                'slug'       => self::$tax_type,
                'with_front' => true
            ],
            'capabilities'          => array (
                'delete_terms' => 'do_not_allow',
            ),
            'show_admin_column'     => false,
            'show_in_rest'          => false,
            'rest_base'             => self::$tax_type,
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'show_in_quick_edit'    => false,
            'show_in_graphql'       => false,
        ];
        register_taxonomy( self::$tax_type, [ $name ], $args );

        // add id field in editable form
        add_action( 'casa_courses_areas_edit_form_fields', function ( $term, $taxonomy ) {
            $image = $icon = '';

            $casa_id = get_term_meta( $term->term_id, Casa_Courses_Worker_Taxonomy_Areas::$meta_id, true );

            $image_id = get_term_meta( $term->term_id, Casa_Courses_Worker_Taxonomy_Areas::$meta_image, true );
            if ( $image_id ) {
                $att_image = wp_get_attachment_image_src( $image_id, 'medium' );
                $image = $att_image ? $att_image[ 0 ] : false;
            }

            $order = get_term_meta( $term->term_id, Casa_Courses_Worker_Taxonomy_Areas::$meta_order, true );

            $text_box = get_term_meta( $term->term_id, Casa_Courses_Worker_Taxonomy_Areas::$meta_text_box, true );
            ?>
            <tr class="form-field">
                <th><label for="casa_courses_areas_id">ID</label></th>
                <td>
                    <input name="casa_courses_areas_id" readonly id="casa_courses_areas_id" type="text"
                           value="<?php echo esc_attr( $casa_id ) ?>"/>
                </td>
            </tr>
            <tr class="form-field">
                <th><label for="casa_courses_areas_order"><?php esc_attr_e( 'Order', 'casa-courses' ) ?></label></th>
                <td>
                    <input name="casa_courses_areas_order" readonly id="casa_courses_areas_order" type="text"
                           value="<?php echo esc_attr( $order ) ?>"/>
                </td>
            </tr>
            <tr class="form-field">
                <th>
                    <label for="_add_areas_icon"><?php esc_attr_e( 'Area Description:', 'casa-courses' ); ?></label>
                </th>
                <td>
                    <?php
                    ob_start();
                    wp_editor( $text_box, Casa_Courses_Worker_Taxonomy_Areas::$meta_text_box, array (
                        'textarea_name' => Casa_Courses_Worker_Taxonomy_Areas::$meta_text_box,
                        'textarea_rows' => 15,
                        'media_buttons' => true,
                        'tinymce'       => array (
                            'toolbar1' => 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                            'toolbar2' => 'formatselect,fontsizeselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        ),
                    ) );

                    echo wp_kses_post( ob_get_clean() );
                    ?>
                </td>
            </tr>
            <tr class="form-field">
                <th>
                    <label for="_add_areas_image"><?php esc_attr_e( 'Areas Image:', 'casa-courses' ); ?></label>
                </th>
                <td>
                    <input type="hidden" name="casa_courses_areas_image"
                           value="<?php echo esc_attr( $image_id ) ?>" id="_casa_courses_areas_image"
                           class="casa-courses-areas-image">
                    <div class="img-wrap">
                        <img src="<?php echo esc_url( $image ) ?>" id="casa_courses_areas_image-view"
                             alt="<?php echo esc_attr( $term->name ) ?>"<?php if ( empty( $image_id ) ) echo ' style="display: none;"'; ?>>
                    </div>
                    <div class="casa_courses-file-action">
                        <input class="upload_image_button button" name="_add_areas_image" id="_add_areas_image"
                               type="button" value="<?php esc_attr_e( 'Select Image', 'casa-courses' ); ?>"/>
                        <span class="casa__courses-link" <?php if ( empty( $image_id ) ) echo 'style="display: none;"'; ?>>
                            <a class="delete" id="_remove_areas_image" type="button"
                               title="<?php esc_attr_e( 'Remove Image', 'casa-courses' ); ?>">
                                <?php esc_attr_e( 'Remove Image', 'casa-courses' ); ?>
                            </a>
                        </span>
                    </div>
                </td>
            </tr>
            <?php
        }, 10, 2 );

        // save/update terma_meta.
        add_action( 'created_casa_courses_areas', 'casa_courses_save_term_fields' );
        add_action( 'edited_casa_courses_areas', 'casa_courses_save_term_fields' );

        function casa_courses_save_term_fields( $term_id ): void
        {
            update_term_meta(
                $term_id,
                Casa_Courses_Worker_Taxonomy_Areas::$meta_id,
                sanitize_text_field( wp_unslash ( $_POST[ Casa_Courses_Worker_Taxonomy_Areas::$meta_id ] ?? '' ) )
            );

            update_term_meta(
                $term_id,
                Casa_Courses_Worker_Taxonomy_Areas::$meta_image,
                sanitize_text_field( wp_unslash ( $_POST[ Casa_Courses_Worker_Taxonomy_Areas::$meta_image ] ?? '' ) )
            );

            update_term_meta(
                $term_id,
                Casa_Courses_Worker_Taxonomy_Areas::$meta_text_box,
                sanitize_text_field( wp_unslash ( $_POST[ Casa_Courses_Worker_Taxonomy_Areas::$meta_text_box ] ?? '' ) )
            );
        }
    }
}
