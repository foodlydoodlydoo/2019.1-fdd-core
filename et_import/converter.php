<?php

namespace FDD\Core\et_im;

require_once 'shortcodes.php';
require_once 'import_filters.php';

function import_start() {
  reg_shortcodes();
  reg_filters();
}
add_action('import_start', 'FDD\Core\et_im\import_start');

function import_end() {
  dereg_shortcodes();
  dereg_filters();
}
add_action('import_end', 'FDD\Core\et_im\import_end');
