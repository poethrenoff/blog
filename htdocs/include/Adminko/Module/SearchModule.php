<?php
namespace Adminko\Module;

use Adminko\System;
use Adminko\Paginator;
use Adminko\Db\Db;
use Adminko\Date;

class SearchModule extends Module
{
    /**
     * Заполнение контента модуля
     */
    protected function actionIndex()
    {
        $records_per_page = max(intval($this->getParam('records_per_page')), 1);

        $search_tag = trim(init_string('tag'));
        $search_text = trim(init_string('text'));

        if ($search_tag !== '' || $search_text !== '') {
            $filter_fields = $filter_binds = $search_words = array();

            if ($search_text !== '') {
                $search_words = preg_split('/\s+/isu', $search_text);

                foreach ($search_words as $word_index => $word_value) {
                    $filter_binds['news_content_' . $word_index] = '%' . $word_value . '%';
                    $filter_fields[] = '( lower( news_content ) like lower( :news_content_' . $word_index . ' ) )';
                }
            }

            if ($search_tag !== '') {
                $news_list = Db::selectAll('
                    select news_tag.news_id
                    from news_tag, tag
                    where
                        tag.tag_id = news_tag.tag_id and
                        lower( tag.tag_title ) = lower( :tag_title )', array('tag_title' => $search_tag));

                $news_list_in = array();
                foreach ($news_list as $news_index => $news_item) {
                    $news_list_in[] = $news_item['news_id'];
                }
                $news_list_in = count($news_list_in) ? join(', ', $news_list_in) : 0;

                $filter_fields[] = '( news_id in (' . $news_list_in . ' ) )';
            }

            $filter_fields[] = 'news_active = 1';
            if (init_cookie('view') != 'all') {
                $filter_fields[] = 'news_publish = 1';
            }

            $search_query = 'select count(*) as _search_count from news where ' . join(' and ', $filter_fields);
            $search_count = Db::selectRow($search_query, $filter_binds);
            $search_count = $search_count['_search_count'];

            $pages = Paginator::create($search_count, array('by_page' => $records_per_page));

            $search_query = 'select * from news where ' . join(' and ', $filter_fields) . '
				order by news_date desc limit ' . $pages['by_page'] . ' offset ' . $pages['offset'];

            $search_list = Db::selectAll($search_query, $filter_binds);

            $search_index = 0;
            foreach ($search_list as $result_index => $result_item) {
                $search_list[$result_index]['search_index'] = ++$search_index + $pages['offset'];

                $search_list[$result_index]['news_content'] = $this->prepareSearchResult(strip_tags($result_item['news_content']), $search_words);

                $search_list[$result_index]['news_date'] = Date::get($result_item['news_date'], 'd.m.Y H:i');
                if (substr($search_list[$result_index]['news_date'], 11) == '00:00') {
                    $search_list[$result_index]['news_date'] = substr($search_list[$result_index]['news_date'], 0, 10);
                }

                $search_list[$result_index]['news_url'] = System::urlFor(array('controller' => '', 'action' => 'post', 'id' => $result_item['news_id']));
            }

            $this->view->assign('search_list', $search_list);
            $this->view->assign('search_text', $search_text);
            $this->view->assign('search_count', $search_count);

            $this->view->assign('form_url', System::selfUrl());

            $this->view->assign('pages', Paginator::fetch($pages));
        }

        $this->assignTagCloud($search_tag);

        $this->content = $this->view->fetch('module/search/search');
    }

    /**
     * Преобразование результатов поиска
     */
    protected function prepareSearchResult($result_text, $search_words, $search_limit = 200)
    {
        $result_text = strip_tags($result_text);

        $result_text_length = mb_strlen($result_text);
        $result_pos_min = $result_text_length;
        $result_pos_max = 0;

        $result_pos_find = false;

        foreach ($search_words as $search_word) {
            $result_left_pos = mb_stripos($result_text, $search_word);
            $result_right_pos = mb_strripos($result_text, $search_word);

            if (( $result_left_pos !== false ) && ( $result_right_pos !== false )) {
                if ($result_left_pos < $result_pos_min) {
                    $result_pos_min = $result_left_pos;
                }
                if ($result_right_pos > $result_pos_max) {
                    $result_pos_max = $result_right_pos + mb_strlen($search_word);
                }

                $result_pos_find = true;
            }
        }

        if ($result_pos_find) {
            $left_pos = max(0, $result_pos_min - $search_limit);
            $right_pos = min($result_pos_max + $search_limit - 1, $result_text_length - 1);
        } else {
            $left_pos = 0;
            $right_pos = min($search_limit - 1, $result_text_length - 1);
        }

        $result_text = mb_substr($result_text, $left_pos, $right_pos - $left_pos + 1);
        foreach ($search_words as $search_word) {
            $result_text = preg_replace('|(' . preg_quote($search_word) . ')|isu', '<b>\\1</b>', $result_text);
        }

        if ($left_pos != 0) {
            $result_text = '...' . $result_text;
        }
        if ($right_pos != $result_text_length - 1) {
            $result_text = $result_text . '...';
        }

        return $result_text;
    }

    /**
     * Облако тегов
     */
    protected function assignTagCloud($search_tag = '')
    {
        $tag_cloud_count = max(intval($this->getParam('tag_cloud_count')), 1);

        $min_font_size = max(intval($this->getParam('min_font_size')), 1);
        $max_font_size = max(intval($this->getParam('max_font_size')), 1);

        $level_count = max($max_font_size - $min_font_size, 1);

        $tag_list = Db::selectAll('
			select tag.tag_title, count( news_tag.tag_id ) as tag_count
			from tag, news_tag, news
			where tag.tag_id = news_tag.tag_id and
				news_tag.news_id = news.news_id and 
				news.news_active = 1 ' . ( ( init_cookie('view') != 'all' ) ? 'and news.news_publish = 1' : '' ) . '
			group by news_tag.tag_id
			order by tag_count desc, tag.tag_title
			limit ' . $tag_cloud_count);

        if (count($tag_list)) {
            $num_links_max = log($tag_list[0]['tag_count']);
            $num_links_min = log($tag_list[count($tag_list) - 1]['tag_count']);
            $level_step = ( $num_links_max - $num_links_min ) / $level_count;

            foreach ($tag_list as $tag_index => $tag_item) {
                if ($level_step > 0) {
                    $tag_list[$tag_index]['font_size'] = round($min_font_size + ( log($tag_item['tag_count']) - $num_links_min ) / $level_step);
                } else {
                    $tag_list[$tag_index]['font_size'] = round($min_font_size + $level_count / 2);
                }

                $tag_list[$tag_index]['tag_url'] = System::urlFor(array('controller' => 'search', 'tag' => $tag_item['tag_title']));

                $tag_list[$tag_index]['tag_selected'] = $tag_item['tag_title'] == $search_tag;
            }

            usort($tag_list, function($a, $b) {
                return strcmp(mb_strtolower($a['tag_title']), mb_strtolower($b['tag_title']));
            });
        }

        $this->view->assign('tag_list', $tag_list);
    }
}
