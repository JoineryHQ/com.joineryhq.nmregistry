<?php
use CRM_Nmregistry_ExtensionUtil as E;

return [
  'name' => 'NmregistryReminder',
  'table' => 'civicrm_nmregistry_reminder',
  'class' => 'CRM_Nmregistry_DAO_NmregistryReminder',
  'getInfo' => fn() => [
    'title' => E::ts('Nmregistry Reminder'),
    'title_plural' => E::ts('Nmregistry Reminders'),
    'description' => E::ts('Automoted reminders for nmregistry'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique NmregistryReminder ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'name' => [
      'title' => E::ts('Name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Human name to identify this reminder in administrative interface.'),
    ],
    'criteria' => [
      'title' => E::ts('Criteria'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'required' => TRUE,
      'description' => E::ts('JSON storage of sending criteria for this reminder'),
    ],
    'msg_template_id' => [
      'title' => E::ts('Msg Template ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to msg_template'),
      'entity_reference' => [
        'entity' => 'MessageTemplate',
        'key' => 'id',
        'on_delete' => 'SET NULL',
      ],
    ],
    'is_archive' => [
      'title' => E::ts('Is Archive'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Set registry profile status to \'archived\' when this reminder runs?'),
      'add' => '4.6',
      'default' => FALSE,
    ],
  ],
];
