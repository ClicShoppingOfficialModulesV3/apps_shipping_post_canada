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

  class unit_lenght extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {
    public $default = 'CM';
    public $sort_order = 80;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_unit_lenght_title');
      $this->description = $this->app->getDef('cfg_postcanada_unit_lenght_description');
    }

    public function getInputField()
    {
      $value = $this->getInputValue();

      $input = HTML::radioField($this->key, 'CM', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_postcanada_unit_lenght_cm') . ' ';
      $input .= HTML::radioField($this->key, 'IN', $value, 'id="' . $this->key . '2" autocomplete="off"') . $this->app->getDef('cfg_postcanada_unit_lenght_in');

      return $input;
    }
  }