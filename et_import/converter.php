<?php

require_once 'shortcodes.php';
require_once 'import_filters.php';

function fdd_import_start() {
  fdd_reg_shortcodes();
  fdd_reg_filters();
}
add_action('import_start', 'fdd_import_start');

function fdd_import_end() {
  fdd_dereg_shortcodes();
  fdd_dereg_filters();
}
add_action('import_end', 'fdd_import_end');
