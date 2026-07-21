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

  var MIN_SELECTIONS = 2;
  var MAX_SELECTIONS = 4;
  var COMPARE_ROUTE = '/civicrm/nmregistry/compare';

  // ts() is provided by civicrm's translation shim. Wrap so we degrade
  // gracefully if it isn't loaded yet (e.g. during early page execution).
  var ts = (typeof window.ts === 'function') ? window.ts : function(s) { return s; };

  // ---------- Selection state (in-memory only) ----------
  // Stored as an ordered array of { cid, name }. Order matches check order,
  // not URL or sort order. The compare route sorts by sort_name on its own.
  var selection = [];

  // ---------- DOM injection ----------
  // Add a new <th> to the header row + a new <td> to each data row, both
  // carrying the .nmregistry-compare-cell class. The new cell sits at
  // cellIndex=6 (just before the existing View cell at cellIndex=7) so the
  // existing CSS rule for `tbody td:last-child` still applies to the View
  // overlay.
  function injectCompareCells() {
    var resultsTable = document.querySelector('.crm-search-results table');
    if (!resultsTable) {
      return false;
    }

    // Header: insert a new <th> at the end of each header row. The
    // header row is hidden by CSS (display: none on tr.columnheader), so
    // this is purely for semantic completeness.
    var headerRow = resultsTable.querySelector('thead tr') || resultsTable.querySelector('tr.columnheader');
    if (headerRow && !headerRow.querySelector('.nmregistry-compare-cell')) {
      var th = document.createElement('th');
      th.className = 'nmregistry-compare-cell';
      th.scope = 'col';
      th.textContent = ts('Compare');
      headerRow.appendChild(th);
    }

    // Data rows: one new <td> per row, before the View cell.
    var rows = resultsTable.querySelectorAll('tbody tr');
    rows.forEach(function(row) {
      if (row.classList.contains('columnheader')) {
        // Skip the hidden header row that some templates put inside tbody.
        return;
      }
      if (row.querySelector('td.nmregistry-compare-cell')) {
        // Already injected.
        return;
      }
      // Find the row's contact id by walking the favorite icon's data attr.
      // This is the same source the existing favorites JS uses, so the two
      // features stay in sync.
      var fav = row.querySelector('.nmregistry-favorite[data-provider-cid]');
      if (!fav) {
        return;
      }
      var cid = parseInt(fav.getAttribute('data-provider-cid'), 10);
      if (!cid) {
        return;
      }
      // The provider name lives in the crm-sort_name cell (cellIndex=1).
      // We pull its text content (the favorite <i> has no text, so this is
      // just the display name).
      var nameCell = row.querySelector('.crm-sort_name') || row.cells[1];
      var name = nameCell ? nameCell.textContent.trim() : '';

      var td = document.createElement('td');
      td.className = 'nmregistry-compare-cell';
      td.setAttribute('data-cid', String(cid));
      td.setAttribute('data-name', name);

      // Build the labeled control. <label> wraps the checkbox + icon + text
      // so the click target is the whole label, not just the small box.
      var label = document.createElement('label');
      label.className = 'nmregistry-compare-label';

      var checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.className = 'nmregistry-compare-checkbox';
      checkbox.setAttribute('data-cid', String(cid));
      checkbox.setAttribute('data-name', name);
      checkbox.setAttribute('aria-label', ts('Add to comparison'));

      var icon = document.createElement('i');
      // Optional: define className empty to avoid placing an icon at all.
      icon.className = ''; // e.g. 'crm-i fa-balance-scale'
      icon.setAttribute('aria-hidden', 'true');

      var labelText = document.createElement('span');
      labelText.className = 'nmregistry-compare-label-text';
      labelText.textContent = ts('Compare');

      label.appendChild(checkbox);
      if (icon.className.length) {
        // Only if i has a classname, append it. (If no classname, it won't display
        // an icon anyway.)
        label.appendChild(icon);
      }
      label.appendChild(labelText);
      td.appendChild(label);

      // insert the compare-cell td.
      row.appendChild(td);
      
      // Per css, compare-cell td is position-absolute, so its parent won't make
      // room for it, therefore it's likely to overlay the boundary box, or at
      // least to be cramped. Calculate its height and increase parent padding-bottom
      // accordingly.
      var tdHeight = td.getBoundingClientRect().height;
      var originalPaddingBottom = $(row).css('padding-bottom');
      var newPaddingBottom = 'calc(' + originalPaddingBottom + ' + ' + tdHeight + 'px)';
      var newPaddingBottom = tdHeight + 'px';
      $(row).css({'padding-bottom': newPaddingBottom});
      console.log($(row).css('padding-bottom'), newPaddingBottom);
      
    });

    return true;
  }

  // ---------- Sticky-bottom tray ----------
  function buildTray() {
    var tray = document.createElement('div');
    tray.className = 'nmregistry-compare-tray';
    tray.setAttribute('role', 'region');
    tray.setAttribute('aria-label', ts('Comparison selection'));
    tray.setAttribute('aria-hidden', 'true');

    tray.innerHTML = ''
      + '<div class="nmregistry-compare-tray-inner">'
      + '  <div class="nmregistry-compare-tray-entries" aria-live="polite"></div>'
      + '  <div class="nmregistry-compare-tray-actions">'
      + '    <span class="nmregistry-compare-tray-count" aria-live="polite"></span>'
      + '    <button type="button" class="crm-pager-link action-item crm-hover-button nmregistry-compare-clear">'
      + ts('Clear') + '</button>'
      + '    <button type="button" class="crm-pager-link action-item crm-hover-button nmregistry-compare-go">'
      + ts('Compare') + '</button>'
      + '  </div>'
      + '</div>';

    document.body.appendChild(tray);
    return tray;
  }

  // ---------- Rendering ----------
  // Re-renders the tray from the current selection array. Called on every
  // checkbox change. Keeps the DOM in lock-step with selection[].
  function render() {
    var tray = document.querySelector('.nmregistry-compare-tray');
    if (!tray) {
      return;
    }
    var count = selection.length;

    if (count === 0) {
      tray.setAttribute('aria-hidden', 'true');
      tray.classList.remove('is-visible');
      // Clear any stale entry chips so the DOM doesn't accumulate ghost
      // children between selections.
      var emptyEntriesEl = tray.querySelector('.nmregistry-compare-tray-entries');
      if (emptyEntriesEl) {
        emptyEntriesEl.innerHTML = '';
      }
      return;
    }

    tray.setAttribute('aria-hidden', 'false');
    tray.classList.add('is-visible');

    // Entries: one chip per selected provider, with X to remove.
    var entriesEl = tray.querySelector('.nmregistry-compare-tray-entries');
    entriesEl.innerHTML = '';
    selection.forEach(function(entry) {
      var chip = document.createElement('span');
      chip.className = 'nmregistry-compare-tray-entry';
      chip.setAttribute('data-cid', String(entry.cid));

      var name = document.createElement('span');
      name.className = 'nmregistry-compare-tray-entry-name';
      name.textContent = entry.name || ('#' + entry.cid);

      var remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'nmregistry-compare-tray-entry-x';
      remove.setAttribute('data-cid', String(entry.cid));
      remove.setAttribute('aria-label', ts('Remove %1 from comparison', { 1: entry.name || ('#' + entry.cid) }));
      remove.textContent = '\u00d7'; // multiplication sign ×, matches fa-x glyph

      chip.appendChild(name);
      chip.appendChild(remove);
      entriesEl.appendChild(chip);
    });

    // Count + Compare button label + enabled/disabled state.
    var countEl = tray.querySelector('.nmregistry-compare-tray-count');
    var goBtn = tray.querySelector('.nmregistry-compare-go');

    if (count >= MAX_SELECTIONS) {
      countEl.textContent = ts('Maximum %1 selected — deselect one to choose a different provider', { 1: MAX_SELECTIONS });
    } else {
      countEl.textContent = ts('%1 of %2–%3 providers selected', {
        1: count,
        2: MIN_SELECTIONS,
        3: MAX_SELECTIONS,
      });
    }

    // Pluralize "Compare N provider(s)".
    var labelText = '';
    // Prepend an icon to the label.
    // labelText = '<i class="crm-i fa-balance-scale" aria-hidden="true"></i> '
    var labelText = labelText + ts('Compare %1 providers', { 1: count });
    
    // The button label is "Compare" + count text. Keep the icon visible too.
    goBtn.innerHTML = labelText;

    goBtn.disabled = (count < MIN_SELECTIONS);
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
    var resultsTable = document.querySelector('.crm-search-results table');
    if (!resultsTable) {
      return;
    }

    // Checkbox change (delegated, so re-renders don't have to re-bind).
    resultsTable.addEventListener('change', function(e) {
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
    resultsTable.addEventListener('click', function(e) {
      var lbl = e.target.closest('.nmregistry-compare-label');
      if (lbl) {
        e.stopPropagation();
      }
    });

    // Tray actions (delegated on the tray itself).
    var tray = document.querySelector('.nmregistry-compare-tray');
    if (tray) {
      tray.addEventListener('click', function(e) {
        // Per-entry X: remove one.
        var xBtn = e.target.closest('.nmregistry-compare-tray-entry-x');
        if (xBtn) {
          e.preventDefault();
          var cid = parseInt(xBtn.getAttribute('data-cid'), 10);
          removeFromSelection(cid);
          // Sync the checkbox in the row.
          var cb = resultsTable.querySelector('.nmregistry-compare-checkbox[data-cid="' + cid + '"]');
          if (cb) {
            cb.checked = false;
          }
          render();
          return;
        }

        // Clear: drop all.
        if (e.target.closest('.nmregistry-compare-clear')) {
          e.preventDefault();
          selection = [];
          // Uncheck all boxes in the table.
          resultsTable.querySelectorAll('.nmregistry-compare-checkbox').forEach(function(cb) {
            cb.checked = false;
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
    if (!injectCompareCells()) {
      return;
    }
    if (!document.querySelector('.nmregistry-compare-tray')) {
      buildTray();
    }
    wireEvents();
    render();
  }

  bootstrap();

  // Watch for ajax re-renders of the search results (e.g. paging, sorting,
  // search submit) and re-bootstrap. The existing JS file in this directory
  // also relies on the results container, so we share its selector.
  var resultsContainer = document.querySelector('.crm-search-results');
  if (resultsContainer && typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function() {
      // Debounce: only act if our cells are missing.
      if (!document.querySelector('.nmregistry-compare-cell')) {
        bootstrap();
      }
    });
    observer.observe(resultsContainer, { childList: true, subtree: true });
  }

});
