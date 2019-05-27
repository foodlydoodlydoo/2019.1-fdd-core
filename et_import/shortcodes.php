<?php

$fdd_im_et_shortcodes = [
  'et_pb_section',
  'et_pb_row',
  'et_pb_column',
  'et_pb_text',
  'et_pb_row_inner',
  'et_pb_column_inner',
  'et_pb_image',
  'et_pb_video',
  'URISP',
];

// Globals
function fdd_reg_shortcodes() {
  global $fdd_im_et_shortcodes;
  foreach ($fdd_im_et_shortcodes as $code) {
    add_shortcode($code, "fdd_sc_$code");
  }
}

function fdd_dereg_shortcodes() {
  global $fdd_im_et_shortcodes;
  foreach ($fdd_im_et_shortcodes as $code) {
    remove_shortcode($code);
  }
}

// Shortcode handlers
function fdd_sc_et_pb_section($atts, $content, $tag) {
  $module_class = $atts['module_class'];
  $result .= "<$tag module_class='$module_class'>";
  $result .= do_shortcode($content);
  $result .= "</$tag>";

  return $result;
}

function fdd_sc_et_pb_row($atts, $content, $tag) {
  $result .= "<$tag>";
  $result .= do_shortcode($content);
  $result .= "</$tag>";

  return $result;
}

function fdd_sc_et_pb_column($atts, $content, $tag) {
  $type = $atts['type'];
  $result .= "<$tag type='$type'>";
  $result .= do_shortcode($content);
  $result .= "</$tag>";

  return $result;
}

function fdd_sc_et_pb_text($atts, $content, $tag) {
  $module_class = $atts['module_class'];
  $result .= "<$tag module_class='$module_class'>";
  $result .= do_shortcode($content);
  $result .= "</$tag>";

  return $result;
}

function fdd_sc_et_pb_row_inner($atts, $content, $tag) {
  $result .= "<$tag>";
  $result .= do_shortcode($content);
  $result .= "</$tag>";

  return $result;
}

function fdd_sc_et_pb_column_inner($atts, $content, $tag) {
  $type = $atts['type'];
  $result .= "<$tag type='$type'>";
  $result .= do_shortcode($content);
  $result .= "</$tag>";

  return $result;
}

function fdd_sc_et_pb_image($atts, $content, $tag) {
  $src = $atts['src'];
  $result .= "<$tag src='$src'/>";

  return $result;
}

function fdd_sc_et_pb_video($atts, $content, $tag) {
  $src = $atts['src'];
  $image_src = $atts['image_src'];
  $result .= "<$tag src='$src' image_src='$image_src'/>";

  return $result;
}

function fdd_sc_URISP($atts, $content, $tag) {
  return "";
}
