<?php

namespace Civi\Api4\Action\NmregistryFavorite;

use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\NmregistryFavorite;
use Civi\Api4\Generic\Result;

/**
 * Get all "favorited" contacts for the current logged-in user.
 */

class GetOwn extends DAOGetAction {

  public function _run(Result $result) {
    $userContactId = \CRM_Core_Session::getLoggedInContactID();

    if (!$userContactId) {
      throw new \CRM_Core_Exception('You must be logged in.');
    }

    $rows = NmregistryFavorite::get(FALSE)
      ->addWhere('user_contact_id', '=', $userContactId)
      ->execute();

    foreach ($rows as $row) {
      $result[] = $row;
    }
  }
  
  /**
   * Ad-hoc field list for this action.
   * 
   * As documented in Civi\Api4\Action\Example\Random (api4example extension)
   *
   * This action doesn't take a user_contact_id param, because it's auto-calculated 
   * from the current user.
   *
   * @return array
   */
  public static function fields() {
    $fields = \Civi\Api4\NmregistryFavorite::_fields();
    // Remove field user_contact_id and id.
    unset($fields['user_contact_id']);
    return $fields;
  }

}
