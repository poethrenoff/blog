<form class="search_form" action="{$form_url}" method="get">
	<table class="search_table">
		<tr>
			<td>
				<input type="text" class="text" name="text" value="{$search_text|escape}" errors="require"/>
				<input type="submit" class="button" value="Найти"/>
			</td>
		</tr>
	</table>
</form>
<div class="tag_cloud">
{foreach from=$tag_list item=tag_item}
	<a href="{$tag_item.tag_url}" style="font-size: {$tag_item.font_size}px" title="{$tag_item.tag_title|escape} ({$tag_item.tag_count})"{if $tag_item.tag_selected} class="selected"{/if}>{$tag_item.tag_title|escape}</a>
{/foreach}
</div>
{foreach from=$search_list item=search_item}
<table class="search_result">
	<tr>
		<td class="index">
			{$search_item.search_index}.
		</td>
		<td>
			<span class="title"><a href="{$search_item.news_url}">{$search_item.news_date}</a></span><br/>{$search_item.news_content}
		</td>
	</tr>
</table>
{foreachelse}
{if $search_text}
<div class="message">
	Ничего не найдено
</div>
{/if}
{/foreach}
{if $pages}
<div class="pages">
	Страницы: {$pages}
</div>
{/if}
