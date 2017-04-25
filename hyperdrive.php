<?php
/**
 * Put your WordPress pages loads into Hyperdrive.
 *
 * @package     Hyperdrive
 * @author      WordCamp Ubud 2017 Plugin Team
 * @link        https://wordpress.stackexchange.com/a/263733/117731
 * @license     GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Hyperdrive
 * Plugin URI:  https://github.com/wp-id/hyperdrive
 * Description: The fastest way to load pages in WordPress.
 * Version:     1.0.0-beta
 * Author:      WordCamp Ubud 2017 Plugin Team
 * License:     GPL-3.0
 * License URI: https://github.com/wp-id/hyperdrive/LICENSE
 */
namespace wcubud;
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
  foreach ( $handles as $idx => $handle ) {
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
 * Also dequeues enqueued scripts so WordPress doesn't load them.
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
      // we'll take it from here
      wp_dequeue_script( $script->handle );
    }
  }
  return $calibration_data;
}

/**
 * Prepares "Calibration data" for Fetch Injection.
 * Dedupes associative array and respect sort order.
 *
 * @since Hyperdrive 1.0.0
 * @param array(array(...)) $calibration_data Destination coordinates
 * @param boolean [$recursing=false] True when generating subparticles
 * @return A list of scripts for use in Fetch Injection
 */
function generate_antimatter( $calibration_data, $recursing = false ) {
  $particle_array = [];
  foreach ( $calibration_data as $idx => $data ) {
    $handle = $data[0];
    $url = $data[1];
    $particle_array[] = "{$url}";
    $subparticles = $data[2];
    if ( $subparticles ) {
      $particle_array[] = generate_antimatter( $subparticles, true );
    }
  }
  array_multisort( $particle_array ); // remove numeric array keys
  // remove duplicate values
  $particle_array = array_map(
    'unserialize', array_unique(
      array_map( 'serialize', $particle_array )
    )
  );
  return $particle_array;
}

/**
 * Converts antimatter particles into dark matter.
 *
 * @since Hyperdrive 1.0.0
 * @link https://github.com/jhabdas/fetch-inject
 * @param array $antimatter_particles Partical array
 * @return A string containing a fully-assembled inline script
 *     for Fetch Injection.
 */
function fold_spacetime( $antimatter_particles ) {
  $injectors = [];
  $fetch_inject_string = '';

  // create ordered array of JSON encoded strings for Fetch Injection
  function walk_recursive( $array, $accumulator, &$injectors, &$injection_json = '') {
    $accumulator = [];
    array_walk( $array, function( $item ) use( &$accumulator, &$injectors, &$injection_json ) {
      $is_array = is_array( $item );
      if ( $is_array ) {
        walk_recursive( $item, $accumulator, $injectors, $injection_json );
      } else {
        $accumulator[] = $item;
      }
    });
    $injection_json = json_encode($accumulator, JSON_UNESCAPED_SLASHES);
    $injectors[] = $injection_json;
  }
  walk_recursive( $antimatter_particles, FALSE, $injectors );

  // assemble Fetch Inject string using ordered array
  $first_element = array_shift(array_values($injectors));
  $last_element = end($injectors);
  foreach ($injectors as $idx => $injector) {
    if ( $injector === $first_element ) {
      $fetch_inject_string = "fetchInject($injector)";
    } else if ( $injector === $last_element ) {
      $fetch_inject_string = "fetchInject($injector, $fetch_inject_string)";
    } else {
      $array_with_empty_string = array(''); // like WordPress core jquery handle
      if ( !(json_decode($injector) === $array_with_empty_string) ) {
        $fetch_inject_string = "fetchInject($injector, $fetch_inject_string)";
      }
    }
  }

  return <<<EOD
(function () {
  if (!window.fetch) return;
  /**
   * Fetch Inject v1.6.8
   * Copyright (c) 2017 Josh Habdas
   * @licence ISC
   */
  var fetchInject=function(){"use strict";const e=function(e,t,n,r,o,i,c){i=t.createElement(n),c=t.getElementsByTagName(n)[0],i.type=r.blob.type,i.appendChild(t.createTextNode(r.text)),i.onload=o(r),c?c.parentNode.insertBefore(i,c):t.head.appendChild(i)},t=function(t,n){if(!t||!Array.isArray(t))return Promise.reject(new Error("`inputs` must be an array"));if(n&&!(n instanceof Promise))return Promise.reject(new Error("`promise` must be a promise"));const r=[],o=n?[].concat(n):[],i=[];return t.forEach(e=>o.push(window.fetch(e).then(e=>{return[e.clone().text(),e.blob()]}).then(e=>{return Promise.all(e).then(e=>{r.push({text:e[0],blob:e[1]})})}))),Promise.all(o).then(()=>{return r.forEach(t=>{i.push({then:n=>{"text/css"===t.blob.type?e(window,document,"style",t,n):e(window,document,"script",t,n)}})}),Promise.all(i)})};return t}();
  $fetch_inject_string;
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
  enter_hyperspace( $dark_energy );
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
