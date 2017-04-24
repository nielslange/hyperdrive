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

function map_dependencies( $handles ) {
  $source_urls = [];
  foreach ($handles as $foo => $handle) {
    $source_url = get_src_for_handle($handle);
    if ($source_url) {
      $source_urls[] = array(
        $handle,
        $source_url
      );
    }
    $deps = get_deps_for_handle($handle);
    if (count($deps) > 0) {
      $source_urls[] = array(
        $handle,
        map_dependencies($deps)
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
      // exclude scripts using conditional comments
      $script_data[] = array(
        $script->handle,
        get_src_for_handle( $script->handle ),
        map_dependencies( $script->deps )
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
// -----------------

function get_dep_for_handle( $handle ) {
  global $wp_scripts;
  return $wp_scripts->registered[ $handle ];
}

function get_src_for_handle( $handle ) {
  $dep = get_dep_for_handle( $handle );
  $suffix = ($dep->src && $dep->ver)
    ? "?ver={$dep->ver}"
    : '';
  return "{$dep->src}{$suffix}";
}

function get_deps_for_handle($handle) {
  $dep = get_dep_for_handle($handle);
  return $dep->deps;
}
