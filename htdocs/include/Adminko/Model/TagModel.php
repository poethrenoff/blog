<?php
namespace Adminko\Model;

use Adminko\System;

class TagModel extends Model
{
    // Количество тегов
    protected $tag_count = 0;
    
    // Размер шрифта
    protected $font_size = null;
    
    public function getTagUrl()
    {
        return System::urlFor(array('controller' => 'search', 'tag' => $this->getTagTitle()));
    }
}