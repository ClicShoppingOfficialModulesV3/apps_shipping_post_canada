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

  class type extends \ClicShopping\Apps\Shipping\PostCanada\Module\ClicShoppingAdmin\Config\ConfigParamAbstract
  {

    public $default = 'D.XP;D.XP.CERT;D.PC;D.LIB;U.EP;U.PW.ENV;U.PW.PAK;U.PW.PARCEL;U.SP.AIR;U.SP.SURF;U.XP;I.XP;I.IP.AIR;I.IP.SURF;I.PW.ENV;I.PW.PAK;I.PW.PARCEL;I.SP.AIR;I.SP.SURF';
    public $sort_order = 30;

    protected $type = [
      'D.RP' => 'Regular Parcel',
      'D.EP' => 'Expedited Parcel',
      'D.XP' => 'Xpresspost',
      'D.XP.CERT' => 'Xpresspost Certified',
      'D.PC' => 'Priority',
      'D.LIB' => 'Library Books',
      'U.EP' => 'Expedited Parcel USA',
      'U.PW.ENV' => 'Priority Worldwide Envelope USA',
      'U.PW.PAK' => 'Priority Worldwide pak USA',
      'U.PW.PARCEL' => 'Priority Worldwide Parcel USA',
      'U.SP.AIR' => 'Small Packet USA Air',
      'U.SP.SURF' => 'Small Packet USA Surface',
      'U.XP' => 'Xpresspost USA',
      'I.XP' => 'Xpresspost International',
      'I.IP.AIR' => 'International Parcel Air',
      'I.IP.SURF' => 'International Parcel Surface',
      'I.PW.ENV' => 'Priority Worldwide Envelope International',
      'I.PW.PAK' => 'Priority Worldwide pak International\'',
      'I.PW.PARCEL' => 'Priority Worldwide parcel International',
      'I.SP.AIR' => 'Small Packet International Air',
      'I.SP.SURF' => 'Small Packet International Surface',
    ];

    protected function init()
    {
      $this->title = $this->app->getDef('cfg_postcanada_type_title');
      $this->description = $this->app->getDef('cfg_postcanada_type_description');
    }

    public function getInputField()
    {
      $active = explode(';', $this->getInputValue());

      $input = '';

      foreach ($this->type as $key => $value) {
        $input .= '<div class="checkbox">' .
          '  <label>' . HTML::checkboxField($this->key . '_pc', $key, in_array($key, $active)) . $value . '</label>' .
          '</div>';
      }

      $input .= HTML::hiddenField($this->key);

      $result = <<<EOT
<div id="typeSelection">
  {$input}
</div>

<script>
$(function() {
  $('#typeSelection input').closest('form').submit(function() {
    $('#typeSelection input[name="{$this->key}"]').val($('input[name="{$this->key}_pc"]:checked').map(function() {
      return this.value;
    }).get().join(';'));
  });
});
</script>
EOT;

      return $result;
    }
  }

