CRM.$(function($) {
  
  // This page may have some pre-help text inserted in the WP post (above the [civicrm] shortcode).
  // Just in case, move our civicrm profile title above anything else int the article.
  $('div.crm-title').prependTo($('article'));
});