
<div id="nmregistry-profile-view">
  <div id="nmregistry-profile-view-header">
    <div id="nmregistry-user-avatar">{$nmregistryUserAvatar}</div>
    {if $isBackgroundCheckGood}
        {capture assign=badgeSrc}{crmResURL ext="nmregistry" file="img"}{/capture}
        <a class="fancybox-iframe" title="About Background Checks" href="{$backgroundCheckInfoUrl}?lightbox=1"  id="nmregistry-profile-background-check-badge-wrapper">
        <img id="nmregistry-profile-background-check-badge" src="{$badgeSrc}/bc-badge.png">
        <img id="nmregistry-profile-background-check-badge-help" src="{$badgeSrc}/help.png"></a>
    {/if}
  </div>

  <div id="nmregistry-intro-text" class="help">{$nmregistryIntroText}</div>

  {include file="CRM/Profile/Page/Dynamic.tpl"}
  
  
  {if $contactProviderUrl}
  <p>
    <a class="crm-pager-link action-item crm-hover-button fancybox-iframe" href="{$contactProviderUrl}">{ts}Contact this provider{/ts}</a>
  </p>
  {/if}
</div>