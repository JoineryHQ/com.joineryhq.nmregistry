CRM.$(function($) {
  
  // Hide qill but don't hide mapping.
  $('div#search-status').after($('div#search-status i.crm-i.fa-map-marker').closest('a'));
  $('div#search-status').hide();
});