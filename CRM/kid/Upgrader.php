<?php

/**
 * Collection of upgrade steps
 */
class CRM_kid_Upgrader extends CRM_kid_Upgrader_Base {

  public function enable() {
    CRM_Core_DAO::executeQuery("
        CREATE TABLE IF NOT EXISTS `civicrm_kid_number` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `entity` varchar(24) NOT NULL,
          `entity_id` int(10) unsigned NOT NULL,
          `contact_id` int(10) unsigned NOT NULL,
          `kid_number` varchar(32) NOT NULL,
          `create_date` DATETIME NOT NULL,
          `created_by_token` int(1) NOT NULL default '0',
          `earmarking` varchar(128) NOT NULL default '',
          `aksjon_id` varchar(128) NOT NULL default '',
          PRIMARY KEY (`id`),
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

    $this->executeCustomDataFile('xml/kid_base.xml');
    CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE temp_kid_base_contact AS (SELECT id as entity_id, id as kid_base FROM civicrm_contact)");
    CRM_Core_DAO::executeQuery("INSERT INTO civicrm_value_kid_base (entity_id, kid_base) (SELECT entity_id, kid_base FROM temp_kid_base_contact)");
    CRM_Core_DAO::executeQuery("DROP TABLE temp_kid_base_contact");
  }

  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001');
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_kid_number ADD 
          (`create_date` DATETIME NOT NULL,
          `created_by_token` int(1) NOT NULL default '0')");
    
    return TRUE;
  }
  
  public function upgrade_1002() {
    $this->ctx->log->info('Applying update 1002');
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_kid_number 
          DROP PRIMARY KEY, 
          ADD (`id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT)");
    //CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_kid_number DROP PRIMARY KEY");
    //CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_kid_number MODIFY `id` NOT NULL PRIMARY KEY");
    return TRUE;
  }
  
  public function upgrade_1003() {
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_kid_number ADD (
        `earmarking` varchar(128) NOT NULL default '',
        `aksjon_id` varchar(128) NOT NULL default '')");
    return true;
  }

  public function upgrade_1004() {
    $this->executeCustomDataFile('xml/kid_base.xml');
    CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE temp_kid_base_contact AS (SELECT id as entity_id, id as kid_base FROM civicrm_contact)");
    CRM_Core_DAO::executeQuery("INSERT INTO civicrm_value_kid_base (entity_id, kid_base) (SELECT entity_id, kid_base FROM temp_kid_base_contact)");
    CRM_Core_DAO::executeQuery("DROP TABLE temp_kid_base_contact");
    return true;
  }

}
