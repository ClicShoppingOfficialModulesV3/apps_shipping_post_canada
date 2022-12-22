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

  namespace ClicShopping\Apps\Shipping\PostCanada\Sites\ClicShoppingAdmin\Pages\Home;

  use ClicShopping\OM\Registry;

  use ClicShopping\Apps\Shipping\PostCanada\PostCanada;

  class Home extends \ClicShopping\OM\PagesAbstract
  {
    public mixed $app;

    protected function init()
    {
      $CLICSHOPPING_PostCanada = new PostCanada();
      Registry::set('PostCanada', $CLICSHOPPING_PostCanada);

      $this->app = $CLICSHOPPING_PostCanada;

      $this->app->loadDefinitions('Sites/ClicShoppingAdmin/main');
    }
  }
