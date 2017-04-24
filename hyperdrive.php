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
 *     method and must be udpated if data structure changes.
 *
 * Example structured data ("Calibration data"):
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
function calibrate_thrusters() {
  $calibration_data = [];
  $scripts = get_enqueued_scripts();
  foreach ( $scripts as $script ) {
    if ( !$script->extra[ 'conditional' ] ) {
      // exclude scripts using conditional comments
      // Internet Explorer will never support fetch
      $calibration_data[] = array(
        $script->handle,
        get_src_for_handle( $script->handle ),
        get_dependency_data( $script->deps )
      );
    }
  }
  return $calibration_data;
}

/**
 * Creates Fetch Injection sequencing scripts using "Calibration data".
 * Inverts control away from `wp_enqueue_script`.
 * Ensures deep dependencies only called once.
 * Data structure must be accurate or proper calibration.
 *
 * @since Hyperdrive 1.0.0
 * @param array(array(...)) $calibration_data Destination coordinates
 * @return A list of scripts for use in Fetch Injection
 */
function generate_antimatter( $calibration_data ) {
  $antimatter_particles = [];
  foreach ( $calibration_data as $idx => $data ) {
    $url = $data[1]; // assumed structure
    $antimatter_particles[] = "{$url}";
  }
  return $antimatter_particles;
}

/**
 * Converts antimatter particles into dark matter.
 *
 * @param array $antimatter_particles Array of particles
 * @return A string containing a fully-assembled inline script
 *     for Fetch Injection.
 */
function fold_spacetime( $antimatter_particles ) {
  // d($antimatter_particles);
  // TODO: fold space until we have all particle arrays
  $particle_array = json_encode($antimatter_particles);
  d($particle_array);
  return <<<EOD
(function () {
  if (!window.fetch) return;
  /**
   * Fetch Inject v1.6.8
   * Copyright (c) 2017 Josh Habdas
   * @licence ISC
   */
  var fetchInject=function(){"use strict";const e=function(e,t,n,r,o,i,c){i=t.createElement(n),c=t.getElementsByTagName(n)[0],i.type=r.blob.type,i.appendChild(t.createTextNode(r.text)),i.onload=o(r),c?c.parentNode.insertBefore(i,c):t.head.appendChild(i)},t=function(t,n){if(!t||!Array.isArray(t))return Promise.reject(new Error("`inputs` must be an array"));if(n&&!(n instanceof Promise))return Promise.reject(new Error("`promise` must be a promise"));const r=[],o=n?[].concat(n):[],i=[];return t.forEach(e=>o.push(window.fetch(e).then(e=>{return[e.clone().text(),e.blob()]}).then(e=>{return Promise.all(e).then(e=>{r.push({text:e[0],blob:e[1]})})}))),Promise.all(o).then(()=>{return r.forEach(t=>{i.push({then:n=>{"text/css"===t.blob.type?e(window,document,"style",t,n):e(window,document,"script",t,n)}})}),Promise.all(i)})};return t}();
  fetchInject($particle_array)
})();
EOD;
}

/**
 * Echos an inline script into the document.
 *
 * @since Hyperdrive 1.0.0
 * @param string $dark_energy An inline script to asynchronously
 *     fetch previously enqueued page resources.
 */
function enter_hyperspace( $dark_energy ) {
  echo "<script>{$dark_energy}</script>";
}

/**
 * Main function engages the hyperdrive.
 * Hook into `wp_head` for optimum performance.
 *
 * @uses calibrate_thrusters
 * @uses generate_antimatter
 * @uses fold_spacetime
 * @uses enter_hyperspace
 * @since Hyperdrive 1.0.0
 */
function engage() {
  $calibration_data = calibrate_thrusters();
  $antimatter_particles = generate_antimatter( $calibration_data );
  $dark_energy = fold_spacetime( $antimatter_particles );
  d($dark_energy);
  // enter_hyperspace( $dark_energy );
  ddd('end');
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
