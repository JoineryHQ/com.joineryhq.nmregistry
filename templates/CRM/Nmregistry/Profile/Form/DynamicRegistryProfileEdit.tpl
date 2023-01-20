

{foreach from=$nmregistryStatusMessages item=nmregistryStatusMessage}
  <div class="messages status nmregistry-status nmregistry-status-{$nmregistryStatusMessage.status} no-popup">
    {icon icon=$nmregistryStatusMessage.iconClass}{/icon}
    <span class="msg-text">{$nmregistryStatusMessage.message_secondPerson}</span>
  </div>
{/foreach}

<figure id="nmregistry-avatar-figure" style="width: {$nmregistryUserAvatarSize}px" class="wp-caption alignnone">
  <a href="{$nmregistryEditAvatarUrl}" class="wp-user-avatar-link wp-user-avatar-custom">
    {$nmregistryUserAvatar}
  </a>
  <figcaption id="nmregistry-avatar-figcaption" class="wp-caption-text"> 
    <a href="{$nmregistryEditAvatarUrl}">
      {if $nmregistryUserHasAvatar}
        {ts}Click to change photo.{/ts}
      {else}
        {ts}Please click to add a photo.{/ts}
      {/if}
    </a>
  </figcaption>
</figure>

{include file="CRM/Profile/Form/Dynamic.tpl"}