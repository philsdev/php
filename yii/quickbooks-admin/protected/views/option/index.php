<?php
/* @var $this OptionController */

$this->pageTitle=Yii::app()->name . ' - Option Codes';

$qbid = (isset($_GET['qbid'])) ? $_GET['qbid'] : '';

?>

<h1>Option Codes</h1>

<form method="get" action="option/create">
  <p><button type="submit">Create Option Code</button></p>
</form>

<form method="get">
  <table>
    <tr>
      <td>Quickbooks ID</td>
      <td><input type="text" name="qbid" value="<?php echo $qbid; ?>" />
      <td><button type="submit">Search</button></td>
    </tr>
  </table>
</form>

<?php $this->widget('zii.widgets.grid.CGridView', array(
  'dataProvider' => $dataProvider,
  'columns' => array(
    array(
      'name' => 'vid',
      'value' => '$data->vid'
    ),
    array(
      'name' => 'qbid',
      'value' => '$data->qbid'
    ),
    array(
      'name' => 'pricediff',
      'value' => '$data->pricediff'
    ),
    array(
      'class' => 'CButtonColumn',
      'template' => '{update}'
    )
  )
)); ?>