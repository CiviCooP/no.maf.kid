<?php

/**
 * Class to set/retrieve the earmarking
 * This class returns the earmarking from the $_POST or you
 * could use it to set the earmarking during the request
 * 
 */
class CRM_kid_Earmarking {
  
  protected static $singleton;
  
  protected $earmarking = '';
  
  protected function __construct() {
    if (isset($_POST['earmarking']) && !empty($_POST['earmarking'])) {
      $this->earmarking = $_POST['earmarking'];
    }
  }
  
  /**
   * @return CRM_kid_Earmarking
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_kid_Earmarking();
    }
    return self::$singleton;
  }
  
  public static function getEarmarking() {
    $instance = CRM_kid_Earmarking::singleton();
    return $instance->earmarking;
  }
  
  public static function setEarmarking($earmarking) {
    $instance = CRM_kid_Earmarking::singleton();
    $instance->earmarking = $earmarking;
  }
  
}