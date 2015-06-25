<?php

class CRM_kid_DAO_Kid extends CRM_Core_DAO {
  
    /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;

  /**
   * empty definition for virtual function
   */
  static function getTableName() {
    return 'civicrm_kid_number';
  }

  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields() {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'entity' => array(
          'name' => 'entity',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 24,
        ),
        'entity_id' => array(
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
        ),
        'contact_id' => array(
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
        ),
        'kid_number' => array(
          'name' => 'kid_number',
          'type' => CRM_Utils_Type::T_STRING,
          'maxlength' => 32,
        ),
      );
    }
    return self::$_fields;
  }

  /**
   * Returns an array containing, for each field, the arary key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys() {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'entity' => 'entity',
        'entity_id' => 'entity_id',
        'contact_id' => 'contact_id',
        'kid_number' => 'kid_number',
      );
    }
    return self::$_fieldKeys;
  }
  
}

