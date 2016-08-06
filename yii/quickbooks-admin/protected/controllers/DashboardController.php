<?php

class DashboardController extends Controller
{
  /**
   *  Install the access control filter
   */
  public function filters()
  {
    return array(
      'accessControl',
    );
  }
  
  /**
   * Declare all actions for this controller
   */
  public $actionArray = array('index');
  
  /**
   * Override access rules 
   */
  public function accessRules()
  {
    return array(
      array(
        'deny',
        'users' => array('?'),
      ),
      array(
        'allow',
        'users' => array('@'),
      )
    );
  }

  /**
   * This is the default 'index' action that is invoked
   * when an action is not explicitly requested by users.
   */
  public function actionIndex()
  {
    // renders the view file 'protected/views/dashboard/index.php'
    // using the default layout 'protected/views/layouts/main.php'
    
    $this->render('index');
  }
}