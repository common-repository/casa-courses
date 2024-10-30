<?php
/**
 * Casa Area Page Template
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

$area = sanitize_text_field( get_query_var( 'current_page' ) );
$term = get_term_by( 'slug', $area, Casa_Courses_Custom_Taxonomy_Areas::$tax_type );
$description = "";

if ( $term ) {
    $description = get_term_meta( $term->term_id, Casa_Courses_Worker_Taxonomy_Areas::$meta_text_box, true );
} else {
    casa_404_page();
}

do_action( 'casa_courses_header' );
?>
    <section class="area-casa-courses">
        <div class="container">
            <div class="row">
                <?php do_action( 'casa_courses_breadcrumb', [ 'title' => $term->name ] ); ?>
                <div class="col-12">
                    <h1 class="area-casa__title"><?php echo esc_attr( $term->name ) ?></h1>
                    <?php if ( $description ) : ?>
                        <div class="area-casa__desc">
                            <?php echo wp_kses_post( $description ) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <?php do_action( 'casa_courses_areas_section', [ 'slug' => $term->slug ] ); ?>
                </div>
                <div class="col-12">
                    <?php do_action( 'casa_courses_area_soon_course', $term ) ?>
                </div>
            </div>
        </div>
    </section>
<?php
do_action( 'casa_courses_footer' );
