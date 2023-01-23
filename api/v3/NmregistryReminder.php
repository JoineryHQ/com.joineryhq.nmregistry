<?php
use CRM_Nmregistry_ExtensionUtil as E;

/**
 * NmregistryReminder.create API specification (optional).
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_nmregistry_reminder_create_spec(&$spec) {
  $spec['name']['api.required'] = 1;
  $spec['criteria']['api.required'] = 1;
  $spec['msg_template_id']['api.required'] = 1;
}

/**
 * NmregistryReminder.create API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_create($params) {
  $criteria = json_decode($params['criteria'], TRUE);
  if (empty($criteria['days'])) {
    throw new API_Exception('Criteria "days" is required.', 'NmregistryReminder_criteria.days_required');
  }
  if ((int) $criteria['days'] != $criteria['days']) {
    throw new API_Exception('Criteria "days" must be an integer.', 'NmregistryReminder_criteria.days_int');
  }

  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'NmregistryReminder');
}

/**
 * NmregistryReminder.delete API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * NmregistryReminder.get API.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, 'NmregistryReminder');
}
