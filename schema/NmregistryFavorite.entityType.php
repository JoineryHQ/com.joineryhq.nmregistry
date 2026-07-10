<?php

use CRM_Nmregistry_ExtensionUtil as E;

return [
  'name' => 'NmregistryFavorite',
  'table' => 'civicrm_nmregistry_favorite',
  'class' => 'CRM_Nmregistry_DAO_NmregistryFavorite',
  'getInfo' => fn() => [
  'title' => E::ts('NmregistryFavorite'),
  'title_plural' => E::ts('NmregistryFavorites'),
  'description' => E::ts('FIXME'),
  'log' => TRUE,
  ],
  'getFields' => fn() => [
  'id' => [
    'title' => E::ts('ID'),
    'sql_type' => 'int unsigned',
    'input_type' => 'Number',
    'required' => TRUE,
    'description' => E::ts('Unique NmregistryFavorite ID'),
    'primary_key' => TRUE,
    'auto_increment' => TRUE,
  ],
  'user_contact_id' => [
    'title' => E::ts('User Contact ID'),
    'sql_type' => 'int unsigned',
    'input_type' => 'EntityRef',
    'required' => TRUE,
    'description' => E::ts('FK to Contact'),
    'entity_reference' => [
      'entity' => 'Contact',
      'key' => 'id',
      'on_delete' => 'CASCADE',
    ],
  ],
  'favorite_contact_id' => [
    'title' => E::ts('Favorite Contact ID'),
    'sql_type' => 'int unsigned',
    'input_type' => 'EntityRef',
    'required' => TRUE,
    'description' => E::ts('FK to Contact'),
    'entity_reference' => [
      'entity' => 'Contact',
      'key' => 'id',
      'on_delete' => 'CASCADE',
    ],
  ],
  ],
  'getIndices' => fn() => [
    'index_user_contact_id' => [
      'fields' => [
        'user_contact_id' => TRUE,
      ],
    ],
    'index_favorite_contact_id' => [
      'fields' => [
        'favorite_contact_id' => TRUE,
      ],
    ],
    'unique_user_favorite' => [
      'fields' => [
        'user_contact_id' => TRUE,
        'favorite_contact_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
  ],
  'getPaths' => fn() => [],
];
