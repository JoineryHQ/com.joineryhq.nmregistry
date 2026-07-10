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
    $cid = \CRM_Core_Session::getLoggedInContactID();

    if (!$cid) {
      throw new \CRM_Core_Exception('You must be logged in.');
    }

    // Force user_contact_id in WHERE.
    $this->addWhere('user_contact_id', '=', $cid);
    return parent::_run($result);

    $rows = NmregistryFavorite::get(FALSE)
      ->addWhere('user_contact_id', '=', $cid)
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
   * This action doesn't use a user_contact_id param, because it's auto-calculated
   * from the current user.
   *
   * @return array
   */
  public static function fields() {
    $fields = \Civi\Api4\NmregistryFavorite::_fields();
    // Note field user_contact_id as useless here.
    $fields['user_contact_id']['description'] = 'Not searchable - always matches current user';
    return $fields;
  }

}
