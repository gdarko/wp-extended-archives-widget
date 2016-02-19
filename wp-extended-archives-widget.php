<?php
/**
 * Plugin Name: WP Extended Archives Widget
 * Plugin URI: http://darkog.com
 * Description: Extendes the original WordPress archives widget with custom functionality
 * Version: 0.1
 * Author: Darko Gjorgjijoski
 * Author URI: http://darkog.com
 *
 */

defined("ABSPATH") OR die("No script kiddies please");
define("DG_EXTENDED_ARCHIVES", plugin_dir_path(__FILE__));

class DG_Extended_Archives
{
    /**
     * @var DG_Extended_Archives
     */
    public static $_instance;

    /**
     * @return DG_Extended_Archives
     */
    public static function get_instance()
    {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
     * DG_Extended_Archives constructor.
     */
    public function __construct()
    {
        $this->includes();
        add_action( "widgets_init", array( $this, "_register_widget" ) );
        add_filter( "getarchives_where", array( $this, "_older_versions_compatibility" ), 10, 2 );
    }

    /**
     * Include the extended widget
     * @return void
     */
    private function includes()
    {
        require DG_EXTENDED_ARCHIVES . "/includes/class-dg-extended-widget-archives.php";
    }

    /**
     * Register the extended widget
     * @return void
     */
    public function _register_widget()
    {
        register_widget("DG_Extended_Widget_Archives");
    }

    /**
     * Add "post_type" support for older versions
     *
     * @param $where
     * @param $args
     * @return string
     */
    public function _older_versions_compatibility($where, $args)
    {
        global $wp_version;
        if ( version_compare( $wp_version, '4.4', '<' ) )
        {
            if( isset( $args["post_type"] ) )
            {
                $post_type = $args['post_type'];
                $where = "WHERE post_type = " . $post_type ." AND post_status = 'publish'";
                return $where;
            }
        }
		return $where;
    }
}
DG_Extended_Archives::get_instance();