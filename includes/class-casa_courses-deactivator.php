<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Casa_courses
 * @subpackage Casa_courses/includes
 * @author     foretagsakademincasa
 */
class Casa_courses_Deactivator
{

    /**
     * Deactivates the plugin by deleting the relevant options and removing a scheduled cron job.
     *
     * @return void
     * @since 1.0.0
     */
    public static function deactivate(): void
    {
        delete_option( 'casa_courses_token' );
        delete_option( 'casa_courses_domain' );
        delete_option( 'casa_courses_project_id' );

        // Remove casa_create_daily_sync cron job
        wp_clear_scheduled_hook( 'casa_cron_sync' );
    }
}
