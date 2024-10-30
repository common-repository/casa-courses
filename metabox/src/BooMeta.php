<?php

namespace BooMeta;

/**
 * Meta Fields
 *
 * @package       BooMeta
 * @author        WP Engine
 *
 * @wordpress-plugin
 * Plugin Name:   Advanced Meta Fields
 * Plugin URI:    
 * Description:   Customize WordPress with powerful fields.
 * Version:       -
 * Author:        -
 * Author URI:    -
 * Update URI:    -
 * Text Domain:   boo_meta
 * Domain Path:   /lang
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('BooMeta')) {

    /**
     * The main BooMeta class
     */
    class BooMeta
    {

        /**
         * The plugin version number.
         *
         * @var string
         */
        public $version = '0.0.1';

        /**
         * Storage for class instances.
         *
         * @var array
         */
        public $instances = array();

        /**
         * Meta Box for class instances.
         *
         * @var array
         */
        public static $meta_box = array();

        /**
         * The plugin settings array.
         *
         * @var array
         */
        public $settings = array();

        /**
         * Returns an instance or null if doesn't exist.
         *
         * @param   string $class The instance class name.
         * @return  ?BooMeta
         */
        public function get_instance($class): ?BooMeta
        {
            $name = strtolower($class);
            return isset($this->instances[$name]) ? $this->instances[$name] : null;
        }

        /**
         * Creates and stores an instance of the given class.
         *
         * @param   string $class The instance class name.
         * @return  BooMeta
         */
        public function new_instance($class): BooMeta
        {
            $instance                 = new $class();
            $name                     = strtolower($class);
            $this->instances[$name] = $instance;
            return $instance;
        }

        /**
         * Returns an instance or null if doesn't exist.
         *
         * @param [type] $fields
         * @param [type] $post
         * @return  void
         */
        public function fields($fields = [], $post = null)
        {
            $meta_box = new BooMetaFields($fields, $post);
            $name     = strtolower($post);

            self::$meta_box[$name] = $meta_box;
        }

        /**
         * Get Meta Box instance by name
         *
         * @param [type] $name
         * @return BooMetaFields
         */
        public static function get_instance_meta_box($name): BooMetaFields
        {
            return self::$meta_box[strtolower($name)];
        }


        /**
         * Defines a constant if doesn`t already exist.
         *
         * @param   string $name The constant name.
         * @param   mixed  $value The constant value.
         * @return  void
         */
        public function define($name, $value = true)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        /**
         * Sets up the ACF plugin.
         *
         * @return  void
         */
        public function initialize()
        {

            // Define constants.
            $this->define('Boo_Meta', true);
            $this->define('Boo_Meta_PATH', __DIR__);
            $this->define('Boo_Meta_VERSION', $this->version);
            $this->define('Boo_Meta_LOCAL', 'boo_meta_box');

            // Define settings.
            $this->settings = array(
                'name'                   => esc_attr__('Meta Fields', 'casa-courses'),
                'version'                => Boo_Meta_VERSION,
                'path'                   => Boo_Meta_PATH,
                'file'                   => __FILE__,
                'url'                    => plugin_dir_url(__FILE__),
                'current_language'       => get_locale(),
                'l10n_textdomain'        => Boo_Meta_LOCAL,

            );
        }

        /**
         * Bootstrap
         */
        public static function bootstrap()
        {
            global $casa_boo_meta;

            // Instantiate only once.
            if (!isset($casa_boo_meta)) {
                $casa_boo_meta = new BooMeta();
                $casa_boo_meta->initialize();
            }
        }
    }
}
