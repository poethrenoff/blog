{foreach item=news_item from=$news_list}
<div class="header">
	<span class="title"><a href="{$news_item.news_url}">{$news_item.news_date}</a>{if !$news_item.news_publish}&nbsp;&nbsp;<img src="/image/design/lock.gif" alt=""/>{/if}</span>{foreach from=$news_item.tag_list item=tag_item}&nbsp;&nbsp;<a href="{$tag_item.tag_url}" class="tag">{$tag_item.tag_title}</a>{/foreach} 
</div>
<div class="content">
	{$news_item.news_content} 
</div>
<div class="footer">
	{if $news_item._comment_count}<a href="{$news_item.news_url}">Комментариев: {$news_item._comment_count}</a>&nbsp;|&nbsp;{/if}<a href="{$news_item.comment_url}">Оставить комментарий</a>
</div>
{/foreach}
{if $pages}
<div class="pages">
	Страницы: {$pages} 
</div>
{/if}
