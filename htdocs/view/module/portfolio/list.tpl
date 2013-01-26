<h3>Портфолио</h3>

<table class="portfolio_table">
{foreach from=$portfolio_table item=row}
	<tr>
{foreach from=$row item=item}
		<td style="width: {$table_cell_width}%">
{if $item}
			<a href="{$item.portfolio_url}" title="{$item.portfolio_title|escape}"><img src="{$item.portfolio_picture}" alt="{$item.portfolio_title|escape}"/></a><br/>
			<a href="{$item.portfolio_url}">{$item.portfolio_title|escape}</a>
{else}
			&nbsp;
{/if}
		</td>
{/foreach}
	</tr>
{/foreach}
</table>
{if $pages}
<div class="pages">
	Страницы: {$pages}
</div>
{/if}

