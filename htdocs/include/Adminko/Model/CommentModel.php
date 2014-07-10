<?php
namespace Adminko\Model;

class CommentModel extends HierarchyModel
{
    public function getCommentUrl()
    {
        return Model::factory('news')->get($this->getCommentNews())->getNewsUrl(true);
    }
}