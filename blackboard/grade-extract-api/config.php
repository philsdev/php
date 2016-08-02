<?php

  define('SERVER_URL', 'https://path.to.blackboard');   // without closing "/"
  define('WSDL_URL', '/webapps/ws/services/');
  define('REGISTRATION_PASSWORD', '********');

  define('VENDOR_ID', 'vendor.id');
  define('PROGRAM_ID', 'program.id');
  define('TOOL_DESCRIPTION', 'A proxy tool to access Learn 9 web services');
  define('SHARED_SECRET', '********'); 
  define('SESSION_LIFE_EXPECT', 180); 
  define('API_LOG_FILE', '/path.to/bb_api_log');
  
  /* create local auth array if not using db or other means of authentication */
  $auth_array = array(
    'user' => '********'
  );
    
  $service_url_array = array(
    'context' => 'Context.WS?wsdl',
    'course_membership' => 'CourseMembership.WS?wsdl',
    'course' => 'Course.WS?wsdl',
    'gradebook' => 'Gradebook.WS?wsdl',
    'user' => 'User.WS?wsdl',
    'util' => 'Util.WS?wsdl'
  );

?>