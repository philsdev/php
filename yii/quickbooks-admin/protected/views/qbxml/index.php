<?php
/* @var $this QbxmlController */

$this->pageTitle=Yii::app()->name . ' - QBXML Details';
?>

<h1>QBXML Details</h1>

<p>This page reads raw data from Volusion and generates formatted QBXML.</p>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm'); ?>

  <?php echo $form->errorSummary($model); ?>

  <table>
    <tr>
      <td><?php echo $form->labelEx($model,'id'); ?></td>
      <td><?php echo $form->textField($model,'id'); ?></td>
    </tr>
    <tr>
      <td></td>
      <td><?php echo CHtml::submitButton('Submit'); ?></td>
    </tr>
  </table>

<?php $this->endWidget(); ?>

<?php 
  if (!empty($model->_data)) {
    echo '';
    
    echo '<h4>Customer</h4>';
    echo '<pre>' . htmlentities($model->_data['CustomerAdd']) . '</pre>';
    echo '<h4>Sales Receipt</h4>';
    echo '<pre>' . htmlentities($model->_data['SalesReceiptAdd']) . '</pre>';

  }
?>

</div>