<?php
/**
 * lib.php
 *
 * Copyright (c) 2015, PDS@UMOL
 */
 
  /* Load WSSESoap library */
  require_once('soap-wsse.php');

  /* Global variables for SOAP username and password */
  $username = 'session';
  $password = 'nosession';

  /* SOAP client class to inject username and password in requests */
  class BbSoapClient extends SoapClient {
  
    function __doRequest($request, $location, $action, $version, $one_way = 0) {

      global $username, $password;

      $doc = new DOMDocument('1.0');
      $doc->loadXML($request);

      $objWSSE = new WSSESoap($doc);

      $objWSSE->addUserToken($username, $password);
      $objWSSE->addTimestamp();

      return parent::__doRequest($objWSSE->saveXML(), $location, $action, $version, $one_way);
    }
  
  }
  
  /* BB Learn API class */
  class BBLearnAPI {    
    
    public function __construct() {
      global $auth_array, $service_url_array;
      
      $this->service_url_array = $service_url_array;
      $this->auth_array = $auth_array;
    }
    
    public function isValidUser($username = '') {
      $this->logRequest('Checking user = ' . $username);
      return array_key_exists($username, $this->auth_array);
    }
    
    public function isAuthorizedUser($username, $auth) {
      $this->logRequest('Authorizing user = ' . $username);
      return ($this->auth_array[$username] == $auth);
    }
    
    public function getServiceUrl($service_name) {
      return (array_key_exists($service_name, $this->service_url_array)) ? SERVER_URL . WSDL_URL . $this->service_url_array[$service_name] : '';
    }
    
    public function getSessionPassword($context_client) {
      global $password;
      
      // initialize
      $result = $context_client->initialize();
      
      // parse password from initialization, update global var
      $password = $result->return;
      
      return $password;
    }
    
    public function registerTool($context_client) {
      $register_tool = new stdClass();
      $register_tool->clientVendorId = VENDOR_ID;
      $register_tool->clientProgramId = PROGRAM_ID;
      $register_tool->registrationPassword = REGISTRATION_PASSWORD;
      $register_tool->description = TOOL_DESCRIPTION;
      $register_tool->initialSharedSecret = SHARED_SECRET;
      $register_tool->requiredToolMethods = array(
        'Context.WS:emulateUser',         
        'Context.WS:getMemberships', 
        'Context.WS:loginTool', 
        'Context.WS:registerTool', 
        'User.WS:getInstitutionRoles', 
        'User.WS:getSystemRoles',
        'User.WS:getUser', 
        'User.WS:getUserInstitutionRoles', 
        'Course.WS:getAvailableGroupTools',
        'Course.WS:getCartridge',
        'Course.WS:getCategories',
        'Course.WS:getClassifications',
        'Course.WS:getCourse',
        'Course.WS:getCourseCategoryMembership',
        'Course.WS:getGroup',
        'Course.WS:getOrg',
        'Course.WS:getOrgCategoryMembership',
        'Course.WS:getServerVersion',
        'Course.WS:getStaffInfo',
        'Course.WS:loadCoursesInTerm',
        'Course.WS:loadTerm',
        'Course.WS:loadTermbyCourseId',
        'Course.WS:loadTerms',
        'Course.WS:loadTermsByName',
        'CourseMembership.WS:getCourseMembership',
        'CourseMembership.WS:getCourseRoles',
        'CourseMembership.WS:getGroupMembership',
        'CourseMembership.WS:getServerVersion',
        'Gradebook.WS:getAttempts',
        'Gradebook.WS:getGradebookColumns',
        'Gradebook.WS:getGradebookTypes',
        'Gradebook.WS:getGrades',
        'Gradebook.WS:getGradingSchemas',
        'Gradebook.WS:getRequiredEntitlements',
        'Gradebook.WS:getServerVersion'
      );
      
      $this->logRequest('Registering tool');
      
      return $context_client->registerTool($register_tool);
    }
    
    public function loginAsTool($context_client) {
      $input = new stdClass();
      $input->password = SHARED_SECRET;
      $input->clientVendorId = VENDOR_ID;
      $input->clientProgramId = PROGRAM_ID;
      $input->loginExtraInfo = '';  // not used but must not be NULL
      $input->expectedLifeSeconds = SESSION_LIFE_EXPECT;
      
      $this->logRequest('Logging in as tool');
      
      return $context_client->loginTool($input);
    }
    
    public function logout($context_client) {
      $this->logRequest('Logging out');
      
      return $context_client->logout();
    }
    
    public function getCourse($id, $course_client) {
      $course = new stdClass();
      $course->filter = new stdClass();
      $course->filter->filterType = 2;
      $course->filter->batchUids = array($id);
      
      $course_result = $course_client->getCourse($course);
      $course_result_return = $course_result->return;
      
      $this->logRequest('Getting course, ID = ' . $id);
      
      return $course_result_return;
    }
    
    public function getCourseMembership($course_pk, $course_membership_client) {
      $user_id_array = array();
      $user_id_array[] = '';
      
      $member = new stdClass();
      $member->courseId = $course_pk;
      $member->f = new stdClass();
      $member->f->userIds = $user_id_array;
      $member->f->filterType = 6;

      $membership_result = $course_membership_client->getCourseMembership($member);  
      $membership_result_array = $membership_result->return;
      
      $this->logRequest('Getting course membership, course PK = ' . $course_pk);

      return $membership_result_array;
    }
    
    public function getGradebookParams($course_pk) {
      $gradebook = new stdClass();
      $gradebook->courseId = $course_pk;
      $gradebook->filter = new stdClass();
      $gradebook->filter->filterType = 1;
      
      $this->logRequest('Getting gradebook params, course PK = ' . $course_pk);
      
      return $gradebook;
    }
    
    public function getGradebookExternalColumnId($course_pk, $gradebook_client) {
      $gradebook = new stdClass();
      $gradebook->courseId = $course_pk;
      $gradebook->filter = new stdClass();
      $gradebook->filter->filterType = 4;
                
      $get_external_grade_result = $gradebook_client->getGradebookColumns($gradebook);
      $get_external_grade_result_return = $get_external_grade_result->return;
      
      $this->logRequest('Getting external grade column, course PK = ' . $course_pk);

      return $get_external_grade_result_return->id;
    }
    
    public function getGradebookColumns($course_pk, $gradebook_client, $gradebook) {
      $get_gradebook_columns_result = $gradebook_client->getGradebookColumns($gradebook);
      $get_gradebook_columns_result_return = $get_gradebook_columns_result->return;
      
      $this->logRequest('Getting gradebook columns, course PK = ' . $course_pk);
      
      return $get_gradebook_columns_result_return;
    }
    
    public function getGradebookColumnId($gradebook_columns) {
      $gradebook_column_id = 0;
            
      foreach($gradebook_columns as $gradebook_column) {
        /* get proper gradebook column */
        if ($gradebook_column->externalGrade == 1) {
          $gradebook_column_id = $gradebook_column->id;
        }                  
      }
      
      $this->logRequest('Getting gradebook external columns');
      
      return $gradebook_column_id;
    }
    
    public function getGrades($course_pk, $gradebook_client, $gradebook_column_id) {
      $gradebook = new stdClass();
      $gradebook->courseId = $course_pk;
      $gradebook->filter = new stdClass();
      $gradebook->filter->filterType = 3;
      $gradebook->filter->columnId = $gradebook_column_id;
                
      $get_grades_result = $gradebook_client->getGrades($gradebook);
      $get_grades_result_return = $get_grades_result->return;
      
      $this->logRequest('Getting grades');
      
      return $get_grades_result_return;
    }
    
    public function getGradeArray($grades) { 
      $grade_array = array();
            
      foreach($grades as $grade) {
        $grade_array[$grade->memberId] = $grade->schemaGradeValue;
      }
      
      $this->logRequest('Getting grade array');
      
      return $grade_array;
    }
    
    public function getUserIdArray($course_membership) {
      $user_id_array = array();
            
      foreach($course_membership as $membership) {
        $user_id_array[] = $membership->userId;
      }
      
      $this->logRequest('Getting user array');
      
      return $course_membership;
    }
    
    public function getUsers($user_id_array, $user_client) {
      $user_params = new stdClass();
      $user_params->filter = new stdClass();
      $user_params->filter->id = $user_id_array;
      $user_params->filter->filterType = 2;
        
      $user_result = $user_client->getUser($user_params);
      $user_result_return = $user_result->return;
      
      $this->logRequest('Getting users');
    
      return $user_result_return;
    }
    
    /* Utility Functions */
    public function getXmlNode($node_name, $node_value) {
      return '<' . $node_name . '>' . $node_value . '</' . $node_name . '>';
    }
    
    public function logRequest($event_description) {
      $content = array();
      
      $content[] = '[' . $_SERVER['REMOTE_ADDR'] . ']';
      $content[] = '[' . date('r') . ']';
      $content[] = '[' . SERVER_URL . ']';
      
      $description = trim($event_description);
      
      $description = str_replace("\r", "", $description);
      $description = str_replace("\n", "", $description);
      $description = str_replace("\t", "", $description);
      
      $content[] = '[' . $description . ']';
      
      $entry = PHP_EOL . implode(" ", $content);
      
      if (is_writable(API_LOG_FILE)) {
        if ($log_file = fopen(API_LOG_FILE, "a")) {
          fwrite($log_file, $entry);
          
          fclose($log_file);
        }
      }
    }
    
  }

?>