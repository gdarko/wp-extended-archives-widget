<?php


function wp_eaw_get_archives( $args = '' ) {
    global $wpdb, $wp_locale;
 
    $defaults = array(
        'type' => 'monthly', 'limit' => '',
        'format' => 'html', 'before' => '',
        'after' => '', 'show_post_count' => false,
        'echo' => 1, 'order' => 'DESC',
        'post_type' => 'post'
    );
 
    $r = wp_parse_args( $args, $defaults );

    $post_types = (array)$r['post_type'];

	foreach($post_types as $post_type){
    	$post_type_object = get_post_type_object( $post_type );
	    if ( ! is_post_type_viewable( $post_type_object ) ) {
	        return;
	    }
	}
 
    //$r['post_type'] = $post_type_object->name;
 
    if ( '' == $r['type'] ) {
        $r['type'] = 'monthly';
    }
 
    if ( ! empty( $r['limit'] ) ) {
        $r['limit'] = absint( $r['limit'] );
        $r['limit'] = ' LIMIT ' . $r['limit'];
    }
 
    $order = strtoupper( $r['order'] );
    if ( $order !== 'ASC' ) {
        $order = 'DESC';
    }
 
    // this is what will separate dates on weekly archive links
    $archive_week_separator = '&#8211;';
 
 	// Loop through the post types and add to the sql query
 	$sql_where = "";
 	for($i = 0; $i < count($post_types); $i++){
 		if( $i == 0 ) {
 			$sql_where = $wpdb->prepare( " WHERE post_type = %s", $post_types[$i] );
 		} else {
 			$sql_where .= $wpdb->prepare( " OR post_type = %s", $post_types[$i] );
 		}
 	}
 	$sql_where .= " AND post_status = 'publish'";

 	//var_dump($sql_where);
    
    /**
     * Filters the SQL WHERE clause for retrieving archives.
     *
     * @since 2.2.0
     *
     * @param string $sql_where Portion of SQL query containing the WHERE clause.
     * @param array  $r         An array of default arguments.
     */
    $where = apply_filters( 'getarchives_where', $sql_where, $r );
 
    /**
     * Filters the SQL JOIN clause for retrieving archives.
     *
     * @since 2.2.0
     *
     * @param string $sql_join Portion of SQL query containing JOIN clause.
     * @param array  $r        An array of default arguments.
     */
    $join = apply_filters( 'getarchives_join', '', $r );
 
    $output = '';
 
    $last_changed = wp_cache_get( 'last_changed', 'posts' );
    if ( ! $last_changed ) {
        $last_changed = microtime();
        wp_cache_set( 'last_changed', $last_changed, 'posts' );
    }
 
    $limit = $r['limit'];
 
    if ( 'monthly' == $r['type'] ) {
        $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
        $key = md5( $query );
        $key = "wp_get_archives:$key:$last_changed";
        if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
            $results = $wpdb->get_results( $query );
            wp_cache_set( $key, $results, 'posts' );
        }
        if ( $results ) {
            $after = $r['after'];
            foreach ( (array) $results as $result ) {
                $url = get_month_link( $result->year, $result->month );

                if( count($post_types) > 0 ){
                	$types_div = implode(',',$post_types);
                	$url = add_query_arg( 'post_type', $types_div, $url );
                }

                /* translators: 1: month name, 2: 4-digit year */
                $text = sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $result->month ), $result->year );
                if ( $r['show_post_count'] ) {
                    $r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                }
                $output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
            }
        }
    } elseif ( 'yearly' == $r['type'] ) {
        $query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date $order $limit";
        $key = md5( $query );
        $key = "wp_get_archives:$key:$last_changed";
        if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
            $results = $wpdb->get_results( $query );
            wp_cache_set( $key, $results, 'posts' );
        }
        if ( $results ) {
            $after = $r['after'];
            foreach ( (array) $results as $result) {
                $url = get_year_link( $result->year );

                if( count($post_types) > 0 ){
                	$types_div = implode(',',$post_types);
                	$url = add_query_arg( 'post_type', $types_div, $url );
                }

                $text = sprintf( '%d', $result->year );
                if ( $r['show_post_count'] ) {
                    $r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                }
                $output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
            }
        }
    } elseif ( 'daily' == $r['type'] ) {
        $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
        $key = md5( $query );
        $key = "wp_get_archives:$key:$last_changed";
        if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
            $results = $wpdb->get_results( $query );
            wp_cache_set( $key, $results, 'posts' );
        }
        if ( $results ) {
            $after = $r['after'];
            foreach ( (array) $results as $result ) {
                $url  = get_day_link( $result->year, $result->month, $result->dayofmonth );
                if( count($post_types) > 0 ){
                	$types_div = implode(',',$post_types);
                	$url = add_query_arg( 'post_type', $types_div, $url );
                }
                $date = sprintf( '%1$d-%2$02d-%3$02d 00:00:00', $result->year, $result->month, $result->dayofmonth );
                $text = mysql2date( get_option( 'date_format' ), $date );
                if ( $r['show_post_count'] ) {
                    $r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                }
                $output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
            }
        }
    } elseif ( 'weekly' == $r['type'] ) {
        $week = _wp_mysql_week( '`post_date`' );
        $query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` $order $limit";
        $key = md5( $query );
        $key = "wp_get_archives:$key:$last_changed";
        if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
            $results = $wpdb->get_results( $query );
            wp_cache_set( $key, $results, 'posts' );
        }
        $arc_w_last = '';
        if ( $results ) {
            $after = $r['after'];
            foreach ( (array) $results as $result ) {
                if ( $result->week != $arc_w_last ) {
                    $arc_year       = $result->yr;
                    $arc_w_last     = $result->week;
                    $arc_week       = get_weekstartend( $result->yyyymmdd, get_option( 'start_of_week' ) );
                    $arc_week_start = date_i18n( get_option( 'date_format' ), $arc_week['start'] );
                    $arc_week_end   = date_i18n( get_option( 'date_format' ), $arc_week['end'] );
                    $url            = add_query_arg( array( 'm' => $arc_year, 'w' => $result->week, ), home_url( '/' ) );
	                if( count($post_types) > 0 ){
	                	$types_div = implode(',',$post_types);
	                	$url = add_query_arg( 'post_type', $types_div, $url );
	                }
                    $text           = $arc_week_start . $archive_week_separator . $arc_week_end;
                    if ( $r['show_post_count'] ) {
                        $r['after'] = '&nbsp;(' . $result->posts . ')' . $after;
                    }
                    $output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
                }
            }
        }
    } elseif ( ( 'postbypost' == $r['type'] ) || ('alpha' == $r['type'] ) ) {
        $orderby = ( 'alpha' == $r['type'] ) ? 'post_title ASC ' : 'post_date DESC, ID DESC ';
        $query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
        $key = md5( $query );
        $key = "wp_get_archives:$key:$last_changed";
        if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
            $results = $wpdb->get_results( $query );
            wp_cache_set( $key, $results, 'posts' );
        }
        if ( $results ) {
            foreach ( (array) $results as $result ) {
                if ( $result->post_date != '0000-00-00 00:00:00' ) {
                    $url = get_permalink( $result );
                    if ( $result->post_title ) {
                        /** This filter is documented in wp-includes/post-template.php */
                        $text = strip_tags( apply_filters( 'the_title', $result->post_title, $result->ID ) );
                    } else {
                        $text = $result->ID;
                    }
                    $output .= get_archives_link( $url, $text, $r['format'], $r['before'], $r['after'] );
                }
            }
        }
    }

    if ( $r['echo'] ) {
        echo $output;
    } else {
        return $output;
    }
}