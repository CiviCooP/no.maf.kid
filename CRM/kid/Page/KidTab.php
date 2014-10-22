<?php

require_once 'CRM/Core/Page.php';

class CRM_kid_Page_KidTab extends CRM_Core_Page_Basic {
  
  /**
   * The action links that we need to display for the browse screen
   *
   * @var array
   * @static
   */
  static $_links = NULL;
  
  protected $contactId;
  
  function getBAOName() {
    return 'CRM_kid_BAO_Kid';
  }
  
  /**
   * Get action Links
   *
   * @return array (reference) of action links
   */
  function &links() {
    if (!(self::$_links)) {
      self::$_links = array(
/*        CRM_Core_Action::UPDATE => array(
          'name' => ts('Edit'),
          'url' => 'civicrm/contact/kidtab',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit KID setting'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => 'civicrm/contact/kidtab',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete KID setting'),
        ),*/
      );
    }
    return self::$_links;
  }
  
  function run() {
    $this->contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);

    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('KID'));

    return parent::run();
  }
  
  /**
   * Browse all Providers.
   *
   * @return void
   * @access public
   * @static
   */
  function browse($action = NULL) {    
    $object = CRM_kid_BAO_Kid::findByContactId($this->contactId);
    
    $rows = array();
    while($object->fetch()) {
      $action = array_sum(array_keys($this->links()));
      $setting['kid_number'] = $object->kid_number;
      $setting['entity'] = $object->entity;
      $setting['entity_id'] = $object->entity_id;
      $createDate = new DateTime($object->create_date);
      $setting['create_date'] = $createDate->format('d-m-Y');
      $setting['created_by_token'] = $object->created_by_token ? ts('Yes') : ts('No');
      $setting['earmarking'] = $object->earmarking;
      $setting['aksjon_id'] = $object->aksjon_id;
      $rows[] = $setting;
    }
    $this->assign('rows', $rows);
  }
  
  /**
   * Get edit form name
   *
   * @return string name of this page.
   */
  function editName() {
    return 'Edit kid';
  }
  
  /**
   * Get name of edit form
   *
   * @return string Classname of edit form.
   */
  function editForm() {
    return 'CRM_kid_Form_Kid';
  }
  
  function userContext($mode = NULL) {
    return 'civicrm/contact/view?selectedChild=kidtab&action=browse&reset=1&cid='.$this->contactId;
  }
}
