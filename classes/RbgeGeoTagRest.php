<?php

class RbgeGeoTagRest extends WP_REST_Controller {
 
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    
    $namespace = 'rbge_geo_tag/v1';
    
    // called with lat/lon for nearby posts
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
    
    // called with abbreviated URL from beacon for category based list of items
    register_rest_route( $namespace, '/beacon' , array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_beacon_posts' ),
        'args' => array(
            'beacon_uri' => array(
                'required' => true,
                'validate_callback' => array($this, 'is_url'),
                'sanitize_callback' => array($this, 'slug_from_url')
            ),
            'category' => array(
                'required' => false,
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
        
        //$out['meta']['sql'] = $sql;
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        $posts = array();
        foreach($results as $row){
            $post = get_post($row['post_id'], 'display');
            $npost = $this->get_npost($post);
            $npost->distance = $row['distance']; // to the current location
            /*
            $wkt = str_replace('POINT(', '', $row['point']);
            $wkt = str_replace(')', '', $wkt);
            $lat_lon = explode(' ', $wkt);
            
            $npost->latitude = $lat_lon[0];
            $npost->longitude = $lat_lon[1];
            */
            
            $posts[] = $npost;
        }
        
        //$out[] = $sql;
        $out['posts'] = $posts;
        
        return new WP_REST_Response( $out, 200 );
  }
  
  public function get_beacon_posts($request){
      
      global $wpdb;
      
      $beacon_slug = $request['beacon_uri'];
      $beacon_cat = get_category_by_slug($beacon_slug);
      $filter_slug = $request['category'];
      $filter_cat = get_category_by_slug($filter_slug);
      
      $out = array();
      $out['meta'] = array();
      $out['meta']['beacon_slug'] = $beacon_slug;
      $out['meta']['beacon_name'] =  $beacon_cat->name;
      $out['meta']['filter_slug'] = $filter_slug;
      
      // get all the posts for this category
      $cat_ids = array($beacon_cat->term_id);
      
      // we add the filter but not if it is 'nearby' - that is only useful in accompany of a lat/lon
      if($filter_cat != null && $filter_slug != 'nearby') $cat_ids[] = $filter_cat->term_id;
      
      $out['meta']['cat_ids'] = $cat_ids;
      $args = array( 'numberposts' => 30, 'category__and' => $cat_ids);
      $posts = get_posts( $args );
      
      $nposts = array();
      $lats = array();
      $lons = array();
      foreach($posts as $post){
          $npost = $this->get_npost($post);
          if(isset($npost->longitude)) $lons[] = $npost->longitude;
          if(isset($npost->latitude)) $lats[] = $npost->latitude;
          $nposts[] = $npost;
      }
      
      if(count($lats) > 0 && count($lons) > 0){
          $out['meta']['centroid'] = array();
          $out['meta']['centroid']['latitude'] = array_sum($lats)/count($lats);
          $out['meta']['centroid']['longitude'] = array_sum($lons)/count($lons);
          $out['meta']['centroid']['accuracy'] = 50; // arbitrary for now.
      }
      
      $out['posts'] = $nposts;
  
      return new WP_REST_Response( $out, 200 );
  
  }
  
  private function get_npost($post){
      
       // add in info relevant to our application
      $npost = new stdClass();
      //$npost->post = $post;
      $npost->id = $post->ID;
      $npost->is_place = false;
      
      $npost->title = $post->post_title;
      $b = strip_shortcodes($post->post_content);
      $b = strip_tags($b, '<b><strong><em><i>');
      $b = preg_replace("/[\r\n]+/", "\n", $b);
      $b = trim($b);
      $paragraphs = preg_split('/\n+/', $b);
      $b = '<p>' . implode('</p><p>', $paragraphs) . '</p>';
      $npost->body = $b;
      
      // get some categories incase we need them and to tag the
      $post_categories = wp_get_post_categories( $post->ID );
      $cats = array();
      foreach($post_categories as $c){
          $cat = get_category( $c );
          $cats[] = array( 'name' => $cat->name, 'slug' => $cat->slug );
          
          if($cat->slug == 'place') $post->rbge_geo->is_place = true;
      }
      $npost->categories = $cats;
      
      // images
      $npost->thumbnail_url = get_the_post_thumbnail_url($post->ID, 'thumbnail');
      $npost->large_url = get_the_post_thumbnail_url($post->ID, 'widescreen');
      
      // mp3 attached?
      $npost->mp3 = false;
      $media = get_attached_media( 'audio/mpeg', $post->ID );
      foreach($media as $key => $val){
          $npost->mp3 = $val->guid;
          break; // just the first one
      }
      
      $lat = get_post_meta($post->ID, 'geo_latitude', true);
      if($lat) $npost->latitude = $lat;
      $lon = get_post_meta($post->ID, 'geo_longitude', true);
      if($lon) $npost->longitude = $lon;
      
      return $npost;
      
  }
  
  public function is_url($url){
      return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
  }
  
  public function slug_from_url($short_url, $setting){
      
      $tiny = filter_var($short_url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED);
      
      $ch = curl_init();
      curl_setopt ($ch, CURLOPT_URL, $tiny);
      curl_setopt($ch, CURLOPT_HEADER, TRUE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $header = curl_exec($ch);
      
      $matches = array();
      if(preg_match('/Location: ([^\n]+)/', $header, $matches)){
          $uri = trim($matches[1]);
          
          if(!preg_match('/^https:\/\/stories.rbge.org.uk/', $uri)){
              return new WP_Error( 'invalid_url', 'URI is not one of ours : ' . $uri );
          }
          
          $matches = array();
          if(preg_match('/\/([^\/]+)$/', $uri, $matches)){
                $slug = trim($matches[1]);
                return $slug;
          }else{
              // throw error
              return new WP_Error( 'invalid_url', 'Unable to extract category slug.' );
          }
      }else{
          // throw error
          return new WP_Error( 'invalid_url', 'Unable to extract redirect location.' );
      }
      
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
