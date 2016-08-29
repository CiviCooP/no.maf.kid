<?php

require_once('kid.civix.php');

/* 
 * KID Number Generator Extension for CiviCRM - Circle Interactive 2013
 * Author: andyw@circle
 *
 * Distributed under the GNU Affero General Public License, version 3
 * http://www.gnu.org/licenses/agpl-3.0.html 
 */

define('NO_MAF_MAILING_ACTIVITY_NAME', 'Direct Mail (with KID)');

function kid_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Task_PDF') {
    $buildForm = new CRM_kid_Buildform_Pdf($form);
    $buildForm->parse();
  }
}
/*
 * Implementation of hook_civicrm_post
 */
function kid_civicrm_pre($op, $objectName, $objectId, &$params) {
  //set earmarking and aksjon id on contribution
  CRM_kid_Post_SetContributionEarmarkingAndAksjonId::pre($op, $objectName, $objectId, $params);
}

/*
 * Implementation of hook_civicrm_post
 */
function kid_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'Activity' && $op == 'create') {
    //save entities at created tokens
    $activityToken = CRM_kid_Post_TokenActivity::singleton();
    $activityToken->post($op, $objectName, $objectId, $objectRef);
  }

  if ($op == 'create' && in_array($objectName, array('Individual', 'Household', 'Organization'))) {
    // Set default KID base number to contact ID
    //Retrieve base number for contact
    $params = array(
      1 => array($objectId, 'Integer')
    );
    $dao = CRM_Core_DAO::executeQuery("SELECT id, kid_base FROM civicrm_value_kid_base WHERE entity_id = %1", $params);
    if ($dao->fetch()) {
      if (empty($dao->kid_base)) {
        CRM_Core_DAO::executeQuery("UPDATE civicrm_value_kid_base SET kid_base = %1 WHERE entity_id = %1", $params);
      }
    } else {
      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_value_kid_base (entity_id, kid_base) VALUES (%1, %1)", $params);
    }
  }
  
  // When various entities are created, generate and store KID number
  if ($op == 'create' and !defined('__BYPASS_HOOK_CIVICRM_POST')) {

    switch ($objectName) {

      case 'Activity':
        CRM_kid_Post_Activity::post($op, $objectName, $objectId, $objectRef);
        break;

      case 'Contribution':
        CRM_kid_Post_Contribution::post($op, $objectName, $objectId, $objectRef);
        break;

      // this is no longer required, as per irc conversation with Steinar, 15/10/2013
      /*
      case 'ContributionRecur':
        // And for recurring contributions, we have a 15-digit KID. Woo hoo!
        $contact_id = $objectRef->contact_id;
        $kid = new CRM_kid_Kid15($contact_id, $objectId);
        $kid_number = $kid->generate();
        CRM_kid_Kid::insert($kid_number, $contact_id, $objectId, $objectName);
        break;
       */
    }
  }
}

function kid_civicrm_tabs(&$tabs, $contactID) {
  $url = CRM_Utils_System::url('civicrm/contact/kidtab', "cid=$contactID&snippet=1");

  //Count rules
  $kids = CRM_kid_BAO_Kid::countByContactId($contactID);
  $tabs[] = array(
    'id' => 'kidtab',
    'url' => $url,
    'count' => $kids,
    'title' => ts('KID'),
    'weight' => -100
   );
}

/*
 * Lookup activity_type_id for Direct Mailing
 */
function kid_get_mailing_activity_type_id() {
    return CRM_Core_BAO_Setting::getItem('no.maf.module.kid', 'mailing_activity_type_id'); 
}

/*
 * Get creating entity and id for the supplied kid number
 */
function kid_number_get_info($kid_number) {

    $dao = CRM_Core_DAO::executeQuery("
        SELECT * FROM civicrm_kid_number WHERE kid_number = %1
    ", array(
          1 => array($kid_number, 'Integer')
       )
    );
    if ($dao->fetch())
        return array(
            'entity'     => $dao->entity,
            'entity_id'  => $dao->entity_id,
            'contact_id' => $dao->contact_id,
            'aksjon_id'  => $dao->aksjon_id,
            'earmarking' => $dao->earmarking,
        );
    return false;

}

/**
 * Lookup kid number from entity and entity_id - used by dependent importers / exporters
 */
function kid_number_lookup($entity, $entity_id) {
    
    return CRM_Core_DAO::singleValueQuery("
        SELECT kid_number FROM civicrm_kid_number 
         WHERE entity = %1 AND entity_id = %2
    ", array(
          1 => array(ucfirst($entity), 'String'),
          2 => array($entity_id, 'Positive')
       )
    );

}

/**
 * Validate kid number - used by dependent importers / exporters
 */
function kid_number_validate($number) {
        
    $sum = 0; 
    $alt = false; 

    for ($i = strlen($number) - 1; $i >= 0; $i--) { 
        $n = substr($number, $i, 1); 
            if($alt) { 
                //square n 
                $n *= 2; 
                if($n > 9) { 
                    //calculate remainder 
                    $n = ($n % 10) +1; 
                } 
            } 
        $sum += $n; 
        $alt = !$alt; 
    } 

    //if $sum divides by 10 with no remainder then it's valid    
    return ($sum % 10 == 0); 
}

/**
 * Use the alterAPIPermissions hook to set the aksjon ID and Earmarking 
 * for 9KID generation in a token.
 * 
 * @param type $entity
 * @param type $action
 * @param type $params
 * @param type $permissions
 */
function kid_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  if (isset($params['earmarking']) && !empty($params['earmarking'])) {
    CRM_kid_Earmarking::setEarmarking($params['earmarking']);
  }
  if (isset($params['aksjon_id']) && !empty($params['aksjon_id'])) {
    CRM_kid_AksjonId::setAksjonId($params['aksjon_id']);
  }
}

function kid_civicrm_tokens(&$tokens) {
  $tokens['kid'] = array(
    'kid.9KID' => '9-digit KID Number',
  );
}

function kid_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  if (!empty($tokens['kid'])) {
    if (in_array('9KID', $tokens['kid'])) {
      _kid_9kid_token($values, $cids, $job, $tokens,$context);
    }
  }
}

/**
 * Function for CiviRules, check if CiviRules is installed
 *
 * @return bool
 */
function _kid_is_civirules_and_pdfapi_installed() {
  $rulesInstalled = FALSE;
  $pdfApiInstalled = FALSE;
  try {
    $extensions = civicrm_api3('Extension', 'get');
    foreach($extensions['values'] as $ext) {
      if ($ext['key'] == 'org.civicoop.civirules' &&$ext['status'] == 'installed') {
        $rulesInstalled = TRUE;
      }
      if ($ext['key'] == 'org.civicoop.pdfapi' &&$ext['status'] == 'installed') {
        $pdfApiInstalled = TRUE;
      }
    }
  } catch (Exception $e) {
    return false;
  }

  if ($pdfApiInstalled  && $rulesInstalled) {
    return true;
  }

  return false;
}

function _kid_9kid_token(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  $contacts = $cids;
  $use_array = true;
  if (!is_array($contacts) && !empty($cids)) {
    $contacts = array($cids);
    $use_array = false;
  }
  if (count($contacts) == 0) {
    return;
  }
  
  $earmarking = CRM_kid_Earmarking::getEarmarking();
  $aksjon_id = CRM_kid_AksjonId::getAksjonId();
  /*if (isset($_POST['earmarking']) && !empty($_POST['earmarking'])) {
    $earmarking = $_POST['earmarking'];
  }
  if (isset($_POST['aksjon_id']) && !empty($_POST['aksjon_id'])) {
    $aksjon_id = $_POST['aksjon_id'];
  }*/
 
  $activityToken = CRM_kid_Post_TokenActivity::singleton();
  foreach($contacts as $cid) {
    $kid_number = CRM_kid_Kid9::getTokenValue($cid, $earmarking, $aksjon_id);
    $activityToken->addKidNumber($kid_number, $cid);
    if (!$use_array) {
      $values['kid.9KID'] = $kid_number;
    } else {
      $values[$cid]['kid.9KID'] = $kid_number;
    }
  }  
}
/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function kid_civicrm_config(&$config) {
  _kid_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function kid_civicrm_xmlMenu(&$files) {
  _kid_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function kid_civicrm_install() {
  return _kid_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function kid_civicrm_uninstall() {
  return _kid_civix_civicrm_uninstall();
  
  // Best not to remove the civicrm_kid_number table if we're uninstalled.
  // We should retain this data regardless.
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function kid_civicrm_enable() {
  return _kid_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function kid_civicrm_disable() {
  return _kid_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function kid_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _kid_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function kid_civicrm_managed(&$entities) {
  return _kid_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function kid_civicrm_caseTypes(&$caseTypes) {
  _kid_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function kid_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _kid_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
