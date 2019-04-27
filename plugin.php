<?php
/**
 * Plugin Name: FDD Core
 * Plugin URI: https://foodlydoodlydoo.com/
 * Author: mhm
 * Author URI: https://foodlydoodlydoo.com/
 * Version: 1.0.0
 */

// 🕸 \\

namespace FDD\Core;

function menu_search_form($title, $item, $args, $depth) {
  if (preg_match('/SEARCH-FORM/', $title)) {
    $title = get_search_form(false);
  }
  return $title;
}

add_filter('walker_nav_menu_start_el', 'FDD\Core\menu_search_form', 10, 4);
