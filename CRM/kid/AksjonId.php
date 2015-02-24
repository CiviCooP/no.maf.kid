<?php

/**
 * Class to set/retrieve the aksjon ID
 * This class returns the Aksjon ID from the $_POST or you
 * could use it to set the Aksjon ID during the request
 * 
 */
class CRM_kid_AksjonId {
  
  protected static $singleton;
  
  protected $aksjonId = '';
  
  protected function __construct() {
    if (isset($_POST['aksjon_id']) && !empty($_POST['aksjon_id'])) {
      $this->aksjonId = $_POST['aksjon_id'];
    }
  }
  
  /**
   * @return CRM_kid_AksjonId
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_kid_AksjonId();
    }
    return self::$singleton;
  }
  
  public static function getAksjonId() {
    $instance = CRM_kid_AksjonId::singleton();
    return $instance->aksjonId;
  }
  
  public static function setAksjonId($aksjonId) {
    $instance = CRM_kid_AksjonId::singleton();
    $instance->aksjonId = $aksjonId;
  }
  
}