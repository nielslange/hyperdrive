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

add_action('wp_head', __NAMESPACE__ .'\engage');

/**
 * Gets scripts registered and enqueued.
 *
 * @since Hyperdrive 1.0.0
 * @return array(_WP_Dependency) A list of enqueued dependencies
 */
function get_enqueued_scripts() {
  global $wp_scripts;
  foreach ( $wp_scripts->queue as $handle ) {
    $enqueued_scripts[] = $wp_scripts->registered[ $handle ];
  }
  return $enqueued_scripts;
}

/**
 * Gets dependency data recursively.
 *
 * @since Hyperdrive 1.0.0
 * @param array(string) $handles An array of handles
 * @return array(array) Dependency data matching expected structure
 */
function get_dependency_data( $handles ) {
  $dependency_data = [];
  foreach ( $handles as $foo => $handle ) {
    $source_url = get_src_for_handle( $handle );
    if ( $source_url ) {
      $dependency_data[] = array(
        $handle,
        $source_url,
        array() // maintain consistency
      );
    }
    $deps = get_deps_for_handle( $handle );
    if ( count($deps) > 0 ) {
      $dependency_data[] = array(
        $handle,
        '', // maintain consistency
        get_dependency_data( $deps ),
      );
    }
  }
  return $dependency_data;
}

/**
 * Prepare structured data for Fetch Injection.
 *
 * @since Hyperdrive 1.0.0
 * @return Associative array containing data
 *
 * Example return value:
 *
 * array(
 *   string "jquery-scrollto"
 *   string "/assets/js/jquery.scrollTo.js?ver=2.1.2"
 *   array(
 *     string "jquery"
 *     string ""
 *     array(
 *       array(
 *         string "jquery-core"
 *         string "/wp-includes/js/jquery/jquery.js?ver=1.12.4"
 *         array(0)
 *       )
 *       array(
 *         string "jquery-migrate"
 *         string "/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1"
 *         array(0)
 *       )
 *     )
 *   )
 * )
 *
 */
function get_script_data() {
  $script_data = [];
  $scripts = get_enqueued_scripts();
  foreach ( $scripts as $script ) {
    if ( !$script->extra[ 'conditional' ] ) {
      // exclude scripts using conditional comments
      // Internet Explorer will never support fetch
      $script_data[] = array(
        $script->handle,
        get_src_for_handle( $script->handle ),
        map_dependencies( $script->deps )
      );
    }
  }
  return $script_data;
}

/**
 * Main function engages the hyperdrive.
 * Hook into wp_head for optimal performance.
 *
 * @since Hyperdrive 1.0.0
 */
function engage() {
  $script_data = get_script_data();
  foreach ( $script_data as $data ) {
    d($data);
  }
  ddd($script_data);
}

// UTILITY FUNCTIONS
// -----------------

/**
 * Gets a script dependency for a handle
 *
 * @since Hyperdrive 1.0.0
 * @param string $handle The handle
 * @return _WP_Dependency associated with input handle
 */
function get_dep_for_handle( $handle ) {
  global $wp_scripts;
  return $wp_scripts->registered[ $handle ];
}

/**
 * Gets the source URL given a script handle.
 *
 * @since Hyperdrive 1.0.0
 * @param string $handle The handle
 * @return URL associated with handle, or empty string
 */
function get_src_for_handle( $handle ) {
  $dep = get_dep_for_handle( $handle );
  $suffix = ( $dep->src && $dep->ver )
    ? "?ver={$dep->ver}"
    : '';
  return "{$dep->src}{$suffix}";
}

/**
 * Gets all dependencies for a given handle.
 *
 * @since Hyperdrive 1.0.0
 * @param string $handle The handle
 */
function get_deps_for_handle( $handle ) {
  $dep = get_dep_for_handle( $handle );
  return $dep->deps;
}
