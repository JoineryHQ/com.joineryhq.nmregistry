CRM.$(function($) {

  // Hide proximity Country criteria
  $('#prox_country_id').closest('tr').hide();

  if ($('div.crm-search-tasks').length) {
    // On search WITH results:
    // Hide qill but don't hide mapping.
    if ($('div#search-status i.crm-i.fa-map-marker').length) {
      $('div#search-status').after($('div#search-status i.crm-i.fa-map-marker').closest('a'));
      $('div#search-status').hide();
    }
    else {
      $('div.crm-search-tasks').hide();
    }
  }
  else if ($('div.qill').closest('div.messages.status').length) {
    // On search WITHOUT results:
    // Clear the long explanation and just say "no results found."
    var divMessagesStatus = $('div.qill').closest('div.messages.status');
    var i = divMessagesStatus.find('i.crm-i:first').clone();
    divMessagesStatus.empty();
    divMessagesStatus.append(i);
    divMessagesStatus.append('<span> ' + ts('No matches found.') + '</span>');
    // Move the now-abbreviated search results block ABOVE the search criteria form.
    $('div.crm-form-block').before(divMessagesStatus);
  }

  // Remove "contact summary overlay" column in profile search results.
  // (These are alreayd hidden by css in the them, but let's just remove them entirely here too.)
  $('div.crm-search-results > table > tbody > tr th:nth-child(1)').remove();
  $('div.crm-search-results > table > tbody > tr td:nth-child(1)').remove();
});