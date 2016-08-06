<?php

/**
 * Volusion class for methods that pull data from the Volusion API
 */
class Volusion {

  /*
   * Get Volusion base URL including authentication
   */
  function getUrlBase() {
    $url = Yii::app()->params['volusionApi']['urlBase'];
    $url .= '?Login=' . Yii::app()->params['volusionApi']['username'];
    $url .= '&EncryptedPassword=' . Yii::app()->params['volusionApi']['password'];
    
    return $url;
  }

  function getOrderUrl($order_id) {
    $url = $this->getUrlBase();
    $url .= "&EDI_Name=Generic\Orders";
    $url .= "&SELECT_Columns=*";
    $url .= "&WHERE_Column=o.OrderID";
    $url .= "&WHERE_Value=" . $order_id;
    
    return $url;
  }

  function getOrderXml($order_id) {
    $url = $this->getOrderUrl($order_id);
    
    $xml = $this->getApiResults($url);
    
    $xml = str_replace('&amp;', '+', $xml);
    $xml = str_replace('&', '+', $xml);
    
    return $xml;
  }

  function getCustomerUrl($customer_id) {
    $url = $this->getUrlBase();
    $url .= "&EDI_Name=Generic\Customers";
    $url .= "&SELECT_Columns=CustomerID,EmailAddress";
    $url .= "&WHERE_Column=CustomerID";
    $url .= "&WHERE_Value=" . $customer_id;
    
    return $url;
  }

  function getCustomerXml($customer_id) {
    $url = $this->getCustomerUrl($customer_id);
    
    $xml = $this->getApiResults($url);
    
    return str_replace('&', '+', $xml);
  }

  function getProductUrl($product_code = '') {
    $url = $this->getUrlBase();
    $url .= "&EDI_Name=Generic\Products";
    $url .= "&SELECT_Columns=p.ProductCode,p.Vendor_PartNo,";
    $url .= "pe.CustomField1,pe.CustomField2,pe.CustomField3,pe.CustomField4,pe.CustomField5,";
    $url .= "p.StockStatus";
    
    if (!empty($product_code)) {
      $url .= "&WHERE_Column=p.ProductCode";
      $url .= "&WHERE_Value=" . $product_code;
    }
    
    return $url;
  }
  
  function getProductXml($product_code) {
    $url = $this->getProductUrl($product_code);
    
    return $this->getApiResults($url);
  }
  
  function getProductStockUrl($stock = 0) {
    $url = $this->getUrlBase();
    $url .= "&EDI_Name=Generic\Products";
    $url .= "&SELECT_Columns=p.ProductCode,p.ProductName,p.Vendor_PartNo";
    $url .= "&WHERE_Column=p.StockStatus";
    $url .= "&WHERE_Value=" . $stock;
    
    return $url;
  }
  
  function getProductStockXml($stock = 0) {
    $url = $this->getProductStockUrl($stock);
    
    return $this->getApiResults($url);
  }
  
  function getEngravingNeededUrl($days = 7) {
    $url = $this->getUrlBase();
    $url .= "&EDI_Name=Generic\EngravingNeeded" . $days;
    
    return $url;
  }
  
  function getEngravingNeededXml($days = 7) {
    $url = $this->getEngravingNeededUrl($days);
    
    return $this->getApiResults($url);
  }
  
  function getDeclinedPaymentsUrl() {
    $url = $this->getUrlBase();
    $url .= "&EDI_Name=Generic\DeclinedPayments";
    
    return $url;
  }
  
  function getDeclinedPaymentsXml() {
    $url = $this->getDeclinedPaymentsUrl();
    
    return $this->getApiResults($url);
  }
  
  /*
   * Request data from Volusion API
   */
  function getApiResults($url) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
    $head = curl_exec($ch);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch); 
    
    return $head;
  }
  
  /*
   * Get URL to use when sending data to Volusion API
   */
  function getApiUpdateUrl() {
    $url = $url = $this->getUrlBase();
    $url . "&Import=Update";
    
    return $url;
  }
  
  /*
   * Send data to Volusion API
   */
  function setApiResults($xml) {
    $url = $this->getApiUpdateUrl();
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded; charset=utf-8", "Content-Action:Volusion_API"));
    
    $head = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch); 
    
    return $head;
  }

}