<?php
/**
 * Casa Home Page Template
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'casa_courses_header' );

?>
    <section class="home-casa__courses container">

        <?php do_action( 'casa_courses_hero_section' ); ?>

        <?php do_action( 'casa_courses_list_view_all_section' ); ?>

    </section>

<?php
do_action( 'casa_courses_footer' );
