<?php

namespace Civi\Api4\Action\NmregistryFavorite;

use Civi\Api4\Generic\Result;

/**
 * Favorite the given contact from the current user contact.
 *
 * @package Civi\Api4\Action\NmregistryFavorite
 */
class Create extends \Civi\Api4\Generic\BasicCreateAction {

  var $values;

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
      'created' => 0,
      'updated' => 0,
    ];
    $createRelationship = TRUE;
    $userContactId = \CRM_Core_Session::getLoggedInContactID();
    $existingRelationships = \Civi\Api4\Relationship::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('relationship_type_id', '=', \Civi\Api4\NmregistryFavorite::relationshipTypeId)
      ->addWhere('contact_id_b', '=', $userContactId)
      ->addWhere('contact_id_a', '=', $this->values['contactId'])
      ->execute();
    foreach ($existingRelationships as $existingRelationship) {
      $createRelationship = FALSE;
      if (!$existingRelationship['is_active']) {
        $existingRelationship['is_active'] = TRUE;
        \Civi\Api4\Relationship::update()
          ->addWhere('id', '=', $existingRelationship['id'])
          ->setValues(['is_active' => TRUE])
          ->execute();
        $counts['updated']++;
      }
    }
    if ($createRelationship) {
      \Civi\Api4\Relationship::create()
        ->setCheckPermissions(FALSE)
        ->setValues([
          'relationship_type_id' => \Civi\Api4\NmregistryFavorite::relationshipTypeId,
          'contact_id_b' => $userContactId,
          'contact_id_a' => $this->values['contactId'],
          'is_active' => TRUE,
        ])
        ->execute();
      $counts['created']++;
    }
    $result[] = $counts;
  }
  
}
