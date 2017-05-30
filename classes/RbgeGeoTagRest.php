<?php

class RbgeGeoTagRest extends WP_REST_Controller {
 
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $namespace = 'rbge_geo_tag/v1';
    register_rest_route( $namespace, '/nearby' , array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_nearby_posts' ),
        'args' => array(
            'lat' => array(
                'required' => true,
                'validate_callback' => array($this, 'valid_latitude')
            ),
            'lon' => array(
                'required' => true,
                'validate_callback' => array($this, 'valid_longitude')
            ),
            'category' => array(
                'default' => 'nearby',
                'validate_callback' => array($this, 'valid_category_slug')
            )
        )
      )
    ));
  
  }
 
  /**
   * Get a nearby posts
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_nearby_posts( $request ) {
        
        global $wpdb;
        
        $out = array();
        
        // header with some generic stuff in
        $out['meta'] = array();
        $out['meta']['google_api_key'] = RBGE_GOOGLE_MAPS_KEY;
        $out['meta']['timestamp'] = time();

        $lat = $request['lat'];
        $lon = $request['lon'];
        $slug = $request['category'];
        
        $out['meta']['location'] = array('lat'=> $lat, 'lon' => $lon);
        $out['meta']['category_slug'] = $slug;
        
        $sql = "SELECT
          post_id,
          (
            ST_Length(
              ST_LineStringFromWKB(
                LineString(
                  geoPoint, 
                  GeomFromText('POINT($lat $lon)')
                )
              )
            )
          ) * 111000
          AS distance,
          ST_AsText(geopoint) as point
        FROM rbge_geo_tag_points AS geo
        JOIN wp_term_relationships rel ON rel.object_id = geo.post_id
        JOIN wp_term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
        JOIN wp_terms t ON t.term_id = tax.term_id
        WHERE t.slug = '$slug'
        ORDER BY distance ASC
        LIMIT 30";
        
        $out['meta']['sql'] = $sql;
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        $posts = array();
        foreach($results as $row){
            
            $post = get_post($row['post_id'], 'display');
            
            // add in info relevant to our application
            $npost = new stdClass();
            //$npost->post = $post;
            $npost->id = $post->ID;
            $npost->is_place = false;
            $npost->distance = $row['distance']; // to the current location
            
            $npost->title = $post->post_title;
            $b = strip_shortcodes($post->post_content);
            $b = strip_tags($b, '<b><strong><em><i>');
            $b = preg_replace("/[\r\n]+/", "\n", $b);
            $b = trim($b);
            $paragraphs = preg_split('/\n+/', $b);
            $b = '<p>' . implode('</p><p>', $paragraphs) . '</p>';
            $npost->body = $b; //nl2br(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", strip_shortcodes($post->post_content)));
            
            // get some categories incase we need them and to tag the places
            $post_categories = wp_get_post_categories( $row['post_id'] );
            $cats = array();
            foreach($post_categories as $c){
                $cat = get_category( $c );
                $cats[] = array( 'name' => $cat->name, 'slug' => $cat->slug );
                
                if($cat->slug == 'place') $post->rbge_geo->is_place = true;
            }
            $npost->categories = $cats;
            
            // images
            $npost->thumbnail_url = get_the_post_thumbnail_url($row['post_id'], 'thumbnail');
            $npost->large_url = get_the_post_thumbnail_url($row['post_id'], 'widescreen');
            
            //$npost->large_url = wp_get_attachment_image(get_post_thumbnail_id($row['post_id']), 'widescreen');
            
            // mp3 attached?
            $npost->mp3 = false;
            $media = get_attached_media( 'audio/mpeg', $row['post_id'] );
            foreach($media as $key => $val){
                $npost->mp3 = $val->guid;
                break; // just the first one
            }
            
            $wkt = str_replace('POINT(', '', $row['point']);
            $wkt = str_replace(')', '', $wkt);
            $lat_lon = explode(' ', $wkt);
            
            $npost->latitude = $lat_lon[0];
            $npost->longitude = $lat_lon[1];
            
            
            $posts[] = $npost;
        }
        
        //$out[] = $sql;
        $out['posts'] = $posts;
        
        return new WP_REST_Response( $out, 200 );
  }
  
  public function valid_latitude($lat){
      if(!is_numeric($lat)) return false;
      if($lat > 90) return false;
      if($lat < -90) return false;
      return true;
  }
 
  public function valid_longitude($lon){
      if(!is_numeric($lon)) return false;
      if($lon > 180) return false;
      if($lon < -180) return false;
      return true;
  }
  
  public function valid_category_slug($slug){
      if(preg_match('/^[a-z0-9-_]+$/', $slug) == 1){
          return true;
      }else{
          return false;
      }
  }
 
}

?>
