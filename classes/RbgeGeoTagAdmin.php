<?php

class RbgeGeoTagAdmin{
    

	public function  render_meta_box() {
	    
	    global $post;
	    
	    // if the API key isn't set then we throw a wobbly
	    if(!RBGE_GOOGLE_MAPS_KEY){
	        echo "<p>The Google Maps API key constant <strong>RBGE_GOOGLE_MAPS_KEY</strong> isn't set. Please add it to wp-config.php.</p>";
	        return;
	    }
	    
	    // get the current values
		$post_id = $post->ID;
	    $lat = get_post_meta($post_id, 'geo_latitude', true);
		$lng = get_post_meta($post_id, 'geo_longitude', true);
		$zoom = get_post_meta($post_id, 'geo_map_zoom', true);
	    
	    // make sure the it is loaded.
	    echo '<p>Click on the map to tag this post to a particular location.</p>';
        echo '<div id="rbge-geo-tag-map"></div>';
        echo '<div id="rbge-geo-tag-form">';
        
        echo '
            <input type="hidden" name="rbge_geo_tag_submit_flag" id="rbge_geo_tag_submit_flag" value="1"/>
            <input type="hidden" name="geo_latitude" id="geo_latitude" value="'.$lat.'"/>
            <input type="hidden" name="geo_longitude" id="geo_longitude" value="'.$lng.'" />
            <input type="hidden" name="geo_map_zoom" id="geo_map_zoom" value="'.$zoom.'" />
            <div style="text-align: right; margin-top: 1em;">
            <button id="rbge_geo_tag_clear_button" onclick="return false;">Clear Marker</button>
            </div>
        ';
        
        echo '</div>';
	    
    }
    
    public function save_post($post_id){
        
        // quick edit will overwrite the metadata values so check for form flag first
        if( !isset($_POST['rbge_geo_tag_submit_flag'])) return;
        update_post_meta($post_id, 'geo_latitude', trim($_POST['geo_latitude']));
        update_post_meta($post_id, 'geo_longitude', trim($_POST['geo_longitude']));
        update_post_meta($post_id, 'geo_map_zoom', trim($_POST['geo_map_zoom']));
        

    }


} // end class





?>