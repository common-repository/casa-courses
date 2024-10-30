<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Casa_courses
 * @subpackage Casa_courses/includes
 * @author     foretagsakademincasa
 */
class Casa_courses
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Casa_courses_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected Casa_courses_Loader $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected string $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected string $version;

    /**
     * Admin Page Menu identifier for this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var     Casa_Courses_Menu
     */
    protected Casa_Courses_Menu $casa_courses_menu;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if ( defined( 'CASA_COURSES_VERSION' ) ) {
            $this->version = CASA_COURSES_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'casa-courses';

        $this->load_dependencies();
        $this->define_init_hooks();
        $this->set_route();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_menu();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Casa_courses_Loader. Orchestrates the hooks of the plugin.
     * - Casa_courses_i18n. Defines internationalization functionality.
     * - Casa_courses_Admin. Defines all hooks for the admin area.
     * - Casa_courses_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies(): void
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-casa_courses-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-casa_courses-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-casa_courses-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-casa_courses-public.php';

        $this->loader = new Casa_courses_Loader();
    }

    /**
     * Register all the hooks related to the init
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_init_hooks(): void
    {
        $plugin_init = new Casa_Courses_Init();

        $this->loader->add_action( 'init', $plugin_init, 'casa_courses_custom_posttype' );
        $this->loader->add_action( 'init', $plugin_init, 'casa_courses_taxonomy_areas' );
        $this->loader->add_action( 'init', $plugin_init, 'casa_courses_taxonomy_radio_buttons' );
        $this->loader->add_action( 'init', $plugin_init, 'rest_api_end_point' );
        $this->loader->add_action( 'init', $plugin_init, 'casa_courses_cron_job' );

        $plugin_init->wp_enqueue_scripts();
    }

    /**
     * Register Custom Menu
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_menu(): void
    {
        $this->casa_courses_menu = new Casa_Courses_Menu();
    }

    /**
     * Define the route for this plugin
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_route(): void
    {
        $this->loader->add_action( 'plugins_loaded', new Casa_Courses_Routes, 'init' );
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Casa_courses_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale(): void
    {

        $plugin_i18n = new Casa_courses_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks(): void
    {
        $plugin_admin = new Casa_courses_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_init', $plugin_admin, 'plugin_initialize' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_post_casa_courses_general_form_response', $plugin_admin, 'casa_courses_general_form_response' );
        $this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'casa_dashboard_widget' );
        $this->loader->add_filter( 'plugin_action_links_' . plugin_basename( dirname( __FILE__, 2 ) . '/casa-courses.php' ), $plugin_admin, 'add_plugin_settings_link' );
    }

    /**
     * Register all the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void
    {

        $plugin_public = new Casa_courses_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'casa_courses_areas_section', $plugin_public, 'show_areas_section' );
        $this->loader->add_action( 'casa_courses_hero_section', $plugin_public, 'show_hero_section' );
        $this->loader->add_action( 'casa_courses_calendar_section', $plugin_public, 'show_calendar_section' );
        $this->loader->add_action( 'casa_courses_registration_section', $plugin_public, 'show_registration_section' );
        $this->loader->add_action( 'casa_courses_calendar_table_section', $plugin_public, 'show_calendar_table_section' );
        $this->loader->add_action( 'casa_courses_area_soon_course', $plugin_public, 'show_area_soon_course' );
        $this->loader->add_action( 'casa_courses_breadcrumb', $plugin_public, 'show_casa_breadcrumb' );
        $this->loader->add_action( 'casa_courses_area_soon_courses', $plugin_public, 'show_area_soon_courses' );
        $this->loader->add_action( 'casa_courses_list_view_all_section', $plugin_public, 'show_list_view_all' );
        $this->loader->add_action( 'casa_courses_events', $plugin_public, 'show_courses_events' );
        $this->loader->add_action( 'casa_courses_form', $plugin_public, 'show_courses_form' );
        $this->loader->add_action( 'casa_courses_header', $plugin_public, 'show_courses_header' );
        $this->loader->add_action( 'casa_courses_footer', $plugin_public, 'show_courses_footer' );
        $this->loader->add_action( 'casa_courses_form_message_sections', $plugin_public, 'show_courses_form_message_sections' );
    }

    /**
     * Run the loader to execute all the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run(): void
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version(): string
    {
        return $this->version;
    }
}
