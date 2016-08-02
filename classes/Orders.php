<?php
/**
 *
 * Demorati Orders
 * @author Demorati
 * @version 1.05
 *
 */
class Orders {

  /**
  * Array of order types
  */
  private $order_types = array(
    'screening' => 'Screening',
    'verification' => 'Verification',
    'rent' => 'Rent'
  );
  
  /**
  * Array of order statuses
  */
  private $order_statuses = array(
    'cart' => 'Cart',
    'checkout' => 'Checkout',
    'pending' => 'Pending',
    'processing' => 'Processing',
    'complete' => 'Complete'
  );
  
  /**
  * Get details of a user's order
  *
  * @param    int       $uid    internal id of user
  * @param    int       $oid    internal id of order
  * @return   object            order details
  * @access   public
  */
  public function get_order($uid, $oid) {
    $sql = array();
    
    $sql[] = "SELECT    o.oid,";
    $sql[] = "          o.uid,";
    $sql[] = "          o.type,"; 
    $sql[] = "          o.status,"; 
    $sql[] = "          o.created,";
    $sql[] = "          o.changed,"; 
    $sql[] = "          o.data,";
    $sql[] = "          o.checkout_form";
    $sql[] = "FROM      demorati_order o";
    $sql[] = "WHERE     o.oid = :oid";
    $sql[] = "AND       o.uid = :uid";
    
    $params = array(
      ':uid' => $uid,
      ':oid' => $oid
    );    
    
    return db_query(
      implode(PHP_EOL, $sql),
      $params
    )->fetchObject();
  }
  
  /**
  * Get the line items of a user's order
  *
  * @param    int       $uid    internal id of user
  * @param    int       $oid    internal id of order
  * @return   array             line item details
  * @access   public
  */
  public function get_order_items($uid, $oid) {
    $sql = array();
    
    $sql[] = "SELECT    o.oid,";
    $sql[] = "          i.pid,";
    $sql[] = "          i.title,";
    $sql[] = "          i.quantity,";
    $sql[] = "          i.cost,";
    $sql[] = "          i.quantity * i.cost as total,";
    $sql[] = "          p.type,";
    $sql[] = "          p.sku,";
    $sql[] = "          p.psid";
    $sql[] = "FROM      demorati_order o";
    $sql[] = "JOIN      demorati_order_item i ON o.oid = i.oid";
    $sql[] = "JOIN      demorati_product p ON i.pid = p.pid";
    $sql[] = "WHERE     o.oid = :oid";
    $sql[] = "AND       o.uid = :uid";
    $sql[] = "AND       i.deleted IS NULL";
    
    $params = array(
      ':uid' => $uid,
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql),
      $params
    )->fetchAll();
  }
  
  /**
  * Get the distinct product types of an order
  *
  * @param    int       $oid    internal id of order
  * @return   array             product types
  * @access   public
  */
  public function get_order_item_types($oid) {
    $sql = array();
    
    $sql[] = "SELECT    DISTINCT p.type";
    $sql[] = "FROM      demorati_order o";
    $sql[] = "JOIN      demorati_order_item i ON o.oid = i.oid";
    $sql[] = "JOIN      demorati_product p ON i.pid = p.pid";
    $sql[] = "WHERE     o.oid = :oid";
    $sql[] = "AND       i.deleted IS NULL";
    
    $params = array(
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql),
      $params
    )->fetchCol();
  }
  
  /**
  * Get the distinct product fields of an order
  *
  * @param    int       $oid    internal id of order
  * @return   array             product fields
  * @access   public
  */
  public function get_order_item_fields($oid) {
    $sql = array();
    
    $sql[] = "SELECT    DISTINCT p.fields";
    $sql[] = "FROM      demorati_order o";
    $sql[] = "JOIN      demorati_order_item i ON o.oid = i.oid";
    $sql[] = "JOIN      demorati_product p ON i.pid = p.pid";
    $sql[] = "WHERE     o.oid = :oid";
    $sql[] = "AND       i.deleted IS NULL";
    $sql[] = "AND       p.fields IS NOT NULL";
    
    $params = array(
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql),
      $params
    )->fetchCol();
  }  
  
  /**
  * Get the internal user id of an order
  *
  * @param    int       $oid    internal id of order
  * @return   int               internal user id
  * @access   public
  */
  public function get_order_uid($oid) {
    $sql = array();
    
    $sql[] = "SELECT    uid";
    $sql[] = "FROM      demorati_order";
    $sql[] = "WHERE     oid = :oid";
    
    $params = array(
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql),
      $params
    )->fetchField();
  }
  
  /**
  * Get the checkout form values of an order
  *
  * @param    int       $oid    internal id of order
  * @return   array             checkout form values
  * @access   public
  */
  public function get_order_checkout_form($oid) {
    $sql = array();
    
    $sql[] = "SELECT    checkout_form";
    $sql[] = "FROM      demorati_order";
    $sql[] = "WHERE     oid = :oid";
    
    $params = array(
      ':oid' => $oid
    );
    
    $checkout_form = db_query(
      implode(PHP_EOL, $sql),
      $params
    )->fetchField();
    
    return unserialize($checkout_form);
  }
  
  /**
  * Set a new order
  *
  * @param    int       $uid    internal id of user
  * @param    string    $ip     IP address of user
  * @param    string    $type   order type  
  * @return   int               internal id of order
  * @access   public
  */
  public function set_order($uid, $ip, $type) {
    $insert_fields = array(
      'uid' => $uid,
      'type' => $type,
      'status' => 'cart',
      'created' => time(),
      'ip' => $ip
    );
  
    $oid = db_insert('demorati_order')
      ->fields($insert_fields)
      ->execute();
    
    return $oid;
  }
  
  /**
  * Set the status of an order
  *
  * @param    int       $uid      internal id of user
  * @param    int       $oid      internal id of order
  * @param    string    $status   status key of order
  * @return   boolean             success of update
  * @access   public
  */
  public function set_order_status($uid, $oid, $status) {
    $update_fields = array(
      'status' => $status, 
      'changed' => time()
    );
    
    return db_update('demorati_order')
      ->fields($update_fields)
      ->condition('uid', $uid)
      ->condition('oid', $oid)
      ->execute();
  }
  
  /**
  * Set the checkout form of an order
  *
  * @param    int       $oid           internal id of order
  * @param    array     $form_values   checkout form values
  * @return   boolean                  success of update
  * @access   public
  */
  public function set_order_checkout_form($oid, $form_values) {
    $update_fields = array(
      'checkout_form' => serialize($form_values), 
      'status' => 'checkout', 
      'changed' => time()
    );
    
    return db_update('demorati_order')
      ->fields($update_fields)
      ->condition('oid', $oid)
      ->execute();
  }
  
  /**
  * Set the order data of an order
  *
  * @param    int       $oid           internal id of order
  * @param    array     $order_data    order data
  * @return   boolean                  success of update
  * @access   public
  */
  public function set_order_data($oid, $order_data) {
    $update_fields = array(
      'data' => serialize($order_data),
      'changed' => time()
    );
    
    return db_update('demorati_order')
      ->fields($update_fields)
      ->condition('oid', $oid)
      ->execute();
  }
  
  /**
  * Set the order data of an order
  *
  * @param    int       $oid      internal id of order
  * @param    string    $notes    order notes
  * @return   boolean             success of update
  * @access   public
  */
  public function set_order_notes($oid, $notes) {
    $update_fields = array(
      'notes' => check_plain($notes),
      'changed' => time()
    );
    
    return db_update('demorati_order')
      ->fields($update_fields)
      ->condition('oid', $oid)
      ->execute();
  }
  
  /**
  * Set the order line item of an order
  *
  * @param    int       $oid        internal id of order
  * @param    int       $pid        internal id of product
  * @param    string    $title      title of line item
  * @param    int       $quantity   quantity of line item
  * @param    float     $cost       cost of line item  
  * @return   int                   internal id of order line item
  * @access   public
  */
  public function set_order_item($oid, $pid, $title, $quantity, $cost) {
    $insert_fields = array(
      'oid' => $oid,
      'pid' => $pid,
      'title' => $title,
      'quantity' => $quantity,
      'cost' => $cost,
      'created' => time()
    );
  
    $iid = db_insert('demorati_order_item')
      ->fields($insert_fields)
      ->execute();
    
    return $iid;
  }
  
  /**
  * Set the order line item as deleted
  *
  * @param    int       $iid      internal id of line item
  * @return   boolean             success of update
  * @access   public
  */
  public function set_order_item_deleted($iid) {
    $params = array(
      'deleted' => time()
    );
    
    return db_update('demorati_order_item')
      ->fields($params)
      ->condition('iid', $iid)
      ->execute();
  }
  
  /**
  * Set the payment of an order based on submission to Paystand
  *
  * @param    int       $iid                internal id of line item
  * @param    int       $psid               paystand id of order
  * @param    int       $org                paystand id of org
  * @param    float     $amount_subtotal    internal id of line item
  * @param    float     $amount_fee         internal id of line item
  * @param    float     $amount_total       internal id of line item
  * @param    string    $type               paystand type key of payment
  * @param    boolean   $success            success of payment
  * @param    string    $status             paystand status of payment
  * @param    object    $data               paystand raw data
  * @return   int                           internal id of order payment
  * @access   public
  */
  public function set_order_payment($oid, $psid=NULL, $org, $amount_subtotal, $amount_fee, $amount_total, $type, $success, $status, $data) {
    $insert_fields = array(
      'oid' => $oid,
      'psid' => $psid,
      'org' => $org,
      'amount_subtotal' => $amount_subtotal,
      'amount_fee' => $amount_fee,
      'amount_total' => $amount_total,
      'type' => $type,
      'success' => $success,
      'status' => $status,
      'data' => serialize($data),
      'created' => time()
    );
  
    $pid = db_insert('demorati_order_payment')
      ->fields($insert_fields)
      ->execute();
    
    return $pid;
  }  
  
  /**
  * Get display name of order status from internal key
  *
  * @param    string    $status   internal status key
  * @return   string              order status
  * @access   public
  */
  public function get_order_status_name($status) {
    $statuses = $this->order_statuses;
    
    if (isset($statuses[$status])) {
      $status_name = $statuses[$status];
    } else {
      $status_name = 'N/A';
    }
    
    return $status_name;
  }
  
  /**
  * Get display name of order type from internal key
  *
  * @param    string    $type   internal type key
  * @return   string            order type
  * @access   public
  */
  public function get_order_type_name($type) {
    $types = $this->order_types;
    
    if (isset($types[$type])) {
      $type_name = $types[$type];
    } else {
      $type_name = 'N/A';
    }
    
    return $type_name;
  }

  /**
  * Get orders for a user
  *
  * @param    int    $uid   internal id of user
  * @return   array         orders
  * @access   public
  */
  function demorati_get_user_orders($uid) {
    $sql = array();
    
    $sql[] = "SELECT        o.oid,";
    $sql[] = "              o.type,";
    $sql[] = "              o.status,";
    $sql[] = "              o.created,";
    $sql[] = "              o.data,";
    $sql[] = "              o.notes,";
    $sql[] = "              p.amount_subtotal,";
    $sql[] = "              p.pid,";
    $sql[] = "              p.type as rail";
    $sql[] = "FROM          demorati_order o";
    $sql[] = "LEFT JOIN     demorati_order_payment p ON o.oid = p.oid";
    $sql[] = "WHERE         o.uid = :uid";
    $sql[] = "AND           o.status IN ('processing', 'pending', 'complete')";
    $sql[] = "ORDER BY      oid DESC";
    
    $params = array(
      ':uid' => $uid
    );
    
    return db_query(
      implode(PHP_EOL, $sql), 
      $params
    )->fetchAll();
  }

  /**
  * Get orders and their line items for a user
  *
  * @param    int    $uid   internal id of user
  * @return   array         orders
  * @access   public
  */
  function demorati_get_user_orders_and_items($uid) {
    $sql = array();
    
    $sql[] = "SELECT        o.oid,";
    $sql[] = "              o.type,";
    $sql[] = "              o.status,";
    $sql[] = "              o.created,";
    $sql[] = "              p.amount_subtotal,";
    $sql[] = "              GROUP_CONCAT(DISTINCT i.title ORDER BY i.title SEPARATOR ', ') AS items";
    $sql[] = "FROM          demorati_order o";
    $sql[] = "LEFT JOIN     demorati_order_item i ON o.oid = i.oid";
    $sql[] = "LEFT JOIN     demorati_order_payment p ON o.oid = p.oid";
    $sql[] = "WHERE         o.uid = :uid";
    $sql[] = "AND           o.status IN ('processing', 'pending', 'complete')";
    $sql[] = "AND           i.deleted IS NULL";
    $sql[] = "GROUP BY      o.oid,";
    $sql[] = "              o.type,";
    $sql[] = "              o.status,";
    $sql[] = "              o.created,";
    $sql[] = "              p.amount_subtotal";
    $sql[] = "ORDER BY      oid DESC";
    
    $params = array(
      ':uid' => $uid
    );
    
    return db_query(
      implode(PHP_EOL, $sql), 
      $params
    )->fetchAll();
  }
  
  /**
  * Get order for a user
  *
  * @param    int    $uid   internal id of user
  * @param    int    $oid   internal id of order
  * @return   object        order
  * @access   public
  */
  function demorati_get_user_order($uid, $oid) {
    $sql = array();
    
    $sql[] = "SELECT      o.oid,";
    $sql[] = "            o.type,";
    $sql[] = "            o.status,";
    $sql[] = "            o.created";
    $sql[] = "FROM        demorati_order o";
    $sql[] = "WHERE       o.uid = :uid";
    $sql[] = "AND         o.oid = :oid";
    
    $params = array(
      ':uid' => $uid,
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql), 
      $params
    )->fetchObject();
  }
  
  /**
  * Get order line items for an order
  *
  * @param    int    $oid   internal id of order
  * @return   array         order line items
  * @access   public
  */
  function demorati_get_user_order_items($oid) {
    $sql = array();
    
    $sql[] = "SELECT      i.title,";
    $sql[] = "            i.quantity,";
    $sql[] = "            i.cost,";
    $sql[] = "            i.quantity * i.cost as total";
    $sql[] = "FROM        demorati_order_item i";
    $sql[] = "WHERE       i.oid = :oid";
    $sql[] = "AND         i.deleted IS NULL";
    
    $params = array(
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql), 
      $params
    )->fetchAll();
  }
  
  /**
  * Get payment details for an order
  *
  * @param    int    $oid   internal id of order
  * @return   object        order payment
  * @access   public
  */
  function demorati_get_user_order_payment($oid) {
    $sql = array();
    
    $sql[] = "SELECT      p.amount_subtotal,";
    $sql[] = "            p.amount_fee,";
    $sql[] = "            p.amount_total,";
    $sql[] = "            p.type,";
    $sql[] = "            p.success,";
    $sql[] = "            p.created";
    $sql[] = "FROM        demorati_order_payment p";
    $sql[] = "WHERE       p.oid = :oid";
    
    $params = array(
      ':oid' => $oid
    );
    
    return db_query(
      implode(PHP_EOL, $sql), 
      $params
    )->fetchObject();
  }
  
}