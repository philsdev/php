<div class="form">

<?php $form=$this->beginWidget('CActiveForm'); ?>
  <p class="note">Fields with <span class="required">*</span> are required.</p>
  
  <?php echo CHtml::errorSummary($model); ?>

  <table class="form">
    <tr> 
      <th><?php echo $form->labelEx($model,'vid'); ?></th> 
      <td>
        <?php echo $form->textField($model,'vid'); ?>
        <?php echo $form->error($model,'vid'); ?>
      </td> 
    </tr>
    <tr> 
      <th><?php echo $form->labelEx($model,'qbid'); ?></th> 
      <td>
        <?php echo $form->textField($model,'qbid'); ?>
        <?php echo $form->error($model,'qbid'); ?>
      </td> 
    </tr>
    <tr> 
      <th><?php echo $form->labelEx($model,'pricediff'); ?></th> 
      <td>
        <?php echo $form->textField($model,'pricediff'); ?>
        <?php echo $form->error($model,'pricediff'); ?>
      </td> 
    </tr>
  </table>

  <p><?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?></p>
<?php $this->endWidget(); ?>
  
</div> 