{if $object_tree}
{foreach from=$object_tree item=item}
{if $item._depth}
{section name=offset start=0 loop=$item._depth}<div class="tree_offset">{/section} 
{/if}
{if $item.object_name}
	<a href="{$item.object_url|escape}"{if $item._selected} class="selected"{/if}>{$item.object_title}</a><br/>
{else}
	<b>{$item.object_title}</b><br/>
{/if}
{if $item._depth}
{section name=offset start=0 loop=$item._depth}</div>{/section} 
{/if}
{/foreach}
{/if}