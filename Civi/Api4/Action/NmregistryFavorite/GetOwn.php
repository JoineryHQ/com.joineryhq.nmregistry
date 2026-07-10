<?php

namespace Civi\Api4\Action\NmregistryFavorite;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\NmregistryFavorite;
use Civi\Api4\Generic\Result;

class GetOwn extends AbstractAction {

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

}
