<?php

/**
 * Class to generate 9 digit kids
 */

class CRM_kid_Kid9 extends CRM_kid_Kid {
  
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
  
  public static function getTokenValue($contact_id) {
    $kid = new CRM_kid_Kid9();
    $kid_number = $kid->generate();
    self::savenTokenValue($kid_number, $contact_id);
    return $kid_number;
  }
  
  protected static function savenTokenValue($kid_number, $contact_id) {
    CRM_Core_DAO::executeQuery("
			INSERT INTO civicrm_kid_number (entity, entity_id, contact_id, kid_number, create_date, created_by_token)
			VALUES ('token', '0', %1, %2, CURDATE(), '1')
            ", array(
				1 => array($contact_id, 'Positive'),
				2 => array($kid_number, 'String')
			)
		);
  }
  
}

