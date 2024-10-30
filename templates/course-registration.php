<?php

/**
 * Casa Event Connect Page Template
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( !isset( $_GET[ 'id' ] ) || empty( sanitize_text_field( wp_unslash( $_GET[ 'id' ] ) ) ) ) {
    casa_404_page();
}

$id = sanitize_text_field( wp_unslash( $_GET[ 'id' ] ) );

$event = Casa_Courses_Worker_Custom_Post_Events::get_event( $id );

if ( !$event ) {
    casa_404_page();
}

$template_id = get_post_meta( $event[ 0 ]->ID, "casa_events_metadata_template_id", true );

/** event sync with the API */
Casa_Courses_Worker_Custom_Post_Events::sync_events_participants( $template_id );

/** refresh event after sync */
$event = Casa_Courses_Worker_Custom_Post_Events::get_event( $id );

if ( !$event ) {
    casa_404_page();
}

$post = get_posts( array (
    'numberposts' => 1,
    'post_status' => 'publish',
    'post_type'   => Casa_Courses_Custom_Posttype_Courses::$post_type,
    'meta_key'    => 'casa_courses_metadata_id',
    'meta_value'  => $template_id,
) );

$title = get_option( 'casa_courses_registration_title' );
$terms = wp_get_object_terms( $post[ 0 ]->ID, 'casa_courses_areas' );

do_action( 'casa_courses_header' );
?>
    <section class="form-casa__courses ">
        <div class="container">
            <div class="row">
                <?php do_action( 'casa_courses_breadcrumb', [ 'title'    => $title,
                                                              'terms'    => $terms,
                                                              'template' => $post[ 0 ]
                ] ); ?>
                <div class="col-12 casa-form__section">
                    <?php do_action( 'casa_courses_registration_section', $event ); ?>

                    <div class="casa-form__section">
                        <?php do_action( 'casa_courses_form', [ 'id'    => $id,
                                                                'event' => $event
                        ] ); ?>
                    </div>
                </div>
                <div class="col-12">
                    <?php do_action( 'casa_courses_form_message_sections', [ 'id'    => $id,
                                                                             'event' => $event
                    ] ); ?>
                </div>
            </div>
        </div>
    </section>
<?php
do_action( 'casa_courses_footer' );
