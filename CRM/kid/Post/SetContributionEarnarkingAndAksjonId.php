<?php

class CRM_kid_Post_SetContributionEarmarkingAndAksjonId {

  public static function post($op, $objectName, $objectId, &$objectRef) {
    if ($objectName != 'Contribution') {
      return;
    }
    if ($op != 'create') {
      return;
    }

    $currentEarmarking = self::getCurrentEarmarking($objectId);
    $currentAksjon_id = self::getCurrentAksjonId($objectId);

    $aksjonId = CRM_kid_AksjonId::getAksjonId() ? CRM_kid_AksjonId::getAksjonId() : $currentAksjon_id;
    $earmarking = CRM_kid_Earmarking::getEarmarking() ? CRM_kid_Earmarking::getEarmarking() : $currentEarmarking;
    self::setEarmarkingAndAksjonId($objectId, $earmarking, $aksjonId);
  }

  protected static function getCurrentEarmarking($contribution_id) {
    $config = CRM_kid_Config_NetsTransactions::singleton();
    $params['id'] = $contribution_id;
    $params['return'] = 'custom_'.$config->getEarmarkingField('id');
    $earmarking = civicrm_api3('Contribution', 'getvalue', $params);
    return $earmarking;
  }

  protected static function getCurrentAksjonId($contribution_id) {
    $config = CRM_kid_Config_NetsTransactions::singleton();
    $params['id'] = $contribution_id;
    $params['return'] = 'custom_'.$config->getAksjonIdField('id');
    $aksjon_id = civicrm_api3('Contribution', 'getvalue', $params);
    return $aksjon_id;
  }

  protected static function setEarmarkingAndAksjonId($contribution_id, $earmarking, $aksjon_id) {
    $config = CRM_kid_Config_NetsTransactions::singleton();
    $params['entityID'] = $contribution_id;
    $params['custom_'.$config->getAksjonIdField()] = (string) $aksjon_id;
    $params['custom_'.$config->getEarmarkingField()] = (string) $earmarking;
    CRM_Core_BAO_CustomValueTable::setValues($params);
  }

}