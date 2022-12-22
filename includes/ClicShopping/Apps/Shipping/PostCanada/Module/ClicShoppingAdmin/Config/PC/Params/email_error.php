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

  class email_error extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {
    public $default = 'No';
    public $sort_order = 190;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_email_error_title');
      $this->description = $this->app->getDef('cfg_postcanada_email_error_description');
    }

    public function getInputField()
    {
      $value = $this->getInputValue();

      $input = HTML::radioField($this->key, 'Yes', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_postcanada_email_error_yes') . ' ';
      $input .= HTML::radioField($this->key, 'No', $value, 'id="' . $this->key . '2" autocomplete="off"') . $this->app->getDef('cfg_postcanada_email_error_no');

      return $input;
    }
  }