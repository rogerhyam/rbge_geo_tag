<?php

class RbgeGeoTagActivation{

public static function activate(){
    
    global $wpdb;
  
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
    
}

public static function deactivate(){
    global $wpdb;
    $sql = "DROP TABLE rbge_geo_tag_points;";
    $wpdb->query($sql);
}


}

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