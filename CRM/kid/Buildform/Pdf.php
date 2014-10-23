<?php

class CRM_kid_Buildform_Pdf {

  protected $form;
  
  public function __construct(&$form) {
    $this->form = $form;
  }
  
  public function parse() { 
    $earmakring_options = CRM_Core_BAO_OptionValue::getOptionValuesAssocArrayFromName('earmarking');
    
    $this->form->add('select', 'earmarking', ts('Earmarking'), $earmakring_options);
    $this->form->add('text', 'aksjon_id', ts('Aksjon ID'));
    
    $snippet['template'] = 'CRM/kid/Buildform/Pdf.tpl';
    CRM_Core_Region::instance('page-body')->add($snippet);
  }

}
