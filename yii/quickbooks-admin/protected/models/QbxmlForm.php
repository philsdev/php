<?php

/**
 * QbxmlForm class.
 * QbxmlForm is the data structure for keeping details form data. 
 * It is used by the 'index' action of 'QbxmlController'.
 */
class QbxmlForm extends CFormModel
{
  public $id;
  public $_data;
  
  /**
   * Declares the validation rules.
   */
  public function rules()
  {
    return array(
      array('id', 'required')
    );
  }

  /**
   * Declares customized attribute labels.
   * If not declared here, an attribute would have a label that is
   * the same as its name with the first letter in upper case.
   */
  public function attributeLabels()
  {
    return array(
      'id' => 'Order Id'
    );
  }
}