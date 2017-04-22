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
	    echo '<p>Click on the map to tag this post to a particular location. [<a href="#" onclick="return rbge_geo_tag_fire_help();">Help</a>]</p>';
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
    

    public function help(){
        
        $help = '
        
        <h3>RBGE Geo Tag</h3>
        <p>
            It is possible to geotag a post to a particular longitude/latitude.
            Navigate and zoom to the location you want to place the post and click on the map.
            To move the marker just click on a new location.
            To remove the marker and not geotag the post click the "Remove Marker" button.
        </p>
        <p>
            If you put the short code <pre>[rbge_geo_tag]</pre> in the body of your post a map will be inserted
            showing the marker.
        </p>
        <p>
            You can plot other posts on an inserted map based on their tags.
            To do this include the machine name (slug) of the tag like this <pre>[rbge_geo_tag tags="native-tree-trail" ]</pre>
            The slug of a tag is displayed in the URL when you click on it.
            Multiple tags can be given separated by commas. When multiple tags are include a post that has any of the tags will be included on the map.
            Markers from other posts act as clickable links.
        </p>
        <p>
            The map will always be full page width but you can specify the height in pixels like this.
            <pre>[rbge_geo_tag height="400" tags="native-tree-trail" ]</pre>
        </p>
        <p>
            It is also possible to just add a link to the location the post is geotagged to.
            <pre>[rbge_map_link text="Open location in Google Maps"]</pre>
        </p>
        
        ';
        
        
        if ($screen = get_current_screen()) {
                $screen->add_help_tab(array(
                    'id' => 'rbge_geo_tag_help',
                    'title' => 'RBGE Geo Tag',
                    'content' => $help,
                ));
        }

    }

} // end class


?>