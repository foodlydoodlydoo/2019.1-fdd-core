<?php
/**
 * Plugin Name: FDD Core
 * Plugin URI: https://foodlydoodlydoo.com/
 * Author: mhm
 * Author URI: https://foodlydoodlydoo.com/
 * Version: 1.0.0
 */

/**
 * Can't work with this until I fully understand how to use
 * custom post types.  This only causes problems and is not
 * strictly necessary to have.
 */
const FDD_ENABLE_CUSTOM_POSTS = false;

if (FDD_ENABLE_CUSTOM_POSTS) {

  function fdd_register_post_type_stepbystep() {
    register_post_type('fdd-stepbystep', array(
      'public' => true,
      'has_archive' => true,
      'rewrite' => array('slug' => 'step-by-step'),
      'can_export' => true,
      'taxonomies' => array('post_tag', 'category'),
      'publicly_queryable' => true,
      'capability_type' => 'post',
      'label' => 'FDD Step By Step',
      'labels' => array(
        'name' => __('FDD: Step By Step'),
        'singular_name' => __('FDD: Step By Step'),
      ),
      'show_in_rest' => true,
      'template' => array(
        array('fdd-block/stepbystep'),
      ),
      'supports' => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields'),
      'show_in_admin_bar' => true,
      'show_in_menu' => false,
    ));
  }

  function ffd_add_post_types_to_main_query($query) {
    if ($query->is_main_query()) {
      $query->set('post_type', array('post', 'fdd-stepbystep'));
    }
    return $query;
  }

  add_action('init', 'fdd_register_post_type_stepbystep');
  add_action('pre_get_posts', 'ffd_add_post_types_to_main_query');

} // if (FDD_ENABLE_CUSTOM_POSTS)
