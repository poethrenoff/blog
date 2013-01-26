{if $count > $by_page}
{foreach from=$pages item=page}
{if $page.link}
<a href="{$page.link|escape}">{$page.number|default:"&hellip;"}</a>
{else}
<b>{$page.number}</b>
{/if}
{/foreach}
{/if}
