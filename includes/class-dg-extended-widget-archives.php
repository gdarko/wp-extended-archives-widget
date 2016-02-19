<?php

class DG_Extended_Widget_Archives extends WP_Widget {

    /**
     * Sets up a new Archives widget instance.
     *
     * @access public
     */
    public function __construct() {
        $widget_ops = array('classname' => 'widget_extended_archive', 'description' => __( 'A monthly archive of your site&#8217;s Custom Post Types.') );
        parent::__construct('extendedarchives', __('Extended WP Archives'), $widget_ops);
    }

    /**
     * Outputs the content for the current Archives widget instance.
     *
     * @access public
     * @param array $args - Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Archives widget instance.
     */
    public function widget( $args, $instance ) {
        $c = ! empty( $instance['count'] ) ? '1' : '0';
        $d = ! empty( $instance['dropdown'] ) ? '1' : '0';
        $ptype = ! empty( $instance['ptype'] ) ? $instance['ptype'] : 'post';
        $ttype = ! empty( $instance['ttype'] ) ? $instance['ttype'] : 'monthly';
        $limit = ! empty( $instance['archive_limit'] ) ? $instance['archive_limit'] : '';

        if(is_int( $limit ) || is_numeric( $limit ) && 0 === (int)$limit) {
            $limit = '';
        }

        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Archives' ) : $instance['title'], $instance, $this->id_base );

        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        if ( $d ) {
            $dropdown_id = "{$this->id_base}-dropdown-{$this->number}";
            ?>
            <label class="screen-reader-text" for="<?php echo esc_attr( $dropdown_id ); ?>"><?php echo $title; ?></label>
            <select id="<?php echo esc_attr( $dropdown_id ); ?>" name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'>
                <?php
                /**
                 * Filter the arguments for the Archives widget drop-down.
                 *
                 * @see wp_get_archives()
                 *
                 * @param array $args An array of Archives widget drop-down arguments.
                 */
                $dropdown_args = apply_filters( 'widget_archives_dropdown_args', array(
                    'type'            => $ttype,
                    'format'          => 'option',
                    'show_post_count' => $c,
                    'post_type'       => $ptype,
                    'limit'           => $limit
                ) );

                switch ( $dropdown_args['type'] ) {
                    case 'yearly':
                        $label = __( 'Select Year' );
                        break;
                    case 'monthly':
                        $label = __( 'Select Month' );
                        break;
                    case 'daily':
                        $label = __( 'Select Day' );
                        break;
                    case 'weekly':
                        $label = __( 'Select Week' );
                        break;
                    default:
                        $label = __( 'Select Post' );
                        break;
                }
                ?>

                <option value=""><?php echo esc_attr( $label ); ?></option>
                <?php wp_get_archives( $dropdown_args ); ?>

            </select>
        <?php } else { ?>
            <ul>
                <?php
                /**
                 * Filter the arguments for the Archives widget.
                 *
                 * @see wp_get_archives()
                 *
                 * @param array $args An array of Archives option arguments.
                 */
                wp_get_archives( apply_filters( 'widget_extended_archives_args', array(
                    'type'            => $ttype,
                    'show_post_count' => $c,
                    'post_type'       => $ptype,
                    'limit'           => $limit
                ) ) );
                ?>
            </ul>
            <?php
        }

        echo $args['after_widget'];
    }

    /**
     * Handles updating settings for the current Archives widget instance.
     *
     * @access public
     * @param array $new_instance New settings for this instance as input by the user via WP_Widget_Archives::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['count'] = $new_instance['count'] ? 1 : 0;
        $instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;
        $instance['ptype'] = !empty($new_instance['ptype']) && is_string($new_instance['ptype']) ? $new_instance['ptype'] : 'post';
        $instance['ttype'] = !empty($new_instance['ttype']) && is_string($new_instance['ttype']) ? $new_instance['ttype'] : 'monthly';
        $instance['archive_limit'] = !empty($new_instance['archive_limit']) ? $new_instance['archive_limit'] : '';
        return $instance;
    }

    /**
     * Outputs the settings form for the Archives widget.
     *
     * @access public
     * @param array $instance Current settings.
     * @return void
     */
    public function form( $instance ) {

        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '', 'alimit' => '') );
        $title    = sanitize_text_field( $instance['title'] );
        $limit    = sanitize_text_field( $instance['archive_limit'] );

        $ptypes = get_post_types( array( "_builtin" => false ) );

        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked( $instance['dropdown'] ); ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label>
            <br/>
            <input class="checkbox" type="checkbox"<?php checked( $instance['count'] ); ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('ttype'); ?>"><?php _e('Choose Archive Type:'); ?></label><br/>
            <select id="<?php echo $this->get_field_id('ttype'); ?>" name="<?php echo $this->get_field_name('ttype'); ?>" class="widefat">
                <option value="yearly" <?php selected( $instance['ttype'], 'yearly' ); ?> >Yearly</option>
                <option value="monthly" <?php selected( $instance['ttype'], 'monthly' ); ?> >Monthly</option>
                <option value="daily" <?php selected( $instance['ttype'], 'daily' ); ?> >Daily</option>
                <option value="weekly" <?php selected( $instance['ttype'], 'weekly' ); ?> >Weekly</option>
            </select>
        </p>
        <p><label for="<?php echo $this->get_field_id('ptype'); ?>"><?php _e('Choose Post Type:'); ?></label><br/>
            <select id="<?php echo $this->get_field_id('ptype'); ?>" name="<?php echo $this->get_field_name('ptype'); ?>" class="widefat">
                <option value="post" <?php selected( $instance['ptype'], 'post' ); ?> >Default (Post)</option>
                <?php foreach($ptypes as $post_type):
                        $post_type_obj = get_post_type_object( $post_type );?>
                        <option value="<?php echo strtolower($post_type); ?>" <?php selected( $instance['ptype'], strtolower($post_type) ); ?> ><?php echo $post_type_obj->labels->menu_name; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><label for="<?php echo $this->get_field_id('archive_limit'); ?>"><?php _e('Limit (For unlimited, use 0):'); ?></label><br/>
            <input type="number" name="<?php echo $this->get_field_name('archive_limit'); ?>" id="<?php echo $this->get_field_id('archive_limit'); ?>" value="<?php echo $limit; ?>" class="widefat">
        </p>
        <?php
    }
}

//new DG_Extended_Widget_Archives();