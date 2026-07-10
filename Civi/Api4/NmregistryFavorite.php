<?php
declare(strict_types = 1);

namespace Civi\Api4;

/**
 * NmregistryFavorite entity.
 *
 * Provided by the New Mexico Registry extension.
 *
 * @package Civi\Api4
 */
class NmregistryFavorite extends Generic\DAOEntity {
  
  /**
   * Alter entity fields for *own actions.
   *
   * These actions don't take a user_contact_id param, because it's auto-calculated 
   * from the current user. (And 'id' is nonsensical.)
   * 
   * @return string
   */
  public static function _ownActionFields() {
    // First get the fields as they would be for the 'create' action.
    $gatherer = \Civi::container()->get('spec_gatherer');
    $fields = $gatherer->getAllFields(static::getEntityName(), 'create');
    // Remove field user_contact_id and id.
    unset($fields['user_contact_id']);
    unset($fields['id']);
    // Improve the description for favorite_contact_id
    $fields['favorite_contact_id']['description'] = "Contact to add to current user's favorites";
    return $fields;
  }
}
