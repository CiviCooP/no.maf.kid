<?php

/**
 * Collection of upgrade steps
 */
class CRM_kid_Upgrader extends CRM_kid_Upgrader_Base {

  /**
   * Example: Run a simple query when a module is enabled
   *
   * 
   */
  public function enable() {
    CRM_Core_DAO::executeQuery("
        CREATE TABLE IF NOT EXISTS `civicrm_kid_number` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `entity` varchar(24) NOT NULL,
          `entity_id` int(10) unsigned NOT NULL,
          `contact_id` int(10) unsigned NOT NULL,
          `kid_number` varchar(32) NOT NULL,
          `create_date` DATETIME NOT NULL,
          `created_by_token` int(1) NOT NULL default '0'
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
  }

  /**
   * Example: Run a simple query when a module is disabled
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   */
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


  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
