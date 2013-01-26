{if $group_path}
<div class="group_path">
{foreach from=$group_path item=path_item name=group_path}
	<a href="{$path_item.path_url}">{$path_item.path_title|escape}</a>{if !$smarty.foreach.group_path.last} :: {/if}
{/foreach}
</div>
{/if}
<div class="work">
<h3>{$work_title|trim|escape}</h3>
<p>{$work_text|rtrim|nl2br}{if $work_comment}<br/><br/>{$work_comment|trim}{/if}</p>
</div>
