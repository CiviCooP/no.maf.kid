<?php

class CRM_kid_Post_SetContributionEarmarkingAndAksjonId {

  public static function pre($op, $objectName, $objectId, &$params) {
    if ($objectName != 'Contribution') {
      return;
    }
    if ($op != 'create') {
      return;
    }

    $config = CRM_kid_Config_NetsTransactions::singleton();
    $aksjonId = CRM_kid_AksjonId::getAksjonId() ? CRM_kid_AksjonId::getAksjonId() : false;
    $earmarking = CRM_kid_Earmarking::getEarmarking() ? CRM_kid_Earmarking::getEarmarking() : false;
    if (empty($params['custom_'.$config->getAksjonIdField()]) && !empty($aksjonId)) {
      $params['custom_'.$config->getAksjonIdField()] = $aksjonId;
    }
    if (empty($params['custom_'.$config->getEarmarkingField()]) && !empty($earmarking)) {
      $params['custom_'.$config->getEarmarkingField()] = $earmarking;
    }
  }

}