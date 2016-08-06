<?php
/* @var $this DetailsController */

$this->pageTitle=Yii::app()->name . ' - API Details';
?>

<h1>API Details</h1>

<p>This page returns raw data from the Volusion API.</p>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm'); ?>

  <?php echo $form->errorSummary($model); ?>

  <table>
    <tr>
      <td><?php echo $form->labelEx($model,'id'); ?></td>
      <td><?php echo $form->textField($model,'id'); ?></td>
    </tr>
    <tr>
      <td><?php echo $form->labelEx($model,'type'); ?></td>
      <td><?php echo $form->dropDownList($model,'type',$model->types); ?></td>
    </tr>
    <tr>
      <td></td>
      <td><?php echo CHtml::submitButton('Submit'); ?></td>
    </tr>
  </table>

<?php $this->endWidget(); ?>

<?php 
  if (!empty($model->_data)) {
    echo '<pre>' . htmlentities($model->_data) . '</pre>';
  }
?>

</div>