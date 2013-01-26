<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
	<head>
		<title>{$meta_title|escape}</title>
		<meta name="description" content="{$meta_description|escape}"/> 
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link rel="shortcut icon" href="/image/favicon.ico"/>
		<link rel="stylesheet" type="text/css" href="/style/index.css"/>
		<link rel="alternate" type="application/rss+xml" href="http://blog.testea.ru/rss" title="Не дождетесь!"/>
		<link rel="search" type="application/opensearchdescription+xml" href="/search.xml" title="Не дождетесь!"/>
	</head>
	<body>
		<div class="main">
			<div class="outer">
				<div class="inner">
					<div class="main_header"><a href="/">He дождетесь!</a></div>
{$content}
				</div>
			</div>
		</div>
		<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
			document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			try {
				var pageTracker = _gat._getTracker("UA-9610682-4");
				pageTracker._trackPageview();
			} catch(err) {}
		</script>
	</body>
</html>
