<?php

// phpcs:disable
use CRM_Nmregistry_ExtensionUtil as E;
// phpcs:enable

class CRM_Nmregistry_Utils_Markup {

  /**
   * Generate standard markup for the 'favorite' control.
   *
   * @param bool $state Format the control as 'on' or 'off'?
   * @param int $providerCid ContactId of Provider to be favorited/unfavorited.
   * @param bool $placeholder Should this be a non-functional control?
   *
   * @return string
   */
  public static function favoriteControl($state, $providerCid, $placeholder = FALSE) {
    if ($placeholder) {
      $primaryClass = 'nmregistry-favorite-placeholder';
      $title = 'Favoriting not available in preview.';
      $faClass = 'fa-solid';
      $data = '1';
    }
    else {
      $primaryClass = 'nmregistry-favorite';
      if ($state) {
        $faClass = 'fa-solid';
        $data = '1';
      }
      else {
        $faClass = 'fa-regular';
        $data = '0';
      }
    }
    return '<i class="' . $primaryClass . ' crm-i fa-heart ' . $faClass . '" data-is-favorited="' . $data . '" data-provider-cid="' . $providerCid . '" title="' . $title . '"></i>';
  }

  /**
   * Generate standard markup for the 'compare' control.
   *
   * @param int $providerCid ContactId of Provider to be favorited/unfavorited.
   * 
   * @return string
   */
  public static function compareControl(int $providerCid, string $providerName) {
    $providerName = htmlspecialchars($providerName);
    
    return '
        <label class="nmregistry-compare-label"><input type="checkbox" class="nmregistry-compare-checkbox" data-cid="' . $providerCid .'" data-name="' . $providerName .'" aria-label="Add to comparison"><span class="nmregistry-compare-label-text">Compare</span></label>
      ';
  }

  public static function compareTray() {
    return '
      <div class="nmregistry-compare-tray is-visible" role="region" aria-label="Comparison selection" aria-hidden="false">
        <div class="nmregistry-compare-tray-inner">
          <div class="nmregistry-compare-tray-entries" aria-live="polite"></div>
          <div class="nmregistry-compare-tray-actions">
            <span class="nmregistry-compare-tray-count" aria-live="polite"></span>
            <button type="button" class="crm-pager-link action-item crm-hover-button nmregistry-compare-clear">' . E::ts('Clear') . '</button>
            <button type="button" class="crm-pager-link action-item crm-hover-button nmregistry-compare-go">' . E::ts('Compare') . '</button>
          </div>
        </div>
      </div>
    ';
  }

}