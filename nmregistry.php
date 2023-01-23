<?php

require_once 'nmregistry.civix.php';
// phpcs:disable
use CRM_Nmregistry_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_post().
 */
function nmregistry_civicrm_post(string $op, string $objectName, int $objectId, &$objectRef) {
  if ($objectName == 'Activity') {
    // Upon updating an activity of the "update registry status" type, we'll make
    // corresponding changes to fields in the "registry status" custom field
    // group for the target contact.
    $statusUpdateActivityTypeId = 58; // TODO: GET THIS FROM A SETTING.
    if ($objectRef->activity_type_id == $statusUpdateActivityTypeId) {
      $activityStatusCustomFieldId = 22; // TODO: GET THIS FROM A SETTING.
      $contactStatusCustomFieldId = 19; // TODO: GET THIS FROM A SETTING.
      $addedToRegistryCustomFieldId = 18; // TODO: GET THIS FROM A SETTING.

      $activityGetSingle = _nmregistry_civicrmapi('Activity', 'getSingle', ['id' => $objectId]);
      $activityContactGet = _nmregistry_civicrmapi('ActivityContact', 'get', [
        'activity_id' => $objectId,
        // "3" = target contact
        'record_type_id' => 3,
        'sequential' => TRUE,
      ]);
      // Assume only one target contact, so use the first one.
      $targetCid = $activityContactGet['values'][0]['contact_id'];
      $newActivityStatus = $activityGetSingle['custom_' . $activityStatusCustomFieldId];

      // Define parameters to update target contact.
      $contactCreateParams = [
        'id' => $targetCid,
        'custom_' . $contactStatusCustomFieldId => $newActivityStatus,
      ];
      // If status == 'approved', and if contact doesn't already have an
      // AddedToRegistry date value, set it now.
      if ($newActivityStatus == 'approved') {
        $currentAddedToRegistryValue = _nmregistry_civicrmapi('contact', 'getValue', [
          'id' => $targetCid,
          'return' => 'custom_' . $addedToRegistryCustomFieldId,
        ]);
        if (empty($currentAddedToRegistryValue)) {
          $contactCreateParams['custom_' . $addedToRegistryCustomFieldId] = CRM_Utils_Date::currentDBDate();
        }
      }
      // Save parameters on the target contact.
      $contactCreate = _nmregistry_civicrmapi('Contact', 'create', $contactCreateParams);
    }
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function nmregistry_civicrm_pageRun($page) {
  $pageName = $page->getVar('_name');
  if ($pageName == 'CRM_Profile_Page_Listings') {
    $individualListingProfileId = 16; // TODO: GET THIS FROM A SETTING.
    $gid = $page->getVar('_gid');
    if ($gid = $individualListingProfileId) {
      CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/CRM_Profile_Page_Listings-registrySearch.js');
    }
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function nmregistry_civicrm_buildForm($formName, &$form) {
  // CRM_Core_Session::setStatus($formName);
  if ($formName == 'CRM_Custom_Form_CustomDataByType') {
    $cdType = $form->getVar('_cdType');
    if ($cdType == 'Activity') {
      $activityRegistryStatusCustomGroupId = 6; // TODO: GET THIS FROM A SETTING.
      $groupTree = $form->getVar('_groupTree');
      if (array_key_exists($activityRegistryStatusCustomGroupId, $groupTree)) {
        // If we're editing "registry status" custom fields on an activity,
        // add javascript to modify form behavior.
        CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/CRM_Custom_Form_CustomDataByType-activityRegistryStatus.js');
      }
    }
  }
  elseif ($formName == 'CRM_Activity_Form_Activity') {
    $action = $form->getVar('_action');
    if ($action == CRM_Core_Action::UPDATE) {
      $updateRegistryStatusActivityTypeId = 58;  // TODO: GET THIS FROM A SETTING.
      $activityTypeId = $form->getVar('_activityTypeId');
      if ($activityTypeId == $updateRegistryStatusActivityTypeId) {
        // We don't allow editing of 'update registry status' activities.
        CRM_Core_Error::statusBounce('This activity type cannot be edited.');
      }
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function nmregistry_civicrm_postProcess($formName, $form) {
  if ($formName == 'CRM_Profile_Form_Edit') {
    $profileId = $form->getVar('_gid');
    $individualListingEditProfileId = 18; // TODO: GET THIS FROM A SETTING.
    if ($profileId == $individualListingEditProfileId) {
      $cid = $form->getVar('_id');
      if ($cid) {
        // We should always have a $cid here, but just in case, we only take action if we do.

        // Create a "last updated" activity.
        $activityTypeId = 55; // TODO: GET THIS FROM A SETTING.
        $contactStatusCustomFieldId = 19; // TODO: GET THIS FROM A SETTING.
        $activityApiParams = [
          'target_id' => $cid,
          'activity_type_id' => $activityTypeId,
          'status_id' => "Completed",
        ];
        $activityCreate = _nmregistry_civicrmapi('activity', 'create', $activityApiParams);

        // Define params to update contact
        $lastUpdateCustomFieldId = 17; // TODO: GET THIS FROM A SETTING.
        $contactUpdateParams = [
          'id' => $cid,
          // Set custom field "last updated" to current timestamp:
          'custom_' . $lastUpdateCustomFieldId => CRM_Utils_Date::currentDBDate(),
          'status_id' => "Completed",
        ];
        // If contact's current Registry Status == 'archived, set it to "Approved".
        $archivedStatusCount = _nmregistry_civicrmapi('contact', 'getCount', [
          'id' => $cid,
          'custom_' . $contactStatusCustomFieldId => 'archived',
        ]);
        if ($archivedStatusCount) {
          $contactUpdateParams['custom_' . $contactStatusCustomFieldId] = 'approved';
        }

        $contactUpdate = _nmregistry_civicrmapi('contact', 'create', $contactUpdateParams);

      }
    }
  }
}

/**
 * Implements hook_civicrm_alterTemplateFile().
 */
function nmregistry_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  if ($formName == 'CRM_Profile_Page_Dynamic') {
    $profileId = $form->getVar('_gid');
    $individualListingProfileId = 16; // TODO: GET THIS FROM A SETTING.
    if ($profileId == $individualListingProfileId) {
      // Special handling, if this is a profile to view an individual provider.
      // Get dispplay name and set title
      $cid = $form->getVar('_id');
      $displayName = _nmregistry_civicrmapi('Contact', 'getValue', ['id' => $cid, 'return' => 'display_name']);
      CRM_Utils_System::setTitle($displayName);
      $tplName = 'CRM/Nmregistry/Profile/Page/DynamicRegistryProfileView.tpl';

      $introText = 'TODO: GET THIS TEXT FROM A SETTING.';
      $form->assign('nmregistryIntroText', $introText);

      $avatarSize = 240; // TODO: GET THIS SIZE FROM A SETTING.

      $uid = CRM_Core_BAO_UFMatch::getUFId($cid);
      $avatar = CRM_Nmregistry_Utils::get_avatar($uid, $avatarSize);
      $form->assign('nmregistryUserAvatar', $avatar);
    }
  }
  elseif ($formName == 'CRM_Profile_Form_Edit') {
    $profileId = $form->getVar('_gid');
    $individualListingEditProfileId = 18; // TODO: GET THIS FROM A SETTING.
    if ($profileId == $individualListingEditProfileId) {
      // Special handling, if this is the profile to edit my provider listing.
      $tplName = 'CRM/Nmregistry/Profile/Form/DynamicRegistryProfileEdit.tpl';

      CRM_Core_Resources::singleton()->addScriptFile(E::LONG_NAME, 'js/CRM_Nmregistry_Profile_Form_DynamicRegistryProfileEdit.js');

      $cid = $form->getVar('_id');
      $uid = CRM_Core_BAO_UFMatch::getUFId($cid);
      $avatarSize = 240; // TODO: GET THIS SIZE FROM A SETTING.
      $avatar = CRM_Nmregistry_Utils::get_avatar($uid, $avatarSize);
      $form->assign('nmregistryUserAvatar', $avatar);
      $form->assign('nmregistryUserAvatarSize', $avatarSize);

      $form->assign('nmregistryEditAvatarUrl', '/edit-my-profile-picture'); // TODO: GET THIS FROM A SETTING.

      $statusChecks = CRM_Nmregistry_Utils::getProviderStatusChecks($cid);
      if (!empty($statusChecks['SIGNED_UP_FOR_LISTING'])) {
        $statusChecks['SIGNED_UP_FOR_LISTING']['message_secondPerson'] .= ' ' . E::ts('You may do so by submitting this form.');
      }
      if (!empty($statusChecks['HIDE_MY_LISTING'])) {
        $statusChecks['HIDE_MY_LISTING']['message_secondPerson'] .= ' ' . E::ts('You may change this preference below if you wish.');
      }
      if (!empty($statusChecks['HAS_IMAGE'])) {
        $statusChecks['HAS_IMAGE']['message_secondPerson'] .= ' ' . E::ts('Please specify an image below.');
      }
      if (($statusChecks['STATUS_APPROVED']['code'] ?? NULL) == 'ARCHIVED') {
        $statusChecks['STATUS_APPROVED']['message_secondPerson'] .= ' ' . E::ts('To remedy this, please review your details and save this form.');
      }
      $statusMessages = CRM_Nmregistry_Utils::formatStatusMessages($statusChecks);
      $form->assign('nmregistryStatusMessages', $statusMessages);

      $form->assign('nmregistryUserHasAvatar', has_wp_user_avatar($uid));

    }
  }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function nmregistry_civicrm_navigationMenu(&$menu) {
  _nmregistry_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('Registry Reminders'),
    'name' => 'nmregistry_reminders',
    'url' => 'civicrm/a/#nmregistry/reminders',
    'permission' => 'administer CiviCRM',
  ));
  _nmregistry_civix_navigationMenu($menu);
}


/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nmregistry_civicrm_config(&$config) {
  _nmregistry_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nmregistry_civicrm_install(): void {
  _nmregistry_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function nmregistry_civicrm_postInstall(): void {
  _nmregistry_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function nmregistry_civicrm_uninstall(): void {
  _nmregistry_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nmregistry_civicrm_enable(): void {
  _nmregistry_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function nmregistry_civicrm_disable(): void {
  _nmregistry_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function nmregistry_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _nmregistry_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function nmregistry_civicrm_entityTypes(&$entityTypes): void {
  _nmregistry_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function nmregistry_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function nmregistry_civicrm_navigationMenu(&$menu): void {
//  _nmregistry_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _nmregistry_civix_navigationMenu($menu);
//}

/**
 * CiviCRM API wrapper. Wraps with try/catch, redirects errors to log, saves
 * typing.
 */
function _nmregistry_civicrmapi(string $entity, string $action, array $params, bool $silence_errors = FALSE) {
  try {
    $result = civicrm_api3($entity, $action, $params);
  }
  catch (CiviCRM_API3_Exception $e) {
    _nmregistry_log_api_error($e, $entity, $action, $params);
    if (!$silence_errors) {
      throw $e;
    }
  }

  return $result;
}

/**
 * Log CiviCRM API errors to CiviCRM log.
 */
function _nmregistry_log_api_error(Exception $e, string $entity, string $action, array $params) {
  $message = E::SHORT_NAME . ": CiviCRM API Error '{$entity}.{$action}': " . $e->getMessage() . '; ';
  $message .= "API parameters when this error happened: " . json_encode($params) . '; ';
  $bt = debug_backtrace();
  $error_location = "{$bt[1]['file']}::{$bt[1]['line']}";
  $message .= "Error API called from: $error_location";
  CRM_Core_Error::debug_log_message($message);
}
