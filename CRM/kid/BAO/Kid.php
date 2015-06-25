<?php

class CRM_kid_BAO_Kid extends CRM_kid_DAO_Kid {
  
  public static function findByContactId($contactId) {
    $sql = "SELECT * FROM `civicrm_kid_number` WHERE `contact_id` = %1 ORDER BY `create_date` DESC";
    $params[1] = array($contactId, 'Integer');
    return CRM_Core_DAO::executeQuery($sql, $params, true, 'CRM_kid_BAO_Kid');
  }
  
  public static function countByContactId($contactId) {
    $sql = "SELECT count(*) as `total` FROM `civicrm_kid_number` WHERE `contact_id` = %1 ORDER BY `create_date` DESC";
    $params[1] = array($contactId, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      return $dao->total;
    }
    return 0;
  }
  
}

