<?php

namespace FDD\Core\et_im;

$et_shortcodes = [
  'et_pb_section',
  'et_pb_column',
  'et_pb_text',
  'et_pb_row_inner',
  'et_pb_column_inner',
  'et_pb_image',
  'et_pb_video',
  'URISP',
];

// Globals
function reg_shortcodes() {
  global $et_shortcodes;
  foreach ($et_shortcodes as $code) {
    add_shortcode($code, "FDD\Core\et_im\sc_$code");
  }
}

function dereg_shortcodes() {
  global $et_shortcodes;
  foreach ($et_shortcodes as $code) {
    remove_shortcode($code);
  }
}

// Shortcode handlers
function sc_et_pb_section($atts, $content, $tag) {
  $module_class = $atts['module_class'];
  $result .= "<$tag module_class='$module_class'>";
  $result .= $content;
  $result .= "</$tag>";

  return $result;
}

function sc_et_pb_column($atts, $content, $tag) {
  $type = $atts['type'];
  $result .= "<$tag type='$type'>";
  $result .= $content;
  $result .= "</$tag>";

  return $result;
}

function sc_et_pb_text($atts, $content, $tag) {
  $module_class = $atts['module_class'];
  $result .= "<$tag module_class='$module_class'>";
  $result .= $content;
  $result .= "</$tag>";

  return $result;
}

function sc_et_pb_row_inner($atts, $content, $tag) {
  $result .= "<$tag>";
  $result .= $content;
  $result .= "</$tag>";

  return $result;
}

function sc_et_pb_column_inner($atts, $content, $tag) {
  $type = $atts['type'];
  $result .= "<$tag type='$type'>";
  $result .= $content;
  $result .= "</$tag>";

  return $result;
}

function sc_et_pb_image($atts, $content, $tag) {
  $src = $atts['src'];
  $result .= "<$tag src='$src'/>";

  return $result;
}

function sc_et_pb_video($atts, $content, $tag) {
  $src = $atts['src'];
  $image_src = $atts['image_src'];
  $result .= "<$tag src='$src' image_src='$image_src'/>";

  return $result;
}

function sc_URISP($atts, $content, $tag) {
  return "";
}
