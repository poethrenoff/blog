<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
	<head>
		<title>{$meta_title|escape}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<script src="/admin/script/jquery.js" type="text/javascript"></script>
		<script src="/admin/script/interface.js" type="text/javascript"></script>
		<script src="/admin/ckeditor/ckeditor.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/admin/style/index.css" type="text/css"/>
	</head>
	<body>
		<table class="main">
			<tr>
				<td class="menu">
					<div class="logo">
						<a href="/admin/" title="Admin&amp;K&deg;"><img src="/admin/image/logo.jpg" alt="Admin&amp;K&deg;"/></a>
					</div>
{$menu} 
				</td>
				<td class="content">
{$auth} 
{$content} 
				</td>
			</tr>
		</table>
	</body>
</html>
