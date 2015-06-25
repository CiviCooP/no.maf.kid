<?php

/**
 * This class is used first to store all kid number/contact which have been generated during 
 * token value
 * We will then afterwards update those kid numbers to link back to the activity
 * 
 */
class CRM_kid_Post_TokenActivity {
  
  protected static $_singleton;
  
  protected $kid_numbers;
  
  protected function __construct() {
    $this->kid_numbers = array();
  }
  
  /**
   * @return CRM_kid_Post_TokenActivity
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_kid_Post_TokenActivity();
    }
    return self::$_singleton;
  }
  
  public function addKidNumber($kid_number, $contact_id) {
    $this->kid_numbers[$kid_number] = $contact_id;
  }
  
  public function post($op, $objectName, $objectId, &$objectRef) {
    if (defined('__BYPASS_HOOK_CIVICRM_POST')) {
      return;
    }
    if ($objectName != 'Activity') {
      return;
    }
    if ($op != 'create') {
      return;
    }
    
    if (count($this->kid_numbers)==0) {
      return;
    }
    
    foreach($this->kid_numbers as $kid => $cid) {
      $sql = "UPDATE `civicrm_kid_number` SET `entity` = 'Activity', `entity_id` = %1 WHERE `contact_id` = %2 AND `kid_number` = %3";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($objectId, 'Integer'),
        2 => array($cid, 'Integer'),
        3 => array($kid, 'String'),
      ));
    }
  }
  
  
}

