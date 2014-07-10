<?php
namespace Adminko\Model;

use Adminko\System;
use Adminko\Date;
use Adminko\Db\Db;

class NewsModel extends Model
{
    // Количество комментариев
    protected $comment_count = 0;
    
    protected function getCondition($only_publish)
    {
        $filter_cond = array(
            'news_active = :news_active'
        );
        $filter_bind = array(
            'news_active' => 1
        );
                
        if ($only_publish) {
            $filter_cond[] = 'news_publish = :news_publish';
            $filter_bind['news_publish'] = 1;
        }
        
        return array($filter_cond, $filter_bind);
    }
    
    public function getCount($only_publish)
    {
        list($filter_cond, $filter_bind) = $this->getCondition($only_publish);
        return Db::selectCell('
            select count(*) from news
            where ' . join(' and ', $filter_cond), $filter_bind);
    }
    
    public function getList($only_publish, $limit, $offset)
    {
        list($filter_cond, $filter_bind) = $this->getCondition($only_publish);
        $records = Db::selectAll('
            select news.*, count(comment_id) as comment_count
            from news left join comment on news_id = comment_news
            where ' . join(' and ', $filter_cond) . '
            group by news_id
            order by news_date desc limit ' . $limit . ' offset ' . $offset, $filter_bind);
        
        $news_list = array();
        foreach ($records as $record) {
            $news_list[$record['news_id']] = Model::factory('news')
                ->get($record['news_id'], $record)->setCommentCount($record['comment_count']);
        }
        return $news_list;
    }
    
    protected function getTagCondition($search_tag, $only_publish)
    {
        $filter_cond = array(
            'news.news_id = news_tag.news_id',
            'tag.tag_id = news_tag.tag_id',
            'lower(tag.tag_title) = lower(:tag_title)',
            'news_active = :news_active'
        );
        $filter_bind = array(
            'tag_title' => $search_tag,
            'news_active' => 1
        );
        
        if ($only_publish) {
            $filter_cond[] = 'news_publish = :news_publish';
            $filter_bind['news_publish'] = 1;
        }
        
        return array($filter_cond, $filter_bind);
    }
    
    public function getCountByTag($search_tag, $only_publish)
    {
        list($filter_cond, $filter_bind) = $this->getTagCondition($search_tag, $only_publish);
        return Db::selectCell('
            select count(*) from news, news_tag, tag
            where ' . join(' and ', $filter_cond), $filter_bind);
    }
    
    public function getListByTag($search_tag, $only_publish, $limit, $offset)
    {
        list($filter_cond, $filter_bind) = $this->getTagCondition($search_tag, $only_publish);
        $records = Db::selectAll('
            select news.* from news, news_tag, tag
            where ' . join(' and ', $filter_cond) . '
            order by news_date desc limit ' . $limit . ' offset ' . $offset, $filter_bind);
        return $this->getBatch($records);
    }
    
    protected function getTextCondition($search_text, $only_publish)
    {
        $filter_cond = array(
            'news_active = :news_active'
        );
        $filter_bind = array(
            'news_active' => 1
        );
        
        $search_words = $search_text !== '' ? preg_split('/\s+/isu', $search_text) : array();
        foreach ($search_words as $word_index => $word_value) {
            $filter_bind['news_content_' . $word_index] = '%' . $word_value . '%';
            $filter_cond[] = '(lower(news_content) like lower(:news_content_' . $word_index . '))';
        }
        
        if ($only_publish) {
            $filter_cond[] = 'news_publish = :news_publish';
            $filter_bind['news_publish'] = 1;
        }
        
        return array($filter_cond, $filter_bind);
    }
    
    public function getCountByText($search_text, $only_publish)
    {
        list($filter_cond, $filter_bind) = $this->getTextCondition($search_text, $only_publish);
        return Db::selectCell('
            select count(*) from news
            where ' . join(' and ', $filter_cond), $filter_bind);
    }
    
    public function getListByText($search_text, $only_publish, $limit, $offset)
    {
        list($filter_cond, $filter_bind) = $this->getTextCondition($search_text, $only_publish);
        $records = Db::selectAll('
            select news.* from news
            where ' . join(' and ', $filter_cond) . '
            order by news_date desc limit ' . $limit . ' offset ' . $offset, $filter_bind);
        return $this->getBatch($records);
    }
    
    public function getTagList()
    {
        $records = Db::selectAll('
            select tag.* from news_tag, tag
            where
                tag.tag_id = news_tag.tag_id and
                news_tag.news_id = :news_id
            order by tag.tag_title', array('news_id' => $this->getId()));
        return Model::factory('tag')->getBatch($records);
    }
    
    public function getTagCloud($only_publish, $limit)
    {
        $records = Db::selectAll('
			select tag.*, count(news_tag.tag_id) as tag_count
			from tag, news_tag, news
			where
                tag.tag_id = news_tag.tag_id and
				news_tag.news_id = news.news_id and 
				news.news_active = 1 ' . ( $only_publish ? 'and news.news_publish = 1' : '' ) . '
			group by news_tag.tag_id
			order by tag_count desc, tag.tag_title
			limit ' . $limit);
        
        $tag_list = array();
        foreach ($records as $record) {
            $tag_list[$record['tag_id']] = Model::factory('tag')
                ->get($record['tag_id'], $record)->setTagCount($record['tag_count']);
        }
        return $tag_list;
    }
    
    public function getNewsItem($news_id, $only_publish)
    {
        $filter_cond = array(
            'news_id = :news_id',
            'news_active = :news_active'
        );
        $filter_bind = array(
            'news_id' => $news_id,
            'news_active' => 1
        );
        
        if ($only_publish) {
            $filter_cond[] = 'news_publish = :news_publish';
            $filter_bind['news_publish'] = 1;
        }
        
        $news_item = Db::selectRow('select * from news where ' . join(' and ', $filter_cond), $filter_bind);
        
        if (!$news_item) {
            throw new \AlarmException('Запись не найдена');
        }
        
        return Model::factory('news')->get($news_item['news_id'], $news_item);
    }
    
    public function getNewsUrl($absolute_url = false)
    {
        return System::urlFor(array('controller' => '', 'action' => 'post', 'id' => $this->getId()),
            $absolute_url ? ('http://' . filter_input(INPUT_SERVER, 'HTTP_HOST')) : '');
    }
    
    public function getReplyUrl()
    {
        return System::urlFor(array('controller' => '', 'action' => 'post', 'id' => $this->getId(), 'mode' => 'reply'));
    }
    
    public function getViewNewsDate()
    {
        return Date::get($this->getNewsDate(),
            (substr($this->getNewsDate(), 8) === '000000') ? 'short' : 'long');
    }
    
    public function getPubNewsDate()
    {
        return Date::get($this->getNewsDate(), 'r');
    }
}