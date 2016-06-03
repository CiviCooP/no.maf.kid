<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_kid_CivirulesAction_PdfKid extends CRM_Pdfapi_CivirulesAction {

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * $access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirules/actions/kid_pdfapi', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $parameters
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return array
   * @access protected
   */
  protected function alterApiParameters($parameters, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    if (!empty($actionParams['earmarking'])) {
      CRM_kid_Earmarking::setEarmarking($actionParams['earmarking']);
    }
    if (!empty($actionParams['aksjon_id'])) {
      CRM_kid_AksjonId::setAksjonId($actionParams['aksjon_id']);
    }

    $parameters = parent::alterApiParameters($parameters, $triggerData);
    return $parameters;
  }

}