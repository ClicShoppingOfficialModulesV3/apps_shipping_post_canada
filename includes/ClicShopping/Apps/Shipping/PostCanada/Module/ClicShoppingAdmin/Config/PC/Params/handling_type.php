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

  class handling_type extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {
    public $default = 'Flat Fee';
    public $sort_order = 120;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_handling_type_title');
      $this->description = $this->app->getDef('cfg_postcanada_handling_type_description');
    }

    public function getInputField()
    {
      $value = $this->getInputValue();

      $input = HTML::radioField($this->key, 'Flat Fee', $value, 'id="' . $this->key . '1" autocompplete="off"') . $this->app->getDef('cfg_postcanada_handling_type_flat_fee') . ' ';
      $input .= HTML::radioField($this->key, 'Percentage', $value, 'id="' . $this->key . '2" autocomplete="off"') . $this->app->getDef('cfg_postcanada_handling_type_percentage');

      return $input;
    }
  }