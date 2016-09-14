<?php
/**
 * Plugin Name: WP Extended Archives Widget
 * Plugin URI: http://darkog.com
 * Description: Extendes the original WordPress archives widget with custom functionality
 * Version: 1.0
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
        add_filter( "getarchives_where", array( $this, "_modify_where_for_compatibility" ), 10, 2 );
        add_filter( "pre_get_posts", array($this, '_modify_archives_query'));
    }

    /**
     * Include the extended widget
     * @return void
     */
    private function includes()
    {
        require DG_EXTENDED_ARCHIVES . "/includes/functions.php";
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
     * Support multiple post_types archives
     *
     * @param $where
     * @param $args
     * @return string
     */
    public function _modify_where_for_compatibility($where, $args)
    {
        
        global $wp_version;
        if ( version_compare( $wp_version, '4.4', '<' ) )
        {
            if( isset( $args["post_type"] ) && strlen($args['post_type']) > 0 )
            {

                $post_type = $args['post_type'];
                $post_type_args = "post_type = 'post'";
                if(strpos($post_type, ',') !== false)
                {
                    $post_types = explode(',', $post_type);
                    if(is_array($post_types) && count($post_types) > 0)
                    {
                        for($i = 0; $i < count($post_types); $i++){

                            if( $i == 0 )
                            {
                                $post_type_args = "post_type = '" . $post_types[$i] . "'";
                            }
                            else
                            {
                                $post_type_args .= " AND WHERE post_type = '" . $post_types[$i] . "'";
                            }
                        }
                    }
                }
                else
                {  
                    $post_type_args = "post_type = '" . $post_type . "' ";
                }
                $where = "WHERE ". $post_type_args ." AND post_status = 'publish'";  

                return $where;
            }
        }
		return $where;
    }


    /**
     * Support multiple post type archives
     *
     * @param $where
     * @param $args
     * @return string
     */
    public function _modify_archives_query( $query ) {
        
        if( isset($_GET['post_type']) && $query->is_main_query() ){

            $post_type = $_GET['post_type'];

            if(strpos($post_type, ',') !== false){
                $post_types = explode(',', $post_type);
            }else {
                $post_types = (array)$_GET['post_type'];
            }

            if ( ! is_archive() && ! in_array( get_post_type(), $post_types ) )
                return $query;
            
            $query->set( 'post_type', $post_types );
        }

        return $query;
    }

}
DG_Extended_Archives::get_instance();