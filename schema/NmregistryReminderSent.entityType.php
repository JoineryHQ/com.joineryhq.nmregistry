<?php
use CRM_Nmregistry_ExtensionUtil as E;

return [
  'name' => 'NmregistryReminderSent',
  'table' => 'civicrm_nmregistry_reminder_sent',
  'class' => 'CRM_Nmregistry_DAO_NmregistryReminderSent',
  'getInfo' => fn() => [
    'title' => E::ts('Nmregistry Reminder Sent'),
    'title_plural' => E::ts('Nmregistry Reminder Sents'),
    'description' => E::ts('Reminders actually sent to contacts'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'nmregistryremindersent_compare_date_time' => [
      'fields' => [
        'compare_date_time' => TRUE,
      ],
    ],
    'nmregistryremindersent_days' => [
      'fields' => [
        'days' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique NmregistryReminderSent ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact to whom reminder was sent'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'compare_date_time' => [
      'title' => E::ts('Compare Date Time'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => E::ts('Reminder was sent because this date (e.g. \'last updated profile\') matched the reminder \'days\' criteria.'),
    ],
    'days' => [
      'title' => E::ts('Days'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Value of \'days\' criteria for the reminder when it was sent.'),
    ],
    'sent_date_time' => [
      'title' => E::ts('Sent Date Time'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'description' => E::ts('Date and time when reminder was sent.'),
      'default' => 'CURRENT_TIMESTAMP',
    ],
  ],
];
