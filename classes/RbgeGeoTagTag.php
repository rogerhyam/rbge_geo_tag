<?php

class RbgeGeoTagTag{
    
    private $occurrence_count = 0;
    
    public function render($params){
        
        // these were registered earlier.
        wp_enqueue_script( 'rbge_geo_tag_google_maps' );
        wp_enqueue_script( 'rbge_geo_tag_main_script' ); 
        wp_enqueue_style( 'rbge_geo_tag_main_style' ); 
        
        // does this post have an associated point?
        $post_id = get_the_ID();
        $lng = get_post_meta($post_id, 'geo_longitude', true);
        $lat = get_post_meta($post_id, 'geo_latitude', true);
        $zoom = get_post_meta($post_id, 'geo_map_zoom', true);
        
        // set the default height if they haven't passed one.
        if(isset($params['height'])) $height = $params['height'];
        else $height = 300;
        $height .= 'px';
        
        // each instance of the tag has its own id
        $map_id = "rbge-geo-tag-display-map-" . $post_id . '-' . $this->occurrence_count;
        
        $out = "<div id=\"$map_id\" class=\"rbge-geo-tag-display-map\" data-lat=\"$lat\" data-lon=\"$lng\" data-zoom=\"$zoom\" style=\"width: 100%; height: $height\"></div>";
        
        // have they passed a list of tag slugs?
        if(isset($params['tags'])){
            $tags = explode(',',$params['tags']);
        }else{
            $tags = array();
        }
        
        $args = array(
            'posts_per_page' => -1,
        	'tax_query' => array(
        		array(
        			'taxonomy' => 'post_tag',
        			'field' => 'slug',
        			'terms' => $tags,
        			'operator' => 'IN'
        		)
        	)
        );
        $postslist = get_posts( $args );
        
        $out .= '<ul style="display: none;">';
        $marker_class = $map_id . '-marker';
        foreach($postslist as $p){
            
            $pid = $p->ID;
            $plng = get_post_meta($pid, 'geo_longitude', true);
            $plat = get_post_meta($pid, 'geo_latitude', true);
            $ptitle = $p->post_title;
            
            $out .= "<li class=\"$marker_class\" data-lat=\"$plat\" data-lon=\"$plng\" data-pid=\"$pid\" >$ptitle</li>";
            
        }
        $out .= '</ul>';
        
        // up the occurrence count for naming of map instances
        $this->occurrence_count++;
        
        return $out ;//.'<pre>' . print_r($postslist, true) . '</pre>';
        
        
    }
    
    public function link( $atts ){

    	$post_id = get_the_ID();
    	$longitude = get_post_meta($post_id, 'geo_longitude', true);
    	$latitude = get_post_meta($post_id, 'geo_latitude', true);

    	if(!empty($atts['zoom'])){
    	    $zoom = $atts['zoom'];
    	}else{
    	    $zoom = 16;
    	}

    	if(!empty($atts['text'])){
    	    $txt = $atts['text'];
    	}else{
    	    $txt = 'Show map.';
    	}

        if(empty($longitude) || empty($latitude)){
            return "<strong>This story hasn't been geocoded.</strong>";
        }else{
            return '<a href="https://maps.google.com/maps?z='.$zoom.'&q=loc:'.$latitude.'+'.$longitude.'">'.$txt.'</a>';
            // directions something like: http://maps.google.com/maps?saddr=%f,%f&daddr=%f,%f
        }

    }
    
    
}


?>