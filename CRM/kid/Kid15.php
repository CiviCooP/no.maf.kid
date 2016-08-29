<?php

class CRM_kid_Kid15 extends CRM_kid_Kid {
  
  protected $contact_id;
  
  protected $entity_id;

  protected $base;
  
  public function __construct($contact_id, $entity_id) {
    $this->contact_id = $contact_id;
    $this->entity_id = $entity_id;
    $this->base = $this->contact_id;

    //Retrieve base number for contact
    $params = array(
      1 => array($this->contact_id, 'Integer')
    );
    $base = CRM_Core_DAO::singleValueQuery("SELECT kid_base FROM civicrm_value_kid_base WHERE entity_id = %1", $params);
    if ($base) {
      $this->base = $base;
    }
  }
  
  public function generate() {
    $kid_number = str_pad($this->base, 6, '0', STR_PAD_LEFT) . str_pad($this->entity_id, 8, '0', STR_PAD_LEFT);
    return $kid_number . $this->checksum($kid_number);
  }
  
}

