<?php

abstract class CRM_kid_Kid {

  abstract public function generate();

  protected function checksum($number) {
    $chars = array_reverse(str_split($number, 1));
    $odd = array_intersect_key($chars, array_fill_keys(range(1, count($chars), 2), null));
    $even = array_intersect_key($chars, array_fill_keys(range(0, count($chars), 2), null));
    $even = array_map(function($n) {
      return ($n >= 5) ? 2 * $n - 9 : 2 * $n;
    }, $even);
    $total = array_sum($odd) + array_sum($even);

    $check_digit = ((floor($total / 10) + 1) * 10 - $total) % 10;

    // for safety, validate the resulting number - trigger error if not valid
    if (!kid_number_validate($number . $check_digit))
      CRM_Core_Error::fatal(ts(
              'Validation test failed while generating checksum digit for kid number %1', array(
        1 => $number . $check_digit
              )
      ));

    return $check_digit;
  }

  public static function insert($kid_number, $contact_id, $objectId, $objectName, $aksjon_id='', $earmarking='') {
    if (isset($kid_number) and isset($contact_id)) {
      CRM_Core_DAO::executeQuery("
			INSERT INTO civicrm_kid_number (entity, entity_id, contact_id, kid_number, create_date, created_by_token, earmarking, aksjon_id)
			VALUES (%1, %2, %3, %4, CURDATE(), '0', %5, %6)
            ", array(
        1 => array($objectName, 'String'),
        2 => array($objectId, 'Positive'),
        3 => array($contact_id, 'Positive'),
        4 => array($kid_number, 'String'),
        5 => array($earmarking, 'String'),
        6 => array($aksjon_id, 'String'),
          )
      );
    }
  }

}
