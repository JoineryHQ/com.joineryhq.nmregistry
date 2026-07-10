<?php

namespace Civi\Api4\Action\NmregistryFavorite;

use Civi\Api4\Generic\DAODeleteAction;
use Civi\Api4\Generic\Result;

/**
 * Remove any "favorite" marker on the given contact for the current logged-in user.
 *
 * This action is idempotent. If the specified contact isn't in the
 * current user's favorites, success is still is returned.
 */
class DeleteOwn extends DAODeleteAction {

  public function _run(Result $result) {
    $cid = \CRM_Core_Session::getLoggedInContactID();

    if (!$cid) {
      throw new \CRM_Core_Exception('You must be logged in.');
    }

    // Force user_contact_id in WHERE.
    $this->addWhere('user_contact_id', '=', $cid);
    $this->setCheckPermissions(FALSE);

    return parent::_run($result);
  
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
    return \Civi\Api4\NmregistryFavorite::_ownActionFields();
  }

}
