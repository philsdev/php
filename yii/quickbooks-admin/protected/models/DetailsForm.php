<?php

/**
 * DetailsForm class.
 * DetailsForm is the data structure for keeping details form data. 
 * It is used by the 'index' action of 'DetailsController'.
 */
class DetailsForm extends CFormModel
{
  public $id;
  public $type;
  public $_data;
  
  public $types = array(
    'order' => 'Order',
    'customer' => 'Customer',
    'product' => 'Product',
    'stock' => 'Product Stock (X qty)',
    'engraving' => 'Engraving Needed (X days)',
    'declined' => 'Declined Payments'
  );
  
  /**
   * Declares the validation rules.
   */
  public function rules()
  {
    return array(
      array('id, type', 'required')
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
      'id' => 'Item Id',
      'type' => 'Item Type'
    );
  }
}