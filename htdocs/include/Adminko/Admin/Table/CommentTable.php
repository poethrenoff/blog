<?php
namespace Adminko\Admin\Table;

use Adminko\Metadata;

class CommentTable extends Table
{
    public function __construct($object)
    {
        // Скрываем древовидность комментариев
        unset(Metadata::$objects[$object]['parent_field']);
        
        parent::__construct($object);
    }
}
