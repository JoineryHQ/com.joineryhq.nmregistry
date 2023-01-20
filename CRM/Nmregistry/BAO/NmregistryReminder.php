<?php
// phpcs:disable
use CRM_Nmregistry_ExtensionUtil as E;
// phpcs:enable

class CRM_Nmregistry_BAO_NmregistryReminder extends CRM_Nmregistry_DAO_NmregistryReminder {

  /**
   * Create a new NmregistryReminder based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Nmregistry_DAO_NmregistryReminder|NULL
   */
  /*
  public static function create($params) {
    $className = 'CRM_Nmregistry_DAO_NmregistryReminder';
    $entityName = 'NmregistryReminder';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
  */

}
