<?php

class DetailsController extends Controller
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
    $model = new DetailsForm();
		
    if(isset($_POST['DetailsForm']))
    {
      $model->attributes = $_POST['DetailsForm'];
      
      if($model->validate())
      {
        $volusion = new Volusion();
        
        $data = '';
        
        switch ($model->attributes['type']) 
        {
          case "order": 
          {
            $data = $volusion->getOrderXml($model->attributes['id']);
            break;
          }
          case "customer": 
          {
            $data = $volusion->getCustomerXml($model->attributes['id']);
            break;
          }
          case "product": 
          {
            $data = $volusion->getProductXml($model->attributes['id']);
            break;
          }
          case "stock": 
          {
            $data = $volusion->getProductStockXml($model->attributes['id']);
            break;
          }
          case "engraving": 
          {
            $data = $volusion->getEngravingNeededXml($model->attributes['id']);
            break;
          }
          case "declined": 
          {
            $data = $volusion->getDeclinedPaymentsXml();
            break;
          }
        }
        
        $model->_data = $data;
      }
    }
    
    // renders the view file 'protected/views/details/index.php'
    // using the default layout 'protected/views/layouts/main.php'
    
    $this->render('index',array('model'=>$model));    
  }
}