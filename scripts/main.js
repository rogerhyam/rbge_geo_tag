
jQuery( document ).ready(function() {
    
    // only run the init if the map div has been loaded
    if(jQuery("#rbge-geo-tag-map").length > 0)  rbge_geo_tag_admin_init();
    if(jQuery(".rbge-geo-tag-display-map").length > 0)  rbge_geo_tag_display_init();

});

/* We may have multiple maps on a page from within a single post or separate posts */
function rbge_geo_tag_display_init(){
    jQuery(".rbge-geo-tag-display-map").each(function( index ) {
        console.log(jQuery( this ));
        rbge_geo_tag_display_map(jQuery( this ).attr('id'));
    });
}

function rbge_geo_tag_display_map(map_id){
    
    var all_markers = [];
    
    // start in Edinburgh as default
    var centerOfMap = new google.maps.LatLng(55.9533, -3.1883);
    var zoomLevel = 6;
    
    // see if we can get centre and zoom from map element.
    var map_div = jQuery("#" + map_id);
    console.log(map_div);
    
    if(map_div.data('lat') && map_div.data('lon')){
        centerOfMap = new google.maps.LatLng(map_div.data('lat'), map_div.data('lon'));
    }
    
    if(map_div.data('zoom')){
        zoomLevel = map_div.data('zoom');
    }

    // actually add the map to the display
    var map = new google.maps.Map(document.getElementById(map_id), {
        zoom: zoomLevel,
        center: centerOfMap,
        mapTypeId: 'hybrid'    
    });
    
    // only add marker after we have map
    var img = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
    
    if(map_div.data('lat') && map_div.data('lon')){
        var marker = new google.maps.Marker({
                           position: centerOfMap, 
                           map: map,
                           icon: img
                    });
        all_markers.push(marker);
    }
    
    // add markers for posts with the tags.
    jQuery("." + map_id + "-marker").each(function( index ) {
        
        var plat = jQuery( this ).data('lat');
        var plng = jQuery( this ).data('lon');
        var pid = jQuery( this ).data('pid');
        var ptitle = jQuery( this ).html();
        var img = "http://maps.google.com/mapfiles/ms/icons/blue-dot.png";
        
        if(plat && plng && pid){
            var marker = new google.maps.Marker({
                               position: new google.maps.LatLng(plat, plng), 
                               map: map,
                               title: ptitle,
                               icon: img
                        });
                        
            google.maps.event.addListener(marker, 'click', function() {
                window.open('/archives/' + pid);
            });
            all_markers.push(marker);
        }
        
    });
    
    // if we have more than one marker on the map
    // zoom it out
    if(all_markers.length > 1){
        var bounds = new google.maps.LatLngBounds();
        for (var i = 0; i < all_markers.length; i++) {
            bounds.extend(all_markers[i].getPosition());
        }
        map.fitBounds(bounds);
    }
    
    
}


function rbge_geo_tag_admin_init(){

    // start in Edinburgh as default
    var centerOfMap = new google.maps.LatLng(55.9533, -3.1883);
    var zoomLevel = 6;
    
    // if we already have a lon/lat set then user those.
    if(jQuery('#geo_latitude').val() && jQuery('#geo_longitude').val()){
        var previous_lat = jQuery('#geo_latitude').val();
        var previous_lon = jQuery('#geo_longitude').val();
        if(previous_lat && previous_lon){
            // center the map and add a marker
            centerOfMap = new google.maps.LatLng(previous_lat.trim(), previous_lon.trim());
        }
        
        // if we have a zoom level set then we add it if not we increase it a bit cause there is a marker to see
        if(jQuery('#geo_map_zoom').val()){
            zoomLevel = parseInt(jQuery('#geo_map_zoom').val());
        }else{
            zoomLevel = 16;
            jQuery('#geo_map_zoom').val(16);
        }
        
    }else{
        // we don't have them already set but may have them from a previous view
        // of the map
        if(localStorage.getItem("rbge_geo_tag_zoom") !== null) zoomLevel = parseInt(localStorage.getItem("rbge_geo_tag_zoom"));
        
        if(localStorage.getItem("rbge_geo_tag_geo_latitude") !== null && localStorage.getItem("rbge_geo_tag_geo_longitude") !== null){
            centerOfMap = new google.maps.LatLng(
                localStorage.getItem("rbge_geo_tag_geo_latitude"), localStorage.getItem("rbge_geo_tag_geo_longitude"));
        }
        
    }

    var map = new google.maps.Map(document.getElementById('rbge-geo-tag-map'), {
        zoom: zoomLevel,
        center: centerOfMap,
        mapTypeId: 'hybrid'    
    });
    
    // if we have previous add a marker to the map at that previous
    if(previous_lat && previous_lon){
        var marker = new google.maps.Marker({
                           position: centerOfMap, 
                           map: map
                    });
        jQuery("#rbge-geo-tag-map").data('marker', marker);
    
    }
    
    google.maps.event.addListener(map, 'click', function(event) {

        // change the position of the old marker
        if(jQuery("#rbge-geo-tag-map").data('marker')){
            jQuery("#rbge-geo-tag-map").data('marker').setPosition(event.latLng);
        }else{
            var marker = new google.maps.Marker({
                position: event.latLng, 
                map: map
            });
            jQuery("#rbge-geo-tag-map").data('marker', marker); 
        }
        
        // update the inputs
        jQuery('#geo_latitude').val(event.latLng.lat());
        jQuery('#geo_longitude').val(event.latLng.lng());
        jQuery('#geo_map_zoom').val(map.getZoom());
        
        // write the lat/lon to the local storage so we can centre the next map
        localStorage.setItem("rbge_geo_tag_geo_latitude",  event.latLng.lat());
        localStorage.setItem("rbge_geo_tag_geo_longitude", event.latLng.lng());
        localStorage.setItem('rbge_geo_tag_zoom', map.getZoom());
    
    });
    
    google.maps.event.addListener(map, 'zoom_changed', function() {
        console.log(map.getZoom());
        jQuery('#geo_map_zoom').val(map.getZoom());
        localStorage.setItem('rbge_geo_tag_zoom', map.getZoom());
    });

    jQuery("#rbge_geo_tag_clear_button").on('click', function(event, ui){
        
        // remove the marker from the map.
        jQuery("#rbge-geo-tag-map").data('marker').setMap(null);
        jQuery("#rbge-geo-tag-map").data('marker', null);
        
        //and the fields
        jQuery('#geo_latitude').val(null);
        jQuery('#geo_longitude').val(null);
        jQuery('#geo_map_zoom').val(null);
        
    });
}