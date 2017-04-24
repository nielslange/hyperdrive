<?php
/**
 * Put your WordPress pages loads into Hyperdrive.
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

add_action('wp_head', __NAMESPACE__ .'\main');

function get_enqueued_scripts() {
  global $wp_scripts;
  foreach ($wp_scripts->queue as $handle) {
    $enqueued_scripts[] = $wp_scripts->registered[ $handle ];
  }
  return $enqueued_scripts;
}

function map_source_urls( $handles ) {
  $source_urls = [];
  foreach ($handles as $foo => $handle) {
    $source_url = get_src_for_handle($handle);
    if ($source_url) {
      $source_urls[] = $source_url;
    }
    $deps = get_deps_for_handle($handle);
    if (count($deps) > 0) {
      $source_urls[] = array(
        map_source_urls($deps)
      );
    }
  }
  return $source_urls;
}

function get_script_data() {
  $script_data = [];
  $scripts = get_enqueued_scripts();
  foreach ($scripts as $script) {
    if (!$script->extra['conditional']) {
      $querystring = $script->ver
        ? "?ver={$script->ver}"
        : '';
      $script_data[] = array(
        $script->handle,
        "{$script->src}{$querystring}",
        map_source_urls( $script->deps )
      );
    }
  }
  return $script_data;
}

function main() {
  $script_data = get_script_data();
  foreach ($script_data as $data) {
    d($data);
  }
  ddd($script_data);
}

// UTILITY FUNCTIONS

function get_dep_for_handle( $handle ) {
  global $wp_scripts;
  return $wp_scripts->registered[ $handle ];
}

function get_src_for_handle( $handle ) {
  $dep = get_dep_for_handle( $handle );
  return $dep->src;
}

function get_deps_for_handle($handle) {
  $dep = get_dep_for_handle($handle);
  return $dep->deps;
}

function _get_deps_for_handle( $handle ) {
  $dep = get_dep_for_handle( $handle );
  $deps = [];
  foreach ( $dep->deps as $deep_dep_handle ) {
    $deps[] = get_dep_for_handle( $deep_dep_handle );
  }
  return $deps;
}

function _main() {
  global $wp_scripts;
  $enqueued_script_deps = [];

  foreach ($wp_scripts->queue as $handle) {
    $enqueued_script_deps[] = get_dep_for_handle( $handle );
  }

  $data = get_enqueued_script_data();
  d($data);
  // d($enqueued_script_deps);

  foreach ( $enqueued_script_deps as $idx => $enqueued_dep ) {
    if ( !$enqueued_dep->extra['conditional'] ) {
      // not Internet Explorer conditional comment
      $enqueued_dep_deps = _get_deps_for_handle( $enqueued_dep->handle );

      $are_deep_deps = count( $enqueued_dep_deps ) > 0;
      if ( $are_deep_deps ) {
        foreach ( $enqueued_dep_deps as $key => $deep_dep ) {
          $deep_dep_handles = $deep_dep->deps;
          foreach ( $deep_dep_handles as $deep_dep_handle ) {
            $deep_deps = _get_deps_for_handle( $deep_dep_handle );

            $are_more_deep_deps = count( $deep_deps ) > 0;
            if ( !$are_more_deep_deps ) {
              $deep_dep_src = get_src_for_handle( $deep_dep_handle );
              // d($deep_dep_src);
            }
            // d( $deep_deps ); // end recursion when no more deps of deps
          }
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
 //
 //
 //
 //


 // $mockdata = [
 //   ['global', '/assets/js/....', ['jquery']],
 //   ['jquery-scrollto', '/assets/js/...', ['jquery']],
 //   ['navigation', '/wp-content/assets/js/...', ['jquery']],
 //   ['jquery', FALSE, ['jquery-core', 'jquery-migrate']],
 //   ['jquery-core', '/wp-content/assets/js/...', FALSE],
 //   ['jquery-migrate', '/wp-content/assets/js/...', FALSE]
 // ];

// $data = array(
//   array('admin-bar' => '/wp-includes/js/admin-bar.min.js', array()),
//   array('twentyseventeen-skip-link-focus-fix' => 'http://wordpress.dev/wp-content/themes/twentyseventeen/assets/js/skip-link-focus-fix.js?ver=1.0', array()),
// );
