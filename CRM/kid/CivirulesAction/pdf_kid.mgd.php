<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
if (_kid_is_civirules_and_pdfapi_installed()) {
  return array (
    0 =>
      array (
        'name' => 'Civirules:Action.KidPdfapi',
        'entity' => 'CiviRuleAction',
        'params' =>
          array (
            'version' => 3,
            'name' => 'kid_pdfapi_send',
            'label' => 'Send PDF (with KID token)',
            'class_name' => 'CRM_kid_CivirulesAction_PdfKid',
            'is_active' => 1
          ),
      ),
  );
}