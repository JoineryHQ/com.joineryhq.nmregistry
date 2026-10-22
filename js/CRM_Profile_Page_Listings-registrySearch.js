CRM.$(function($) {

  // Message listener (for message from popup iframes)
  var listenMessage = function listenMessage(e) {
    if (e.origin !== window.location.origin) {
      // The iframe is not our site, so ignore the message.
      return;
    }

    if (e.data.type === 'isFavoriteChanged') {
      // Message is about a favorited-status chagne.

      var elFavoriteControl = $('.nmregistry-favorite[data-provider-cid=' + e.data.providerCid + ']');

      if (e.data.isFavorite) {
        elFavoriteControl.data('is-favorited', 1);
        elFavoriteControl.addClass('fa-solid');
        elFavoriteControl.removeClass('fa-regular');
      }
      else {
        elFavoriteControl.data('is-favorited', 0);
        elFavoriteControl.addClass('fa-regular');
        elFavoriteControl.removeClass('fa-solid');
      }
    }
  }

  // Remove ajax-driven click handlers in results pager.
  // TODO: This is needed in order to preserve popup behavior (which gets broken
  // under ajax paging), but it has the effect of requiring 'user strike enter'
  // on civicrm "page N of M" pager controls; perhaps better to just hide those controls?
  var formSelector = ('form.' + CRM.vars.nmregistry.formClass || '#crm-main-content-wrapper');
  var $form = $(formSelector);
  $form.off('.crm-pager');

  // Remove "contacts" label in pager (confusing for UX)
  var pagerLabel = $('.crm-pager').first().find('.crm-pager-nav').last();
  if (pagerLabel.length) {
    // Only do this if we actually have a pager label (which doesn't exist on short search results).
    pagerLabel.html(pagerLabel.html().replace(/\sContact\s/, ''));
  }

  // Hide proximity Country criteria in form.
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

  // Expose 'favorites' if that feature is enabled.
  if (CRM.vars.nmregistry.userCanFavorite) {
    // If user can favorite/un-favorite:
    // Add a button to toggle between 'My Favorites' and full registry.
    if (CRM.vars.nmregistry.isFavorites) {
      $('div.crm-title').append('<a class="crm-pager-link action-item crm-hover-button" href="/provider-search/?crmRowCount=18">All Providers</a>');
      // Also hide the search criteria -- My Favorites is not searchable.
      CRM.$('.crm-form-block').remove();
    }
    else {
      $('div.crm-title').append('<a class="crm-pager-link action-item crm-hover-button" href="/provider-search/?crmRowCount=18&registryFavorites=1">My Favorites</a>');
    }
  }
  
  // Modify 'Edit Search Criteria' to something more obvious
  // (many people haven't even searched yet.)
  $('form.CRM_Profile_Form_Search div.crm-form-block summary').each(function(idx, el){
    var jqEl = $(el);
    if(jqEl.html().trim() == ts('Edit Search Criteria')) {
      jqEl.html('Filter listings ...');
    }
  });

  // Define a listener for messages from child pages (iframes).
  window.addEventListener('message', listenMessage);
  
});