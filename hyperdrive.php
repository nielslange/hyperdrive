<?php
/**
 * Put your WordPress page loads into Hyperdrive.
 *
 * @package     Hyperdrive
 * @author      WordCamp Ubud 2017
 * @license     GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Hyperdrive
 * Plugin URI:  https://github.com/wp-id/hyperdrive
 * Description: The fastest way to load pages in WordPress.
 * Version:     1.0.0
 * Author:      WordCamp Ubud 2017 Plugin Team
 * License:     GPL-3.0
 * License URI: https://github.com/wp-id/hyperdrive/LICENSE
 */

 namespace hyperdrive;

 add_action('wp_head', __NAMESPACE__ .'\get_enqueued_scripts');

 /**
  * Get info for a given dependency.
  *
  * @param string $handle Resource handle to lookup
  * @return Dependency source URL, or array of dependency handles
  */
 // function get_dependency_info( $handle ) {
 //   global $wp_scripts;
 //   $registered = $wp_scripts->registered[$handle];
 //   d($registered);
 //   return [$registered->src, $registered->deps];
 //  //  return ( !$registered->src )
 //  //    ? $registered->deps
 //  //    : $registered->src;
 // }



// $mockdata = [
//   ['global', '/assets/js/....', ['jquery']],
//   ['jquery-scrollto', '/assets/js/...', ['jquery']],
//   ['navigation', '/wp-content/assets/js/...', ['jquery']],
//   ['jquery', FALSE, ['jquery-core', 'jquery-migrate']],
//   ['jquery-core', '/wp-content/assets/js/...', FALSE],
//   ['jquery-migrate', '/wp-content/assets/js/...', FALSE]
// ];

function get_dep_for_handle( $handle ) {
  global $wp_scripts;
  return $wp_scripts->registered[ $handle ];
}

function get_src_for_handle( $handle ) {
  $dep = get_dep_for_handle( $handle );
  return $dep->src;
}

function get_dep_handles_for_dep( $dep ) {
  $handles = [];
  foreach ($dep->deps as $key => $value) {
    $handles[] = $value;
  }
  return $handles;
}

function get_enqueued_scripts() {
  global $wp_scripts;
  $enqueued_deps = [];

  foreach ($wp_scripts->queue as $handle) {
    $enqueued_deps[] = get_dep_for_handle( $handle );
  }

  foreach ( $enqueued_deps as $idx => $dep ) {
    if ( !$dep->extra['conditional'] ) {
      // if not Internet Explorer conditional comment
      $deps = $dep->deps;
      if ( $deps ) {
        // if dependency has dependencies
        foreach ($deps as $key => $handle) {
          // d($key);
          $dep = get_src_for_handle( $handle );
          d($dep);
          // d(get_src_for_dep( $dep ));
        }
      }
    }
  }

  //  $dep_info = get_dependency_info('jquery');

  //  foreach ($dep_info as $index => $value) {
  //    d($dep_info);
  //    d($value);
  //    d($index);
  //    if ($info instanceof ArrayObject) {
  //      foreach ($variable as $key => $value) {
  //        get_dependency_info( );
  //      }
  //    }
  //  }

die('done');





   // global $wp_scripts;
   // foreach( $wp_scripts->queue as $script ) :
   //   $registered_scripts[] = $wp_scripts->registered[$script];
   // endforeach;
   // foreach( $registered_scripts as $script ) :
   //   if ( $script->deps ) {
   //     $registered_with_deps[] = $script;
   //   }
   // endforeach;
   // foreach ( $registered_with_deps as $script ) :
   //   $all_dependency_handles[] = $script->deps;
   // endforeach;
   // foreach ( $all_dependency_handles as $handle ) :
   //   // $registered_scripts[$handle];
   // endforeach;


   // d($registered_with_deps);
   // d($all_dependency_handles);


   // FOR EACH dependency, get and output source
   // foreach( $registered_scripts as $script ) :
   //   if ( count( $script->deps ) > 0 ) {
   //     d($script->src);
   //   }
   // endforeach;

   // d($script_sources);
   // d($registered_scripts);

 }


 // add_action('wp_head', __NAMESPACE__ .'\print_wp_scripts');
 // function print_wp_scripts(){
 //   global $wp_scripts;
 //   //d($wp_scripts);
 //   die('done');
 //   $wp_scripts->
 // }
