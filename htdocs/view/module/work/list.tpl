{if $group_path}
<div class="group_path">
{foreach from=$group_path item=path_item name=group_path}
{if $smarty.foreach.group_path.last}
	{$path_item.path_title|escape}
{else}
	<a href="{$path_item.path_url}">{$path_item.path_title|escape}</a> :: 
{/if}
{/foreach}
</div>
{/if}
{if $group_tree}
<div class="group_tree">
{foreach from=$group_tree item=group_item}
{if $group_item._depth}
{section name=offset start=0 loop=$group_item._depth}<div class="tree_offset">{/section} 
{/if}
	<a href="{$group_item.group_url}">{$group_item.group_title|escape}</a>{if $group_item.group_comment} ({$group_item.group_comment|escape}){/if}<br/>
{if $group_item._depth}
{section name=offset start=0 loop=$group_item._depth}</div>{/section} 
{/if}
{/foreach}
</div>
{/if}
{if $group_tree && $work_list}
<div class="separator"></div>
{/if}
{if $work_list}
<table class="work_table">
	<tr>
		<td>
{foreach from=$work_list item=work_item name=work_list}
			{$smarty.foreach.work_list.iteration}.&nbsp;&nbsp;<a href="{$work_item.work_url}">{$work_item.work_title|escape}</a><br/>
{if $smarty.foreach.work_list.iteration == ceil($smarty.foreach.work_list.total / 2)}
		</td>
		<td>
{/if}
{/foreach}
		</td>
	</tr>
</table>
{/if}
