<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

class CRM_Nmregistry_Utils_Markup {

  /**
   * Generate standard markup for the 'favorite' control.
   *
   * @param bool $state Format the control as 'on' or 'off'?
   * @param int $providerCid ContactId of Provider to be favorited/unfavorited.
   * @param bool $placeholder Should this be a non-functional control?
   * @return type
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
}