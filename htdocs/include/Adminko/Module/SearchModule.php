<?php
namespace Adminko\Module;

use Adminko\System;
use Adminko\Paginator;
use Adminko\Db\Db;
use Adminko\Model\Model;
use Adminko\Date;

class SearchModule extends Module
{
    // Разрешен просмотр только опубликованных новостей
    protected $only_publish = false;

    // Инициализация модуля
    public function init($action = 'index', $params = array(), $is_main = false)
    {
        $this->only_publish = init_cookie('view') != 'all';

        parent::init($action, $params, $is_main);
    }

    /**
     * Заполнение контента модуля
     */
    protected function actionIndex()
    {
        $records_per_page = max(intval($this->getParam('records_per_page')), 1);

        $search_tag = trim(init_string('tag'));
        $search_text = trim(init_string('text'));
        
        $search_list = $search_words = array();
        
        $news_model = Model::factory('news');
        if ($search_tag = trim(init_string('tag'))) {
            $search_count = $news_model->getCountByTag($search_tag, $this->only_publish);
            $pages = Paginator::create($search_count, array('by_page' => $records_per_page));
            $search_list = $news_model->getListByTag($search_tag, $this->only_publish, $pages['by_page'], $pages['offset']);
        } elseif ($search_text = trim(init_string('text'))) {
            $search_count = $news_model->getCountByText($search_text, $this->only_publish);
            $pages = Paginator::create($search_count, array('by_page' => $records_per_page));
            $search_list = $news_model->getListByText($search_text, $this->only_publish, $pages['by_page'], $pages['offset']);
        }
        
        foreach ($search_list as $search_item) {
            $search_item->setNewsContent(
                $this->prepareSearchResult($search_item->getNewsContent(), $search_text)
            );
            
            $news_date = $search_item->getNewsDate();
            $search_item->setNewsDate(
                Date::get($news_date, (substr($news_date, 8) === '000000') ? 'd.m.Y' : 'd.m.Y H:i')
            );
        }
        
        $this->view->assign('search_list', $search_list);
        $this->view->assign('search_text', $search_text);
        $this->view->assign('search_count', $search_count);
        $this->view->assign('search_index', $pages['offset'] + 1);

        $this->view->assign('pages', Paginator::fetch($pages));

        $this->assignTagCloud($search_tag);

        $this->content = $this->view->fetch('module/search/search');
    }

    /**
     * Преобразование результатов поиска
     */
    protected function prepareSearchResult($result_text, $search_text, $search_limit = 200)
    {
        $result_text = strip_tags($result_text);
        $search_words = $search_text !== '' ? preg_split('/\s+/isu', $search_text) : array();

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
        
        $tag_list = Model::factory('news')->getTagList($this->only_publish, $tag_cloud_count);

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
