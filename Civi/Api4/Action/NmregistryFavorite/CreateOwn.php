<?php

namespace Civi\Api4\Action\NmregistryFavorite;

use Civi\Api4\Generic\DAOCreateAction;
use Civi\Api4\Generic\Result;
use Civi\Api4\NmregistryFavorite;

/**
 * Create a "favorite" marker on the given contact for the current logged-in user.
 *
 * This action is idempotent. If the specified contact is already in the
 * current user's favorites, the existing favorite is returned.
 */
class CreateOwn extends DAOCreateAction {

  public function _run(Result $result) {
    // Get the current user's cid, and use it as user_contact_id or else throw exception.
    $cid = \CRM_Core_Session::getLoggedInContactID();
    if (!$cid) {
      throw new \CRM_Core_Exception('You must be logged in.');
    }

    // Call the save() api, because it supports setMatch; this way we're idempotent
    // and don't get errors on the unique index.
    $saveResult = \Civi\Api4\NmregistryFavorite::save($this->getCheckPermissions())
      ->addRecord([
        'user_contact_id' => $cid,
        'favorite_contact_id' => $this->values['favorite_contact_id'],
      ])
      ->setMatch([
        'user_contact_id',
        'favorite_contact_id',
      ])
      ->execute();

    $result->exchangeArray($saveResult);
    $result->setCountMatched($saveResult->countMatched());
  }
  
  /**
   * Ad-hoc field list for this action.
   * 
   * As documented in Civi\Api4\Action\Example\Random (api4example extension)
   *
   * This action doesn't take a user_contact_id param, because it's auto-calculated 
   * from the current user. (And 'id' is nonsensical here.)
   *
   * @return array
   */
  public static function fields() {
    return \Civi\Api4\NmregistryFavorite::_ownActionFields();
  }

}
