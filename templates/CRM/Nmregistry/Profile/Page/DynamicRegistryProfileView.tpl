
<div id="nmregistry-profile-view">
  <div id="nmregistry-profile-view-header">
    <div id="nmregistry-user-avatar">{$nmregistryUserAvatar}</div>
    {if $isBackgroundCheckGood}
      {capture assign=badgeSrc}{crmResURL ext="nmregistry" file="img/bc-badge.png"}{/capture}
      <img id="nmregistry-profile-has-background-check" src="{$badgeSrc}">
    {/if}
  </div>

  <div id="nmregistry-intro-text" class="help">{$nmregistryIntroText}</div>

  {include file="CRM/Profile/Page/Dynamic.tpl"}
</div>