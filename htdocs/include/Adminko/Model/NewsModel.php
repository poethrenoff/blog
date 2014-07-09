<?php
namespace Adminko\Model;

use Adminko\System;
use Adminko\Db\Db;

class NewsModel extends Model
{
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
        $news_list = Db::selectAll('
            select news.* from news, news_tag, tag
            where ' . join(' and ', $filter_cond) . '
            order by news_date desc limit ' . $limit . ' offset ' . $offset, $filter_bind);
        return $this->getBatch($news_list);
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
        $news_list = Db::selectAll('
            select news.* from news
            where ' . join(' and ', $filter_cond) . '
            order by news_date desc limit ' . $limit . ' offset ' . $offset, $filter_bind);
        return $this->getBatch($news_list);
    }
    
    public function getTagList($only_publish, $limit)
    {
        return Db::selectAll('
			select tag.tag_title, count(news_tag.tag_id) as tag_count
			from tag, news_tag, news
			where tag.tag_id = news_tag.tag_id and
				news_tag.news_id = news.news_id and 
				news.news_active = 1 ' . ( $only_publish ? 'and news.news_publish = 1' : '' ) . '
			group by news_tag.tag_id
			order by tag_count desc, tag.tag_title
			limit ' . $limit);
    }
    
    public function getNewsUrl()
    {
        return System::urlFor(array('controller' => '', 'action' => 'post', 'id' => $this->getId()));
    }
}