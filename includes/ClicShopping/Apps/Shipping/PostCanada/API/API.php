<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @licence MIT - Portion of osCommerce 2.4
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Shipping\PostCanada\API;

  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\Registry;

  class API
  {

    protected $unit_length;
    protected $email_errors;
    protected $access_username;
    protected $access_password;
    protected $access_customer_number;
    protected $timeout;
    protected $quotetype;
    protected $contract_id;
    protected $origin_postalcode;
    protected $use_insurance;
    protected $turnaround;
    protected $protocol;
    protected $host;
    protected $port;
    protected $path;

    public function __construct()
    {

      $this->access_username = CLICSHOPPING_APP_POSTCANADA_PC_USERNAME;
      $this->access_password = CLICSHOPPING_APP_POSTCANADA_PC_PASSWORD;
      $this->access_customer_number = CLICSHOPPING_APP_POSTCANADA_PC_CUSTOMER_NUMBER;
      $this->timeout = '60';
      $this->quotetype = CLICSHOPPING_APP_POSTCANADA_PC_QUOTE_TYPE;
      $this->contract_id = CLICSHOPPING_APP_POSTCANADA_PC_CONTRACT_ID;
      $this->origin_postalcode = CLICSHOPPING_APP_POSTCANADA_PC_POSTALCODE;
      $this->use_insurance = ((CLICSHOPPING_APP_POSTCANADA_PC_INSURE == 'True') ? true : false);
      $sdate = CLICSHOPPING_APP_POSTCANADA_PC_TURNAROUNDTIME;
      $this->turnaround = date('Y-m-d', strtotime('+' . $sdate . ' day'));

      $this->protocol = 'https';
      $this->host = ((CLICSHOPPING_APP_POSTCANADA_PC_MODE == 'Test') ? 'ct.soa-gw.canadapost.ca' : 'soa-gw.canadapost.ca');
      $this->port = '443';
      $this->path = '/rs/ship/price';

// the variables for unit weight, unit length, and dimensions support were moved to
// shop admin -> Configuration -> Shipping/Packaging in
// Run the configuration_shipping.sql to add these to your configuration

      $this->unit_weight = SHIPPING_WEIGHT_UNIT;

      if (defined('CLICSHOPPING_APP_POSTCANADA_PC_UNIT_LENGTH')) {
        $this->unit_length = CLICSHOPPING_APP_POSTCANADA_PC_UNIT_LENGTH;
      }

      if (CLICSHOPPING_APP_POSTCANADA_PC_DIMENSIONS_SUPPORT == 'Ready-to-ship only') {
        $this->dimensions_support = 1;
      } elseif (CLICSHOPPING_APP_POSTCANADA_PC_DIMENSIONS_SUPPORT == 'With product dimensions') {
        $this->dimensions_support = 2;
      } else {
        $this->dimensions_support = 0;
      }

      $this->items_qty = 0;

      // insurance addition
      $this->insure_package = false;
      if (CLICSHOPPING_APP_POSTCANADA_PC_INSURE == 'True') {
        $this->insure_package = true;
      }
    }


    public function canadaPostOrigin($city, $stateprov, $country, $postal)
    {
      $this->_canadaPostOriginCity = $city;
      $this->_canadaPostOriginStateProv = $stateprov;
      $this->_canadaPostOriginCountryCode = $country;
      $postal = str_replace(' ', '', $postal);

      if ($country == 'US') {
        $this->_canadaPostOriginPostalCode = substr($postal, 0, 5);
      } else {
        $this->_canadaPostOriginPostalCode = strtoupper($postal);
      }
    }


    public function canadaPostDest($city, $stateprov, $country, $postal)
    {
      $this->_canadaPostDestCity = $city;
      $this->_canadaPostDestStateProv = $stateprov;
      $this->_canadaPostDestCountryCode = $country;
      $postal = str_replace(' ', '', $postal);

      if ($country == 'US') {
        $this->_canadaPostDestPostalCode = substr($postal, 0, 5);
      } else if ($country == 'BR') {
        $this->_canadaPostDestPostalCode = substr($postal, 0, 5);
      } else if ($country == 'CA') {
        $this->_canadaPostDestPostalCode = strtoupper($postal);
      } else {
        $this->_canadaPostDestPostalCode = $postal;
      }
    }

    public function addItem($length, $width, $height, $weight, $price = 0)
    {
      // Add box or item to shipment list. Round weights to 1 decimal places.
      if ((float)$weight < 1.0) {
        $weight = 1;
      } else {
        $weight = round($weight, 3);
      }
      $index = $this->items_qty;

      $this->item_length[$index] = ($length ? (string)round((float)$length, 1) : '0');
      $this->item_width[$index] = ($width ? (string)round((float)$width, 1) : '0');
      $this->item_height[$index] = ($height ? (string)round((float)$height, 1) : '0');
      $this->item_weight[$index] = ($weight ? (string)round((float)$weight, 1) : '0');
      $this->item_price[$index] = $price;
      $this->items_qty++;
    }

    public function canadaPostGetQuote()
    {
//need to loop throught all items or packages
      $capost = [];

//first item/package
      for ($i = 0; $i < count($this->item_width); $i++) {
        $xmlRequest = $this->GetRequestXml(0);

        $result = $this->post($xmlRequest);

        if ($result === false) return false;

        $box = $this->parseResult($result);

        if ($box === false) return false;

        for ($i3 = 0; $i3 < count($box); $i3++) {
          $merge = true;

          for ($i2 = 0; $i2 < count($capost); $i2++) {
            if (strcmp($capost[$i2]['service-code'], $box[$i3]['service-code']) == 0) {
              $capost[$i2]['base'] += $box[$i3]['base'];
              $merge = false;
              break;
            }
          }

          if ($merge) {
            $capost[] = ['service-name' => $box[$i3]['service-name'],
              'base' => $box[$i3]['base'],
              'service-code' => $box[$i3]['service-code'],
              'expected-delivery-date' => $box[$i3]['expected-delivery-date']
            ];
          }
        }
      }

      return $capost;
    }


    public function parseResult($xmlResult)
    {
      $aryProducts = false;
      $priceQuotes = $xmlResult->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v4');

      if ($priceQuotes->{'price-quote'}) {
        $aryProducts = array();

        foreach ($priceQuotes as $priceQuote) {
          $title = (string)$priceQuote->{'service-name'};
          $code = (string)$priceQuote->{'service-code'};
          $charge = (float)$priceQuote->{'price-details'}->{'base'};
          $ddate = (string)$priceQuote->{'service-standard'}->{'expected-delivery-date'}; //2011-09-21*/
          $aryProducts[] = ['service-name' => $title,
            'base' => $charge,
            'service-code' => $code,
            'expected-delivery-date' => $ddate
          ];
        }
      }

      return $aryProducts;
    }

    //******************************************************************
    private function post($xmlRequest)
    {
      $CLICSHOPPING_Customer = Registry::get('Customer');

      $url = $this->protocol . '://' . $this->host . ':' . $this->port . $this->path;
      $curl = curl_init($url); // Create REST Request
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($curl, CURLOPT_CAINFO, CLICSHOPPING::getConfig('dir_root', 'Shop') . 'includes/ClicShopping/External/cacert.pem'); // Signer Certificate in PEM format
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlRequest);
      curl_setopt($curl, CURLOPT_TIMEOUT, (int)$this->timeout);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_USERPWD, $this->access_username . ':' . $this->access_password);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.cpc.ship.rate-v4+xml', 'Accept: application/vnd.cpc.ship.rate-v4+xml'));
      $curl_response = curl_exec($curl); // Execute REST Request

// send email if enabled in the admin section
      if (curl_errno($curl) && $this->email_errors) {
        $error_from_curl = sprintf('Error [%d]: %s', curl_errno($curl), curl_error($curl));
        error_log("Error from cURL: " . $error_from_curl . " experienced by customer with id " . $CLICSHOPPING_Customer->customersID() . " on " . date('Y-m-d H:i:s'), 1, STORE_OWNER_EMAIL_ADDRESS);
      }

      curl_close($curl);

      libxml_use_internal_errors(true);

      $xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/', '', $curl_response) . '</root>');

      if (!$xml) {
        $error_from_curl = 'Failed loading XML' . "\n" . $curl_response . "\n";

        if ($this->email_errors) error_log("Error from cURL: " . $error_from_curl . " experienced by customer with id " . $CLICSHOPPING_Customer->customersID() . " on " . date('Y-m-d H:i:s'), 1, STORE_OWNER_EMAIL_ADDRESS);
      } else {
        if ($xml->{'price-quotes'}) {
          return $xml;
        }

        if ($xml->{'messages'}) {
          $messages = $xml->{'messages'}->children('https://www.canadapost.ca/ws/messages');
          $error_from_curl = '';
          foreach ($messages as $message) {
            $error_from_curl .= 'Error Code: ' . $message->code . "\n";
            $error_from_curl .= 'Error Msg: ' . $message->description . "\n\n";
          }

          if ($this->email_errors) error_log("Error from cURL: " . $error_from_curl . " experienced by customer with id " . $CLICSHOPPING_Customer->customersID() . " on " . date('Y-m-d H:i:s'), 1, STORE_OWNER_EMAIL_ADDRESS);
        }
      }
      return false;
    }

    public function exclude_choices($type)
    {

      if (substr($type, 0, 4) == 'DOM.') {
        $type = 'D.' . substr($type, 4);
      } else if (substr($type, 0, 4) == 'USA.') {
        $type = 'U.' . substr($type, 4);
      } else if (substr($type, 0, 4) == 'INT.') {
        $type = 'I.' . substr($type, 4);
      }

      $disallowed_types = explode(";", CLICSHOPPING_APP_POSTCANADA_PC_TYPE);

      for ($za = 0; $za < count($disallowed_types); $za++) {
        // when no disallowed types are present, --none-- is in the db but causes an error because --none-- is
        // not added as a define
        if ($disallowed_types[$za] == '--none--') continue;

        if ($type == trim($disallowed_types[$za])) {
          return true;
        }
      }
// if the type is not disallowed:
      return false;
    }

// Next public function used for sorting the shipping quotes on rate: low to high is default.
    public function rate_sort_func($a, $b)
    {

      $av = array_values($a);
      $av = $av[0];
      $bv = array_values($b);
      $bv = $bv[0];

      //  return ($av == $bv) ? 0 : (($av < $bv) ? 1 : -1); // for having the high rates first
      return ($av == $bv) ? 0 : (($av > $bv) ? 1 : -1); // low rates first

    }

    private function GetRequestXml($index)
    {
      $CLICSHOPPING_Weight = Registry::get('Weight');
//http://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf
//The weight of the parcel in kilograms.
//Details of the parcel dimensions in centimeters.

      $weight = $this->item_weight[$index];
      $price = round($this->item_price[$index], 2);
      $width = $this->item_width[$index];
      $height = $this->item_height[$index];
      $length = $this->item_length[$index];

// Take only the kg
// gr to kg
      if ($this->unit_weight == 1) {
        $weight = round($CLICSHOPPING_Weight->convert($weight, $this->unit_weight, 2), 3);
      }

//Ounce to kg
      if ($this->unit_weight == 3) {
        $weight = round($CLICSHOPPING_Weight->convert($weight, $this->unit_weight, 2), 3);
      }

//Ounce to kg
      if ($this->unit_weight == 4) {
        $weight = round($CLICSHOPPING_Weight->convert($weight, $this->unit_weight, 2), 3);
      }

      /*
            if($this->unit_weight == 'LBS') {
      //change to kilograms
              $weight = round($weight/2.2,3);
              $weight = round($CLICSHOPPING_Weight->convert($weight, $Qproduct->valueInt('products_weight_class_id'), SHIPPING_WEIGHT_UNIT), 3);
            }
      */
      if ($this->unit_length == 'IN') {
//change to centimeters
        $width = round($width * 2.54, 1);
        $height = round($height * 2.54, 1);
        $length = round($length * 2.54, 1);
      }

      $xmlRequest = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
    <mailing-scenario xmlns=\"http://www.canadapost.ca/ws/ship/rate-v4\">
     <customer-number>$this->access_customer_number</customer-number>
     <quote-type>$this->quotetype</quote-type>";
      if ($this->contract_id) {
        $xmlRequest .= "<contract-id>$this->contract_id</contract-id>";
      }

      $xmlRequest .= "<expected-mailing-date>$this->turnaround</expected-mailing-date>";

      if (($this->use_insurance) && ($price > 0)) {
        $xmlRequest .= "<options><option>
       <option-code>COV</option-code>
       <option-amount>$price</option-amount>
     </option>
    </options>";
      }

      $xmlRequest .= "  <parcel-characteristics>
    <weight>$weight</weight>";
      if (($this->dimensions_support > 0) && ($width > 0) && ($height > 0) && ($length > 0)) {
        $xmlRequest .= "<dimensions><length>$length</length>
       <width>$width</width>
       <height>$height</height>
      </dimensions>";
      }

      $xmlRequest .= "</parcel-characteristics>
     <origin-postal-code>$this->_canadaPostOriginPostalCode</origin-postal-code>
     <destination>";
      if ($this->_canadaPostDestCountryCode == 'CA') {
        $xmlRequest .= "<domestic>
      <postal-code>$this->_canadaPostDestPostalCode</postal-code>
    </domestic>";
      } else if ($this->_canadaPostDestCountryCode == 'US') {
        $xmlRequest .= "<united-states>
      <zip-code>$this->_canadaPostDestPostalCode</zip-code>
    </united-states>";
      } else {
        $xmlRequest .= "<international>
      <country-code>$this->_canadaPostDestCountryCode</country-code>
    </international>";
      }

      $xmlRequest .= "  </destination>
   </mailing-scenario>";

      return $xmlRequest;
    }
  }