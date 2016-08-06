<?php
/**
 * The followings are the available columns in table 'tbl_post':
 * @property integer $id
 * @property string $type
 * @property string $data
 */
class Details extends CActiveRecord
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
   * @return array customized attribute labels (name=>label)
   */
  public function attributeLabels()
  {
    return array(
      'id' => 'Item Id',
      'type' => 'Item Type',
      'data' => 'Data'
    );
  }

  /**
   * @return string the URL that shows the detail of the post
   */
  public function getUrl()
  {
    return Yii::app()->createUrl('details/view', array(
      'type' => $this->type,
      'id'=>$this->id
    ));
  }
  
  public function search()
  {
    $criteria=new CDbCriteria;

    $criteria->compare('title',$this->title,true);

    $criteria->compare('status',$this->status);

    return new CActiveDataProvider('Post', array(
      'criteria'=>$criteria,
      'sort'=>array(
        'defaultOrder'=>'status, update_time DESC',
      ),
    ));
  }
}