<?php

/**
 * Class to generate 9 digit kids
 */

class CRM_kid_Kid9 extends CRM_kid_Kid {

  protected static $kid9 = array();
  
  public function generate() {
    $sql = "SELECT MAX(kid_number) as kid_number FROM `civicrm_kid_number` WHERE length(kid_number) = 9";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $newKid = 1;
    if ($dao->fetch()) {
      $newKid = (int) substr($dao->kid_number, 0, -1); //remove checksum
      $newKid ++;
    }
    
    $kid_number = str_pad($newKid, 8, '0', STR_PAD_LEFT);
    return $kid_number . $this->checksum($kid_number);
  }
  
  public static function getTokenValue($contact_id, $earmarking='', $aksjon_id='') {
    $e = (!empty($earmarking) ? $earmarking : 0);
    $a = (!empty($aksjon_id) ? $aksjon_id : 0);
    if (empty(self::$kid9[$contact_id]) || empty(self::$kid9[$contact_id][$a]) || empty(self::$kid9[$contact_id][$a][$e])) {
      $kid = new CRM_kid_Kid9();
      $kid_number = $kid->generate();
      self::savenTokenValue($kid_number, $contact_id, $earmarking, $aksjon_id);
      self::$kid9[$contact_id][$a][$e] = $kid_number;
    }
    return self::$kid9[$contact_id][$a][$e];
  }
  
  protected static function savenTokenValue($kid_number, $contact_id, $earmarking='', $aksjon_id='') {
    CRM_Core_DAO::executeQuery("
			INSERT INTO civicrm_kid_number (entity, entity_id, contact_id, kid_number, create_date, created_by_token, earmarking, aksjon_id)
			VALUES ('token', '0', %1, %2, CURDATE(), '1', %3, %4)
            ", array(
				1 => array($contact_id, 'Positive'),
				2 => array($kid_number, 'String'),
        3 => array($earmarking, 'String'),
        4 => array($aksjon_id, 'String'),
			)
		);
  }
  
}

