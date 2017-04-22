<?php

/*
    Functionality for every geocoded post
    that adds machine readable tags to header and feeds
*/
class RbgeGeoTagMachine{
    
    
    
    public function page_header(){
        
        global $post;
		$id = $post->ID;
		$name = $post->post_title;
		if(is_home() || is_front_page()){
			return;
		}
	
		$lat = get_post_meta($post->ID, 'geo_latitude', true);
		$lng = get_post_meta($post->ID, 'geo_longitude', true);
			
		if(isset($lat) && is_numeric($lat) && isset($lng) && is_numeric($lng)) {
		    echo "\n<!-- RBGE Geo Tag data - start -->\n";
			echo '<meta name="ICBM" content="'.$lat.', '.$lng.'" />'."\n";
			echo '<meta name="geo.position" content="'.$lat.', '.$lng.'" />'."\n";
			echo '<script type="application/ld+json">
{
   "@context": "http://schema.org",
   "@type": "Place",
   "geo": {
     "@type": "GeoCoordinates",
     "latitude": "'.$lat.'",
     "longitude": "'.$lng.'"
   },
   "name": "'.$name.'"
 }
 </script>';
             echo "\n<!-- RBGE Geo Tag data - end -->\n";
		}
        
    }
    
    public function rss() {	

        global $post;
        $id = $post->ID;
        $name = strip_tags($post->post_title);

        $lat = get_post_meta($post->ID, 'geo_latitude', true);
        $lng = get_post_meta($post->ID, 'geo_longitude', true);

        if(isset($lat) && is_numeric($lat) && isset($lng) && is_numeric($lng)) {
        	echo "\n<geo:lat>$lat</geo:lat>\n<geo:long>$lng</geo:long>\n";
        	echo "<georss:point>$lat $lng</georss:point>\n";
        	echo "<georss:featurename>$name</georss:featurename>\n";		
        }

    }
    
}


?>