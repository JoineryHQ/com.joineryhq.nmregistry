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

/**
 * NmregistryReminder.Processall API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_nmregistry_reminder_Processall($params) {
  $returnValues = [
    'archivedCount' => 0,
  ];
  $processedCids = [];

  $lastUpdateCustomFieldId = 17; // TODO: GET THIS FROM A SETTING.
  $provdersListedGroupId = 2; // TODO: GET THIS FROM A SETTING.
  $contactStatusCustomFieldId = 19; // TODO: GET THIS FROM A SETTING.

  // Get all configured reminders.
  $remindersGet = _nmregistry_civicrmapi('nmregistryReminder', 'get', [
    'options' => ['limit' => 0],
  ]);
  $reminders = $remindersGet['values'];
  $sort = [];
  foreach ($reminders as &$reminder) {
    $reminder['criteria'] = json_decode($reminder['criteria'], TRUE);
    $sort[] = $reminder['criteria']['days'];
  }
  // Unset by-reference foreach param. (Reference https://stackoverflow.com/a/5810200/6476602)
  unset($reminder);

  // Sort by days (largest first)
  array_multisort($sort, SORT_DESC, SORT_NUMERIC, $reminders);

  // For each reminder
  foreach ($reminders as $reminder) {
    // It's possible that the reminder.msg_template_id is null (e.g. if the
    // specified message template was later deleted). In that case, skip this reminder.
    if (empty($reminder['msg_template_id'])) {
      continue;
    }
    // Ensure the message template is active; if not, skip this reminder.
    $templateGetcount = _nmregistry_civicrmapi('MessageTemplate', 'getcount', [
      'id' => $reminder['msg_template_id'],
      'is_active' => 1,
    ]);
    if (!$templateGetcount) {
      continue;
    }

    // Create a variable to count the number of emails sent for this reminder.
    $returnValues['emailsSentPerReminder'][$reminder['name']] = 0;

    // Find all listed providers who match this reminder.
    // First get a date that represents $days ago. This reminder will be sent
    // if the contact's "last updated listing" value is older than this timestamp.
    $daysTime = strtotime($reminder['criteria']['days'] . ' days ago');
    $contactGetParams = [
      'sequential' => 1,
      'return' => [
        'id',
        'custom_' . $lastUpdateCustomFieldId,
      ],
      'group' => $provdersListedGroupId,
      'custom_' . $lastUpdateCustomFieldId => ['<=' => CRM_Utils_Date::currentDBDate($daysTime)],
      'options' => ['limit' => 0],
    ];
    $contactGet = _nmregistry_civicrmapi('Contact', 'get', $contactGetParams);
    foreach ($contactGet['values'] as $contactGetValue) {
      // For each contact:
      $cid = $contactGetValue['id'];
      $lastProfileUpdated = $contactGetValue['custom_' . $lastUpdateCustomFieldId];
      // verify that this contact hasn't already gotten a reminder in this run.(if they have, skip them)
      if (in_array($cid, $processedCids)) {
        continue;
      }
      // Verify that this contact hasn't gotten a reminder that matches these criteria:
      //   - Was sent for the current "last profile update" date value.
      //   - Was sent for this number of days or less.
      // If they have, skip this contact.
      $reminderSentCount = _nmregistry_civicrmapi('nmregistryReminderSent', 'getCount', [
        'contact_id' => $cid,
        'compare_date_time' => $lastProfileUpdated,
        'days' => ['<=' => $reminder['criteria']['days']],
      ]);
      if ($reminderSentCount) {
        continue;
      }

      // send an email with the appropriate message template
      $result = _nmregistry_civicrmapi('Email', 'send', [
        'contact_id' => $cid,
        'template_id' => $reminder['msg_template_id'],
      ]);
      // Record in database that this contact received this reminder based on
      // a comparison of his 'last profile update' value with this reminder's
      // "days" criteria.
      _nmregistry_civicrmapi('nmregistryReminderSent', 'create', [
        'contact_id' => $cid,
        'compare_date_time' => $lastProfileUpdated,
        'days' => $reminder['criteria']['days'],
      ]);
      // Increment the count of emails sent for this reminder.
      $returnValues['emailsSentPerReminder'][$reminder['name']]++;
      // note that this contact has received a reminder in this run.
      $processedCids[] = $cid;

      // If this reminder is final, update the contact's listing to "archived".
      if ($reminder['is_archive']) {
        $contactCreate = _nmregistry_civicrmapi('contact', 'create', [
          'id' => $cid,
          'custom_' . $contactStatusCustomFieldId => 'archived',
        ]);
        $returnValues['archivedCount']++;
      }
    }
  }
  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($returnValues, $params, 'NmregistryReminder', 'Processall');
}
