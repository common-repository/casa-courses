<?php

/**
 *
 * @link              https://github.com/Foretagsakademin/casa-wordpress-plugin.git
 * @since             1.0.0
 * @package           casa_courses
 *
 * @wordpress-plugin
 * Plugin Name:       Casa Courses
 * Plugin URI:        https://github.com/Foretagsakademin/casa-wordpress-plugin.git
 * Description:       Connect your Casa installation to your WordPress installation. With the plugin you will be able to list all of your templates and events. Your visitors will be able to book themselves on events with available seats.
 * Version:           1.0.1
 * Author:            foretagsakademincasa
 * Author URI:        https://www.foretagsakademin.se
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       casa-courses
 * Domain Path:       /languages
 * Requires at least: 6.4
 * Requires PHP:      8.1
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

use BooMeta\BooMeta;
use Carbon\Carbon;

/**
 * Current plugin version.
 */
define( 'CASA_COURSES_VERSION', '1.0.1' );

define( 'CASA_COURSES_API', '/api/v1/public/' );
define( 'CASA_COURSES_PROJECT_API', CASA_COURSES_API . 'projects/' );


require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

//Metabox API
require_once plugin_dir_path( __FILE__ ) . 'metabox/src/BooMeta.php';
require_once plugin_dir_path( __FILE__ ) . 'metabox/src/BooMetaFields.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-casa_courses-init.php';
//CPT
require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-casa_courses-custom-posttype_courses.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-casa_courses-custom-posttype_events.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/init/class-casa_courses-custom-taxonomy_areas.php';
//Worker
require_once plugin_dir_path( __FILE__ ) . 'includes/worker/class-casa_courses-worker-taxonomy_areas.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/worker/class-casa_courses-worker-custom_post_courses.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/worker/class-casa_courses-worker-custom_post_events.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/worker/class-casa_courses-worker-industry.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/worker/class-casa_courses-worker-dietary_preferences.php';
//helper
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers/helper.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers/class-casa_courses_api.php';

// Menu
require_once plugin_dir_path( __FILE__ ) . 'includes/class-casa_courses-menu.php';

//routes
require_once plugin_dir_path( __FILE__ ) . 'includes/class-casa_courses-routes.php';

if ( !function_exists( 'casa_courses_activate' ) ) {
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-casa_courses-activator.php
     */
    function casa_courses_activate(): void
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-casa_courses-activator.php';
        Casa_courses_Activator::activate();
    }
}

if ( !function_exists( 'casa_courses_deactivate' ) ) {
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-casa_courses-deactivator.php
     */
    function casa_courses_deactivate(): void
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-casa_courses-deactivator.php';
        Casa_courses_Deactivator::deactivate();

        unregister_post_type( Casa_Courses_Custom_Posttype_Courses::$post_type );
    }
}

register_activation_hook( __FILE__, 'casa_courses_activate' );
register_deactivation_hook( __FILE__, 'casa_courses_deactivate' );

//flush route
register_activation_hook( __FILE__, array (
    new Casa_Courses_Routes,
    'flush_rules'
) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-casa_courses.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
( function () {
    Carbon::setLocale( get_locale() );
    BooMeta::bootstrap();

    $plugin = new Casa_courses();
    $plugin->run();
} )();
