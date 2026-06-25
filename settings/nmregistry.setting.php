<?php

use CRM_Nmregistry_ExtensionUtil as E;

return [
  'nmregistry_avatar_size' => [
    'group_name' => 'nmregistry',
    'group' => 'nmregistry',
    'name' => 'nmregistry_avatar_size',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Size (in pixels) of user image in user-facing pages',
    'title' => E::ts('User image size'),
    'type' => 'Int',
    'quick_form_type' => 'Element',
    'default' => 240,
    'html_type' => 'Text',
    'X_form_rules_args' => [
      [E::ts('The field "User image size" is required.'), 'required'],
    ],
  ],
  'nmregistry_listing_preface' => [
    'group_name' => 'nmregistry',
    'group' => 'nmregistry',
    'name' => 'nmregistry_listing_preface',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => E::ts('Content to dislpay above the registry profile (applies both to the search form and to individual listings)'),
    'title' => E::ts('Provider listing preface'),
    'type' => 'Text',
    'default' => '<p>Like all caregivers in this registry, this caregiver has achieved certification in the Respite Care Provider Training program provided by the ARCH National Respite Network.</p>',
    'html_type' => 'textarea',
    'quick_form_type' => 'Element',
    'html_attributes' => [
      'class' => 'crm-form-wysiwyg',
    ],
  ],
//  'com.joineryhq.nmregistry' => [
//    'group_name' => 'nmregistry',
//    'group' => 'nmregistry',
//    'name' => 'nmregistry_contact',
//    'add' => '5.0',
//    'is_domain' => 1,
//    'is_contact' => 0,
//    'description' => '',
//    'title' => E::ts('Select Contact for nmregistry'),
//    'type' => 'Int',
//    'quick_form_type' => 'Element',
//    'html_type' => 'Select',
//    'html_attributes' => [
//      'class' => 'crm-select2',
//      'style' => "width:auto;",
//    ],
//    'X_options_callback' => 'CRM_Extensionname_Form_Settings::getContactList',
//  ],
//  'com.joineryhq.nmregistry' => [
//    'group_name' => 'nmregistry',
//    'group' => 'nmregistry',
//    'name' => 'nmregistry_internal_id',
//    'add' => '5.0',
//    'is_domain' => 1,
//    'is_contact' => 0,
//    'description' => '',
//    'title' => E::ts('nmregistry Internal ID'),
//    'type' => 'Text',
//    'default' => FALSE,
//    'html_type' => 'textarea',
//    // Omit 'quick_form_type' property to hide from settings form.
//    'quick_form_type' => NULL,
//  ],
];
