<?php

// phpcs:disable
use CRM_Nmregistry_ExtensionUtil as E;
// phpcs:enable

class CRM_Nmregistry_Utils {

  public static function get_avatar($ufId, $sizePx = 96) {
    if (function_exists('get_wp_user_avatar')) {
      $avatar = get_wp_user_avatar($ufId, $sizePx);
    }
    else {
      $avatar = get_avatar($ufId, $sizePx);
    }
    return $avatar;
  }

  public static function getProviderStatusChecks($cid) {
    $statusChecks = [];

    // Compare all of these criteria, and return a status message for each criteria not met.
    // Is an individual contact in cvicrm (user is logged in viewing their own profile, so must be an Individual)
    $isIndividualCount = _nmregistry_civicrmapi('Contact', 'getcount', [
      'contact_id' => $cid,
      'contact_type' => 'individual',
    ]);
    if (!$isIndividualCount) {
      $statusChecks['INDIVIDUAL_CONTACT'] = [
        'status' => 'error',
        'code' => 'CONTACT_NOT_FOUND',
        'message_thirdPerson' => E::ts('This contact does not exist or is not an Individual contact.'),
        'message_secondPerson' => E::ts('Something is wrong with your profile record; please contact us about it. The error message is: "INDIVIDUAL_CONTACT_CHECK_FAILED".'),
      ];
      // We don't want to check anything else, just return now.
      return $statusChecks;
    }
    // has a user account in WordPress (is CMS user) (user is logged in, so must have be WP user)
    $uid = CRM_Core_BAO_UFMatch::getUFId($cid);
    if (!$uid) {
      $statusChecks['USER_ACCOUNT'] = [
        'status' => 'error',
        'code' => 'USER_NOT_FOUND',
        'message_thirdPerson' => E::ts('This contact does not have a WordPress user account.'),
        'message_secondPerson' => E::ts('Something is wrong with your profile record; please contact us about it. The error message is: "USER_ACCOUNT_CHECK_FAILED".'),
      ];
    }

    // has submitted their profile for listing
    $groupCount = _nmregistry_civicrmapi('GroupContact', 'getcount', [
      'group_id' => "Providers_all_3", // TODO: GET THIS FROM A SETTING
      'status' => "Added",
      'contact_id' => $cid,
    ]);
    if (!$groupCount) {
      $statusChecks['SIGNED_UP_FOR_LISTING'] = [
        'status' => 'info',
        'code' => 'NOT_SIGNED_UP',
        'message_thirdPerson' => E::ts('This contact has not yet signed up for a profile listing.'),
        'message_secondPerson' => E::ts('You haven\'t yet signed up for a profile listing.'),
      ];
    }
    // Get several custom values from this contact so we can run the following checks.
    $approvalStatusCustomFieldId = 19; // TODO: GET THIS FROM A SETTING.
    $hasUserImageCustomFieldId = 20; // TODO: GET THIS FROM A SETTING.
    $disableMyListingCustomFieldId = 16; // TODO: GET THIS FROM A SETTING.
    $contactGetParams = [
      'id' => $cid,
      'return' => [
        'custom_' . $approvalStatusCustomFieldId,
        'custom_' . $hasUserImageCustomFieldId,
        'custom_' . $disableMyListingCustomFieldId,
      ],
    ];
    $contactGetSingle = _nmregistry_civicrmapi('contact', 'getSingle', $contactGetParams);
    // Now we have the values, we can do the checks:

    // is approved (read-only custom field "Approval status", controlled by Activities)
    $approvalValue = $contactGetSingle['custom_' . $approvalStatusCustomFieldId];
    $approvalOptions = CRM_Contact_BAO_Contact::buildOptions('custom_' . $approvalStatusCustomFieldId);
    $label = $approvalOptions[$approvalValue];
    if ($approvalValue != 'approved') {
      switch ($approvalValue) {
        case 'pending':
          $statusChecks['STATUS_APPROVED'] = [
            'status' => 'info',
            'code' => 'PENDING',
            'message_thirdPerson' => E::ts('This listing is still pending review.'),
            'message_secondPerson' => E::ts('Your listing is still pending review.'),
          ];
          break;

        case 'suspended':
          $statusChecks['STATUS_APPROVED'] = [
            'status' => 'error',
            'code' => 'SUSPENDED',
            'message_thirdPerson' => E::ts('This listing has been suspended.'),
            'message_secondPerson' => E::ts('Your listing has been suspended. Please contact us to address this.'),
          ];
          break;

        case 'archived':
          $statusChecks['STATUS_APPROVED'] = [
            'status' => 'warning',
            'code' => 'ARCHIVED',
            'message_thirdPerson' => E::ts('This listing has been archived due to inactivity.'),
            'message_secondPerson' => E::ts('Your listing has been archived due to inactivity.'),
          ];
          break;
      }
    }

    // has a user image (read-only custom field "Has user image?", controlled by nmregistry WP plugin)
    if (!$contactGetSingle['custom_' . $hasUserImageCustomFieldId]) {
      $statusChecks['HAS_IMAGE'] = [
        'status' => 'warning',
        'message_thirdPerson' => E::ts('This provider has no profile image.'),
        'message_secondPerson' => E::ts('Your listing lacks a profile image.'),
      ];
    }

    // has not hidden their own listing (custom field "User preference: Disable my Listing?", editable in "Edit My Listing" form)
    if ($contactGetSingle['custom_' . $disableMyListingCustomFieldId] != 'show') {
      $statusChecks['HIDE_MY_LISTING'] = [
        'status' => 'warning',
        'message_thirdPerson' => E::ts('This listing is hidden by request of the provider.'),
        'message_secondPerson' => E::ts('Your listing is hidden.'),
      ];
    }

    // TODO: NOT SURE HOW TO CHECK THIS ONE YET: has updated or reviewed their listing recently equals x days (read-only custom field "Last updated listing", controlled by nmregistry extension on submit of Edit My Listing form)

    if (empty($statusChecks)) {
      // If we haven't logged any issues, add one 'success' status indicating that
      // the profile is passing all checks.

      /* We'll need to assemle a url to view "my" listing. Note that
       * CRM_Utils_System::url() doesn't work for us because it forces the baseurl
       * of the current page, which is different from the baseurl of a profile listing.
       */
      $individualListingProfileId = 16; // TODO: GET THIS FROM A SETTING.
      $providerSearchPageUrl = '/provider-search'; // TODO: GET THIS FROM A SETTING.
      $queryParams = [
        'civiwp' => 'CiviCRM',
        'q' => 'civicrm/profile/view',
        'reset' => 1,
        'gid' => 16,
        'id' => $cid,
      ];
      $viewUrl = $providerSearchPageUrl;
      if (strpos($viewUrl, '?') !== FALSE) {
        $viewUrl .= '&' . http_build_query($queryParams);
      }
      else {
        $viewUrl .= '?' . http_build_query($queryParams);
      }
      $statusChecks['ALL_CHECKS_PASSING'] = [
        'status' => 'success',
        'message_thirdPerson' => E::ts('This listing passees all requirements for listing.'),
        'message_secondPerson' => E::ts("Your listing meets all requirements and now appears in the directory. <a href='%1'>You can view it here.</a>", [1 => $viewUrl]),
      ];
    }
    return $statusChecks;
  }

  public static function formatStatusMessages($statusChecks) {
    foreach ($statusChecks as &$statusCheck) {
      switch ($statusCheck['status']) {
        case 'error':
          $statusCheck['iconClass'] = 'fa-exclamation-circle';
          break;

        case 'warning':
          $statusCheck['iconClass'] = 'fa-exclamation-triangle';
          break;

        case 'success':
          $statusCheck['iconClass'] = 'fa-check-circle';
          break;

        default:
          $statusCheck['iconClass'] = 'fa-info-circle';
      }
    }
    return $statusChecks;
  }

}
