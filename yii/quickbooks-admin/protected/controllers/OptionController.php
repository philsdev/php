<?php

class OptionController extends Controller
{
  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_model;
  
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
   * Displays a particular model.
   */
  public function actionView()
  {
    $option = $this->loadModel();

    $this->render('view',array(
      'model' => $option
    ));
  }
  
  /**
   * Creates a new model.
   * If creation is successful, the browser will be redirected to the 'update' page.
   */
  public function actionCreate()
  {
    $model = new Option();
    
    if(isset($_POST['Option']))
    {
      $model->attributes=$_POST['Option'];

      if($model->save())
      {
        $this->redirect(array('update','id'=>$model->id));
      }
    }
    
    // renders the view file 'protected/views/option/create.php'
    // using the default layout 'protected/views/layouts/main.php'
    
    $this->render('create',array(
      'model'=>$model,
    ));
  }
  
  /**
   * Updates a particular model.
   * If update is successful, the browser will be redirected to the 'update' page.
   */
  public function actionUpdate()
  {
    $model=$this->loadModel();

    if(isset($_POST['Option']))
    {
      $model->attributes=$_POST['Option'];
      
      if($model->save())
      {
        $this->redirect(array('update','id'=>$model->vid));
      }
    }

    // renders the view file 'protected/views/option/update.php'
    // using the default layout 'protected/views/layouts/main.php'
    
    $this->render('update',array(
      'model' => $model,
    ));
  }

  /**
   * This is the default 'index' action that is invoked
   * when an action is not explicitly requested by users.
   */
  public function actionIndex()
  {
    $criteria=new CDbCriteria(array(
      'condition' => 'LENGTH(qbid) > 0',
      'order' => 'qbid ASC'
    ));
    
    if(isset($_GET['vid']))
    {
      $criteria->addSearchCondition('vid', $_GET['vid']);
    }
    
    if(isset($_GET['qbid']))
    {
      $criteria->addSearchCondition('qbid', $_GET['qbid']);
    }
    
    $dataProvider=new CActiveDataProvider('Option', array(
      'pagination'=>array(
        'pageSize'=>Yii::app()->params['pagination']['recordsPerPage'],
      ),
      'criteria'=>$criteria,
    ));

    // renders the view file 'protected/views/option/index.php'
    // using the default layout 'protected/views/layouts/main.php'
    
    $this->render('index',array(
      'dataProvider' => $dataProvider,
    ));
  }
  
  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   */
  public function loadModel()
  {    
    if($this->_model === null)
    {
      if(isset($_GET['id']))
      {
        $this->_model = Option::model()->findByPk($_GET['id']);
      }
      
      if($this->_model === null)
      {
        throw new CHttpException(404,'The requested page does not exist.');
      }

      return $this->_model;
    }
  }
}