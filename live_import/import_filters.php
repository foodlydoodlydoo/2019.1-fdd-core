<?php

const SOURCE_DOMAIN = "https://foodlydoodlydoo.com";

// globals
function fdd_reg_filters() {
  add_filter('wp_import_post_data_raw', 'fdd_filter_wp_import_post_data_raw');
}

function fdd_dereg_filters() {
  remove_filter('wp_import_post_data_raw', 'fdd_filter_wp_import_post_data_raw');
}

// private
function fdd__fix_image_hrefs($src) {
  $src = str_replace(SOURCE_DOMAIN, get_home_url(), $src);
  $src = preg_replace("/\/uploads\/\d+\/\d+\//", '/uploads/', $src);
  return $src;
}

// filters
function fdd_filter_wp_import_post_data_raw($post) {
  if ($post['post_type'] != 'post') {
    // ignore
    return $post;
  }

  print("FDD: Importing from live \"" . $post['post_title'] . "\"<br/>");

  $content_in = $post['post_content'];
  $content_out = fdd__fix_image_hrefs($content_in);

  $post['post_content'] = $content_out;
  return $post;
}
