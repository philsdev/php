<?php

/**
 * @file
 * Administration page callbacks
 */
 
define('FIELD_BULK_UPDATE_VALUE_DELIMITER', '|');
define('FIELD_BULK_UPDATE_VALUE_LIMIT', '250');

  
function field_bulk_update_form($form, $form_state) {
  
  $form['criteria'] = array(
    '#title' => t('Choose Criteria'),
    '#type' => 'fieldset'    
  );

  $form['criteria']['node_type'] = array(
    '#type' => 'select',
    '#title' => t('Node Type'),
    '#description' => t('Select the type of node'),
    '#options' => field_bulk_update_admin_get_node_type_options(),
  );
  
  $form['criteria']['node_type']['#ajax'] = array(
    'callback' => 'field_bulk_update_admin_field_callback',
    'wrapper' => 'field_selector'
  );
    
  /* determine whether or not node type is selected */
  $selected_node_type = isset($form_state['values']['node_type']) ? $form_state['values']['node_type'] : ''; 
  
  /* determine whether or not field selector should be displayed */
  $criteria_field_style = isset($form_state['values']['node_type']) ? '' : 'display:none'; 
  
  $field_options = field_bulk_update_admin_get_field_options($selected_node_type);
    
  $form['criteria']['field'] = array(
    '#title' => t('Field'),
    '#type' => 'select',
    '#options' => $field_options,
    '#multiple' => FALSE,
    '#prefix' => '<div id="field_selector" style="' . $criteria_field_style . '">',
    '#suffix' => '</div>'
  );
  
  $chosen_keyword = isset($form_state['values']['keyword']) ? $form_state['values']['keyword'] : '';
  
  $form['criteria']['keyword'] = array(
    '#title' => t('Keyword'),
    '#type' => 'textfield',
    '#default_value' => $chosen_keyword
  );
  
  $form['criteria']['field']['#ajax'] = array(
    'callback' => 'field_bulk_update_admin_field_values_callback',
    'wrapper' => 'field_values'
  );
  
  /* determine whether or not field is selected */
  $selected_field = isset($form_state['values']['field']) ? $form_state['values']['field'] : ''; 
  
  /* determine whether or not field values should be displayed */
  $field_values_fieldset_style = !empty($selected_field) ? 'display:block' : 'display:none'; 
  
  $form['fields'] = array(
    '#title' => t('Field Values (LIMIT: ' . FIELD_BULK_UPDATE_VALUE_LIMIT . ')'),
    '#type' => 'fieldset',
    '#prefix' => '<div id="field_values_fieldset">', //style="' . $field_values_fieldset_style . '">',
    '#suffix' => '</div>'
  );
  
  $form['fields']['field_values'] = array(
    '#prefix' => '<div id="field_values">',
    '#markup' => field_bulk_update_admin_get_field_values($selected_node_type, $selected_field, $chosen_keyword),
    '#suffix' => '</div>'
  );

  return system_settings_form($form, TRUE);  
}

function field_bulk_update_admin_get_node_type_options() {

  $node_types = db_query("SELECT type, name FROM {node_type} WHERE locked = 0 AND disabled = 0 ORDER BY name");
  
  $node_type_options = array(
    '' => t('Choose One ...')
  );
  
  foreach ($node_types as $node_type) {
    $node_type_options[$node_type->type] = $node_type->name;
  }
  
  return $node_type_options;
}

function field_bulk_update_admin_get_field_options($node_type = '') {
  $field_options = array(
    '' => t('Choose One ...')
  );
  
  if (!empty($node_type)) {
    $fields = db_query(
      'SELECT field_name, data FROM {field_config_instance} WHERE bundle = :bundle AND deleted = 0 ORDER BY field_name',
      array('bundle' => $node_type)
    );
    
    foreach ($fields as $field) {    
      $field_options[$field->field_name] = $field->field_name; 
    }
  }
  
  return $field_options;
}

function field_bulk_update_admin_get_field_values($node_type, $field_name, $keyword='') {
  $output = array();
  
  if (!empty($node_type) && !empty($field_name)) {
  
    // update session val so field values are not truncated by group_concat
    $session_update = db_query('SET SESSION group_concat_max_len=2000');
    
    $params = array(
      ':entity_type' => 'node',
      ':bundle' => $node_type
    );
    
    $sql = array();
    
    $sql[] = "SELECT     n.status, ";
    $sql[] = "           n.title, ";
    $sql[] = "           n.nid, ";
    $sql[] = "           GROUP_CONCAT(f." . $field_name . "_value SEPARATOR '" . FIELD_BULK_UPDATE_VALUE_DELIMITER . "') as field_values ";
    $sql[] = "FROM       field_data_" . $field_name . " f ";
    $sql[] = "JOIN       node n ON f.entity_id = n.nid ";
    $sql[] = "WHERE      f.entity_type = :entity_type ";
    $sql[] = "AND        f.bundle = :bundle ";
    
    if (!empty($keyword)) {
      $sql[] = "AND        f." . $field_name . "_value LIKE :keyword ";
      
      $params[':keyword'] = '%' . $keyword . '%';
    }
    
    $sql[] = "GROUP BY   n.status, ";
    $sql[] = "           n.title, ";
    $sql[] = "           n.nid ";
    $sql[] = "LIMIT      0," . FIELD_BULK_UPDATE_VALUE_LIMIT . " ";
    
    $field_values = db_query(
      implode(PHP_EOL, $sql),
      $params
    );
    
    $output[] = '<table>';
    $output[] = '<tr>';
    $output[] = '<th></th>';
    $output[] = '<th>Status</th>';	
    $output[] = '<th>NID</th>';
    $output[] = '<th>Node Title</th>';
    $output[] = '<th>Field Value(s)</th>';
    $output[] = '</tr>';
    
    $idx = 0;
    
    foreach ($field_values as $field_value) {
      $idx++;
      
      $output[] = '<tr>';
      $output[] = '<td>' . $idx . '</td>';
      $output[] = '<td>' . $field_value->status . '</td>';
      $output[] = '<td>' . $field_value->nid . '</td>';
      $output[] = '<td>' . $field_value->title . '</td>';
      $output[] = '<td>';
      
      $field_values_array = explode(FIELD_BULK_UPDATE_VALUE_DELIMITER, $field_value->field_values);
      $field_values_count = count($field_values_array);
      
      if ($field_values_count == 0) {
        $field_values_output = '<div></div>';
      } else if ($field_values_count == 1) {
        $value_output = htmlspecialchars($field_value->field_values);
        
        if (!empty($keyword)) {
          $value_output = str_replace($keyword, '<span class="hilite">' . $keyword . '</span>', $value_output);
        }
        
        $field_values_output = '<div>' . $value_output . '</div>';
      } else {
        $field_values_output = '';
        
        foreach ($field_values_array as $key => $value) {
          $value_output = htmlspecialchars($value);
          
          if (!empty($keyword)) {
            $value_output = str_replace($keyword, '<span class="hilite">' . $keyword . '</span>', $value_output);
          }
          
          $field_values_output .= '<div>' . $value_output . '</div>';
        }
      }
      
      $output[] = $field_values_output;
      $output[] = '</td>';
      $output[] = '</tr>';
    }
    
    $output[] = '</table>';
  }
  
  return implode(PHP_EOL, $output);
}

function field_bulk_update_admin_field_callback($form, $form_state) {
  return $form['criteria']['field'];
}

function field_bulk_update_admin_field_values_callback($form, $form_state) {
  return $form['fields']['field_values'];
}