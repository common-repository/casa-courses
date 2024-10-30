<?php

/**
 * Casa Calendar Page Template
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'casa_courses_header' );

$title = get_option( 'casa_courses_calendar_title' );

?>
    <section class="calendar-casa__courses">
        <div class="container">
            <div class="col-12">
                <?php do_action( 'casa_courses_breadcrumb', [ 'title' => $title ] ); ?>

                <?php do_action( 'casa_courses_calendar_section' ); ?>
            </div>
            <div class="col-12">
                <?php do_action( 'casa_courses_calendar_table_section' ); ?>
            </div>
        </div>
    </section>
<?php
do_action( 'casa_courses_footer' );
