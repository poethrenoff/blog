<?php
namespace Adminko\Admin\Table;

use Adminko\System;
use Adminko\Db\Db;

class NewsTable extends Table
{
    /**
     * Имя поля "Теги" в форме редактирования записи
     */
    protected $tag_field = 'tag_string';

    /**
     * Сохраняем теги после добавления записи
     */
    protected function actionAddSave($redirect = true)
    {
        $primary_field = parent::actionAddSave(false);

        $this->setTags($primary_field, init_string($this->tag_field));

        if ($redirect) {
            $this->redirect();
        }

        return $primary_field;
    }

    /**
     * Сохраняем теги после изменения записи
     */
    protected function actionEditSave($redirect = true)
    {
        parent::actionEditSave(false);

        $this->setTags(System::id(), init_string($this->tag_field));

        if ($redirect) {
            $this->redirect();
        }
    }

    /**
     * Добавляем в метаданные поле "Теги"
     */
    protected function recordCard($action = 'edit')
    {
        $this->fields = array(
            'news_id' => $this->fields['news_id'],
            'news_content' => $this->fields['news_content'],
            $this->tag_field => array('title' => 'Теги', 'type' => 'string', 'errors' => array()),
            'news_date' => $this->fields['news_date'],
            'news_publish' => $this->fields['news_publish'],
            'news_active' => $this->fields['news_active']
        );

        parent::recordCard($action);
    }

    /**
     * Заполняем поле "Теги" в форме редактирования записи
     */
    protected function getRecord($primary_field = '')
    {
        $record = parent::getRecord($primary_field);

        $record[$this->tag_field] = $this->getTags($record[$this->primary_field]);

        return $record;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * Метод обработки строки тегов
     */
    public function prepareTags($tag_string)
    {
        $tag_array = array();

        $tag_items = array_unique(array_map('trim', explode(',', trim($tag_string))));

        foreach ($tag_items as $tag) {
            if ($tag === '') {
                continue;
            }

            $tag_item = Db::selectRow('select * from tag where lower( tag_title ) = lower( :tag_title )', array('tag_title' => $tag));

            if ($tag_item) {
                $tag_array[] = $tag_item['tag_id'];
            } else {
                Db::insert('tag', array('tag_title' => $tag));

                $tag_array[] = Db::lastInsertId();
            }
        }

        return $tag_array;
    }

    /**
     * Метод извлечения из базы тегов текущей записи
     */
    public function getTags($primary_field)
    {
        $tag_list = Db::selectAll('
            select tag.tag_title from tag, news_tag
            where tag.tag_id = news_tag.tag_id and
                news_tag.news_id = :news_id
            order by tag.tag_title', array('news_id' => $primary_field));

        $tag_array = array();
        foreach ($tag_list as $tag_row) {
            $tag_array[] = $tag_row['tag_title'];
        }

        return join(', ', $tag_array);
    }

    /**
     * Метод сохранения в базе тегов текущей записи
     */
    public function setTags($primary_field, $tag_string)
    {
        Db::delete('news_tag', array('news_id' => $primary_field));

        $tag_array = $this->prepareTags($tag_string);
        foreach ($tag_array as $tag_id) {
            Db::insert('news_tag', array('tag_id' => $tag_id, 'news_id' => $primary_field));
        }
    }
}
