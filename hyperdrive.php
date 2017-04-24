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
 * Gets dependency data recursively.
 *
 * @uses get_deps_for_handle
 * @uses get_src_for_handle
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
        get_dependency_data( $deps )
      );
    }
  }
  return $dependency_data;
}

/**
 * Prepare structured data for Fetch Injection.
 *
 * @uses get_dependency_data
 * @uses get_enqueued_scripts
 * @uses get_src_for_handle
 * @since Hyperdrive 1.0.0
 * @return Associative array containing structured data. Data
 *     structure is assumed by functions using and used by this
 *     method and must be udpated if the data structure changes.
 *
 * Example structured data ("Destination coordinates"):
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
function set_destination() {
  $destination_data = [];
  $scripts = get_enqueued_scripts();
  foreach ( $scripts as $script ) {
    if ( !$script->extra[ 'conditional' ] ) {
      // exclude scripts using conditional comments
      // Internet Explorer will never support fetch
      $destination_data[] = array(
        $script->handle,
        get_src_for_handle( $script->handle ),
        map_dependencies( $script->deps )
      );
    }
  }
  return $destination_data;
}

/**
 * Creates Fetch Injection sequencing script using structured data.
 * Data structure must be accurate or proper calibration.
 *
 * @since Hyperdrive 1.0.0
 * @param array(array(array)) $coordinates Destination coordinates
 */
function calibrate_thrusters( $coordinates ) {
  return;
}

/**
 * Echos an inline script into the document.
 *
 * @since Hyperdrive 1.0.0
 * @param string $antimatter_particles An inline script
 */
function fold_spacetime( $antimatter_particles ) {
  echo "<script>{$antimatter_particles}</script>";
}

/**
 * Main function engages the hyperdrive.
 * Hook into `wp_head` for optimal performance.
 *
 * @since Hyperdrive 1.0.0
 */
function engage() {
  $destination_coordinates = set_destination();
  $antimatter_particles = calibrate_thrusters( $destination_coordinates );
  fold_spacetime( $antimatter_particles );
  ddd('abend');
}

// UTILITY FUNCTIONS
// -----------------

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
