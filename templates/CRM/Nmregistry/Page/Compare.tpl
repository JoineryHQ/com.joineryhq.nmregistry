{* Compare-providers view for the NM Respite Registry. *}

<div id="nmregistry-compare">
  {if $isShortcode}<h2>{ts}Compare providers{/ts}</h2>{/if}
  <nav class="nmregistry-compare-nav">
    <a class="crm-pager-link action-item crm-hover-button" href="/provider-search/?crmRowCount=18"><i class="crm-i fa-chevron-left" role="img" aria-hidden="true"></i> {ts}Return to registry{/ts}</a>
  </nav>
  {if $errorMessage}
    {* In-body error path -- hit when the cid count is out of bounds after
       filtering to valid contacts. Replaces a previous use of statusBounce, which
       leaked 'headers already sent' warnings inside the [registry-compare]
       shortcode. The visible heading uses h1 in route context (this is the
       page's main heading) and h2 in shortcode context (let the surrounding
       WordPress page own the h1). *}
    <div class="crm-help" role="alert">
      <p>{$errorMessage}</p>
      <p>
        <a href="#" onclick="event.preventDefault(); history.back(); return false;"
           class="crm-pager-link action-item crm-hover-button"
           title="{ts}Go back to the previous page{/ts}">
          <i class="crm-i fa-chevron-left" role="img" aria-hidden="true"></i>
          {ts}Back to previous page{/ts}
        </a>
      </p>
    </div>
  {else}
    <div class="nmregistry-compare-table-wrap">
      <table class="crm-table nmregistry-compare-table" aria-label="{ts}Compare providers{/ts}">
        <thead>
          <tr>
            <th class="nmregistry-compare-corner"></th>
            {foreach from=$columns item=col}
              <th class="nmregistry-compare-header" data-cid="{$col.cid}">
                <div class="nmregistry-compare-avatar">{$col.avatar}</div>
                <div class="nmregistry-compare-name">{$col.display_name}</div>
                <div class="nmregistry-compare-actions">
                  <a class="crm-pager-link action-item crm-hover-button nmregistry-compare-view" href="{crmURL p="civicrm/profile/view" q="reset=1&id=`$col.cid`&gid=`$profileId`"}" title="{ts}View this provider's profile{/ts}"><i class="crm-i fa-expand" role="img" aria-hidden="true"></i> {ts}View{/ts}</a>
                  {if $showRemove}
                    <a class="crm-pager-link action-item crm-hover-button nmregistry-compare-remove" href="#" data-cid="{$col.cid}" title="{ts 1=$col.display_name}Remove %1 from this comparison{/ts}"><i class="crm-i fa-x" role="img" aria-hidden="true"></i> {ts}Remove{/ts}</a>
                  {/if}
                </div>
              </th>
            {/foreach}
          </tr>
        </thead>
        <tbody>
          {foreach from=$rows item=row}
            <tr class="nmregistry-compare-row {cycle values="odd-row,even-row"} nmregistry-visibility-{$row.visibility|replace:' ':'-'|lower}">
              <th scope="row" class="nmregistry-compare-label">{$row.label}</th>
              {foreach from=$columns item=col}
                <td class="nmregistry-compare-value" data-cid="{$col.cid}">{$row.values[$col.cid]}</td>
              {/foreach}
            </tr>
          {/foreach}
        </tbody>
        {* Duplicate View + Remove actions at the bottom of each column so users
           who scroll down don't have to scroll back up to take action. TODO: this is duplicate code from earlier in the template. *}
        <tfoot>
          <tr>
            <td class="nmregistry-compare-corner"></td>
            {foreach from=$columns item=col}
              <td class="nmregistry-compare-footer" data-cid="{$col.cid}">
                <div class="nmregistry-compare-actions">
                  <a class="crm-pager-link action-item crm-hover-button nmregistry-compare-view" href="{crmURL p="civicrm/profile/view" q="reset=1&id=`$col.cid`&gid=`$profileId`"}" title="{ts}View this provider's profile{/ts}"><i class="crm-i fa-expand" role="img" aria-hidden="true"></i> {ts}View{/ts}</a>
                  {if $showRemove}
                    <a class="crm-pager-link action-item crm-hover-button nmregistry-compare-remove" href="#" data-cid="{$col.cid}" title="{ts 1=$col.display_name}Remove %1 from this comparison{/ts}"><i class="crm-i fa-x" role="img" aria-hidden="true"></i> {ts}Remove{/ts}</a>
                  {/if}
                </div>
              </td>
            {/foreach}
          </tr>
        </tfoot>
      </table>
    </div>
  {/if}

</div>

{* TODO: I'd rather have this css and js in separate files, but need to test how
 * well that works with the shortcode path.
 *}
<style type="text/css">
{literal}
  #nmregistry-compare .nmregistry-compare-nav {
    margin: 0 0 1em 0;
  }
  #nmregistry-compare .nmregistry-compare-nav a {
    text-decoration: none;
  }
  #nmregistry-compare .nmregistry-compare-table {
    width: 100%;
    table-layout: auto;
  }
  #nmregistry-compare .nmregistry-compare-corner {
    background-color: #f6f6f6;
  }
  #nmregistry-compare .nmregistry-compare-header {
    text-align: center;
    vertical-align: top;
    min-width: 180px;
  }
  #nmregistry-compare .nmregistry-compare-footer {
    text-align: center;
    vertical-align: bottom;
    padding-top: .5em;
  }
  #nmregistry-compare .nmregistry-compare-avatar {
    margin-bottom: .5em;
  }
  #nmregistry-compare .nmregistry-compare-name {
    font-weight: 600;
    margin-bottom: .5em;
  }
  #nmregistry-compare .nmregistry-compare-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .3em;
    justify-content: center;
  }
  #nmregistry-compare .nmregistry-compare-actions .crm-pager-link {
    margin: 0;
  }
  #nmregistry-compare .nmregistry-compare-label {
    text-align: left;
    vertical-align: top;
    width: 30%;
    font-weight: 500;
  }
  #nmregistry-compare .nmregistry-compare-value {
    text-align: left;
    vertical-align: top;
  }
  /* Subtle highlight for fields with restricted visibility (audit-only). */
  #nmregistry-compare .nmregistry-visibility-user-and-user-admin-only .nmregistry-compare-label::after {
    content: " *";
    color: #999;
    font-weight: 400;
  }
{/literal}
</style>

<script type="text/javascript">
{literal}
(function () {
  // Hide a column (header + all body cells + footer cell) when its Remove
  // button is clicked. No page reload; the URL is not updated.
  // The minimum visible column count is MIN_CONTACTS (2); once removal would
  // drop below that, all remaining Remove buttons are hidden.
  var root = document.getElementById('nmregistry-compare');
  if (!root) return;
  var MIN_CONTACTS = 2;
  root.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('.nmregistry-compare-remove');
    if (!btn) return;
    e.preventDefault();
    var cid = btn.getAttribute('data-cid');
    if (!cid) return;
    var nodes = root.querySelectorAll('[data-cid="' + cid + '"]');
    for (var i = 0; i < nodes.length; i++) {
      nodes[i].style.display = 'none';
    }
    var visibleHeaders = Array.prototype.filter.call(
      root.querySelectorAll('.nmregistry-compare-header[data-cid]'),
      function (el) { return el.style.display !== 'none'; }
    );
    var showRemove = visibleHeaders.length > MIN_CONTACTS;
    var removeBtns = root.querySelectorAll('.nmregistry-compare-remove');
    for (var j = 0; j < removeBtns.length; j++) {
      removeBtns[j].style.display = showRemove ? '' : 'none';
    }
  });
})();
{/literal}
</script>