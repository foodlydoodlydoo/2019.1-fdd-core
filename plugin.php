<?php
/**
 * Plugin Name: FDD Core
 * Plugin URI: https://foodlydoodlydoo.com/
 * Author: mhm
 * Author URI: https://foodlydoodlydoo.com/
 * Version: 1.0.0
 */

namespace FDD\Core;

add_filter('option_use_smilies', '__return_false');
add_filter('pre_get_posts', function($query) {
  if ($query->is_search) {
    $query->set('post_type', 'post');
  }
  return $query;
});


add_filter('embed_oembed_html', function($cache) {
  $html = \DOMDocument::loadHTML($cache);
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
  $cache = $html->saveHtml();

  return $cache;
}, 10, 1);

require_once 'et_import/converter.php';
