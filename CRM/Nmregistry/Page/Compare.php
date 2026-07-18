<?php

use CRM_Nmregistry_ExtensionUtil as E;

/**
 * Compare N contacts side-by-side for a given profile.
 *
 * Accessible at /civicrm/nmregistry/compare?cid=A,B,C,D.
 * 2-4 contacts are accepted; the columns are sorted by civicrm_contact.sort_name
 * (URL cid order is irrelevant once parsed).
 *
 * Values are rendered via CRM_Core_BAO_UFGroup::getValues(), which delegates
 * to the same per-field display logic that Civi uses for normal profile views
 * (option_value resolution, multi-value joining, Boolean Yes/No, custom-field
 * formatting, etc.). The horizontal layout is local to Compare.tpl; everything
 * else stays in lock-step with how Civi renders the same profile elsewhere.
 */
class CRM_Nmregistry_Page_Compare extends CRM_Core_Page {

  /**
   * Minimum and maximum number of contacts allowed per comparison view.
   */
  const MIN_CONTACTS = 2;
  const MAX_CONTACTS = 4;

  /**
   * The single UFGroup (profile) this view renders against. 
   */
  const PROFILE_ID = 16; // TODO: GET THIS FROM A SETTING.

  /**
   * Whether this page is being rendered in a [registry-compare] shortcode
   * context (TRUE) or as a standalone civicrm route (FALSE).
   * 
   * Shortcode to be provided by WP plugin 'jregistry'.
   *
   * Routes use <h1> for the visible page heading; shortcodes use <h2> so the
   * surrounding WordPress page can keep its own <h1>. Set by the shortcode
   * handler via setIsShortcode() before run() is called.
   */
  public $isShortcode = FALSE;

  /**
   * Setter for the isShortcode flag. Called by the [registry-compare] shortcode
   * handler in nmregistry.php before invoking run() so the template knows
   * whether to render an h1 or h2.
   */
  public function setIsShortcode($value) {
    $this->isShortcode = (bool) $value;
  }

  /**
   * Run the page.
   *
   * The page reads `cid` from \$_GET via CRM_Utils_Request. To pass
   * cids from the [registry-compare] shortcode, the handler temporarily sets
   * \$_GET['cid'] and then restores it; this run() then reads the same key.
   */
  public function run() {
    $cidQueryArg = ($_GET['cid'] ?? '');

    // Parse cid list (comma-separated); trim empties.
    $cids = array_values(array_filter(array_map(
      'intval',
      explode(',', $cidQueryArg)
    )));

    // Filter to valid (existing + not deleted) contacts before counting.
    if (!empty($cids)) {
      $cids = $this->filterValidContacts($cids);
    }

    $cidCount = count($cids);

    CRM_Utils_System::setTitle(E::ts('Compare providers'));
    $this->assign('isShortcode', $this->isShortcode);
    $this->assign('profileId', self::PROFILE_ID);

    if ($cidCount < self::MIN_CONTACTS || $cidCount > self::MAX_CONTACTS) {
      // Hand the error to the template instead of statusBounce. statusBounce
      // would attempt a header() redirect, which fails with 'headers already
      // sent' whenever this view is embedded inside a WordPress page via the
      // [registry-compare] shortcode (the ob_get_clean() has already flushed
      // output to the page). For the route path the in-body message is also
      // better UX than a redirect to civicrm's generic error page -- the user
      // stays on /compare?cid=... with a clear explanation.
      $this->assign('errorMessage', E::ts(
        'Comparison is allowed for between %1 and %2 providers. Please try again.',
        [1 => self::MIN_CONTACTS, 2 => self::MAX_CONTACTS]
      ));
      $this->assign('cids', $cids);
      $this->assign('columns', []);
      $this->assign('rows', []);
      $this->assign('showRemove', FALSE);
      parent::run();
      return;
    }

    // Existence of the profile is implied by getFields() returning a non-empty
    // array. If the profile is missing that's a system-level misconfiguration
    //  -- throw so civicrm renders a proper error page; do NOT statusBounce,
    //  since shortcode context would leak 'headers already sent'.
    $ufGroupFields = CRM_Core_BAO_UFGroup::getFields(
      self::PROFILE_ID,
      FALSE,
      CRM_Core_Action::VIEW,
      NULL,
      FALSE,
      FALSE,
      NULL,
      TRUE
    );
    if (empty($ufGroupFields)) {
      throw new \CRM_Core_Exception(E::ts(
        'Profile %1 not found or has no visible fields.',
        [1 => self::PROFILE_ID]
      ));
    }

    // Cheap batched Contact.get: just identity + display fields. The actual
    // per-cell rendering is done by UFGroup::getValues() below.
    // filterValidContacts() above already filtered to existing+not-deleted
    // rows, so this is expected to always succeed for in-bounds counts; the
    // guard below is defensive (e.g. if a race condition deletes a contact
    // between the filter call and now). statusBounce here is safe because
    // a missing-data race is genuinely an error, not a 'wrong number of
    // contact ids' user mistake.
    $contactGet = _nmregistry_civicrmapi('Contact', 'get', [
      'id' => ['IN' => $cids],
      'return' => 'id,display_name,sort_name',
      'options' => [
        'sort' => 'sort_name',
        'limit' => 0,
      ],
    ]);

    // Materialize each contact's display-shape values via Civi's own profile
    // renderer. $values is populated by reference, keyed by the field's title
    // (with collision-handling appended spaces).
    $contactValues = [];
    $columns = [];
    foreach ($contactGet['values'] as $row) {
      $cid = (int) $row['id'];
      // populate contactValues for this contact.
      $vals = [];
      CRM_Core_BAO_UFGroup::getValues($cid, $ufGroupFields, $vals);
      $contactValues[$cid] = $vals;
      
      // poulate columns for this contact
      $ufId = CRM_Core_BAO_UFMatch::getUFId($cid) ?: 0;
      $columns[] = [
        'cid' => $cid,
        'display_name' => $row['display_name'] ?? '',
        'avatar' => $ufId ? CRM_Nmregistry_Utils::get_avatar((int) $ufId, 96) : '',
      ];
      
    }

    // Build rows: one per profile field, values per column. Reconstruct the
    // same title-collision "index" key that UFGroup::getValues() used so we
    // can look up $contactValues[$cid][$index] reliably.
    $rows = [];
    $usedIndexes = [];
    foreach ($ufGroupFields as $fieldName => $field) {
      $title = $field['title'] ?? ' ';
      $index = ($title === '' ? ' ' : $title);
      while (array_key_exists($index, $usedIndexes)) {
        $index .= ' ';
      }
      $usedIndexes[$index] = TRUE;

      $row = [
        'label' => trim($title) ?: $fieldName,
        'visibility' => $field['visibility'] ?? 'Public Pages',
        'values' => [],
      ];
      foreach ($columns as $col) {
        $cid = $col['cid'];
        $val = $contactValues[$cid][$index] ?? '';
        // getValues() can leave scalar values as-is (most fields) or arrays
        // (multi-value custom fields with no VALUE_SEPARATOR join). Normalise
        // to a single human-readable string for the cell.
        $row['values'][$cid] = self::stringifyCell($val);
      }
      $rows[] = $row;
    }

    CRM_Utils_System::setTitle(E::ts('Compare providers'));

    $this->assign('profileId', self::PROFILE_ID);
    $this->assign('showRemove', count($columns) > self::MIN_CONTACTS);
    $this->assign('columns', $columns);
    $this->assign('rows', $rows);

    parent::run();
  }

  /**
   * Normalise a single cell value to a display string.
   *
   * UFGroup::getValues() usually returns a scalar (rendered label, formatted
   * date, "Yes"/"No", etc.) but for a few field shapes (notably multi-value
   * custom fields in some configs) it can return an array. Comma-join scalars
   * from arrays; pass through scalars unchanged; empty array -> ''.
   *
   * @param mixed $value
   *
   * @return string
   */
  private static function stringifyCell($value): string {
    if ($value === NULL || $value === '' || $value === []) {
      return '';
    }
    if (is_array($value)) {
      $parts = [];
      foreach ($value as $v) {
        if (is_scalar($v) && $v !== '' && $v !== NULL) {
          $parts[] = (string) $v;
        }
      }
      return implode(', ', $parts);
    }
    return (string) $value;
  }

  /**
   * Filter a list of candidate contact ids down to ones that could be shown
   * in the self::PROFILE_ID profile.
   *
   * Valid contacts are:
   *   - not deleted
   *   - a member of the group to which the profile is limited (if any)
   *
   * @param int[] $cids
   *   Candidate ids, already cast to int. Order is preserved (stable).
   *
   * @return int[]
   *   Subset of the input that exists in civicrm_contact with is_deleted=0.
   */
  private function filterValidContacts(array $cids): array {
    $ret = [];
    
    $profile = \Civi\Api4\UFGroup::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', self::PROFILE_ID)
      ->execute()
      ->first();
    $groupId = ($profile['limit_listings_group_id'] ?? NULL);

    if (empty($cids)) {
      return [];
    }
    foreach ($cids as $cid) {
      $contact = \Civi\Api4\Contact::get()
        ->setCheckPermissions(FALSE)
        ->addWhere('id', '=', $cid)
        ->addWhere('is_deleted', '=', 0);
      if ($groupId) {
        $contact->addWhere('groups', 'IN', $groupId);
      }
      $count = $contact->execute()
        ->count();
      if ($count) {
        $ret[] = (int)$cid;
      }
    }
    
    return $ret;
  }

}