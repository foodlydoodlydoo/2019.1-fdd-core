<?php

// ----------------------------------------------------------------------------
// The generated "Subscribe Form" (1) from AWeber for 'Weekly newsletter' list
// ----------------------------------------------------------------------------
$subscribe_inline_1 = <<<HTML
<form method="post" class="af-form-wrapper" accept-charset="UTF-8" action="https://www.aweber.com/scripts/addlead.pl"  >
<div style="display: none;">
<input type="hidden" name="meta_web_form_id" value="81309244" />
<input type="hidden" name="meta_split_id" value="" />
<input type="hidden" name="listname" value="awlist5120652" />
<input type="hidden" name="redirect" value="https://stage.foodlydoodlydoo.com/thanks-for-your-subscription/" id="redirect_8f3521caa6bbf86aea1d12cfa3197de4" />

<input type="hidden" name="meta_adtracking" value="Subscribe_Form" />
<input type="hidden" name="meta_message" value="1" />
<input type="hidden" name="meta_required" value="email" />

<input type="hidden" name="meta_tooltip" value="" />
</div>
<div id="af-form-81309244" class="af-form"><div id="af-body-81309244" class="af-body af-standards">
<div class="af-element">
<div class="af-textWrap"><input placeholder="your@email" class="text" id="awf_field-105194248" type="email" name="email" value="" tabindex="500" onfocus=" if (this.value == '') { this.value = ''; }" onblur="if (this.value == '') { this.value='';} " />
</div>
</div>
<div class="af-element buttonContainer">
<input name="submit" class="submit" type="submit" value="Subscribe" tabindex="501" />
<div class="af-clear"></div>
</div>
<div class="af-element privacyPolicy" style="text-align: Right"><p>We respect your <a title="Privacy Policy" href="https://www.aweber.com/permission.htm" target="_blank" rel="nofollow">email privacy</a></p>
<div class="af-clear"></div>
</div>
</div>
</div>
<div style="display: none;"><img src="https://forms.aweber.com/form/displays.htm?id=HIzMDJxMLCw=" alt="" /></div>
</form>
<script type="text/javascript">
// Special handling for in-app browsers that don't always support new windows
(function() {
    function browserSupportsNewWindows(userAgent) {
        var rules = [
            'FBIOS',
            'Twitter for iPhone',
            'WebView',
            '(iPhone|iPod|iPad)(?!.*Safari\/)',
            'Android.*(wv|\.0\.0\.0)'
        ];
        var pattern = new RegExp('(' + rules.join('|') + ')', 'ig');
        return !pattern.test(userAgent);
    }

    if (!browserSupportsNewWindows(navigator.userAgent || navigator.vendor || window.opera)) {
        document.getElementById('af-form-81309244').parentElement.removeAttribute('target');
    }
})();
</script><script type="text/javascript">
    <!--
    (function() {
        var IE = /*@cc_on!@*/false;
        if (!IE) { return; }
        if (document.compatMode && document.compatMode == 'BackCompat') {
            if (document.getElementById("af-form-81309244")) {
                document.getElementById("af-form-81309244").className = 'af-form af-quirksMode';
            }
            if (document.getElementById("af-body-81309244")) {
                document.getElementById("af-body-81309244").className = "af-body inline af-quirksMode";
            }
            if (document.getElementById("af-header-81309244")) {
                document.getElementById("af-header-81309244").className = "af-header af-quirksMode";
            }
            if (document.getElementById("af-footer-81309244")) {
                document.getElementById("af-footer-81309244").className = "af-footer af-quirksMode";
            }
        }
    })();
    -->
</script>
HTML;
// ----------------------------------------------------------------------------
// END OF "Subscribe Form" (1)
// ----------------------------------------------------------------------------

$title_in_content = <<<HTML
Do you like the conent? Subscribe&nbsp;for&nbsp;news!
HTML;


function fdd_aweber_form_sc($atts, $content, $tag) {
  global $title_in_content;

  $title = $atts['title'];
  switch ($title) {
  case "(in-content)":
    $title = $title_in_content;
    break;
  }

  $classes = explode(" ", $atts['class']);

  $form = $atts['name'];
  switch ($form) {
  case "subscribe-inline-1":
    $classes = join(" ", array_merge($classes, array(
      "fdd-aweber-inline-form",
      "fdd-subscribe-inline-1"
    )));

    global $subscribe_inline_1;
    $content = <<<HTML
      <div class="fdd-aweber-inline-form--wrapper">
      <div class="$classes">
      <span class="fdd-aweber--title">$title</span>
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
