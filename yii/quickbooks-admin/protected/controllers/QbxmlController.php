<?php

class QbxmlController extends Controller
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
    $model = new QbxmlForm();
		
    if(isset($_POST['QbxmlForm']))
    {
      $model->attributes = $_POST['QbxmlForm'];
      
      if($model->validate())
      {
        $data = '';
        
        $volusion = new Volusion();
        $qbxml = new QbXml();
        
        $order_xml = $volusion->getOrderXml($model->attributes['id']);
        
        if ($qbxml->isValidOrderXml($order_xml))
        {          
          /* get option array */
          $option_array = $qbxml->getOptionArray();
	  
          /* get order xml request */
          $qb_xml_array = $qbxml->getXmlRequestArray($order_xml, $option_array);
          
          $data = $qb_xml_array;
        }
        
        $model->_data = $data;
      }
    }
    
    // renders the view file 'protected/views/qbxml/index.php'
    // using the default layout 'protected/views/layouts/main.php'
    
    $this->render('index',array('model'=>$model));    
  }
}