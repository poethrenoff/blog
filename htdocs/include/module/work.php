<?php
class module_work extends module
{
    // Вывод списка корневых разделов
    protected function action_index()
    {
        $this->display_group();
    }
    
    // Вывод списка подразделов
    protected function action_group()
    {
        $group_item = $this->get_group_item(id());
        $this->display_group($group_item['group_id']);
    }
    
    // Вывод произведения
    protected function action_view($id = null)
    {
        $work_item = $this->get_work_item($id ? $id : id());
        
        $group_parent = $work_item['group_id'];
        
        $group_path = array();
        while (true) {
            $group_item = db::select_row('
                select group_id, group_parent, group_title
                from work_group where group_id = :group_id',
                    array('group_id' => $group_parent));
            if ( !$group_item ) {
                break;
            }
            $group_parent = $group_item['group_parent'];
            $group_path[] = array('path_title' => $group_item['group_title'],
                'path_url' => url_for(array('action' => 'group', 'id' => $group_item['group_id'])));
        };
        
        if ($group_path) {
            $group_path[] = array('path_title' => 'Творчество', 'path_url' => url_for(array('action' => 'index')));
        }
        
        $this->view->assign($work_item);
        $this->view->assign('group_path', array_reverse($group_path));
        $this->content = $this->view->fetch('module/work/view.tpl');
    }
    
    // Вывод случайного произведения
    protected function action_random()
    {
        $random_work_item = db::select_row( '
            select * from work, work_group where
                work_group = group_id and work_active = 1 and group_active = 1
            order by rand() limit 1');
        $this->action_view($random_work_item['work_id']);
    }
    
    // Получение раздела
    protected function get_group_item($group_id)
    {
        $group_item = db::select_row( '
            select * from work_group where group_id = :group_id and group_active = 1',
                array('group_id' => $group_id));
        if (!$group_item) {
            not_found();
        }
        
        return $group_item;
    }
    
    // Получение произведения
    protected function get_work_item($work_id)
    {
        $work_item = db::select_row( '
            select * from work, work_group where work_id = :work_id and
                work_group = group_id and work_active = 1 and group_active = 1',
                    array('work_id' => $work_id));
        if (!$work_item) {
            not_found();
        }
        
        $work_item['work_text'] = preg_replace_callback ('/^ +| {2,}/m', create_function(
            '$matches', 'return str_repeat( \'&nbsp;\', strlen($matches[0]) );'
        ), $work_item['work_text']);
        
        return $work_item;
    }
    
    // Вывод списка разделов
    protected function display_group($group_parent = 0)
    {
        $group_list = db::select_all( '
            select * from work_group where group_active = 1 order by group_order' );
        $group_tree = tree::get_tree( $group_list, 'group_id', 'group_parent', $group_parent );
        foreach ($group_tree as $group_index => $group_item) {
            $group_tree[$group_index]['group_url'] =
                url_for(array('action' => 'group', 'id' => $group_item['group_id']));
        }
        
        $work_list = db::select_all( '
            select work_id, work_title from work
            where work_group = :work_group and work_active = 1
            order by work_order',
                array('work_group' => $group_parent));
        foreach ($work_list as $work_index => $work_item) {
            $work_list[$work_index]['work_url'] =
                url_for(array('action' => 'view', 'id' => $work_item['work_id']));
        }
        
        $group_path = array();
        while (true) {
            $group_item = db::select_row('
                select group_id, group_parent, group_title
                from work_group where group_id = :group_id',
                    array('group_id' => $group_parent));
            if ( !$group_item ) {
                break;
            }
            $group_parent = $group_item['group_parent'];
            $group_path[] = array('path_title' => $group_item['group_title'],
                'path_url' => url_for(array('action' => 'group', 'id' => $group_item['group_id'])));
        };
        
        if ($group_path) {
            $group_path[] = array('path_title' => 'Творчество', 'path_url' => url_for(array('action' => 'index')));
        }
        
        $this->view->assign('group_tree', $group_tree);
        $this->view->assign('work_list', $work_list);
        $this->view->assign('group_path', array_reverse($group_path));
        $this->content = $this->view->fetch('module/work/list.tpl');
    }
}