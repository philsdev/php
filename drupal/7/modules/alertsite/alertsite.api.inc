<?php
/**
 * @file
 * Class file to handle Alertsite API calls
 * Author: PDS
 */

class Alertsite {
  public function __construct() {
  }
  
  /**
   * Return header elements used in calls to Alertsite API.
   */
  private function getHeader() {
    return array(ALERTSITE_USER_AGENT, ALERTSITE_CONTENT_TYPE);
  }
  
  /**
   * Prepare the XML request for an API login
   *
   * @param $username
   * @param $password
   * @return
   *   An XML string containing the login request.
   */
  private function getLoginRequestXml($username, $password) {
    $login = array();

    $login[] = '<Login>';
    $login[] = '  <Login>' . $username . '</Login>';
    $login[] = '  <Password>' . $password . '</Password>';
    $login[] = '</Login>'; 
    
    return implode(PHP_EOL, $login);
  }
    
  /**
   * Log into the Alertsite API and get a unique user session
   *
   * @param $username
   * @param $password
   * @return
   *   A string containing the Session ID.
   */
  public function getSessionId($username, $password) {
    $login_request_xml = $this->getLoginRequestXml($username, $password);
    
    $login_header = $this->getHeader();
    
    $ch = curl_init(); 
    
    curl_setopt($ch, CURLOPT_URL, ALERTSITE_BASE_URL . 'user/login');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $login_request_xml); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $login_header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $head = curl_exec($ch);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch); 

    $login_response_xml = substr($head, strpos($head, ALERTSITE_RESPONSE_START));

    /* create xml parser */
    $xml_parser = xml_parser_create();

    /* set xml parser options */
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);

    /* check validity of xml */
    $struct_parse_success = xml_parse_into_struct($xml_parser, $login_response_xml, $xml_value_array, $xml_index_array);
    
    /* free xml parser */
    xml_parser_free($xml_parser);

    $session_id = $xml_value_array[$xml_index_array['SessionID'][0]]['value'];
    
    return $session_id;
  }
  
  /**
   * Prepare the XML request for the device status list
   *
   * @param $session_id
   * @param $username
   * @return
   *   An XML string containing the device status request.
   */
  private function getDeviceStatusRequestXml($session_id, $username) {
    $instances = array();
    
    $instances[] = '<Status>';
    $instances[] = '  <APIVersion>1.1</APIVersion>';
    $instances[] = '  <TxnHeader>';
    $instances[] = '    <Request>';
    $instances[] = '      <Login>' . $username . '</Login>';
    $instances[] = '      <SessionID>' . $session_id . '</SessionID>';
    $instances[] = '    </Request>';
    $instances[] = '  </TxnHeader>';
    $instances[] = '  <Source>REST_Client</Source>';
    $instances[] = '</Status>';
  
    return implode(PHP_EOL, $instances);
  }
  
  /**
   * Get device status list from API and return device status array
   *
   * @param $status_request_xml
   * @return
   *   An array containing the device statuses.
   */
  private function getDeviceStatusArray($status_request_xml) {
    $status_header = $this->getHeader();

    $ch = curl_init(); 
    
    curl_setopt($ch, CURLOPT_URL, ALERTSITE_BASE_URL . 'devices/status');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $status_request_xml); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $status_header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $head = curl_exec($ch);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch); 

    $status_response_xml = substr($head, strpos($head, ALERTSITE_RESPONSE_START));

    /* create xml parser */
    $xml_parser = xml_parser_create();

    /* set xml parser options */
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);

    /* check validity of xml */
    $struct_parse_success = xml_parse_into_struct($xml_parser, $status_response_xml, $xml_value_array, $xml_index_array);
      
    /* free xml parser */
    xml_parser_free($xml_parser);

    /* get instance count */
    $total_instances = count($xml_index_array['Device']) / 2;

    $device_array = array();
    $device_id_array = array();

    for($instance_index = 0; $instance_index < $total_instances; $instance_index++) {
      /* ignore the first ObjDevice element in the main response (index + 1) */
      $device_id_index = $xml_index_array['ObjDevice'][$instance_index+1];
      $device_id_value = $xml_value_array[$device_id_index]['value'];
      
      $device_id_array[] = $device_id_value;
      
      if (!array_key_exists($device_id_value, $device_array)) {
        $device_name_index = $xml_index_array['Descrip'][$instance_index];
        $device_name_value = $xml_value_array[$device_name_index]['value'];
      
        $device_array[$device_id_value] = array(
          'name' => urldecode($device_name_value),
          'locations' => array()
        );
      }
      
      $instance_location_index = $xml_index_array['Location'][$instance_index];
      $instance_location_value = $xml_value_array[$instance_location_index]['value']; 
      
      $instance_status_index = $xml_index_array['LastStatusCode'][$instance_index];
      $instance_status_value = $xml_value_array[$instance_status_index]['value'];
      
      $instance_monitor_index = $xml_index_array['Monitor'][$instance_index];
      $instance_monitor_value = $xml_value_array[$instance_monitor_index]['value'];
      
      $device_array[$device_id_value]['locations'][] = array(
        'location' => $instance_location_value,
        'status' => $instance_status_value,
        'monitor' => (strtolower($instance_monitor_value) == 'y') ? 1 : 0
      );
    }
    
    return $device_array;
  }
  
  /**
   * Prepare the XML request for the device status list
   *
   * @param $device_status_array
   * @return
   *   A string containing the device status content.
   */
  private function getDeviceStatusContent($device_status_array) {
    $rows = array();  
    $content = '';
    
    foreach ($device_status_array as $deviceId => $device_detail_array) {
      $monitor_total = 0;
      foreach ($device_detail_array['locations'] as $device_location_index => $device_location_array) {
        if ($device_location_array['monitor']) {
          $monitor_total++;
          
          if ($monitor_total == 1) {
            $row = array();
            $row[] = array(
              'data' => $device_detail_array['name'],
              'colspan' => 2,
              'class' => array('device')
            );
            $rows[] = $row;
          }
          
          $row = array();
          $row[] = array(
            'data' => $this->getDeviceStatusIcon($device_location_array['status']),
            'class' => array('status')
          );
          $row[] = array(
            'data' => $device_location_array['location'],
            'class' => array('location')
          );
          $rows[] = $row;
        }
      }
    }
    
    $table = array(
      'rows' => $rows,
      'attributes' => array(
        'class' => 'alertsite'      
      )
    );
    
    /* generate table */
    $content = theme('table', $table);
    
    return $content;
  }
  
  /**
   * Prepare the XML request for the device status list
   *
   * @param $session_id
   * @param $username
   * @return
   *   A string containing the device status content.
   */
  public function getDevices($session_id, $username) {
    $status_request_xml = $this->getDeviceStatusRequestXml($session_id, $username);
    
    $device_status_array = $this->getDeviceStatusArray($status_request_xml);
    
    $device_status_content = $this->getDeviceStatusContent($device_status_array);
    
    return $device_status_content;
  }
  
  /**
   * Prepare the XML request for the device status list
   *
   * @param $device_status
   * @return
   *   HTML for a device status image.
   */
  private function getDeviceStatusIcon($device_status) {
    
    switch ($device_status) {
      case 0: {
        $status_icon = 'tick-circle.png';
        break;
      }
      default: {
        $status_icon = 'cross-circle.png';
        break;
      }
    }
    
    $icon_params = array(
      'path' => ALERTSITE_ICON_PATH . $status_icon
    );
    
    return theme('image', $icon_params);
  }
  
  /**
   * Log out of the Alertsite API.
   */
  public function logout() {
    $ch = curl_init(); 
    
    curl_setopt($ch, CURLOPT_URL, ALERTSITE_BASE_URL . 'user/logout');
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $head = curl_exec($ch);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch); 
  }
  
}