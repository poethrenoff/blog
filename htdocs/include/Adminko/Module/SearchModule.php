<?php
namespace Adminko\Module;

use Adminko\Paginator;
use Adminko\Model\Model;

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

        $search_list = array();

        $news_model = Model::factory('news');
        if (!is_empty($search_tag)) {
            $search_count = $news_model->getCountByTag($search_tag, $this->only_publish);
            $pages = Paginator::create($search_count, array('by_page' => $records_per_page));
            $search_list = $news_model->getListByTag($search_tag, $this->only_publish, $pages['by_page'], $pages['offset']);
        } elseif (!is_empty($search_text)) {
            $search_count = $news_model->getCountByText($search_text, $this->only_publish);
            $pages = Paginator::create($search_count, array('by_page' => $records_per_page));
            $search_list = $news_model->getListByText($search_text, $this->only_publish, $pages['by_page'], $pages['offset']);
        }

        if ($search_list) {
            foreach ($search_list as $search_item) {
                $search_item->setNewsContent(
                    $this->prepareSearchResult($search_item->getNewsContent(), $search_text)
                );
            }

            $this->view->assign('search_list', $search_list);
            $this->view->assign('search_text', $search_text);
            $this->view->assign('search_tag', $search_tag);
            $this->view->assign('search_count', $search_count);
            $this->view->assign('search_index', $pages['offset'] + 1);

            $this->view->assign('pages', Paginator::fetch($pages));
        }

        $this->view->assign('tag_cloud', $this->getTagCloud());

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
    protected function getTagCloud()
    {
        $tag_cloud_count = max(intval($this->getParam('tag_cloud_count')), 1);

        $min_font_size = max(intval($this->getParam('min_font_size')), 1);
        $max_font_size = max(intval($this->getParam('max_font_size')), 1);

        $level_count = max($max_font_size - $min_font_size, 1);

        $tag_list = Model::factory('news')->getTagCloud($this->only_publish, $tag_cloud_count);

        if (count($tag_list)) {
            $first_tag = reset($tag_list);
            $last_tag = end($tag_list);

            $num_links_max = log($first_tag->getTagCount());
            $num_links_min = log($last_tag->getTagCount());
            $level_step = ( $num_links_max - $num_links_min ) / $level_count;

            foreach ($tag_list as $tag_index => $tag_item) {
                if ($level_step > 0) {
                    $tag_item->setFontSize(round($min_font_size + (log($tag_item->getTagCount()) - $num_links_min) / $level_step));
                } else {
                    $tag_item->setFontSize(round($min_font_size + $level_count / 2));
                }
            }

            usort($tag_list, function($a, $b) {
                return strcmp(mb_strtolower($a->getTagTitle()), mb_strtolower($b->getTagTitle()));
            });
        }

        return $tag_list;
    }
}
