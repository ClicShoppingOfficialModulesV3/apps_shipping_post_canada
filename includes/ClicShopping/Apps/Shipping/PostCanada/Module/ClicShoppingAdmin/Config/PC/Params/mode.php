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

  class mode extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {
    public $default = 'Test';
    public $sort_order = 70;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_mode_title');
      $this->description = $this->app->getDef('cfg_postcanada_mode_description');
    }

    public function getInputField()
    {
      $value = $this->getInputValue();

      $input = HTML::radioField($this->key, 'Test', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_postcanada_mode_test') . ' ';
      $input .= HTML::radioField($this->key, 'Prodction', $value, 'id="' . $this->key . '2" autocomplete="off"') . $this->app->getDef('cfg_postcanada_mode_production');

      return $input;
    }
  }