<?php

class CRM_kid_Post_Contribution {

  public static function post($op, $objectName, $objectId, &$objectRef) {
    if (defined('__BYPASS_HOOK_CIVICRM_POST')) {
      return;
    }
    if ($objectName != 'Contribution') {
      return;
    }
    if ($op != 'create') {
      return;
    }

    // When various entities are created, generate and store KID number
    // if contribution is part of a contribution recur, generate 15 digit kid number
    if (isset($objectRef->contribution_recur_id) and !empty($objectRef->contribution_recur_id)) {
      $contact_id = $objectRef->contact_id;
      $kid = new CRM_kid_Kid15($contact_id, $objectId);
      $kid_number = $kid->generate();
      CRM_kid_Kid::insert($kid_number, $contact_id, $objectId, $objectName);
    }
    /*
     * BOS1403431 Contributions that are part of a Memberships with AvtaleGiro need 15 digit KID too\
     * Erik Hommel (CiviCooP) <erik.hommel@civicoop.org> 12 Mar 2014
     */
    $finTypeParams = array(
      'name' => "Medlem",
      'return' => "id"
    );
    try {
      $membershipFinTypeId = civicrm_api3('FinancialType', 'Getvalue', $finTypeParams);
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::fatal(ts("Could not find a valid financial type for Medlem, 
                        error from API entity FinancialType, action Getsingle is : " . $e->getMessage()));
    }
    $optionGroupParams = array(
      'name' => "payment_instrument",
      'return' => "id"
    );
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::fatal(ts("Could not find a valid option group for payment_instrument, 
                        error from API entity OptionGroup, action Getvalue is : " . $e->getMessage()));
    }
    $avtaleGiroParams = array(
      'option_group_id' => $optionGroupId,
      'name' => "AvtaleGiro",
      'return' => "value"
    );
    try {
      $avtaleGiroId = civicrm_api3('OptionValue', 'Getvalue', $avtaleGiroParams);
    } catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::fatal(ts("Could not find a valid option value for payment instrument AvtaleGiro, 
                        error from API entity OptionValue, action Getvalue is : " . $e->getMessage()));
    }
    if ($objectRef->financial_type_id == $membershipFinTypeId && $objectRef->payment_instrument_id == $avtaleGiroId) {
      $contact_id = $objectRef->contact_id;
      $kid = new CRM_kid_Kid15($contact_id, $objectId);
      $kid_number = $kid->generate();
      CRM_kid_Kid::insert($kid_number, $contact_id, $objectId, $objectName);
    }
    // end BOS1403431
  }

}
