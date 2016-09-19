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

  /**
   * Method to get the KID base for the contact
   *
   * @param int $sourceContactId
   * @return int
   * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
   */
  public static function getKidBaseContactId($sourceContactId) {
    $query = 'SELECT kid_base FROM civicrm_value_kid_base WHERE entity_id = %1';
    $params = array(1 =>array($sourceContactId, 'Integer'));
    return CRM_Core_DAO::singleValueQuery($query, $params);
  }
  
}

