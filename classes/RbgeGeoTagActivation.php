<?php

class RbgeGeoTagActivation{

public static function activate(){
    
    global $wpdb;
    global $wp_rewrite;
  
    /*
        // firstly the geo points table - recreated and populated each time we activate
    */
  
    $sql = "CREATE TABLE rbge_geo_tag_points (
        post_id mediumint(9) NOT NULL,
        geoPoint POINT NOT NULL,
        SPATIAL INDEX(geoPoint),
        PRIMARY KEY post_id (post_id)
    ) ENGINE=MyISAM";

    if ( ! function_exists('dbDelta') ) {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    dbDelta( $sql );

    // populate the db
    $sql = "INSERT INTO rbge_geo_tag_points (post_id, geopoint) 
    select latitude.post_id, ST_GeomFromText(concat('POINT(', trim(latitude.meta_value), ' ', trim(longitude.meta_value), ')'))
    from wp_postmeta as longitude
    join wp_postmeta as latitude on longitude.post_id = latitude.post_id
    join wp_posts as posts on latitude.post_id = posts.id
    where
    	longitude.meta_key = 'geo_longitude'
    and
    	latitude.meta_key = 'geo_latitude'
    and 
    	LENGTH(longitude.meta_value) > 0
    and 
    	LENGTH(latitude.meta_value) > 0
    and
        posts.post_type = 'post'";
    	
    $wpdb->query($sql);
    
    /*
        Now a table to store locations tagged with soothingness
    */
    
    $sql = "CREATE TABLE IF NOT EXISTS `rbge_geo_tag_soothe` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `timestamp` timestamp NULL DEFAULT NULL,
      `soothed_slider` tinyint(4) DEFAULT NULL,
      `excited_slider` tinyint(4) DEFAULT NULL,
      `anxious_slider` tinyint(4) DEFAULT NULL,
      `latitude` decimal(10,8) DEFAULT NULL,
      `longitude` decimal(11,8) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `memorable_word` varchar(255) DEFAULT NULL,
      `comments` text,
      PRIMARY KEY (`id`)
      ) ENGINE=MyISAM";

    dbDelta( $sql );
    
    
    /*
        And one to track locations of data requests
    */
    $sql = "CREATE TABLE IF NOT EXISTS `rbge_geo_tag_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `latitude` decimal(10,8) NOT NULL,
      `longitude` decimal(11,8) NOT NULL,
      `beacon` varchar(100) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM";

    dbDelta( $sql );
    
    
}

public static function deactivate(){
    global $wpdb;
    $sql = "DROP TABLE rbge_geo_tag_points;";
    $wpdb->query($sql);
    
    // FIXME: We should have a warning displayed that tables will remain after deactivation.
    
}


} // end class

/*
    how to get the post by distance - saved for later
    SELECT
      post_id,
      (
        ST_Length(
          ST_LineStringFromWKB(
            LineString(
              geoPoint, 
              GeomFromText('POINT(55.96509 -3.21003)')
            )
          )
        )
      ) * 111000
      AS distance
    FROM rbge_geo_tag_points
    ORDER BY distance ASC
    LIMIT 100

*/


?>