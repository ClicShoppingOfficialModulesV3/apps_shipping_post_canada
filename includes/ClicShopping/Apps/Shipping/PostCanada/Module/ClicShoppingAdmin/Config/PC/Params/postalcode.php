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

  class postalcode extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {

    public $default = '';
    public $app_configured = true;
    public $sort_order = 110;

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_postalcode_title');
      $this->description = $this->app->getDef('cfg_postcanada_postalcode_description');
    }
  }
