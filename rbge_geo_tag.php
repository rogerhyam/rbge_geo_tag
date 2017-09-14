<?php
/*
Plugin Name: RBGE Geo Tag
Description: Allows posts to be geotagged and Google Maps of geo tagged posts to be inserted
Version: 0.1
Author: Roger Hyam
License: GPL2
*/

require_once plugin_dir_path( __FILE__ ) . 'classes/RbgeGeoTagActivation.php';
register_activation_hook( __FILE__, array( 'RbgeGeoTagActivation', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'RbgeGeoTagActivation', 'deactivate' ) );

if(is_admin()){

    //things on the back end
    
    add_action('admin_enqueue_scripts', function(){
        wp_enqueue_script('rbge_geo_tag_google_maps', 'https://maps.googleapis.com/maps/api/js?key=' . RBGE_GOOGLE_MAPS_KEY);
        wp_enqueue_script('rbge_geo_tag_main_script', plugins_url('scripts/main.js', __FILE__));
        wp_enqueue_style( 'rbge_geo_tag_main_style', plugins_url('styles/main.css', __FILE__));
    });
    
    add_action('admin_menu', function() {
        require_once plugin_dir_path( __FILE__ ) . 'classes/RbgeGeoTagAdmin.php';
        $admin = new RbgeGeoTagAdmin();

        add_meta_box('geotag', 'RBGE Geo Tag', array($admin, 'render_meta_box'), 'post', 'normal', 'high');
        add_action('save_post', array($admin, 'save_post'));
        add_action('in_admin_header', array($admin, 'help'));

    });
    
}else{
    
    // things on the front end
    add_action( 'wp_enqueue_scripts', function(){
        // n.b. only register it - will be turned on in short code function.
        wp_register_script('rbge_geo_tag_google_maps', 'https://maps.googleapis.com/maps/api/js?key=' . RBGE_GOOGLE_MAPS_KEY);
        wp_register_script('rbge_geo_tag_main_script', plugins_url('scripts/main.js', __FILE__));
        wp_register_style( 'rbge_geo_tag_main_style', plugins_url('styles/main.css', __FILE__));
        
    } );
    
    require_once plugin_dir_path( __FILE__ ) . 'classes/RbgeGeoTagTag.php';
    $tag = new RbgeGeoTagTag();
    add_shortcode( 'rbge_geo_tag', array($tag, 'render') );
    add_shortcode( 'rbge_map_it', array($tag, 'render') ); // legacy support for the old tag    
    add_shortcode( 'rbge_map_link', array($tag, 'link') ); // moved from the simple plugin
    
    require_once plugin_dir_path( __FILE__ ) . 'classes/RbgeGeoTagMachine.php';
    $machine = new RbgeGeoTagMachine();
    add_action('wp_head', array($machine, 'page_header'));
    add_action('rss2_item', array($machine, 'rss'));
    
    require_once plugin_dir_path( __FILE__ ) . 'classes/RbgeGeoTagSootheForm.php';
    $soothe = new RbgeGeoTagSootheForm();
    add_shortcode( 'rbge_soothe_form', array($soothe, 'render') );
    
    
}

// REST API call
add_action('rest_api_init', function(){
    
    /*
    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
    add_filter( 'rest_pre_serve_request', function( $value ) {
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
        header( 'Access-Control-Allow-Credentials: true' );
        return $value;
    });
    */

  require_once plugin_dir_path( __FILE__ ) . 'classes/RbgeGeoTagRest.php';
  $rest = new RbgeGeoTagRest();
  $rest->register_routes();

});

// general init stuff
add_action( 'init', function(){
    add_image_size( 'widescreen', 1024, 576, true);
});



?>