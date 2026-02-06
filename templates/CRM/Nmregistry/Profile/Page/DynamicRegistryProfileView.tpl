
<div id="nmregistry-profile-view">
  <div id="nmregistry-profile-view-header">
    <div id="nmregistry-user-avatar">{$nmregistryUserAvatar}</div>
    {if $isBackgroundCheckGood}
      <div id="nmregistry-profile-background-check-badge-wrapper">
        {capture assign=badgeSrc}{crmResURL ext="nmregistry" file="img"}{/capture}
        <img id="nmregistry-profile-background-check-badge" src="{$badgeSrc}/bc-badge.png">
        <span id="nmregistry-profile-background-check-badge-help">
          <a class="fancybox-iframe" title="About Background Checks" href="/respite-provider-background-checks"><img src="{$badgeSrc}/help.png"></a>
        </span>
      </div>
    {/if}
  </div>

  <div id="nmregistry-intro-text" class="help">{$nmregistryIntroText}</div>

  {include file="CRM/Profile/Page/Dynamic.tpl"}
</div>