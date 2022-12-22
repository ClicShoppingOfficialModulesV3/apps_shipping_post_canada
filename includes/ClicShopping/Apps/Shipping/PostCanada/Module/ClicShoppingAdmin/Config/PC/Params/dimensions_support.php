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


  namespace ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\PC\Params;

  use ClicShopping\OM\HTML;

  class dimensions_support extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {
    public $default = 'No';
    public $sort_order = 90;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_dimension_support_title');
      $this->description = $this->app->getDef('cfg_postcanada_dimension_support_description');
    }

    public function getInputField()
    {
      $value = $this->getInputValue();

      $input = HTML::radioField($this->key, 'No', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_postcanada_dimension_support_no') . ' ';
      $input .= HTML::radioField($this->key, 'Ready-to-ship only', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_postcanada_dimension_support_ready_to_ship') . ' ';
      $input .= HTML::radioField($this->key, 'With product dimensions', $value, 'id="' . $this->key . '2" autocomplete="off"') . $this->app->getDef('cfg_postcanada_dimension_support_product_dimension');

      return $input;
    }
  }