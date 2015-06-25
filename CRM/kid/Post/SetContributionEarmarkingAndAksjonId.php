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
    if (empty($params['custom'][$config->getAksjonIdFieldParameter('id')]) && !empty($aksjonId)) {
      $customValue = $config->getAksjonIdField();
      $customValue['value'] = $aksjonId;
      $customValue['type'] = 'String';
      $customValue['table_name'] = $config->getCustomGroup('table_name');
      $params['custom'][$config->getAksjonIdFieldParameter('id')][] = $customValue;
    }
    if (empty($params['custom'][$config->getEarmarkingFieldParameter()]) && !empty($earmarking)) {
      $customValue = $config->getEarmarkingField();
      $customValue['value'] = $earmarking;
      $customValue['type'] = 'String';
      $customValue['table_name'] = $config->getCustomGroup('table_name');
      $params['custom_'.$config->getEarmarkingFieldParameter('id')][] = $customValue;
    }
  }

}