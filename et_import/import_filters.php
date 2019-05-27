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

function fdd__next_non_text_node($node) {
  $node = $node->nextSibling;
  while ($node && $node->nodeType == XML_TEXT_NODE) {
    $node = $node->nextSibling;
  }

  return $node;
}

function fdd__get_inner_text($node) {
  if (!$node) {
    return '';
  }

  while ($node) {
    if ($node->nodeType == XML_TEXT_NODE) {
      $text = $node->textContent;
      $text = trim($text);
      return $text;
    }

    $node = $node->nextSibling;
  }

  return false;
}

function fdd__convert_recipe_specs($node) {
  if (!$node) {
    return '';
  }

  $level = fdd__find_child_node($node, 'class', "/recipe-basic-specs-level/");
  $level = fdd__find_child_node($level, 'class', "/recipe-basic-specs-level-/");
  $level = fdd__get_inner_text($level->firstChild);

  $specs = fdd__find_child_node($node, 'class', "/recipe-basic-specs-list/");
  $specs = $specs->firstChild;
  $prep_time = fdd__get_inner_text($specs->firstChild);
  $specs = fdd__next_non_text_node($specs);
  $cook_time = fdd__get_inner_text($specs->firstChild);
  $specs = fdd__next_non_text_node($specs);
  $portions = fdd__get_inner_text($specs->firstChild);

  $result .= "<!-- wp:fdd-block/recipe--characteristics {\"level\":\"$level\",\"prep_time\":\"$prep_time\",\"cook_time\":\"$cook_time\",\"portions\":\"$portions\"} /-->\n\n";

  return $result;
}

function fdd__convert_recipe_paras($doc, $text_nodes) {
  foreach ($text_nodes as $node) {
    if (!preg_match("/-column-/", $node->getAttribute('module_class'))) {
      continue;
    }

    $node = $node->firstChild;
    do {
      // for possible repeated titles inside one et_pb_text
      $repeat = false;

      $content = '';
      $title = '';
      while ($node) {
        if ($node->nodeType == XML_TEXT_NODE) {
          $text = fdd__get_inner_text($node);
          if ($text) {
            $content .= "<!-- wp:paragraph -->\n";
            $content .= $text;
            $content .= "\n<!-- /wp:paragraph -->\n";
          }
          $node = $node->nextSibling;
          continue;
        }

        $class = $node->getAttribute('class');

        if (preg_match("/-title$/", $class)) {
          if ($title) {
            $repeat = true;
            break; // flush current para-with-title block, but return here (hence no shifting to the next sibling)
          }
          $title = fdd__get_inner_text($node->firstChild);
          $node = $node->nextSibling;
          continue;
        }
        if (preg_match("/-subtitle$/", $class)) {
          $content .= "<!-- wp:paragraph -->\n<p><strong>\n";
          $content .= fdd__get_inner_text($node->firstChild);
          $content .= "\n</strong></p>\n<!-- /wp:paragraph -->\n";
          $node = $node->nextSibling;
          continue;
        }

        $node->removeAttribute('class');

        if ($node->localName == 'strong') {
          $content .= "<!-- wp:paragraph -->\n";
          $content .= $doc->saveHTML($node);
          $content .= "\n<!-- /wp:paragraph -->\n";
          $node = $node->nextSibling;
          continue;
        }
        if ($node->localName == 'p') {
          // Fix for the 'Prep:' weirdness...
          if (fdd__get_inner_text($node->firstChild->firstChild) == "Prep:") {
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
      } // while inside one et_pb_text

      $title = strtolower($title);
      $title = ucfirst($title);
      $class_name = preg_match("/Ingre/", $title) ? ",\"className\":\"is-style-two-columns\"" : '';
      $result .= "<!-- wp:fdd-block/para-with-title {\"title\":\"$title\"$class_name} -->\n";
      $result .= $content;
      $result .= "<!-- /wp:fdd-block/para-with-title -->\n";
    } while ($repeat);
  }

  return $result;
}

function fdd__collect_media($images, $text_nodes) {
  $results = [];

  foreach ($images as $image) {
    $result = '';
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

    array_push($results, $result);
  }
  foreach ($text_nodes as $video) {
    $result = '';
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

    array_push($results, $result);
  }

  return $results;
}

function fdd__convert_recipe($doc) {
  $images = $doc->getElementsByTagName('et_pb_image');
  $text_nodes = $doc->getElementsByTagName('et_pb_text');

  $result .= "<!-- wp:fdd-block/recipe--page -->\n";

  $result .= "<!-- wp:fdd-block/recipe--media -->\n";
  $result .= implode('\n', fdd__collect_media($images, $text_nodes));
  $result .= "<!-- /wp:fdd-block/recipe--media -->\n\n";

  $result .= "<!-- wp:fdd-block/recipe--text -->\n";
  $result .= fdd__convert_recipe_specs(fdd__find_node($text_nodes, 'module_class', "/recipe-basic-specs/"));
  $result .= fdd__convert_recipe_paras($doc, $text_nodes);
  $result .= "<!-- /wp:fdd-block/recipe--text -->\n";

  $result .= "<!-- /wp:fdd-block/recipe--page -->\n";

  return $result;
}

function fdd__convert_art($doc) {
  $text_nodes = $doc->getElementsByTagName('et_pb_text');
  $image_nodes = $doc->getElementsByTagName('et_pb_image');

  $node = fdd__find_node($text_nodes, 'module_class', "/recipe-basic-specs/"); // no comment...:)
  if ($node) {
    $description .= "<!-- wp:fdd-block/art--description-container -->\n";
    $subtitle = fdd__find_child_node($node, 'class', "/-subtitle/");
    if ($subtitle) {
      $node->removeChild($subtitle);
    }
    foreach ($node->childNodes as $child) {
      // TODO - copy the inner loop from fdd__convert_recipe_paras..
      $description .= $doc->saveHTML($child);
    }
    $description .= "<!-- /wp:fdd-block/art--description-container -->\n";
  }

  $images = fdd__collect_media($image_nodes, $text_nodes);

  $result .= "<!-- wp:fdd-block/art -->\n";
  $result .= array_shift($images);
  $result .= $description;
  foreach ($images as $image) {
    $result .= $image;
  }
  $result .= "<!-- /wp:fdd-block/art -->\n";

  return $result;
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
