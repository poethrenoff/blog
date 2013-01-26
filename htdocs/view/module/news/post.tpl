<script type="text/javascript" src="/script/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	editor_config = {
		theme: 'advanced', skin: 'o2k7', skin_variant: 'silver', language: 'ru',
		content_css: '/style/tiny_mce.css',
		plugins : 'safari,emotions,preview,searchreplace,fullscreen,xhtmlxtras',
		
		theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect',
		theme_advanced_buttons2 : 'forecolor,backcolor,|,bullist,numlist,|,outdent,indent,|,link,unlink,image,|,charmap,emotions,|,undo,redo,|,cut,copy,paste,|,search,replace,|,removeformat,fullscreen,preview,code',
		theme_advanced_buttons3 : '', theme_advanced_buttons4 : '',
		theme_advanced_toolbar_location : 'top', theme_advanced_toolbar_align : 'left',
		theme_advanced_statusbar_location : 'bottom', theme_advanced_resizing : true
	};
	
	function showCommentForm( sCommentId )
	{
		if ( ed_old = tinyMCE.get('comment_content') ) {
			ed_old.hide(); ed_old.destroy(); tinyMCE.remove( ed_old );
		}
		
		var oCommentForm = document.forms['comment_form'];
		oCommentForm.comment_parent.value = sCommentId;
		
		var oCommentDiv = document.getElementById( 'comment_' + sCommentId );
		oCommentDiv.appendChild( oCommentForm );
		
		oCommentForm.style.display = 'block';
		
		tinyMCE.add( ed = new tinymce.Editor('comment_content', editor_config ) ); ed.render();
	}
</script>

<div class="header">
	<span class="title">{$news_date}{if !$news_publish}&nbsp;&nbsp;<img src="/image/design/lock.gif" alt=""/>{/if}</span>{foreach from=$tag_list item=tag_item}&nbsp;&nbsp;<a href="{$tag_item.tag_url}" class="tag">{$tag_item.tag_title}</a>{/foreach} 
</div>
<div class="content">
	{$news_content} 
</div>
{if $error}
<div class="message">
	{$error|escape} 
</div>
{/if}
<div id="comment_0" class="footer">
	<a href="" onclick="showCommentForm( '0' ); return false">Оставить комментарий</a>
</div>

<form id="comment_form" class="comment_form" action="{$form_url}" method="post">
	<table class="comment_table">
		<col class="col1"/><col class="col2"/><col class="col3"/>
		<tr>
			<td class="title">
				Имя:
			</td>
			<td colspan="2">
				<input type="hidden" name="mode" value="reply"/>
				<input type="hidden" name="action" value="comment"/>
				<input type="hidden" name="comment_parent" value=""/>
				<input type="text" class="text name" name="comment_author" value="{$comment_author|escape}"/>
			</td>
		</tr>
		<tr>
			<td class="title">
				Комментарий:
			</td>
			<td colspan="2">
				<textarea id="comment_content" name="comment_content" rows="5" cols="5">{$comment_content|escape}</textarea>
			</td>
		</tr>
		<tr>
			<td class="title">
				Число:
			</td>
			<td>
				<input type="text" class="text code" name="captcha_value" value="" autocomplete="off"/>
			</td>
			<td>
				<img src="/image/captcha.php" class="code" alt="Контрольное число"/>
			</td>
		</tr>
		<tr>
			<td/>
			<td colspan="2">
				<input type="submit" class="button send" value="Отправить"/>
			</td>
		</tr>
	</table>
</form>
{foreach item=comment_item from=$comment_tree}
{if $comment_item._depth}
{section name=offset start=0 loop=$comment_item._depth}<div class="tree_offset">{/section} 
{/if}
<div class="comment_header">
	{$comment_item.comment_author|escape} ({$comment_item.comment_date|escape})
</div>
<div class="comment_content">
	{$comment_item.comment_content} 
</div>
{if $comment_item._depth}
{section name=offset start=0 loop=$comment_item._depth}</div>{/section} 
{/if}
<div id="comment_{$comment_item.comment_id}" class="footer">
	<a href="" onclick="showCommentForm( '{$comment_item.comment_id}' ); return false">Оставить комментарий</a>
</div>
{/foreach}
{if $show_form !== null}
<script type="text/javascript">
	showCommentForm( '{$show_form}' );
</script>
{/if}

