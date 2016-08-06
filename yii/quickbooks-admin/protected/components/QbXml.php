<?php

/**
 * QbXml class for creating QBXML Files to be imported by Quickbooks Enterprise
 */
class QbXml {

  private $shipping_methods = array(
    '101' => 'Free Shipping',
    '103' => 'In-store Pickup',
    '104' => 'Online Delivery / No Shipping',
    '805' => 'USPS First-Class Mail Package',    
    '808' => 'USPS Priority Mail',
    '813' => 'USPS Priority Mail Express 1-2 Day',
    '1000' => 'Free Shipping',
    '1022' => 'USPS Priority Mail Express 1-Day',
    '1023' => 'USPS Priority Mail Express 2-Day',
    '1059' => 'USPS Priority Mail 2-Day'
  );

  private $payment_methods = array(
    '1' => 'NONE',
    '2' => 'Check',
    '3' => 'Electronic Check',
    '5' => 'Visa',
    '6' => 'MasterCard',
    '7' => 'American Express',
    '8' => 'Discover',
    '9' => 'Diners Club',
    '12' => 'JCB',
    '13' => 'Purchase Order',
    '14' => 'Wire Transfer',
    '15' => 'COD',
    '16' => 'Money Order ',
    '17' => 'Cash',
    '18' => 'PayPal',
    '19' => 'FirePay',
    '20' => 'Delta',
    '21' => 'SOLO',
    '22' => 'Switch',
    '23' => 'Laser',
    '24' => 'Google Checkout',
    '25' => 'PayPal Express',
    '26' => 'PayU',
    '27' => 'Amazon'
  );
  
  function getShippingMethod($method_id = '') {
    return (isset($this->shipping_methods[$method_id])) ? $this->shipping_methods[$method_id] : '';  
  }

  function getPaymentMethod($method_id = '') {
    return (isset($this->payment_methods[$method_id])) ? $this->payment_methods[$method_id] : '';  
  }  
  
  public function getOptionArray() {

    $option_array = array();
    
    $options = Yii::app()->db->createCommand()
      ->select('vid, qbid, pricediff')
      ->from('volusion_options')
      ->queryAll();
      
      
    foreach ($options as $option){
      $option_array[$option['vid']] = array(
        'qbid' => $option['qbid'],
        'pricediff' => $option['pricediff']
      );
    }    
    
    return $option_array;
  }
  
  function getArrayFromXml($xml) {
    $xml_string = simplexml_load_string($xml);
    
    $json = json_encode($xml_string);
    
    $array = json_decode($json,TRUE);
    
    return $array;
  }

  function isValidXml($xml) {
    return (simplexml_load_string($xml) === FALSE) ? FALSE : TRUE;
  }

  function isValidOrderXml($xml) {
    $order_array = $this->getArrayFromXml($xml);
    
    return (isset($order_array['Orders'])) ? TRUE : FALSE;
  }
  
  function getCustomerEmail($customer_id) {
    $volusion = new Volusion();
    
    $customer_xml = $volusion->getCustomerXml($customer_id);
  
    $customer_array = $this->getArrayFromXml($customer_xml);
  
    return (isset($customer_array['Customers']['EmailAddress'])) ? $customer_array['Customers']['EmailAddress'] : '';
  } 
  
  function getFormattedDate($date_time) {
    $date_time_parts = explode(" ", $date_time);
    $date = $date_time_parts[0];
    $date_parts = explode("/", $date);
    $date_final = '';
    
    if (count($date_parts) == 3) {
      /* 2-digit month required in QB */
      if (strlen($date_parts[0]) == 1) {
        $date_parts[0] = '0' . $date_parts[0];
      }  
      
      /* 2-digit day required in QB */
      if (strlen($date_parts[1]) == 1) {
        $date_parts[1] = '0' . $date_parts[1];
      }
      
      /* Quickbooks format YYYY-MM-DD */
      $date_final = $date_parts[2] . '-' . $date_parts[0] . '-' . $date_parts[1];
    }
    
    return $date_final;
  }

  function getFormattedPrice($price = 0) {
    return number_format($price, 2, ".", ",");
  }
  
  function getOptionQbid($option_ids, $option_array) {
    $option_id_array = explode(",", $option_ids);
    
    foreach($option_id_array as $option_id) {
      if (isset($option_array[$option_id])) {
        return $option_array[$option_id]['qbid'];
      }
    }
    
    return '';
  }

  function getOptionPriceDiff($option_ids, $option_array) {
    $option_id_array = explode(",", $option_ids);
    
    foreach($option_id_array as $option_id) {
      if (isset($option_array[$option_id])) {
        return $option_array[$option_id]['pricediff'];
      }
    }
    
    return 0;
  }

  function getProductQbid($product_code) {
    $volusion = new Volusion();
    
    $product_xml = $volusion->getProductXml($product_code);
    
    $product_array = $this->getArrayFromXml($product_xml);
    
    return (isset($product_array['Products']['Vendor_PartNo'])) ? $product_array['Products']['Vendor_PartNo'] : '';  
  }

  function isProductCodeToIgnore($product_code) {
    return (substr($product_code, 0, 3) == 'DSC') ? TRUE : FALSE;
  }
  
  function isActiveOrder($order_status) {
    return ($order_status == 'Cancelled') ? FALSE : TRUE;
  }
  
  function getEngravingText($option_ids, $option_descriptions) {
    $option_containers_list = str_replace("][", "],[", $option_descriptions);
    $option_containers_array = explode(",", $option_containers_list);
    
    foreach($option_containers_array as $option_container) {
      $option_description = trim($option_container, "[]");
      $option_description_split = explode(":", $option_description);
      
      if (count($option_description_split) == 2) {
        if ($option_description_split[0] == 'Engraving') {
          return $option_description;
        }
      }
    }
    
    return '';
  }

  function getEngravingPrice($option_ids, $option_array) {
    $option_id_array = explode(",", $option_ids);
    $engraving_price = 0;
    
    foreach($option_id_array as $option_id) {
      $option_split = explode("___", $option_id);
      
      if (count($option_split) == 2) {
        $option = $option_split[0];
        
        $engraving_price = $this->getOptionPriceDiff($option, $option_array);
      }
    }
    
    return $engraving_price;
  }

  function getEngravedProductRate($rate, $engraving_price) {
    return $rate - $engraving_price;
  }

  function getEngravedProductAmount($rate, $engraving_price, $quantity) {
    return $quantity * ($rate - $engraving_price);
  }
  
  function getAmount($rate, $quantity) {
    return $rate * $quantity;
  }
  
  function getOptionalElement($name, $data) {
    $element = '';
    
    if (!empty($data)) {
      $element = '<' . $name . '>' . $data . '</' . $name . '>';
    }
    
    return $element;
  }

  function getTrimmedValue($val, $length) {
    if (is_array($val)) {
      return $val;
    }
    
    $return_val = trim($val);
    
    if (strlen($return_val)) {
      $return_val = substr($return_val, 0, $length);
    }
    
    return $return_val;
  }

  function getCustomerRefName($first_name, $last_name, $customer_id) {
    return trim($last_name) . ', ' . trim($first_name) . ' ' . trim($customer_id);
  }
  
  /*
   * Get array containing Customer Record XML, Sales Receipt XML and other data
   */
  function getXmlRequestArray($order_xml, $option_array) {
    $return_array = array();
    
    /* Customer ID */
    $return_array['CID'] = '0';
    
    /* Customer Record XML */
    $return_array['CustomerAdd'] = '';
    
    /* Sales Receipt XML */
    $return_array['SalesReceiptAdd'] = '';
    
    /* Customer data to supplement Constant Contact mailing list(s) */
    $return_array['ConstantContact'] = '';
    
    /* Customer data to send review request */
    $return_array['TrustPilot'] = '';
    
    /* Customer data to check if order is not from USA */
    $return_array['ForeignCheck'] = '';
    
    /* Customer data to supplement Robly mailing list(s) */
    $return_array['Robly'] = '';
    
    /* Student Flag, to determine if customer wants 3M App Code */
    $return_array['IsStudent'] = FALSE;
    
    /* Customer Data used to send 3M App Code */
    $return_array['LearningApp'] = '';
    
    $order_array = $this->getArrayFromXml($order_xml);
    
    $order = $order_array['Orders'];
    
    if (!isset($order['BillingCompanyName'])) {
      $order['BillingCompanyName'] = '';
    }
    
    if (!isset($order['BillingAddress2'])) {
      $order['BillingAddress2'] = '';
    }
    
    if (!isset($order['ShipAddress2'])) {
      $order['ShipAddress2'] = '';
    }
    
    if (isset($order['Custom_Field_Student'])) {
      $return_array['IsStudent'] = TRUE;
    }
    
    $order_status = $order['OrderStatus'];
    
    if (!($this->isActiveOrder($order_status))) {
      return $return_array;
    } else {  
      $customer_ref_name = $this->getCustomerRefName(
        $order['BillingFirstName'], 
        $order['BillingLastName'], 
        $order['CustomerID']
      );
      
      $customer_email = $this->getCustomerEmail($order['CustomerID']);
      
      $return_array['ConstantContact'] = array(
        'email' => $customer_email,
        'firstname' => $order['BillingFirstName'],
        'lastname' => $order['BillingLastName']
      );
    
      $return_array['TrustPilot'] = array(
        'email' => $customer_email,
        'firstname' => $order['BillingFirstName'],
        'lastname' => $order['BillingLastName'],
        'orderid' => $order['OrderID']
      );
      
      $return_array['ForeignCheck'] = array(
        'ip' => $order['Customer_IPAddress'],
        'orderid' => $order['OrderID']
      );
      
      $return_array['Robly'] = array(
        'email' => $customer_email,
        'firstname' => $order['BillingFirstName'],
        'lastname' => $order['BillingLastName']
      );
      
      $return_array['LearningApp'] = array(      
        'email' => $customer_email,
        'firstname' => $order['BillingFirstName'],
        'lastname' => $order['BillingLastName'],
        'orderid' => $order['OrderID'],
        'customerid' => $order['CustomerID']    
      );
      
      $c_xml = array();

      $c_xml[] = '<?xml version="1.0" encoding="utf-8"?>';
      $c_xml[] = '<?qbxml version="10.0" ?>';
      $c_xml[] = '<QBXML>';
      $c_xml[] = '  <QBXMLMsgsRq onError="continueOnError">';
      $c_xml[] = '    <CustomerAddRq>';
      $c_xml[] = '      <CustomerAdd>';
      $c_xml[] = '        <Name>' . $customer_ref_name . '</Name>';
      $c_xml[] = '        ' . $this->getOptionalElement('CompanyName', $this->getTrimmedValue($order['BillingCompanyName'], 40));
      $c_xml[] = '        <FirstName>' . $this->getTrimmedValue($order['BillingFirstName'], 25) . '</FirstName>';
      $c_xml[] = '        <LastName>' . $this->getTrimmedValue($order['BillingLastName'], 25) . '</LastName>';
      $c_xml[] = '        <BillAddress>';
      $c_xml[] = '          <Addr1>' . $this->getTrimmedValue($order['BillingAddress1'], 40) . '</Addr1>';
      $c_xml[] = '          ' . $this->getOptionalElement('Addr2', $this->getTrimmedValue($order['BillingAddress2'], 40));
      $c_xml[] = '          <City>' . $order['BillingCity'] . '</City>';
      $c_xml[] = '          <State>' . $order['BillingState'] . '</State>';
      $c_xml[] = '          <PostalCode>' . $order['BillingPostalCode'] . '</PostalCode>';
      $c_xml[] = '          <Country>' . $order['BillingCountry'] . '</Country>';
      $c_xml[] = '        </BillAddress>';
      $c_xml[] = '        <ShipAddress>';
      $c_xml[] = '          <Addr1>' . $this->getTrimmedValue($order['ShipAddress1'], 40) . '</Addr1>';
      $c_xml[] = '          ' . $this->getOptionalElement('Addr2', $order['ShipAddress2']);
      $c_xml[] = '          <City>' . $order['ShipCity'] . '</City>';
      $c_xml[] = '          <State>' . $order['ShipState'] . '</State>';
      $c_xml[] = '          <PostalCode>' . $order['ShipPostalCode'] . '</PostalCode>';
      $c_xml[] = '          <Country>' . $order['ShipCountry'] . '</Country>';
      $c_xml[] = '        </ShipAddress>';
      $c_xml[] = '        <Phone>' . $this->getTrimmedValue($order['BillingPhoneNumber'], 20) . '</Phone>';
      $c_xml[] = '        <Email>' . $customer_email . '</Email>';
      $c_xml[] = '        <Contact>' . $order['BillingFirstName'] . ' ' . $order['BillingLastName'] . '</Contact>';
      $c_xml[] = '        <CustomerTypeRef>';
      $c_xml[] = '          <FullName>Internet</FullName>';
      $c_xml[] = '        </CustomerTypeRef>';
      $c_xml[] = '        <TermsRef>';
      /* TODO: change this to Authorize.net */
      $c_xml[] = '          <FullName>VeriSign PayFlow Pro</FullName>';
      $c_xml[] = '        </TermsRef>';
      /* HACK: sometimes Volusion incorrectly says there is tax on states other than MA so don't allow that */
      if ($order['SalesTaxRate1'] > 0 && $order['BillingState'] = 'MA') {
        $c_xml[] = '        <SalesTaxCodeRef>';
        $c_xml[] = '          <FullName>Tax</FullName>';
        $c_xml[] = '        </SalesTaxCodeRef>';
        $c_xml[] = '        <ItemSalesTaxRef>';
        $c_xml[] = '          <FullName>' . $order['BillingState'] . '</FullName>';
        $c_xml[] = '        </ItemSalesTaxRef>';
      } else {
        $c_xml[] = '        <ItemSalesTaxRef>';
        $c_xml[] = '          <FullName>Out of State</FullName>';
        $c_xml[] = '        </ItemSalesTaxRef>';
      }
      $c_xml[] = '      </CustomerAdd>';
      $c_xml[] = '    </CustomerAddRq>';
      $c_xml[] = '  </QBXMLMsgsRq>';
      $c_xml[] = '</QBXML>';
      
      $return_array['CustomerAdd'] = implode(PHP_EOL, $c_xml);
      $return_array['CID'] = $order['CustomerID'];
	  
      $o_xml = array();
      
      $o_xml[] = '<?xml version="1.0" encoding="utf-8"?>';
      $o_xml[] = '<?qbxml version="10.0" ?>';
      $o_xml[] = '<QBXML>';
      $o_xml[] = '  <QBXMLMsgsRq onError="continueOnError">';
      $o_xml[] = '    <SalesReceiptAddRq>';
      $o_xml[] = '      <SalesReceiptAdd>';
      $o_xml[] = '        <CustomerRef>';
      $o_xml[] = '          <FullName>' . $customer_ref_name . '</FullName>';
      $o_xml[] = '        </CustomerRef>';
      $o_xml[] = '        <TxnDate>' . $this->getFormattedDate($order['OrderDate']) . '</TxnDate>';
      $o_xml[] = '        <RefNumber>' . $order['OrderID'] . '</RefNumber>';
      $o_xml[] = '        <BillAddress>';
      $o_xml[] = '          <Addr1>' . $order['BillingLastName'] . ', ' . $order['BillingFirstName'] . '</Addr1>';
      $o_xml[] = '          <Addr2>' . $order['BillingAddress1'] . '</Addr2>';
      $o_xml[] = '          <City>' . $order['BillingCity'] . '</City>';
      $o_xml[] = '          <State>' . $order['BillingState'] . '</State>';
      $o_xml[] = '          <PostalCode>' . $order['BillingPostalCode'] . '</PostalCode>';
      $o_xml[] = '          <Country>' . $order['BillingCountry'] . '</Country>';
      $o_xml[] = '        </BillAddress>';
      $o_xml[] = '        <ShipAddress>';
      $o_xml[] = '          <Addr1>' . $order['ShipLastName'] . ', ' . $order['ShipFirstName'] . '</Addr1>';
      $o_xml[] = '          <Addr2>' . $order['ShipAddress1'] . '</Addr2>';
      $o_xml[] = '          <City>' . $order['ShipCity'] . '</City>';
      $o_xml[] = '          <State>' . $order['ShipState'] . '</State>';
      $o_xml[] = '          <PostalCode>' . $order['ShipPostalCode'] . '</PostalCode>';
      $o_xml[] = '          <Country>' . $order['ShipCountry'] . '</Country>';
      $o_xml[] = '        </ShipAddress>';
      $o_xml[] = '        <PaymentMethodRef>';
      $o_xml[] = '          <FullName>' . $this->getPaymentMethod($order['PaymentMethodID']) . '</FullName>';
      $o_xml[] = '        </PaymentMethodRef>';
      $o_xml[] = '        <SalesRepRef>';
      $o_xml[] = '          <FullName>WEB</FullName>';
      $o_xml[] = '        </SalesRepRef>';
      $o_xml[] = '        <ShipMethodRef>';
      $o_xml[] = '          <FullName>USPS</FullName>';
      //$o_xml[] = '          <FullName>' . $this->getShippingMethod($order['ShippingMethodID']) . '</FullName>';
      $o_xml[] = '        </ShipMethodRef>';
      $o_xml[] = '        <FOB>*' . $order['OrderID'] . '*</FOB>';
      $o_xml[] = '        <IsToBePrinted>0</IsToBePrinted>';
      $o_xml[] = '        <IsToBeEmailed>0</IsToBeEmailed>';
      
      if (isset($order['OrderDetails']['OrderDetailID']) && count($order['OrderDetails']['OrderDetailID']) == 1) {
        $order_details_array = array();
        $order_details_array[] = $order['OrderDetails'];
      } else {
        $order_details_array = $order['OrderDetails'];
      }
      
      foreach($order_details_array as $order_detail) {
        /* 
         * Products assigned to inventory grids and w/o engraving
         * will not have options -- look to "vendor part no" on product      
         */   
        if (!empty($order_detail['OptionIDs']) && !empty($order_detail['Options'])) {
          $option_ids = $order_detail['OptionIDs'];
          
          $options = $order_detail['Options'];
          
          /* get quickbooks id for this item */
          $qbid = $this->getOptionQbid($option_ids, $option_array);
          
          /* get quickbooks id from product */
          if (empty($qbid)) {
            $qbid = $this->getProductQbid($order_detail['ProductCode']);
          }
          
          /* get engraving text (if any) */
          $engraving_text = $this->getEngravingText($option_ids, $options);
        } else {
          /* get quickbooks id for this item */
          $qbid = $this->getProductQbid($order_detail['ProductCode']);
          
          /* there won't be any engraving on a product w/o options */
          $engraving_text = '';
        }
              
        /* show two line items for engraved products */
        if (strlen($engraving_text) > 0) {
          $engraving_price = $this->getEngravingPrice($option_ids, $option_array);
          
          $rate = $this->getEngravedProductRate(
            $order_detail['ProductPrice'], 
            $engraving_price
          );
          
          $quantity = $order_detail['Quantity'];
          
          $amount = $this->getAmount($rate, $quantity);
          
          /* line item 1 for product */
          $o_xml[] = '        <SalesReceiptLineAdd>';
          $o_xml[] = '          <ItemRef>';
          $o_xml[] = '            <FullName>' . $qbid . '</FullName>';
          $o_xml[] = '          </ItemRef>';
          $o_xml[] = '          <Quantity>' . $quantity .'</Quantity>';
          $o_xml[] = '          <Rate>' . $this->getFormattedPrice($rate) .'</Rate>';
          $o_xml[] = '        </SalesReceiptLineAdd>';
          
          /* line item 2 for engraving */
          $o_xml[] = '        <SalesReceiptLineAdd>';
          $o_xml[] = '          <ItemRef>';
          $o_xml[] = '           <FullName>Engraving</FullName>';
          $o_xml[] = '          </ItemRef>';
          $o_xml[] = '          <Desc>' . $engraving_text . '</Desc>';
          $o_xml[] = '          <Quantity>' . $quantity . '</Quantity>';
          $o_xml[] = '          <Rate>' . $this->getFormattedPrice($engraving_price) .'</Rate>';
          $o_xml[] = '        </SalesReceiptLineAdd>';
        } else if ($this->isProductCodeToIgnore($order_detail['ProductCode'])) {
          /* line item should be ignored (eg: discount line item) */
        } else {
          /* line item for product */
          $o_xml[] = '        <SalesReceiptLineAdd>';
          $o_xml[] = '          <ItemRef>';
          $o_xml[] = '            <FullName>' . $qbid . '</FullName>';
          $o_xml[] = '          </ItemRef>';
          $o_xml[] = '          <Quantity>' . $order_detail['Quantity'] .'</Quantity>';
          $o_xml[] = '          <Rate>' . $this->getFormattedPrice($order_detail['ProductPrice']) .'</Rate>';
          $o_xml[] = '        </SalesReceiptLineAdd>';
        }
      }
      
      /* line item for shipping */
      $o_xml[] = '        <SalesReceiptLineAdd>';
      $o_xml[] = '          <ItemRef>';
      $o_xml[] = '            <FullName>Shipping:Priority Mail</FullName>';
      $o_xml[] = '          </ItemRef>';
      $o_xml[] = '          <Desc>' . $this->getShippingMethod($order['ShippingMethodID']) . '</Desc>';
      $o_xml[] = '          <Quantity>1</Quantity>';
      $o_xml[] = '          <Rate>' . $this->getFormattedPrice($order['TotalShippingCost']) . '</Rate>';
      $o_xml[] = '        </SalesReceiptLineAdd>';
      $o_xml[] = '      </SalesReceiptAdd>';
      $o_xml[] = '    </SalesReceiptAddRq>';
      
      $o_xml[] = '  </QBXMLMsgsRq>';
      $o_xml[] = '</QBXML>';
      
      $return_array['SalesReceiptAdd'] =  implode(PHP_EOL, $o_xml);
            
      return $return_array;
    }
  }
}