<?php

/**
 * @file
 * Administration page callbacks for cache item clearing from Varnish.
 */

function vache_admin_cache_form() {
  drupal_set_title('Clear Individual Items');
  
  $form['#submit'][] = 'vache_admin_cache_form_submit';
  
  global $base_url;

  if (isset($_SESSION[VACHE_ADMIN_SESSION_BASE]['page_url'])) {
    $default_page_url = $_SESSION[VACHE_ADMIN_SESSION_BASE]['page_url'];
  } else {
    $default_page_url = '/';
  }

  $form['page_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Page URL'),
    '#field_prefix' => $base_url,
    '#default_value' => $default_page_url,
    '#required' => TRUE
  );
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Clear Item Cache')
  );
  
  if (isset($_SESSION[VACHE_ADMIN_SESSION_BASE])) {
    $form['results'] = array(
      '#title' => t('Results'),
      '#type' => 'fieldset'
    );
  
    $form['results']['before'] = array(
      '#prefix' => '<h3>' . t('Before') . '</h3><pre>',
      '#markup' => $_SESSION[VACHE_ADMIN_SESSION_BASE]['before'],
      '#suffix' => '</pre>'
    );
    
    $form['results']['after'] = array(
      '#prefix' => '<h3>' . t('After') . '</h3><pre>',
      '#markup' => $_SESSION[VACHE_ADMIN_SESSION_BASE]['after'],
      '#suffix' => '</pre>'
    );
  }
  
  return $form; 
}

function vache_admin_cache_form_submit($form, &$form_state) {
  global $base_url;
  
  if (isset($form_state['values']['page_url'])) {
    $full_url = $base_url . $form_state['values']['page_url'];
    
    if (filter_var($full_url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {    
      $_SESSION[VACHE_ADMIN_SESSION_BASE]['page_url'] = $form_state['values']['page_url'];
      $_SESSION[VACHE_ADMIN_SESSION_BASE]['before'] = shell_exec('curl -I ' . $full_url);
      $_SESSION[VACHE_ADMIN_SESSION_BASE]['after'] = shell_exec('curl -I -X PURGE ' . $full_url);    
    }
  }

  /* set message */
  drupal_set_message(t('Cache has been cleared'));
}