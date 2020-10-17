<?php
/**
 * Plugin Name: FDD Core
 * Plugin URI: https://foodlydoodlydoo.com/
 * Author: mhm
 * Author URI: https://foodlydoodlydoo.com/
 * Version: 1.0.0
 */

namespace FDD\Core;

function get_custom($name) {
  if (function_exists('\get_custom')) {
    return \get_custom($name);
  }
  return "";
}

add_filter('option_use_smilies', '__return_false');

/**
 * Disabled until confirmed it doesn't break layout
 **/
add_filter('wp_lazy_loading_enabled', '__return_false');

/**
 * Disabled because it breaks layout!
 */
add_filter('wp_img_tag_add_width_and_height_attr', '__return_false');

add_filter('pre_get_posts', function($query) {
  if ($query->is_search) {
    $query->set('post_type', 'post');
  }
  return $query;
});


add_filter('embed_oembed_html', function($cache) {
  $html = new \DOMDocument();
  $html->loadHTML($cache,
    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT);

  $iframe = $html->getElementsByTagName('iframe')[0];
  if (!$iframe) {
    if (current_user_can('administrator')) {
      throw new \Error('Cannot set additional args on the embeded video');
    }
    return $cache;
  }

  $src = $iframe->getAttribute('src');
  $src .= '&rel=0&modestbranding=1&cc_load_policy=0';

  $iframe->setAttribute('src', $src);
  $iframe->removeAttribute('frameborder'); // obsolete
  $cache = $html->saveHtml();

  return $cache;
}, 10, 1);

require_once 'et_import/converter.php';
require_once 'live_import/converter.php';
require_once 'aweber/shortcodes.php';
