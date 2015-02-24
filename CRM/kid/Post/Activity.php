<?php

class CRM_kid_Post_Activity {

  public static function post($op, $objectName, $objectId, &$objectRef) {
    if (defined('__BYPASS_HOOK_CIVICRM_POST')) {
      return;
    }
    if ($objectName != 'Activity') {
      return;
    }
    if ($op != 'create') {
      return;
    }

    // If an activity of type 'Direct Mailing'
    if ($objectRef->activity_type_id == kid_get_mailing_activity_type_id()) {
      // lookup target_contact_id for this activity 
      // (does not support multiple targets, will default to the first returned)
      $dao = CRM_Core_DAO::executeQuery("
                        SELECT id, target_contact_id FROM civicrm_activity_target WHERE activity_id = %1
                    ", array(
            1 => array($objectId, 'Positive')
              )
      );
      while ($dao->fetch()) {
        $contact_id = $dao->target_contact_id;
        $kid = new CRM_kid_Kid9();
        $kid_number = $kid->generate();
        
        $aksjon_id = '';
        $earmarking = '';
        
        // lookup aksjon_id from the activity, if it exists
        if (empty($aksjon_id) && !empty($objectId)) {
          $aksjon_id = CRM_Core_DAO::singleValueQuery("SELECT aksjon_id_38 FROM civicrm_value_maf_norway_aksjon_import_1578 WHERE entity_id = %1", array(1 => array($objectId, 'Positive')));
        }
        if (empty($earmarking) && !empty($objectId)) {
          $earmarking = CRM_Core_DAO::singleValueQuery("SELECT _remerking_98 FROM civicrm_value_kid_earmark_1591 WHERE entity_id = %1", array(1 => array($objectId, 'Positive')));
        }
        
        CRM_kid_Kid::insert($kid_number, $contact_id, $objectId, 'ActivityTarget', $aksjon_id, $earmarking);
      }
    }
  }

}
