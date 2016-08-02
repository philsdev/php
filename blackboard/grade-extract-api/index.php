<?php
/**
 * index.php
 *
 * Copyright (c) 2015, PDS@UMOL
 */
 
  /* increase timeout due to large enrollements */
  set_time_limit (600);

  /* Load configuration settings */
  require_once('config.php');
  
  /* Load dependent library files */
  require_once('lib.php');
  
  /* Register API Class */
  $api = new BBLearnAPI();
  
  /* Set up initial parameters */
  $output = array();
  $msg = '';    
  $ok = TRUE;
  $start_time = time();
  
  if (!isset($_REQUEST['action']) || !isset($_REQUEST['auth'])) {
    $ok = FALSE;
    $msg = '<error>Invalid parameters</error>';    
  }
  
  /* Set up parameters */
  if ($ok) {    
    $action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
    $auth = (isset($_REQUEST['auth'])) ? $_REQUEST['auth'] : '';
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    /* $username and $password are globals used in SOAP headers */
    $session_username = '';
    $session_password = '';
    $target = '';
    $action_array = explode(".", $action);   
    
    if (count($action_array) != 2) {
      $ok = FALSE;
      $msg = '<error>Invalid action parameter</error>';
    } else {
      $session_username = $action_array[0];
      $target = $action_array[1];
    }
  }
  
  /* verify that user is valid */
  if ($ok) {
    if (!$api->isValidUser($session_username)) {
      $ok = FALSE;
      $msg = '<error>Invalid user account</error>';
    }
  }
      
  /* verify that user credentials are valid */
  if ($ok) {
    if (!$api->isAuthorizedUser($session_username, $auth)) {      
      $ok = FALSE;
      $msg = '<error>Invalid user authorization</error>';
    }
  }  
  
  /* Create SOAP clients */
  if ($ok) {
    try {
      $context_client = new BbSoapClient($api->getServiceUrl('context'));
    } catch (Exception $e) {      
      $ok = FALSE;
      $msg = '<error>cannot create SOAP client: ' . $e->getMessage() . '</error>';      
    }
  }
  
  if ($ok) {
    try {
      $course_membership_client = new BbSoapClient($api->getServiceUrl('course_membership'));
    } catch (Exception $e) {      
      $ok = FALSE;
      $msg = '<error>cannot create SOAP client: ' . $e->getMessage() . '</error>';      
    }
  }

  if ($ok) {
    try {
      $course_client = new BbSoapClient($api->getServiceUrl('course'));
    } catch (Exception $e) {      
      $ok = FALSE;
      $msg = '<error>cannot create SOAP client: ' . $e->getMessage() . '</error>';
    }
  }
  
  if ($ok) {
    try {
      $gradebook_client = new BbSoapClient($api->getServiceUrl('gradebook'));
    } catch (Exception $e) {      
      $ok = FALSE;
      $msg = '<error>cannot create SOAP client: ' . $e->getMessage() . '</error>';
    }
  }
  
  if ($ok) {
    try {
      $user_client = new BbSoapClient($api->getServiceUrl('user'));
    } catch (Exception $e) {      
      $ok = FALSE;
      $msg = '<error>cannot create SOAP client: ' . $e->getMessage() . '</error>';
    }
  }
    
  /* Get a session ID */
  if ($ok) {
    try {
      $session_password = $api->getSessionPassword($context_client);
    } catch (Exception $e) {
      $ok = FALSE;
      $msg = '<error>Invalid session: ' . $e->getMessage() .'</error>';
    }
  }
          
  /* Log in as a tool */
  if ($ok) {    
    try {
      $login_result = $api->loginAsTool($context_client);
    } catch (Exception $e) {
      $ok = FALSE;
      $msg .= '<error>Cannot login as tool: ' . $e->getMessage() . '</error>';
    }
  }          

  if ($ok) {
    switch($target) {  
      case 'course': {
        /* get course details */
        try {          
          $course = $api->getCourse($id, $course_client);
        } catch (Exception $e) {
          $ok = FALSE;
          $msg = '<error>Cannot get course details:' . $e->getMessage() . '</error>';
        }
        
        /* validate course */
        if ($ok && !isset($course->id)) {
          $ok = FALSE;
          $msg = '<error>Invalid course</error>';
        }
        
        if ($ok) {
          try {
            $output[] = '<course>';
            $output[] = '<id>' . $course->id . '</id>';
            $output[] = '<courseId>' . $course->courseId . '</courseId>';
            $output[] = '<title>' . $course->name . '</title>';
            $output[] = '<description>' . $course->description . '</description>'; 
            $output[] = '<coursePace>' . $course->coursePace . '</coursePace>';
            $output[] = '<courseServiceLevel>' . $course->courseServiceLevel . '</courseServiceLevel>';
            $output[] = '<startDate>' . $course->startDate . '</startDate>';
            $output[] = '<endDate>' . $course->endDate . '</endDate>';
            
            $end_time = time();
            $elapsed_time = (int) $end_time - $start_time;
            
            $output[] = '<execution>' . $elapsed_time . '</execution>';
            $output[] = '</course>';
            
            $log_msg = 'Course Results: Elapsed Time = ' . $elapsed_time;

            /* log results */
            $api->logRequest($log_msg);
        
            $msg = implode(PHP_EOL, $output);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get assemble course data</error>';
          }
        }
        
        break;
      }
      case 'grades': {
        /* get course details */  
        try {          
          $course = $api->getCourse($id, $course_client);
        } catch (Exception $e) {
          $ok = FALSE;
          $msg = '<error>Cannot get course details</error>';
        }
        
        /* validate course */
        if ($ok && !isset($course->id)) {
          $ok = FALSE;
          $msg = '<error>Invalid course</error>';
        }
        
        /* set course details */ 
        if ($ok) {
          try {   
            $course_pk = $course->id;
            $course_name = $course->name;
            $course_id = $course->courseId;
            $course_id_array = explode("-", $id);
            $course_number = (count($course_id_array) == 2) ? $course_id_array[0] : "N/A";
            $course_term = (count($course_id_array) == 2) ? $course_id_array[1] : "N/A";
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot set course details</error>';
          }
        }
        
        /* get course membership */
        if ($ok) {
          try {            
            $course_membership = $api->getCourseMembership($course_pk, $course_membership_client);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get course membership</error>';
          }
        }        

        /* set user id array */
        if ($ok) {
          try {
            $user_id_array = array();
          
            foreach($course_membership as $membership) {
              if ($membership->roleId == 'S') {            
                $user_id = $membership->userId;
                
                $user_id_array[] = $user_id;
              }
            }
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot set user array</error>';
          }
        }
        
        /* get external grade column id for this course */
        if ($ok) {
          try {            
            $gradebook_column_id = $api->getGradebookExternalColumnId($course_pk, $gradebook_client);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get external grade column id</error>';
          }
        }
        
        /* get grades for this course */
        if ($ok) {
          try {
            $grades = $api->getGrades($course_pk, $gradebook_client, $gradebook_column_id);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get course grades</error>';
          }
        }
        
        /* get grade array */
        if ($ok) {
          try {
            $grade_array = $api->getGradeArray($grades);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get grade array</error>';
          }
        }
        
        /* get users in this course */
        if ($ok) {
          try {
            $users = $api->getUsers($user_id_array, $user_client);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get users</error>';
          }
        }       
        
        if ($ok) {
          try {
            $output[] = '<course>';
            $output[] = '<id>' . $course_id . '</id>';
            $output[] = '<title>' . $course_name . '</title>';
            $output[] = '<number>' . $course_number . '</number>';
            $output[] = '<term>' . $course_term . '</term>';
            $output[] = '<students>';
            
            $student_count = 0;
            $grade_count = 0;
            
            foreach($course_membership as $membership) {
              /* student role */
              if ($membership->roleId == 'S') {
                $student_count++;
                
                $user_id = $membership->userId;
                $member_id = $membership->id;
                    
                foreach($users as $user) {
                  if ($user->id == $user_id) {                    
                    $user_name = $user->name;
                    $user_empl_id = $user->studentId;
                    
                    $user_name_array = explode("_", $user_name);
                    $user_net_id = (count($user_name_array) == 2) ? $user_name_array[1] : $user_name; 
            
                    if (isset($grade_array[$member_id])) {
                      $grade_count++;
                      $user_grade = $grade_array[$member_id];
                    } else {
                      $user_grade = '';
                    }

                    $output[] = '<student>';          
                    $output[] = '<emplid>' . $user_empl_id . '</emplid>'; 
                    $output[] = '<netid>' . $user_net_id . '</netid>';
                    $output[] = '<grade>' . $user_grade . '</grade>';
                    $output[] = '</student>';
                  }
                }
              }
            }

            $end_time = time();
            $elapsed_time = (int) $end_time - $start_time;
            
            $output[] = '</students>';   
            $output[] = '<debug>';   
            $output[] = '<elapsed>' . $elapsed_time . '</elapsed>';
            $output[] = '<students>' . $student_count . '</students>';
            $output[] = '<grades>' . $grade_count . '</grades>';
            $output[] = '</debug>';   
            $output[] = '</course>';
            
            $log_msg = 'Grade Results: Students = ' . $student_count . ', Grades = ' . $grade_count . ', Elapsed Time = ' . $elapsed_time;

            /* log results */
            $api->logRequest($log_msg);
            
            $msg = implode(PHP_EOL, $output);
          } catch (Exception $e) {
            $ok = FALSE;
            $msg = '<error>Cannot get assemble user data</error>';
          }
        }
        
        break;
      }
      default: {
        $ok = FALSE;
        $msg = '<error>Invalid action</error>';
      }
    }
  }
  
  /* Logout */
  if ($ok) {
    try {
      $logout = $api->logout($context_client);
    } catch (Exception $e) {
      $ok = FALSE;
      $msg = '<error>Cannot logout: ' . $e->getMessage() . '</error>';
    }
  }
  
  header('Content-Type: text/xml; charset=utf-8');
  header("Pragma: no-cache");
  print $msg;
  
  if (!$ok) {
    /* log error msg */
    $api->logRequest($msg);
  }
  
?>