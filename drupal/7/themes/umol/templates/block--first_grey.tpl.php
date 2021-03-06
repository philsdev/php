<?php
  $title = (isset($title)) ? $title : '';
  
  switch(strtolower($title)) {
    case 'course finder': {
      $div_id = 'course-finder';
      break;
    }
    case 'explore our campuses': {
      $div_id = 'explore-our-campuses';
      break;
    }
    default: {
      $div_id = 'second-grey';
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
  </div>
</div><!-- /.block -->