<b>{$title}</b><br/><br/>
<form id="form" action="{$form_url}" method="post" enctype="multipart/form-data" onsubmit="return form_submit( this )">
	<table class="record">
		<tr>
			<td class="title">
				Текст <span class="require">*</span>:
			</td>
			<td class="field">
				<table style="width: 100%">
					<tr>
						<td style="width: 50%; vertical-align: top;">
							<textarea name="text" cols="" rows="" errors="require" style="height: 400px">{$smarty.post.text|escape}</textarea>
						</td>
						<td style="width: 50%; vertical-align: top; padding-left: 20px;">
{if $work_title}
							<h3>{$work_title|trim|escape}</h3>
							<p>{$work_text|rtrim|nl2br}{if $work_comment}<br/><br/>{$work_comment|trim}{/if}</p>
							<p style="color: red; font-weight: bold">
								{$result_percent}% ({$result_words})<br>
							</p>
{/if}
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="Найти" class="button"/>
			</td>
		</tr>
	</table>
</form>
