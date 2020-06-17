<?php
  $footer_class = '';
  
  switch($block_html_id) {
    case 'block-demorati-demorati-footer-about': {
      $footer_class = 'about';
      break;
    }
    case 'block-demorati-demorati-footer-recent-posts': {
      $footer_class = 'posts';
      break;
    }
    case 'block-demorati-demorati-footer-links': {
      $footer_class = 'links';
      break;
    }
    case 'block-demorati-demorati-footer-follow-us': {
      $footer_class = 'social';
      break;
    }
  }
?>

<div id="<?php print $block_html_id; ?>" class="col span_1_of_4 <?php print $footer_class . ' ' . $classes; ?>"<?php print $attributes; ?>>

  <?php print render($title_prefix); ?>
  <?php if (@$title): ?>
    <h4><?php print $title; ?></h4>
  <?php endif; ?>
  <?php print render($title_suffix); ?>

  <div class="inner">
    <?php print $content; ?>
  </div>
</div><!-- /.block -->
