<?php

namespace Civi\Api4;

/**
 * API pseudo-entity for the Nmregistry extension.
 *
 * @package Civi\Api4
 */
class NmregistryFavorite extends Generic\AbstractEntity {
  const relationshipTypeId = 11; // TODO: GET THIS FROM A SETTING.

  /**
   * Every entity **must** implement `getFields`.
   *
   * This tells the action classes what input/output fields to expect,
   * and also populates the _API Explorer_.
   *
   * The `BasicGetFieldsAction` takes a callback function. We could have defined the function elsewhere
   * and passed a `callable` reference to it, but passing in an anonymous function works too.
   *
   * The callback function takes the `BasicGetFieldsAction` object as a parameter in case we need to access its properties.
   * Especially useful is the `getAction()` method as we may need to adjust the list of fields per action.
   *
   * Note that it's possible to bypass this function if an action class lists its own fields by declaring a `fields()` method.
   *
   * Read more about how to implement your own `GetFields` action:
   * @see \Civi\Api4\Generic\BasicGetFieldsAction
   *
   * @param bool $checkPermissions
   *
   * @return Generic\BasicGetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new Generic\BasicGetFieldsAction(__CLASS__, __FUNCTION__, function($getFieldsAction) {
      return [
        [
          'name' => 'contactId',
          'required' => TRUE,
          'data_type' => 'Int',
          'fk_entity' => 'Contact',
          'fk_column' => 'id',
          'input_type' => 'EntityRef',
          'description' => 'ID of contact to be favorited/unfavorited by current user',
        ],
      ];
    }))->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Generic\BasicGetAction
   */
//  public static function get($checkPermissions = TRUE) {
//    return (new Action\NmregistryFavorite\Get(__CLASS__, __FUNCTION__))
//      ->setCheckPermissions($checkPermissions);
//  }

  /**
   * @param bool $checkPermissions
   * @inheritDoc
   * @see \Civi\Api4\Generic\BasicCreateAction
   * @return Generic\BasicCreateAction
   */
  public static function create($checkPermissions = TRUE) {
    return (new Action\NmregistryFavorite\Create(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return DAODeleteAction
   */
  public static function delete($checkPermissions = TRUE) {
    return (new Action\NmregistryFavorite\Delete(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

}
