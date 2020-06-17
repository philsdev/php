<?php
  $title = (isset($title)) ? $title : '';
  $tag = 'h2';
  
  switch(strtolower($title)) {
    case 'degree finder': {
      $div_id = 'degree-finder';
      break;
    }
    case 'degree finder': {
      $div_id = 'degree-finder';
      break;
    }
    case 'learn online at umass': {
      $div_id = 'rfi-home-learn-umol';
      $tag = 'h1';
      break;
    }
    default: {
      $div_id = 'second-white';
      break;
    }
  }
?>

<div id="<?php print $div_id; ?>">
  <div id="<?php print $block_html_id; ?>" class="wrapper-960 <?php print $classes; ?>"<?php print $attributes; ?>>
    <?php print render($title_prefix); ?>
    <?php if (@$title): ?>
      <<?php print $tag;?> class="block-title" <?php print $title_attributes; ?>><?php print $title; ?></<?php print $tag;?>>
    <?php endif; ?>
    <?php print render($title_suffix); ?>

    <div class="content padding-ver-50"<?php print $content_attributes; ?>>
      <?php print $content; ?>
    </div>
  </div>
</div><!-- /.block -->