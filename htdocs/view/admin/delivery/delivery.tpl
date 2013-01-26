<b>{$title}</b><br/><br/>
{if $mail_count}
<div style="border: 1px solid red; background-color: #fff0f0; padding: 10px; margin-bottom: 15px">
	<table class="record">
		<tr>
			<td>
				<b style="color: red">Писем в очереди: {$mail_count}</b>
			</td>
			<td style="text-align: right">
				<form action="{$cancel_url}" method="post">
					<input type="submit" value="Отменить" class="button"/>
				</form>
			</td>
		</tr>
	</table>
</div>
{/if}
<form id="form" action="{$form_url}" method="post" enctype="multipart/form-data" onsubmit="return form_submit( this )">
	<table class="record">
		<tr>
			<td class="title">
				Тема рассылки <span class="require">*</span>:
			</td>
			<td class="field">
				<input type="text" name="subject" value="{$prev_mail.body_subject|escape}" class="text" errors="require"/>
			</td>
		</tr>
		<tr>
			<td class="title">
				От кого (email) <span class="require">*</span>:
			</td>
			<td class="field">
				<input type="text" name="email" value="{$prev_mail.body_email|escape}" class="text" errors="require|email"/>
			</td>
		</tr>
		<tr>
			<td class="title">
				От кого (имя):
			</td>
			<td class="field">
				<input type="text" name="name" value="{$prev_mail.body_name|escape}" class="text"/>
			</td>
		</tr>
		<tr>
			<td class="title">
				Текст рассылки <span class="require">*</span>:
			</td>
			<td class="field">
				<textarea name="message" cols="" rows="" errors="require" class="ckeditor">{$prev_mail.body_text|escape}</textarea>
			</td>
		</tr>
		<tr>
			<td class="title">
				Тип рассылки <span class="require">*</span>:
			</td>
			<td class="field">
				<input type="radio" name="type" value="send_to_all"> Всем пользователям<br/>
				<input type="radio" name="type" value="send_to_admin" checked="checked"> Тестовое письмо (только администраторам)<br/>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="Отправить" class="button"/>
			</td>
		</tr>
	</table>
</form>
