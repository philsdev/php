<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:Web="http://schemas.live.com/Web/">
<head>
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/styles.css" />
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/assets/js/jquery-1.5.min.js"></script>
<script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/assets/js/admin.js?v=03-20-2014"></script>
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>

<div id="main">

  <div id="header"><?php echo CHtml::encode(Yii::app()->name); ?></div>

  <div id="nav">
    <?php $this->widget('zii.widgets.CMenu',array(
      'items'=>array(
        array('label'=>'Dashboard', 'url'=>array('/dashboard')),
        array('label'=>'API Details', 'url'=>array('/details')),
        array('label'=>'QBXML Details', 'url'=>array('/qbxml')),
        array('label'=>'Current Queue', 'url'=>array('/queue')),
        array('label'=>'Error Log', 'url'=>array('/errors')),
        array('label'=>'Failures', 'url'=>array('/failures')),
        array('label'=>'Fetch New Items', 'url'=>array('/fetch')),
        array('label'=>'Option Codes', 'url'=>array('/options')),
        array('label'=>'Queue Log', 'url'=>array('/log')),
        array('label'=>'Re-Queue', 'url'=>array('/requeue')),
        array('label'=>'Recent Imports', 'url'=>array('/recent')),
        array('label'=>'3M Learning App Codes', 'url'=>array('/codes')),
        array('label'=>'Login', 'url'=>array('/admin/login'), 'visible'=>Yii::app()->user->isGuest),
        array('label'=>'Logout ('.Yii::app()->user->name.')', 'url'=>array('/admin/logout'), 'visible'=>!Yii::app()->user->isGuest)
      ),
    )); ?>
  </div>
  
  <div id="body">
  <?php echo $content; ?>
  </div>
  
  <div class="clear"></div>

</div>

</body>
</html>
