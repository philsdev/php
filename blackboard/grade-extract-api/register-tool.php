<?php
/**
 * register-tool.php
 *
 * Copyright (c) 2015, PDS@UMOL
 */
 
  // Load configuration settings
  require_once('config.php');
  
  // Load dependent library files
  require_once('lib.php');
  
  // Register API Class
  $api = new BBLearnAPI();
  
  // Set up initial parameters
  $output = array();
  $msg = '';    
  $ok = TRUE;
  
  // Create SOAP clients
  if ($ok) {
    try {
      $context_client = new BbSoapClient($api->getServiceUrl('context'));
    } catch (Exception $e) {      
      $ok = FALSE;
      $msg = $api->getXmlNode('error', $e->getMessage());      
    }
  }
    
  // Register Proxy Tool
  if ($ok) {
    try {
      $result = $api->registerTool($context_client);
      $registration_status = $result->return->status;
      if ($registration_status) {
        $msg = $api->getXmlNode('status', 'Success! Make the proxy tool available in Learn 9');
      } else {
        $ok = FALSE;
        $msg = $api->getXmlNode('status', 'Fail! Tool may already be registered.');
      }
    } catch (Exception $e) {
      $ok = FALSE;
      $msg = $api->getXmlNode('error', $e->getMessage());
    }
  }
  
  header('Content-Type: text/xml; charset=utf-8');
  header("Pragma: no-cache");
  print $msg;

?>