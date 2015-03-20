<?php

class CRM_kid_Kid15 extends CRM_kid_Kid {
  
  protected $contact_id;
  
  protected $entity_id;
  
  public function __construct($contact_id, $entity_id) {
    $this->contact_id = $contact_id;
    $this->entity_id = $entity_id;
  }
  
  public function generate() {
    $kid_number = str_pad($this->contact_id, 6, '0', STR_PAD_LEFT) . str_pad($this->entity_id, 8, '0', STR_PAD_LEFT);
    return $kid_number . $this->checksum($kid_number);
  }
  
}

