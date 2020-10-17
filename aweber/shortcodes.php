<?php

// ----------------------------------------------------------------------------
// The generated "Subscribe Form" (1) from AWeber for 'Weekly newsletter' list
// ----------------------------------------------------------------------------
$thank_you_url = get_site_url(null, "/thanks-for-your-subscription/");

$subscribe_inline_1 = <<<HTML
<form method="post" class="af-form-wrapper" accept-charset="UTF-8" action="https://www.aweber.com/scripts/addlead.pl"  >
<div style="display: none;">
<input type="hidden" name="meta_web_form_id" value="81309244" />
<input type="hidden" name="meta_split_id" value="" />
<input type="hidden" name="listname" value="awlist5120652" />
<input type="hidden" name="redirect" value="$thank_you_url" id="redirect_66ae5b3ee67e6a357388bac0a975b230" />

<input type="hidden" name="meta_adtracking" value="Subscribe_Form" />
<input type="hidden" name="meta_message" value="1" />
<input type="hidden" name="meta_required" value="name,email" />
<!--input type="hidden" name="meta_forward_vars" value="1" /-->

<input type="hidden" name="meta_tooltip" value="" />
</div>
<div id="af-form-81309244" class="af-form">
<div id="af-body-81309244" class="af-body af-standards">

<div class="af-element">
<div class="af-textWrap">
<input placeholder="Name" id="awf_field-105814770" type="text" name="name" class="text" value=""  onfocus=" if (this.value == '') { this.value = ''; }" onblur="if (this.value == '') { this.value='';} " tabindex="500" />
</div>
<div class="af-clear"></div></div>

<div class="af-element">
<div class="af-textWrap">
<input placeholder="your@email" class="text" id="awf_field-105814771" type="email" name="email" value="" tabindex="501" onfocus=" if (this.value == '') { this.value = ''; }" onblur="if (this.value == '') { this.value='';} " />
</div>
</div>

<div class="af-element buttonContainer">
<input name="submit" class="submit" type="submit" value="Subscribe" tabindex="502" />
<div class="af-clear"></div>
</div>

<div class="af-element privacyPolicy" style="text-align: Right"><p>We respect your <a title="Privacy Policy" href="https://www.aweber.com/permission.htm" target="_blank" rel="nofollow">email privacy</a></p>
<div class="af-clear"></div>
</div>
</div>
</div>
<div style="display: none;"><img src="https://forms.aweber.com/form/displays.htm?id=HIzMDJxMLCw=" alt="" /></div>
</form>
HTML;
// ----------------------------------------------------------------------------
// END OF "Subscribe Form" (1)
// ----------------------------------------------------------------------------

$title_in_content = <<<HTML
Do you like the content? Subscribe&nbsp;for&nbsp;news!
HTML;


function fdd_aweber_form_sc($atts, $content, $tag) {
  global $title_in_content;

  $title = $atts['title'];
  switch ($title) {
  case "(in-content)":
    $title = $title_in_content;
    break;
  }

  $message = FDD\Core\get_custom('subscribe_notice');

  $classes = array_key_exists('class', $atts) ? explode(" ", $atts['class']) : array();

  $form = $atts['name'];
  switch ($form) {
  case "subscribe-inline-1":
    $classes = join(" ", array_merge($classes, array(
      "fdd-aweber-inline-form",
      "fdd-subscribe-inline-1"
    )));

    global $subscribe_inline_1;
    $content = <<<HTML
      <div class="fdd-aweber-inline-form--wrapper home-call-to-action--wrap">
      <div class="$classes">
      <span class="fdd-aweber--title">$title</span>
      <span class="fdd-aweber--message">$message</span>
      $subscribe_inline_1
      </div>
      </div>
HTML;
    break;
  }

  return $content;
}

add_action('init', function() {
  add_shortcode("fdd_aweber_form", "fdd_aweber_form_sc");
});
