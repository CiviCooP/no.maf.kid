<?php

class CRM_kid_Config_NetsTransactions {

  private static $singleton;

  private $custom_group;

  private $aksjon_id_field;

  private $earmarking_field;

  private function __construct() {
    $this->custom_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'nets_transactions'));
    $this->aksjon_id_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'Aksjon_ID', 'custom_group_id' => $this->custom_group['id']));
    $this->earmarking_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'earmarking', 'custom_group_id' => $this->custom_group['id']));
  }

  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_kid_Config_NetsTransactions();
    }
    return self::$singleton;
  }

  public function getAksjonIdField($key='id') {
    return $this->aksjon_id_field[$key];
  }

  public function getEarmarkingField($key='id') {
    return $this->earmarking_field[$key];
  }

  public function getCustomGroup($key='id') {
    return $this->custom_group[$key];
  }

}