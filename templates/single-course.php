<?php

/**
 * Casa Single Courses Page Template
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'casa_courses_header' );

the_post();

$extra_price = 0;

$title = get_the_title();

$show_price = get_option( 'casa_courses_show_price', true );


$description = @json_decode( get_post_meta( $post->ID, 'casa_courses_metadata_description', true ) );
// sorting by order
is_array( $description ) && usort( $description, function ( $a1, $a2 ) {
    return $a1->order <=> $a2->order;
} );

$extra_costs = @json_decode( get_post_meta( $post->ID, 'casa_courses_metadata_extra_costs_price', true ) );

if ( is_array( $extra_costs ) && count( $extra_costs ) ) {
    foreach ( $extra_costs as $cost ) {
        $extra_price += $cost->price;
    }
}

$price = get_post_meta( $post->ID, 'casa_courses_metadata_price', true );
$currency = get_post_meta( $post->ID, 'casa_courses_metadata_currency', true );
$project_price = get_post_meta( $post->ID, 'casa_courses_metadata_project_price', true );
$project_currency = get_post_meta( $post->ID, 'casa_courses_metadata_project_currency', true );
$number_of_days = get_post_meta( $post->ID, 'casa_courses_metadata_number_of_days', true );
$formatter = new NumberFormatter( get_locale(), NumberFormatter::CURRENCY );
$formatter->setAttribute( NumberFormatter::MAX_FRACTION_DIGITS, 0 );

$final_price = $extra_price + $price;

if ( !empty( $project_price ) ) {
    $final_project_price = $extra_price + $project_price;
}

$terms = wp_get_object_terms( $post->ID, 'casa_courses_areas' );

?>
    <div class="main-content">
        <div class="container course-detail">
            <?php if ( is_array( $terms ) && count( $terms ) > 0 && is_object( $terms[ 0 ] ) ) : ?>
                <?php do_action( 'casa_courses_breadcrumb', [
                    'title' => get_the_title(),
                    'terms' => $terms
                ] ); ?>
            <?php endif ?>
            <div class="row">
                <div class="col-12">
                    <h1><?php the_title(); ?></h1>
                </div>
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-12 content casa__single-course-desc">
                            <?php the_content(); ?>
                        </div>
                        <?php
                        if ( !empty( $description ) && is_array( $description ) ) :
                            foreach ( $description as $value ) :
                                echo '<div class="col-12 description">';
                                if ( $value->is_subject_visible ) :
                                    echo '<h2>' . esc_attr( $value->subject ) . '</h2>';
                                endif;
                                echo wp_kses_post( $value->information );
                                echo '</div>';
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                <div class="col-lg-4 aside">
                    <?php if ( $show_price === 'true' || $number_of_days > 0 ) : ?>
                        <div class="card">
                            <div class="card-header">
                                <?php if ( !empty( $number_of_days ) && $show_price === 'true' ) : ?>
                                    <?php esc_attr_e( 'Price and duration', 'casa-courses' ) ?>
                                <?php elseif ( !empty( $number_of_days ) ) : ?>
                                    <?php esc_attr_e( 'Number of days', 'casa-courses' ) ?>
                                <?php else : ?>
                                    <?php esc_attr_e( 'Price', 'casa-courses' ) ?>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="card-text">
                                    <?php if ( !empty( $final_price ) && $show_price === 'true' ) :
                                        if ( !empty( $final_project_price ) && $final_project_price !== $final_price ) :
                                        ?>
                                        <div class="template-price">
                                            <?php esc_attr_e( 'Your price', 'casa-courses' ) ?>: <?php echo esc_attr( str_replace( ',', ' ', $formatter->formatCurrency( (int)$final_project_price, $project_currency ) ) ) ?>
                                        </div>
                                        <div class="template-price">
                                            <?php esc_attr_e( 'Regular price', 'casa-courses' ) ?>: <?php echo esc_attr( str_replace( ',', ' ', $formatter->formatCurrency( (int)$final_price, $currency ) ) ) ?>
                                        </div>
                                        <?php
                                        else:
                                        ?>
                                        <div class="template-price">
                                            <?php esc_attr_e( 'Price', 'casa-courses' ) ?>: <?php echo esc_attr( str_replace( ',', ' ', $formatter->formatCurrency( (int)$final_price, $currency ) ) ) ?>
                                        </div>
                                        <?php
                                            endif;
                                            if ( !empty( $number_of_days ) ) : ?>
                                            <div class="template-length">
                                                <?php
                                                /* translators: %s: number of days in course */
                                                echo esc_attr( sprintf( _n( '%s day', '%s days', $number_of_days, 'casa-courses' ), $number_of_days ) );
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ( !empty( $number_of_days ) ) : ?>
                                        <div class="template-length">
                                            <?php
                                            /* translators: %s: number of days in course */
                                            echo esc_attr( sprintf( _n( '%s day', '%s days', $number_of_days, 'casa-courses' ), $number_of_days ) );
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php do_action( 'casa_courses_events', $post->ID ); ?>
                </div>
            </div>
        </div>
    </div>

<?php
do_action( 'casa_courses_footer' );
