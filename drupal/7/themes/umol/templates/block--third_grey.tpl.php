<?php
  $title = (isset($title)) ? $title : '';
  
  switch(strtolower($title)) {
    case 'menu': {
      $div_id = 'site-menu';
      break;
    }
    default: {
      $div_id = 'third-grey';
      break;
    }
  }
?>

<div id="<?php print $div_id; ?>" class="bg-grey">
  <div id="<?php print $block_html_id; ?>" class="wrapper-960 <?php print $classes; ?>"<?php print $attributes; ?>>
    <?php print render($title_prefix); ?>
    <?php if (@$title): ?>
      <h2 class="block-title" <?php print $title_attributes; ?>><?php print $title; ?></h2>
    <?php endif; ?>
    <?php print render($title_suffix); ?>

    <div class="content padding-ver-50"<?php print $content_attributes; ?>>
      <?php print $content; ?>
    </div>
    <div class="clear"></div>
  </div>
</div><!-- /.block -->