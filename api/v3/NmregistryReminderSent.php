<?php
use CRM_Nmregistry_ExtensionUtil as E;

/**
 * NmregistryReminderSent.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_nmregistry_reminder_sent_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * NmregistryReminderSent.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_sent_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'NmregistryReminderSent');
}

/**
 * NmregistryReminderSent.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_sent_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * NmregistryReminderSent.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_sent_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'NmregistryReminderSent');
}
