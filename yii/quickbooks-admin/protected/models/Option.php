<?php

/**
 * The followings are the available columns in table 'volusion_options':
 * @property integer $vid
 * @property integer $qbid
 * @property float $pricediff
 */
class Option extends CActiveRecord
{

  /**
   * Returns the static model of the specified AR class.
   * @return static the static model class
   */
  public static function model($className=__CLASS__)
  {
    return parent::model($className);
  }

  /**
   * @return string the associated database table name
   */
  public function tableName()
  {
    return 'volusion_options';
  }
  
  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    // NOTE: you should only define rules for those attributes that
    // will receive user inputs.
    return array(
      array('vid, qbid, pricediff', 'required')
    );
  }
      
  /**
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'vid' => 'Volusion Option ID',
      'qbid' => 'Quickbooks ID',
      'pricediff' => 'Price Difference',
    );
  }

}