<?php

const SOURCE_DOMAIN = "https://foodlydoodlydoo.com";
const DESTINATION_DOMAIN = "https://stage.foodlydoodlydoo.com";

// globals
function fdd_reg_filters() {
  add_filter('import_post_meta_key', 'fdd_filter_import_post_meta_key');
  add_filter('wp_import_post_comments', 'fdd_filter_wp_import_post_comments');
  add_filter('wp_import_post_data_raw', 'fdd_filter_wp_import_post_data_raw');
}

function fdd_dereg_filters() {
  remove_filter('import_post_meta_key', 'fdd_filter_import_post_meta_key');
  remove_filter('wp_import_post_comments', 'fdd_filter_wp_import_post_comments');
  remove_filter('wp_import_post_data_raw', 'fdd_filter_wp_import_post_data_raw');
}

// filters
function fdd_filter_import_post_meta_key($key) {
  if (preg_match("/^_?et_|^_yoast_", $key)) {
    return false;
  }
  return $key;
}

function fdd_filter_wp_import_post_comments($comments) {
  return [];
}

// Private code for converting the et content to fdd.2019 blocks

function fdd__fix_markup($content) {
  $lines = explode('\n', $content);

  // make sure lines ^<p> have </p>$
  foreach ($lines as &$line) {
    if (!preg_match("/^<p[\s>]/", $line)) {
      continue;
    }
    if (preg_match("/</p>\s*$/", $line)) {
      continue;
    }
    $line .= "</p>";
  }

  $content = implode('\n', $lines);
  return $content;
}

function fdd__find_node($node_list, $attr, $value) {
  if (!$node_list) {
    return false;
  }

  foreach ($node_list as $node) {
    if (preg_match($value, $node->getAttribute($attr))) {
      return $node;
    }
  }

  return false;
}

function fdd__find_child_node($node, $attr, $value) {
  if (!$node) {
    return false;
  }

  $node = $node->firstChild;
  while ($node) {
    if ($node->nodeType != XML_TEXT_NODE) {
      $target = $node->getAttribute($attr);
      if (preg_match($value, $target)) {
        return $node;
      }
      $result = fdd__find_child_node($node, $attr, $value);
      if ($result) {
        return $result;
      }
    }
    $node = $node->nextSibling;
  }

  return false;
}

function fdd__get_inner_text($node) {
  if (!$node) {
    return '';
  }

  // returns all text in all child nodes.. fix using: +recursion
  // $node->nodeType == XML_TEXT_NODE
  $text = $node->textContent;
  $text = trim($text);
  if (!$text) {
    // Just the first...?
    return fdd__get_inner_text($node->firstChild);
  }

  return $text;
}

function fdd__convert_recipe_specs($node) {
  if (!$node) {
    return '';
  }

  $level = fdd__find_child_node($node, 'class', "/recipe-basic-specs-level/");
  $level = fdd__find_child_node($level, 'class', "/recipe-basic-specs-level-/");
  $level = fdd__get_inner_text($level);

  $specs = fdd__find_child_node($node, 'class', "/recipe-basic-specs-list/");
  $specs = $specs->firstChild;
  $prep_time = fdd__get_inner_text($specs);
  $specs = $specs->nextSibling;
  $cook_time = fdd__get_inner_text($specs);
  $specs = $specs->nextSibling;
  $portions = fdd__get_inner_text($specs);

  $result .= "<!-- wp:fdd-block/recipe--characteristics {\"level\":\"$level\",\"prep_time\":\"$prep_time\",\"cook_time\":\"$cook_time\",\"portions\":\"$portions\"} /-->\n\n";

  return $result;
}

function fdd__convert_recipe_paras($doc, $text_nodes) {
  foreach ($text_nodes as $node) {
    if (!preg_match("/-column-/", $node->getAttribute('module_class'))) {
      continue;
    }

    $node = $node->firstChild;
    $content = '';
    while ($node) {
      if ($node->nodeType == XML_TEXT_NODE) {
        $node = $node->nextSibling;
        continue;
      }

      $class = $node->getAttribute('class');

      if (preg_match("/-title$/", $class)) {
        $title = fdd__get_inner_text($node);
        $node = $node->nextSibling;
        continue;
      }
      if (preg_match("/-subtitle$/", $class)) {
        $content .= "<!-- wp:paragraph -->\n<p><strong>\n";
        $content .= fdd__get_inner_text($node);
        $content .= "\n</strong></p>\n<!-- /wp:paragraph -->\n";
        $node = $node->nextSibling;
        continue;
      }

      $node->removeAttribute('class');

      if ($node->localName == 'p') {
        if (fdd__get_inner_text($node->firstChild) == "Prep:") {
          $title = "Preparation";
          $node->removeChild($node->firstChild);
        }
        $content .= "<!-- wp:paragraph -->\n";
        $content .= $doc->saveHTML($node);
        $content .= "\n<!-- /wp:paragraph -->\n";
        $node = $node->nextSibling;
        continue;
      }
      if ($node->localName == 'ol') {
        $content .= "<!-- wp:list {\"ordered\":true} -->\n";
        $content .= $doc->saveHTML($node);
        $content .= "\n<!-- /wp:list -->\n";
        $node = $node->nextSibling;
        continue;
      }
      if ($node->localName == 'ul') {
        $content .= "<!-- wp:list -->\n";
        $content .= $doc->saveHTML($node);
        $content .= "\n<!-- /wp:list -->\n";
        $node = $node->nextSibling;
        continue;
      }

      $node = $node->nextSibling;
    }

    $title = strtolower($title);
    $title = ucfirst($title);
    $class_name = preg_match("/Ingre/", $title) ? ",\"className\":\"is-style-two-columns\"" : '';
    $result .= "<!-- wp:fdd-block/para-with-title {\"title\":\"$title\"$class_name} -->\n";
    $result .= $content;
    $result .= "<!-- /wp:fdd-block/para-with-title -->\n";
  }

  return $result;
}

function fdd__convert_recipe($doc) {
  $images = $doc->getElementsByTagName('et_pb_image');
  $text_nodes = $doc->getElementsByTagName('et_pb_text');
  $videos = $doc->getElementsByTagName('et_pb_video');

  $result .= "<!-- wp:fdd-block/recipe--page -->\n";

  $result .= "<!-- wp:fdd-block/recipe--media -->\n";
  foreach ($images as $image) {
    $src = $image->getAttribute('src');
    $src = str_replace(SOURCE_DOMAIN, DESTINATION_DOMAIN, $src);
    $image_id = attachment_url_to_postid($src);
    if (!$image_id) {
      // TODO REVERT!
      //throw new Exception("Image not found by id $image_id!");
    }

    $result .= "<!-- wp:image {\"id\":$image_id,\"linkDestination\":\"media\"} -->\n";
    $result .= "<figure class=\"wp-block-image\"><a href=\"$src\"><img src=\"$src\" alt=\"\" class=\"wp-image-$image_id\"/></a></figure>\n";
    $result .= "<!-- /wp:image -->\n\n";
  }
  foreach ($text_nodes as $video) {
    $module_class = $video->getAttribute('module_class');
    if (!preg_match("/mediaelement-video/", $module_class)) {
      continue;
    }

    $iframe = fdd__find_child_node($video, 'src', "/youtube/");
    if (!$iframe) {
      continue;
    }

    $src_iframe = $iframe->getAttribute('src');
    preg_match("/embed\/([^\?]+)/", $src_iframe, $match);
    $reference = esc_attr($match[1]);
    $src = "https://www.youtube.com/watch?v=$reference";

    $vertical = preg_match("/-vertical/", $module_class);
    $vertical = $vertical ? " is-style-vertical" : "";

    $result .= "<!-- wp:core-embed/youtube {\"url\":\"$src\",\"type\":\"video\",\"providerNameSlug\":\"youtube\",\"className\":\"wp-embed-aspect-4-3 wp-has-aspect-ratio$vertical\"} -->";
    $result .= "\n<figure class=\"wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-4-3 wp-has-aspect-ratio$vertical\">";
    $result .= "<div class=\"wp-block-embed__wrapper\">\n";
    $result .= $src;
    $result .= "\n</div>";
    $result .= "</figure>\n";
    $result .= "<!-- /wp:core-embed/youtube -->\n\n";
  }
  $result .= "<!-- /wp:fdd-block/recipe--media -->\n\n";

  $result .= "<!-- wp:fdd-block/recipe--text -->\n";
  $result .= fdd__convert_recipe_specs(fdd__find_node($text_nodes, 'module_class', "/recipe-basic-specs/"));
  $result .= fdd__convert_recipe_paras($doc, $text_nodes);
  $result .= "<!-- /wp:fdd-block/recipe--text -->\n";

  $result .= "<!-- /wp:fdd-block/recipe--page -->\n";

  return $result;
}

function fdd__convert_art($doc) {
  // TODO
  return '';
}

function fdd_filter_wp_import_post_data_raw($post) {
  if ($post['post_type'] != 'post') {
    // ignore
    return $post;
  }

  $content_in = $post['post_content'];
  $content_in = fdd__fix_markup($content_in);
  $content_in = do_shortcode($content_in);

  $categories = array_filter($post['terms'], function ($val) {
    return $val['domain'] == "category";
  });
  $category = $categories[0]['slug'];

  libxml_use_internal_errors(true);
  $doc = \DOMDocument::loadHTML("<html><head><meta charset=\"UTF-8\" /></head><body>\n$content_in\n</body></html>",
    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS);
  libxml_clear_errors();

  if (!$doc) {
    throw new Exception("Filter: post content converted html can't be parsed");
  }

  switch ($category) {
  case 'recipes':
    $content_out = fdd__convert_recipe($doc);
    break;

  case 'food-art':
  case 'behind-the-scenes':
    $content_out = fdd__convert_art($doc);
    break;

  default:
    $content_out = $content_in;
  }

  $post['post_content'] = $content_out;
  return $post;
}