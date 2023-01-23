<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return [
  [
    'name' => 'NmregistryReminder.processall',
    'entity' => 'Job',
    'params' =>
    [
      'version' => 3,
      'name' => 'nmregistry: process reminders',
      'description' => 'Process all configured registry reminders',
      'run_frequency' => 'Daily',
      'api_entity' => 'NmregistryReminder',
      'api_action' => 'Processall',
      'parameters' => '',
    ],
  ],
];
