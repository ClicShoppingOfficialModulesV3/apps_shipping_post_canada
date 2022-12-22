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

  namespace ClicShopping\Apps\Shipping\PostCanada\Module\Shipping;

  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;
  use ClicShopping\Sites\Common\B2BCommon;
  use ClicShopping\OM\ObjectInfo;

  use ClicShopping\Apps\Shipping\PostCanada\PostCanada as PostCanadaApp;
  use ClicShopping\Apps\Shipping\PostCanada\API\API;

  class PC implements \ClicShopping\OM\Modules\ShippingInterface
  {

    public $code;
    public $title;
    public $description;
    public $enabled;
    public $icon;
    public mixed $app;
    public $quotes;
    public $boxcount;

    public $tax_class;
    public $handling_fee;
    public $handling_type;
    public $items_qty;


    public function __construct()
    {
      $CLICSHOPPING_Customer = Registry::get('Customer');

      if (!Registry::exists('PostCanada')) {
        Registry::set('PostCanada', new PostCanadaApp());
      }

      $this->app = Registry::get('PostCanada');
      $this->app->loadDefinitions('Module/Shop/PC/PC');

      $this->signature = 'PostCanada|' . $this->app->getVersion() . '|1.0';
      $this->api_version = $this->app->getApiVersion();

      $this->code = 'PC';
      $this->title = $this->app->getDef('module_postcanada_title');
      $this->public_title = $this->app->getDef('module_postcanada_public_title');
      $this->sort_order = defined('CLICSHOPPING_APP_POSTCANANDA_PC_SORT_ORDER') ? CLICSHOPPING_APP_POSTCANANDA_PC_SORT_ORDER : 0;

      if (defined('CLICSHOPPING_APP_POSTCANADA_PC_STATUS')) {
// Activation module du paiement selon les groupes B2B
        if ($CLICSHOPPING_Customer->getCustomersGroupID() != 0) {
          if (B2BCommon::getShippingUnallowed($this->code)) {
            if (defined('CLICSHOPPING_APP_POSTCANANDA_PC_STATUS') && CLICSHOPPING_APP_POSTCANANDA_PC_STATUS == 'True') {
              $this->enabled = true;
            } else {
              $this->enabled = false;
            }
          }
        } else {
          if (CLICSHOPPING_APP_POSTCANADA_PC_NO_AUTHORIZE == 'True' && $CLICSHOPPING_Customer->getCustomersGroupID() == 0) {
            if ($CLICSHOPPING_Customer->getCustomersGroupID() == 0) {
              if (CLICSHOPPING_APP_POSTCANADA_PC_NO_AUTHORIZE == 'True') {
                $this->enabled = true;
              } else {
                $this->enabled = false;
              }
            }
          }
        }

        if ($CLICSHOPPING_Customer->getCustomersGroupID() != 0) {
          if (B2BCommon::getTaxUnallowed($this->code) || !$CLICSHOPPING_Customer->isLoggedOn()) {
            $this->tax_class = defined('CLICSHOPPING_APP_POSTCANADA_PC_TAX_CLASS') ? CLICSHOPPING_APP_POSTCANADA_PC_TAX_CLASS : 0;

          }
        } else {
          if (B2BCommon::getTaxUnallowed($this->code)) {
            $this->tax_class = CLICSHOPPING_APP_POSTCANADA_PC_TAX_CLASS;
          }
        }


        $this->handling_fee = CLICSHOPPING_APP_POSTCANADA_PC_HANDLING;
        $this->handling_type = CLICSHOPPING_APP_POSTCANADA_PC_HANDLING_TYPE;
        $this->items_qty = 0;

        if (CLICSHOPPING_APP_POSTCANADA_PC_DIMENSIONS_SUPPORT == 'Ready-to-ship only') {
          $this->dimensions_support = 1;
        } elseif (CLICSHOPPING_APP_POSTCANADA_PC_DIMENSIONS_SUPPORT == 'With product dimensions') {
          $this->c = 2;
        } else {
          $this->dimensions_support = 0;
        }
      }
    }

    public function quote($method = '')
    {

      if (Registry::exists('Order')) {
        $CLICSHOPPING_Order = Registry::get('Order');
      } else {
        $CLICSHOPPING_Order = new ObjectInfo(array());
      }

      $CLICSHOPPING_Tax = Registry::get('Tax');
      $CLICSHOPPING_Template = Registry::get('Template');
      $CLICSHOPPING_ShoppingCart = Registry::get('ShoppingCart');
      $CLICSHOPPING_Shipping = Registry::get('Shipping');
      $CLICSHOPPING_ProductsLength = Registry::get('ProductsLength');

      $shipping_num_boxes = 1;
      $shipping_weight = $CLICSHOPPING_Shipping->getShippingWeight();

      $CLICSHOPPING_API = new API();

      $this->pkgvalue = ceil($CLICSHOPPING_Order->info['subtotal'] ?? null); // is divided by number of boxes later

      if (CLICSHOPPING_APP_POSTCANADA_PC_LOGO) {
        $this->icon = $CLICSHOPPING_Template->getDirectoryTemplateImages() . 'logos/shipping/' . CLICSHOPPING_APP_POSTCANADA_PC_LOGO;
      } else {
        $this->icon = '';
      }

      if (!empty($CLICSHOPPING_Order->delivery['city'])) {
        $state = $CLICSHOPPING_Order->delivery['state'] ?? null;

        $Qcheck = $this->app->db->get('zones', 'zone_code', ['zone_name' => $CLICSHOPPING_Order->delivery['state'] ?? null,
            'zone_country_id' => $CLICSHOPPING_Order->delivery['country']['id'] ?? null
          ]
        );

        if ($Qcheck->fetch()) {
          $state = $Qcheck->value('zone_code');
        }

        $CLICSHOPPING_API->canadaPostOrigin(CLICSHOPPING_APP_POSTCANADA_PC_CITY, CLICSHOPPING_APP_POSTCANADA_PC_STATEPROV, CLICSHOPPING_APP_POSTCANADA_PC_COUNTRY, CLICSHOPPING_APP_POSTCANADA_PC_POSTALCODE);
        $CLICSHOPPING_API->canadaPostDest($CLICSHOPPING_Order->delivery['city'] ?? null, $state, $CLICSHOPPING_Order->delivery['country']['iso_code_2'] ?? null, $CLICSHOPPING_Order->delivery['postcode'] ?? null);

// support

// the check on $packing being an object will puzzle people who do things wrong (no changes when
// you enable dimensional support without changing checkout_shipping.php) but better be safe
        if ($this->dimensions_support > 0) {
          $item = $CLICSHOPPING_ShoppingCart->get_products();

// all in one box
          if ($this->dimensions_support == 1) {
            $depth = 1;
            $width = 1;
            $height = 1;
            $weight = 1;
            $price = 1;

            for ($i = 0; $i < count($item); $i++) {
              $depth += (float)$item[$i]['products_dimension_depth'];
              $width += (float)$item[$i]['products_dimension_width'];
              $height += (float)$item[$i]['products_dimension_height'];
              $weight += $item[$i]['weight'];
              $price += $item[$i]['price'];
            }

            if ($depth < 0.1 || $width < 0.1 || $height < 0.1 || $weight < 0.1 || $price < 0.1) {
              $depth = 1;
              $width = 1;
              $height = 1;
              $weight = 1;
              $price = 1;
            }

            $CLICSHOPPING_API->addItem($depth, $width, $height, $weight, number_format(($price / $shipping_num_boxes), 2, '.', ''));
          } else {
//one product y box
           for ($i = 0; $i < count($item); $i++) {

// convert in cm
              $products_length_class_id = $item[$i]['products_length_class_id'];
              $products_dimension_depth = $CLICSHOPPING_ProductsLength->convert($item[$i]['products_dimension_depth'], $products_length_class_id, 2); //2 -> cm
              $products_dimension_width = $CLICSHOPPING_ProductsLength->convert($item[$i]['products_dimension_width'], $products_length_class_id, 2); //2 -> cm
              $products_dimension_height = $CLICSHOPPING_ProductsLength->convert($item[$i]['products_dimension_height'], $products_length_class_id, 2); //2 -> cm

              $CLICSHOPPING_API->addItem($products_dimension_depth, $products_dimension_width, $products_dimension_height, (float)$item[$i]['weight'], number_format(($item[$i]['price'] / $shipping_num_boxes), 2, '.', ''));
            }
          }
        } else {

        $this->items_qty = 0; //reset quantities
// $this->pkgvalue has been set as order subtotal around line 108, it will cause overcharging
// of insurance if not divided by the number of boxes

          for ($i = 0; $i < $shipping_num_boxes; $i++) {
            $CLICSHOPPING_API->addItem(0, 0, 0, $shipping_weight, number_format(($this->pkgvalue / $shipping_num_boxes), 2, '.', ''));
          }
        }

        $canadaPostQuote = $CLICSHOPPING_API->canadaPostGetQuote();

        if ((is_array($canadaPostQuote)) && (count($canadaPostQuote) > 0)) {

          $this->quotes = ['id' => $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code,
            'module' => $this->title
          ];

          $methods = [];

         foreach ($canadaPostQuote as $value) {
//list($type, $cost) = each($canadaPostQuote[$i]);
            $basetype = $value['service-code'];
            $cost = $value['base'];
            $type = $value['service-name'];
            $ddate = $value['expected-delivery-date'];

            if ($CLICSHOPPING_API->exclude_choices($basetype)) continue;

            if ($method == null || $method == $basetype) {
// changed to make handling percentage based
              if ($this->handling_type == 'Percentage') {
                if (!empty($type)) {
                  $methods[] = ['id' => $basetype,
                    'title' => $type . ' ' . $ddate,
                    'cost' => (float)((($this->handling_fee * $cost) / 100) + $cost)
                  ];
                }
              } else {
                if (!empty($type)) {
                  $methods[] = ['id' => $basetype,
                    'title' => $type . ' ' . $ddate,
                    'cost' => (float)($this->handling_fee + $cost)
                  ];
                }
              }
            }
          }

          if ($this->tax_class > 0) {
            $this->quotes['tax'] = $CLICSHOPPING_Tax->getTaxRate($this->tax_class, $CLICSHOPPING_Order->delivery['country']['id'] ?? null, $CLICSHOPPING_Order->delivery['zone_id'] ?? null);
          }

          $this->quotes['methods'] = $methods;
        } else {
          if ($canadaPostQuote != false) {
            $errmsg = $canadaPostQuote;
          } else {
            if (defined('MODULE_SHIPPING_CANADAPOST_TEXT_UNKNOWN_ERROR')) {
              $errmsg = CLICSHOPPING::getDef('modules_shipping_canapost_text_unknown_error');
            } else {
              $errmsg = '';
            }
          }

          $errmsg .= '<br>' . STORE_NAME . ' via <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '"><u>Email</u></a>.';

          $this->quotes = ['module' => $this->title,
            'error' => $errmsg,
            'methods' => ''
          ];
        }

        $CLICSHOPPING_API->canadaPostOrigin(CLICSHOPPING_APP_POSTCANADA_PC_CITY, CLICSHOPPING_APP_POSTCANADA_PC_STATEPROV, CLICSHOPPING_APP_POSTCANADA_PC_COUNTRY, CLICSHOPPING_APP_POSTCANADA_PC_POSTALCODE);
        $CLICSHOPPING_API->canadaPostDest($CLICSHOPPING_Order->delivery['city'] ?? null, $state, $CLICSHOPPING_Order->delivery['country']['iso_code_2'] ?? null, $CLICSHOPPING_Order->delivery['postcode'] ?? null);
      }

      if (!is_null($this->icon)) $this->quotes['icon'] = '&nbsp;&nbsp;&nbsp;' . HTML::image($this->icon, $this->title);

      return $this->quotes;
    }

    public function check()
    {
      return defined('CLICSHOPPING_APP_POSTCANADA_PC_STATUS') && (trim(CLICSHOPPING_APP_POSTCANADA_PC_STATUS) != '');
    }

    public function install()
    {
      $this->app->redirect('Configure&Install&module=PostCanada');
    }

    public function remove()
    {
      $this->app->redirect('Configure&Uninstall&module=PostCanada');
    }

    public function keys()
    {
      return array('CLICSHOPPING_APP_POSTCANADA_PC_SORT_ORDER');
    }
  }