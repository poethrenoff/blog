<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
	<channel>
		<title>Не дождетесь!</title>
		<link>http://{$smarty.server.HTTP_HOST}/</link>
		<description>Блог о здоровье и его отсутствии</description>
		<copyright>Copyright 2003-{$smarty.now|date_format:"%Y"}, ДК</copyright>
		<webMaster>webmaster@testea.ru (Константинов Дмитрий)</webMaster>
		<image>
			<url>http://{$smarty.server.HTTP_HOST}/image/logo.gif</url>
			<title>Не дождетесь!</title>
			<link>http://{$smarty.server.HTTP_HOST}/</link>
		</image>
{foreach item=news_item from=$news_list}
		<item>
			<title>{$news_item.news_date}</title>
			<link>{$news_item.news_link}</link>
			<description><![CDATA[{$news_item.news_content}]]></description>
			<guid>{$news_item.news_link}</guid>
			<pubDate>{$news_item.news_pub_date}</pubDate>
		</item>
{/foreach}
	</channel>
</rss>
