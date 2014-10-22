<?php

class CRM_kid_Buildform_Pdf {

  protected $form;
  
  public function __construct(&$form) {
    $this->form = $form;
  }
  
  public function parse() {    
    $this->form->add('text', 'earmarking', ts('Earmarking'));
    $this->form->add('text', 'aksjon_id', ts('Aksjon ID'));
    
    $snippet['template'] = 'CRM/kid/Buildform/Pdf.tpl';
    CRM_Core_Region::instance('page-body')->add($snippet);
  }

}
