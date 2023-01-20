CRM.$(function($) {

  // Hide qill but don't hide mapping.
  $('div#search-status').after($('div#search-status i.crm-i.fa-map-marker').closest('a'));
  $('div#search-status').hide();

  // Remove "contact summary overlay" column in profile search results.
  // (These are alreayd hidden by css in the them, but let's just remove them entirely here too.)
  $('div.crm-search-results > table > tbody > tr th:first').remove();
  $('div.crm-search-results > table > tbody > tr td:first').remove();
});