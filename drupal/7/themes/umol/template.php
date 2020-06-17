<?php

/**
 * Override or insert variables into the block templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */

function umol_get_program_group_banner($gid) {
  $params = array(
    ':gid' => $gid,
  );

  return db_query("
    SELECT  banner 
    FROM    {program_group} 
    WHERE   gid = :gid
    LIMIT   0,1
    ", $params
  )->fetchField();
}
 
function umol_get_campus_header($node) {
  $content = array();
  
  if (isset($node->field_campus[LANGUAGE_NONE][0]['value'])) {
    $campus_id = $node->field_campus[LANGUAGE_NONE][0]['value'];
  
    $content[] = '<div class="campus-banner">';
    $content[] = '  <div class="campus-banner-image"><img src="/sites/all/themes/umol/images/header/campus/' . $campus_id . '.jpg"></div>';
    
    /* HACK: show message for program groups with banner */
    if (isset($node->field_group[LANGUAGE_NONE][0]['value'])) {
      $banner = umol_get_program_group_banner($node->field_group[LANGUAGE_NONE][0]['value']);

      if (!empty($banner)) {
        $content[] = '  <div class="campus-banner-deadline">' . $banner . '</div>';
      }
    }    
    
    $content[] = '</div>';
  }

  return implode(PHP_EOL, $content);
}

function umol_get_landing_page_header($node) {
  $content = array();
  
  if (!empty($node->field_text_header[LANGUAGE_NONE][0]['value'])) {
    $headline = $node->field_text_header[LANGUAGE_NONE][0]['value'];
    
    // HACK: strip br tags now that headline is a single line
    $headline = str_replace('<br />', ' ', $headline);
  } else {
    $headline = 'Thinking about getting your degree in 2020? Think UMassOnline';
  }
  
  $content[] = '<div id="landing_page_banner">';
  $content[] = '  <div class="wrapper-960 center-txt">';
  $content[] = '    <h1>' . $headline . '</h1>';
  $content[] = '  </div>';
  $content[] = '</div>';
  
  return implode(PHP_EOL, $content);
}

function umol_process_block(&$variables, $hook) {
  // Drupal 7 should use a $title variable instead of $block->subject.
  $variables['title'] = $variables['block']->subject;
}

function umol_preprocess_node(&$variables) {
  if (!empty($variables['submitted']) && !empty($variables['user_picture'])) {
    $variables['submitted'] .= $variables['user_picture'] . '<div class="clear"></div>';
  }
}