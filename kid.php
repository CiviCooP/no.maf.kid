<?php

/* 
 * KID Number Generator Extension for CiviCRM - Circle Interactive 2013
 * Author: andyw@circle
 *
 * Distributed under the GNU Affero General Public License, version 3
 * http://www.gnu.org/licenses/agpl-3.0.html 
 */

define('NO_MAF_MAILING_ACTIVITY_NAME', 'Direct Mail (with KID)');

/*
 * Implementation of hook_civicrm_enable
 */
function kid_civicrm_enable() {
    
    CRM_Core_DAO::executeQuery("
        CREATE TABLE IF NOT EXISTS `civicrm_kid_number` (
          `entity` varchar(24) NOT NULL,
          `entity_id` int(10) unsigned NOT NULL,
          `contact_id` int(10) unsigned NOT NULL,
          `kid_number` varchar(32) NOT NULL,
          PRIMARY KEY (`entity`,`entity_id`, `contact_id`),
          UNIQUE KEY `index_kid_number` (`kid_number`),
          KEY `index_contact_id` (`contact_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");

    // Create 'Direct Mail' activity type
    $activity_type_group_id = CRM_Core_DAO::singleValueQuery("
        SELECT id FROM civicrm_option_group WHERE name = 'activity_type'
    ");

    // temp - increase 'entity' field on civicrm_kid_number to 24 chars if not already
    CRM_Core_DAO::executeQuery("
        ALTER TABLE `civicrm_kid_number` 
        CHANGE `entity` `entity` VARCHAR(24) 
        CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL 
    ");
        
    // Check if it aready exists ..
    if (!CRM_Core_DAO::singleValueQuery("
        SELECT 1 FROM civicrm_option_value WHERE option_group_id = %1 AND name = %2
    ", array(
          1 => array($activity_type_group_id, 'Positive'),
          2 => array(NO_MAF_MAILING_ACTIVITY_NAME, 'String')
       )
    )) {
        // If not, create it:

        // Find next available id for activity type ..
        $activity_type_id = CRM_Core_DAO::singleValueQuery("
            SELECT MAX(CAST(value AS UNSIGNED)) FROM civicrm_option_value WHERE option_group_id = %1
        ", array(
            1 => array($activity_type_group_id, 'Positive')
        )) + 1;

        CRM_Core_DAO::executeQuery("
            INSERT INTO civicrm_option_value (id, option_group_id, label, value, name, weight, description, filter, is_active, is_reserved)
            VALUES (NULL, %1, %2, %3, %2, %3, %4, 0, 1, 1)
        ", array(
            1 => array($activity_type_group_id, 'Positive'),
            2 => array(NO_MAF_MAILING_ACTIVITY_NAME, 'String'),
            3 => array($activity_type_id, 'Positive'),
            4 => array(ts('Activity type for Direct Mailings with KID number'), 'String')
        ));

        // Store the activity_type_id in civicrm_setting, for ease of lookup
        CRM_Core_BAO_Setting::setItem($activity_type_id, 'no.maf.module.kid', 'mailing_activity_type_id');

    }

}

/*
 * Implementation of hook_civicrm_post
 */
function kid_civicrm_post($op, $objectName, $objectId, &$objectRef) {
    
    // When various entities are created, generate and store KID number
    if ($op == 'create' and !defined('__BYPASS_HOOK_CIVICRM_POST')) {
        
        switch ($objectName) {
            
            case 'Activity':
                
                // If an activity of type 'Direct Mailing'
                if ($objectRef->activity_type_id == kid_get_mailing_activity_type_id()) {

                    // lookup contact_id for this activity
                    // (does not support multiple targets, will default to the first returned)
                    $dao = CRM_Core_DAO::executeQuery("
                        SELECT id, contact_id FROM civicrm_activity_contact WHERE activity_id = %1 AND record_type_id = %2
                    ", array(
                          1 => array($objectId, 'Positive'),
                          2 => array(3, 'Positive')
                       )
                    );
                    while ($dao->fetch()) {
                        $contact_id = $dao->contact_id;
                        $kid_number = kid_number_generate_9digit($dao->id);
						kid_insert($kid_number, $contact_id, $objectId, 'ActivityTarget');
                    }
                }
                break;

            case 'Contribution':

                // if contribution is part of a contribution recur, generate 15 digit kid number
                if (isset($objectRef->contribution_recur_id) and !empty($objectRef->contribution_recur_id)) {
                    $contact_id = $objectRef->contact_id;
                    $kid_number = kid_number_generate_15digit($contact_id, $objectId);
                    kid_insert($kid_number, $contact_id, $objectId, $objectName);
                }
                /*
                 * BOS1403431 Contributions that are part of a Memberships with AvtaleGiro need 15 digit KID too\
                 * Erik Hommel (CiviCooP) <erik.hommel@civicoop.org> 12 Mar 2014
                 */
                $finTypeParams = array(
                    'name'  =>  "Medlem",
                    'return'=>  "id"
                );
                try {
                    $membershipFinTypeId = civicrm_api3('FinancialType', 'Getvalue', $finTypeParams);
                } catch (CiviCRM_API3_Exception $e) {
                    CRM_Core_Error::fatal(ts("Could not find a valid financial type for Medlem, 
                        error from API entity FinancialType, action Getsingle is : ".$e->getMessage()));
                }
                $optionGroupParams = array(
                    'name'      =>  "payment_instrument",
                    'return'    =>  "id"
                );
                try {
                    $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
                } catch (CiviCRM_API3_Exception $e) {
                    CRM_Core_Error::fatal(ts("Could not find a valid option group for payment_instrument, 
                        error from API entity OptionGroup, action Getvalue is : ".$e->getMessage()));
                }
                $avtaleGiroParams = array(
                    'option_group_id'   =>  $optionGroupId,
                    'name'              =>  "AvtaleGiro",
                    'return'            =>  "value"
                );
                try {
                    $avtaleGiroId = civicrm_api3('OptionValue', 'Getvalue', $avtaleGiroParams);
                } catch (CiviCRM_API3_Exception $e) {
                    CRM_Core_Error::fatal(ts("Could not find a valid option value for payment instrument AvtaleGiro, 
                        error from API entity OptionValue, action Getvalue is : ".$e->getMessage()));
                }
                if ($objectRef->financial_type_id == $membershipFinTypeId && $objectRef->payment_instrument_id == $avtaleGiroId) {
                    $contact_id = $objectRef->contact_id;
                    $kid_number = kid_number_generate_15digit($contact_id, $objectId);
                    kid_insert($kid_number, $contact_id, $objectId, $objectName);
                }
                // end BOS1403431
                break;
            
            // this is no longer required, as per irc conversation with Steinar, 15/10/2013
            /*
            case 'ContributionRecur':
                
                // And for recurring contributions, we have a 15-digit KID. Woo hoo!
                $contact_id = $objectRef->contact_id;
                $kid_number = kid_number_generate_15digit($contact_id, $objectId);
				kid_insert($kid_number, $contact_id, $objectId, $objectName);
                break;
            */
        }
    }
}

function kid_insert($kid_number, $contact_id, $objectId, $objectName) {
	if (isset($kid_number) and isset($contact_id)) {
		CRM_Core_DAO::executeQuery("
			INSERT INTO civicrm_kid_number (entity, entity_id, contact_id, kid_number)
			VALUES (%1, %2, %3, %4)
            ", array(
				1 => array($objectName, 'String'),
				2 => array($objectId,   'Positive'),
				3 => array($contact_id, 'Positive'),
				4 => array($kid_number, 'String')
			)
		);
	}
}

/*
 * Implementation of hook_civicrm_uninstall
 */
function kid_civicrm_uninstall() {
    // Best not to remove the civicrm_kid_number table if we're uninstalled.
    // We should retain this data regardless.
}

/*
 * Lookup activity_type_id for Direct Mailing
 */
function kid_get_mailing_activity_type_id() {
    return CRM_Core_BAO_Setting::getItem('no.maf.module.kid', 'mailing_activity_type_id'); 
}

/*
 * Generate a 9 digit kid number
 */
function kid_number_generate_9digit($entity_id) {
    
    $kid_number = str_pad($entity_id, 8, '0', STR_PAD_LEFT);
    return $kid_number . kid_number_generate_checksum_digit($kid_number);

}

/*
 * Generate a 15 digit kid number
 */
function kid_number_generate_15digit($contact_id, $entity_id) {
    
    $kid_number = str_pad($contact_id, 6, '0', STR_PAD_LEFT) . str_pad($entity_id, 8, '0', STR_PAD_LEFT);
    return $kid_number . kid_number_generate_checksum_digit($kid_number);

}

/*
 * Generate checksum digit using the Luhn algorithm
 */
function kid_number_generate_checksum_digit($number) {
    
    $chars = array_reverse(str_split($number, 1));
    $odd   = array_intersect_key($chars, array_fill_keys(range(1, count($chars), 2), null));
    $even  = array_intersect_key($chars, array_fill_keys(range(0, count($chars), 2), null));
    $even  = array_map(function($n) { return ($n >= 5)?2 * $n - 9:2 * $n; }, $even);
    $total = array_sum($odd) + array_sum($even);
    
    $check_digit = ((floor($total / 10) + 1) * 10 - $total) % 10;

    // for safety, validate the resulting number - trigger error if not valid
    if (!kid_number_validate($number . $check_digit))
        CRM_Core_Error::fatal(ts(
            'Validation test failed while generating checksum digit for kid number %1',
            array(
                1 => $number . $check_digit
            )
        ));       

    return $check_digit;

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
            'contact_id' => $dao->contact_id
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
