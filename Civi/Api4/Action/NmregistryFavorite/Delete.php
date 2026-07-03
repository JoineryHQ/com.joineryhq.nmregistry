<?php

namespace Civi\Api4\Action\NmregistryFavorite;

use Civi\Api4\Generic\Result;

/**
 * Un-favorite the given contact from the current user contact.
 *
 * @package Civi\Api4\Action\NmregistryFavorite
 */
class Delete extends \Civi\Api4\Generic\AbstractAction {

  var $values;
  var $where;

  /**
   * Every action must define a _run function to perform the work and place results in the Result object.
   *
   * When using the set of Basic actions, they define _run for you and you just need to provide a getter/setter function.
   *
   * @param Result $result
   *
   * @throws CRM_Core_Exception if afform not found.
   */
  public function _run(Result $result) {
    $counts = [
      'deleted' => 0,
    ];
    $userContactId = \CRM_Core_Session::getLoggedInContactID();
    $counts['deleted'] += \Civi\Api4\Relationship::delete()
      ->setCheckPermissions(FALSE)
      ->addWhere('relationship_type_id', '=', \Civi\Api4\NmregistryFavorite::relationshipTypeId)
      ->addWhere('contact_id_b', '=', $userContactId)
      ->addWhere('contact_id_a', '=', $this->values['contactId'])
      ->execute()
      ->count();
    $result[] = $counts;
  }

}
