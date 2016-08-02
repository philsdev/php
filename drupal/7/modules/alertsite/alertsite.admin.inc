<?php

/**
 * @file
 * Administration page callbacks for the Alertsite module.
 */

function alertsite_admin_settings() {

  $form['alertsite_api_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#description' => t('Enter the username associated with your API account.'),
    '#default_value' => variable_get('alertsite_api_username'),
    '#size' => 50,
    '#required' => TRUE
  );
  
  $form['alertsite_api_password'] = array(
    '#type' => 'password',
    '#title' => t('Password'),
    '#description' => t('Enter the password associated with your API account.'),
    '#default_value' => variable_get('alertsite_api_password'),
    '#size' => 50,
    '#required' => TRUE
  );

  return system_settings_form($form, TRUE);   
}

function alertsite_admin_settings_validate($form, $form_state) {

  $username = $form_state['values']['alertsite_api_username'];
  $password = $form_state['values']['alertsite_api_password'];

  variable_set('alertsite_api_username', $username);
  variable_set('alertsite_api_password', $password);
  
  /* set message */
  drupal_set_message(t('Alertsite API information has been saved'));    
}