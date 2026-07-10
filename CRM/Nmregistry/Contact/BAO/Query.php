<?php

/**
 * "Query Object" class. This is the only way I could find to alter the rows
 * returned in a profile search.
 * 
 * This allows us to re-use the primary Registry Search profile for an alternate
 * "only show my favorites" display, when the url query parameter registryFavorites=1
 * is set.
 */
class CRM_Nmregistry_Contact_BAO_Query extends CRM_Contact_BAO_Query_Interface {

  public static function where(&$query) {
    // Only if mode is 'contacts' and we haven't done this already.
    if (
      $query->_mode != CRM_Contact_BAO_Query::MODE_CONTACTS
      || Civi::$statics['NMREGISTRY_LIMIT_FAVORITES_DONE']
    ) {
      return;
    }
    
    // Also, only if we're on a page that requests the 'favorites' view of the registry.
    $q = CRM_Utils_Request::retrieve('q', 'String', FALSE, NULL);
    $gid = CRM_Utils_Request::retrieve('gid', 'Int', FALSE, NULL);
    $registryFavorites = CRM_Utils_Request::retrieve('registryFavorites', 'Int', FALSE, NULL);
    if (
      $q != 'civicrm/profile'
      || $gid != 16 // TODO: GET THIS FROM A SETTING.
      || $registryFavorites != 1
    ) {
      return;
    }
    
    // Also, only if this query is attempting to limit to group 2 (which is what
    // the registry profile does).
    $paramTestParts = [];
    $paramTestParts[] = ($query->_params[0][0] ?? '');
    $paramTestParts[] = ($query->_params[0][1] ?? '');
    if (is_array($query->_params[0][2])) {
      $paramTestParts[] = implode(',', ($query->_params[0][2] ?? []));
    }
    $paramTestString = implode(' ', $paramTestParts);
    if ($paramTestString != "group IN 2") {
      return;
    }
    
    // If we're still here, we'll limit to "my favorites" only.
    // Define a subquery we can insert into the WHERE clauses.
    $subQuerySql = "
      SELECT favorite_contact_id
      FROM civicrm_nmregistry_favorite
      WHERE user_contact_id = %1
    ";
    $subQueryParams = [
      '1' => [CRM_Core_Session::getLoggedInContactID(), 'Int'],
    ];
    $subQuery = CRM_Core_DAO::composeQuery($subQuerySql, $subQueryParams);
    // Append our subquery to the WHERE clauses.
    $query->_where[] = ["(contact_a.id IN ($subQuery))"];
    // Note that we've done this one, no need to do it again this run.
    Civi::$statics['NMREGISTRY_LIMIT_FAVORITES_DONE'] = TRUE;
  }

  /*
   * The remaining methods in this class are placeholders and do nothing, but
   * many are required to exist. CiviCRM's "Query Object" model is scantily documented
   * and various places in civcirm core expect methods to exist even if they're
   * not mentioned in CRM_Contact_BAO_Query_Interface. This has been learned by
   * trial-and-error, and by borrowing from CiviHR (which is quite dated and seems
   * to have been one of the more notable users of the Query Object interface) .
   */
  function &getFields() {return [];}
  public static function from($name, $mode, $side) {}
  public static function select(&$query) {}
  function setTableDependency(&$tables) {}
  public function registerAdvancedSearchPane(&$panes) {}
  public function getPanesMapper(&$panes) {}
  public function buildAdvancedSearchPaneForm(&$form, $type) {}
  public function setAdvancedSearchPaneTemplatePath(&$paneTemplatePathArray, $type) {}
  public function alterSearchBuilderOptions(&$apiEntities, &$fieldOptions) {}
}
