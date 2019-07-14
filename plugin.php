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

require_once 'et_import/converter.php';
