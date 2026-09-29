/*
 * Compare-providers affordances for the registry listing page.
 *
 * See joc1-tickets#23. Composes with the existing compare-providers view
 * (CRM/Nmregistry/Page/Compare.php) at /civicrm/nmregistry/compare?cid=...
 * which already enforces MIN_CONTACTS=2 and MAX_CONTACTS=4. This file:
 *
 *   - injects a "Compare" checkbox into each provider row (cellIndex 6,
 *     before the existing View cell);
 *   - injects a sticky-bottom "Compare tray" that shows the current
 *     selection count and a Compare CTA;
 *   - keeps all state in memory (no localStorage, no cookies)
 *   - hard-locks at MAX_SELECTIONS=4 selections — no more boxes can be
 *     checked, and the tray shows a "Maximum 4 selected" indicator;
 *   - Compare CTA is disabled below MIN_SELECTIONS=2 (button label still
 *     pluralizes: "Compare 1 provider" / "Compare N providers");
 *   - Clear button and per-entry X markers remove from the selection.
 *
 * Wire-up: nmregistry_civicrm_pageRun() loads this file on
 * CRM_Profile_Page_Listings + gid=16 (same as the existing
 * CRM_Profile_Page_Listings-registrySearch.js).
 */
CRM.$(function($) {

  var MIN_SELECTIONS = CRM.vars.nmregistry.compare_min_contacts;
  var MAX_SELECTIONS = CRM.vars.nmregistry.compare_max_contacts;
  var COMPARE_ROUTE = '/civicrm/nmregistry/compare';

  // ---------- Selection state (in-memory only) ----------
  // Stored as an ordered array of { cid, name }. Order matches check order,
  // not URL or sort order. The compare route sorts by sort_name on its own.
  var selection = [];

  function adjustLayout() {
    $('td.crm-nrmregistry-compare').each(function(idx, el) {      
      td = $(el);
      tr = td.closest('tr')
      // Per css, compare td is position-absolute, so its parent won't make
      // room for it, therefore it's likely to overlay the boundary box, or at
      // least to be cramped. Calculate its height and increase parent padding-bottom
      // accordingly.
      var tdHeight =  td[0].getBoundingClientRect().height;
      var newPaddingBottom = tdHeight + 'px';
      tr.css({'padding-bottom': newPaddingBottom});
      
      // data.'crmInitialValue' is used to check if the form is dirty, but we
      // don't want that for these checkboxes.
      td.find('input[type="checkbox"]').removeData('crmInitialValue');
    });

    return true;
  }

  // ---------- Sticky-bottom tray ----------
  function buildTray() {
    $('body').append(CRM.vars.nmregistry.compareTrayHtml);
  }

  // ---------- Rendering ----------
  // Re-renders the tray from the current selection array. Called on every
  // checkbox change. Keeps the DOM in lock-step with selection[].
  function render() {
    var tray = $('div.nmregistry-compare-tray');
    if (!tray.length) {
      return;
    }
    var count = selection.length;

    var trayEntries = tray.find('.nmregistry-compare-tray-entries');
    console.log('trayEntries', trayEntries);
    if (count === 0) {
      tray.attr('aria-hidden', 'true');
      tray.removeClass('is-visible');
      // Clear any stale entry chips so the DOM doesn't accumulate ghost
      // children between selections.
      if (!trayEntries.length) {
        trayEntries.empty();
      }
      return;
    }

    tray.attr('aria-hidden', 'false');
    tray.addClass('is-visible');

    // Entries: one chip per selected provider, with X to remove.
    trayEntries.empty();
    selection.forEach(function(entry) {
      var chip = $('<span class="nmregistry-compare-tray-entry"></span>').appendTo(trayEntries);
      chip.attr('data-cid', String(entry.cid));

      var name = $('<span class="nmregistry-compare-tray-entry-name"></span>').appendTo(chip);
      name.html(entry.name || ('#' + entry.cid));

      var remove = $('<button class="nmregistry-compare-tray-entry-x"></button>').appendTo(chip);
      remove.attr('data-cid', String(entry.cid));
      remove.attr('aria-label', ts('Remove %1 from comparison', { 1: entry.name || ('#' + entry.cid) }));
      remove.html('\u00d7'); // multiplication sign ×, matches fa-x glyph
    });


    // Count + Compare button label + enabled/disabled state.
    var countEl = tray.find('.nmregistry-compare-tray-count');
    console.log('countEl', countEl);
    var goBtn = tray.find('.nmregistry-compare-go');

    if (count >= MAX_SELECTIONS) {
      countEl.html(ts('Maximum %1 selected — deselect one to choose a different provider', { 1: MAX_SELECTIONS }));
    } else {
      countEl.html(ts('%1 of %2–%3 providers selected', {
        1: count,
        2: MIN_SELECTIONS,
        3: MAX_SELECTIONS,
      }));
    }

    // Pluralize "Compare N provider(s)".
    // The button label is "Compare" + count text
    goBtn.html(ts('Compare %1 %2', {
      1: count,
      2: (count > 1 ? 'providers' : 'provider')
    }));

    goBtn.attr('disabled', (count < MIN_SELECTIONS));
  }

  // ---------- State mutators ----------
  function addToSelection(cid, name) {
    cid = parseInt(cid, 10);
    if (!cid) {
      return;
    }
    if (selection.length >= MAX_SELECTIONS) {
      // Hard lock: refuse further adds.
      return;
    }
    // De-dup by cid.
    for (var i = 0; i < selection.length; i++) {
      if (selection[i].cid === cid) {
        return;
      }
    }
    selection.push({ cid: cid, name: name || '' });
  }

  function removeFromSelection(cid) {
    cid = parseInt(cid, 10);
    selection = selection.filter(function(e) { return e.cid !== cid; });
  }

  function isSelected(cid) {
    cid = parseInt(cid, 10);
    for (var i = 0; i < selection.length; i++) {
      if (selection[i].cid === cid) {
        return true;
      }
    }
    return false;
  }

  // ---------- Event wiring ----------
  function wireEvents() {
    var resultsTable = $('.crm-search-results table');
    if (!resultsTable.length) {
      return;
    }

    // Checkbox change (delegated, so re-renders don't have to re-bind).
    resultsTable.on('change', function(e) {
      var checkbox = e.target.closest('.nmregistry-compare-checkbox');
      if (!checkbox) {
        return;
      }
      var cid = parseInt(checkbox.getAttribute('data-cid'), 10);
      var name = checkbox.getAttribute('data-name') || '';

      if (checkbox.checked) {
        if (selection.length >= MAX_SELECTIONS) {
          // Hard lock: snap the box back to unchecked.
          checkbox.checked = false;
          return;
        }
        addToSelection(cid, name);
      } else {
        removeFromSelection(cid);
      }
      render();
    });

    // Click on the label/icon/text should not bubble to the card's
    // whole-row click handler (which navigates to the provider profile via
    // the View overlay). The default action -- toggling the checkbox --
    // must still happen, so we only stopPropagation here, never
    // preventDefault. (preventDefault on a click event suppresses the
    // checkbox's default toggle, which is why the click appeared to do
    // nothing in early testing.) The View overlay <a> lives in a different
    // cell, so it does not receive this click event regardless.
    resultsTable.on('click', function(e) {
      var lbl = e.target.closest('.nmregistry-compare-label');
      if (lbl) {
        e.stopPropagation();
      }
    });

    // Tray actions (delegated on the tray itself).
    var tray = $('.nmregistry-compare-tray');
    if (tray) {
      tray.on('click', function(e) {
        // Per-entry X: remove one.
        var xBtn = e.target.closest('.nmregistry-compare-tray-entry-x');
        if (xBtn) {
          e.preventDefault();
          var cid = parseInt(xBtn.getAttribute('data-cid'), 10);
          removeFromSelection(cid);
          // Sync the checkbox in the row.
          var cb = resultsTable.find('.nmregistry-compare-checkbox[data-cid="' + cid + '"]');
          if (cb.length) {
            cb.attr('checked', false);
          }
          render();
          return;
        }

        // Clear: drop all.
        if (e.target.closest('.nmregistry-compare-clear')) {
          e.preventDefault();
          selection = [];
          // Uncheck all boxes in the table.
          resultsTable.find('.nmregistry-compare-checkbox').each(function(idx, el) {
            $(el).attr('checked', false);
          });
          render();
          return;
        }

        // Compare CTA: navigate.
        if (e.target.closest('.nmregistry-compare-go')) {
          e.preventDefault();
          if (selection.length < MIN_SELECTIONS) {
            return;
          }
          var cids = selection.map(function(e) { return e.cid; }).join(',');
          window.location.href = COMPARE_ROUTE + '?cid=' + cids;
          return;
        }
      });
    }
  }

  // ---------- Bootstrap ----------
  // The listing page may render its results via ajax (e.g. after a search
  // criteria submit), so we re-run injection on DOM mutations under the
  // results container. Cheap because injectCompareCells is idempotent.
  function bootstrap() {
    adjustLayout();
    if (!$('.nmregistry-compare-tray').length) {
      buildTray();
    }
    wireEvents();
    render();
  }

  bootstrap();

  // Watch for ajax re-renders of the search results (e.g. paging, sorting,
  // search submit) and re-bootstrap. The existing JS file in this directory
  // also relies on the results container, so we share its selector.
  var resultsContainer = $('.crm-search-results');
  if (resultsContainer.length && typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function() {
      // Debounce: only act if our cells are missing.
      if (!$('.nmregistry-compare').length) {
        bootstrap();
      }
    });
    observer.observe(resultsContainer[0], { childList: true, subtree: true });
  }

});
