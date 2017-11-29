<?php
/*
Plugin Name: Gravity Forms HubSpot Addon
Plugin URI: http://www.gravityforms.com
Description: Integrates Gravity Forms with HubSpot allowing form submissions to be automatically sent to your HubSpot account
Version: 3.6
Author: danharper
Author URI:
Text Domain: gravityformshubspot
Domain Path: /languages

*/

add_action( 'gform_loaded', array( 'GF_HubSpot_Bootstrap', 'load' ), 5 );

class GF_HubSpot_Bootstrap {

  public static function load(){

    if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
      return;
    }

    require_once( 'class-gf-hubspot.php' );

    GFAddOn::register( 'GFHubSpot' );
  }
}

function gf_hubspot(){
  return GFHubSpot::get_instance();
}
