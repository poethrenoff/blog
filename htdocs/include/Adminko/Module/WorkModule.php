<?php
namespace Adminko\Module;

use Adminko\Tree;
use Adminko\System;
use Adminko\Db\Db;

class WorkModule extends Module
{
    // Вывод списка корневых разделов
    protected function actionIndex()
    {
        $this->displayGroup();
    }

    // Вывод списка подразделов
    protected function actionGroup()
    {
        $group_item = $this->getGroupItem(System::id());
        $this->displayGroup($group_item['group_id']);
    }

    // Вывод произведения
    protected function actionView($id = null)
    {
        $work_item = $this->getWorkItem($id ? $id : System::id());

        $group_parent = $work_item['group_id'];

        $group_path = array();
        while (true) {
            $group_item = Db::selectRow('
                select group_id, group_parent, group_title
                from work_group where group_id = :group_id', array('group_id' => $group_parent));
            if (!$group_item) {
                break;
            }
            $group_parent = $group_item['group_parent'];
            $group_path[] = array('path_title' => $group_item['group_title'],
                'path_url' => System::urlFor(array('action' => 'group', 'id' => $group_item['group_id'])));
        }

        if ($group_path) {
            $group_path[] = array('path_title' => 'Творчество', 'path_url' => System::urlFor(array('action' => 'index')));
        }

        $this->view->assign($work_item);
        $this->view->assign('group_path', array_reverse($group_path));
        $this->content = $this->view->fetch('module/work/view');
    }

    // Вывод случайного произведения
    protected function actionRandom()
    {
        $random_work_item = Db::selectRow('
            select * from work, work_group where
                work_group = group_id and work_active = 1 and group_active = 1
            order by rand() limit 1');
        $this->action_view($random_work_item['work_id']);
    }

    // Получение раздела
    protected function getGroupItem($group_id)
    {
        $group_item = Db::selectRow('
            select * from work_group where group_id = :group_id and group_active = 1', array('group_id' => $group_id));
        if (!$group_item) {
            System::notFound();
        }

        return $group_item;
    }

    // Получение произведения
    protected function getWorkItem($work_id)
    {
        $work_item = Db::selectRow('
            select * from work, work_group where work_id = :work_id and
                work_group = group_id and work_active = 1 and group_active = 1', array('work_id' => $work_id));
        if (!$work_item) {
            System::notFound();
        }

        $work_item['work_text'] = preg_replace_callback('/^ +| {2,}/m', create_function(
                '$matches', 'return str_repeat( \'&nbsp;\', strlen($matches[0]) );'
            ), $work_item['work_text']);

        return $work_item;
    }

    // Вывод списка разделов
    protected function displayGroup($group_parent = 0)
    {
        $group_list = Db::selectAll('
            select * from work_group where group_active = 1 order by group_order');
        $group_tree = Tree::getTree($group_list, 'group_id', 'group_parent', $group_parent);
        foreach ($group_tree as $group_index => $group_item) {
            $group_tree[$group_index]['group_url'] = System::urlFor(array('action' => 'group', 'id' => $group_item['group_id']));
        }

        $work_list = Db::selectAll('
            select work_id, work_title from work
            where work_group = :work_group and work_active = 1
            order by work_order', array('work_group' => $group_parent));
        foreach ($work_list as $work_index => $work_item) {
            $work_list[$work_index]['work_url'] = System::urlFor(array('action' => 'view', 'id' => $work_item['work_id']));
        }

        $group_path = array();
        while (true) {
            $group_item = Db::selectRow('
                select group_id, group_parent, group_title
                from work_group where group_id = :group_id', array('group_id' => $group_parent));
            if (!$group_item) {
                break;
            }
            $group_parent = $group_item['group_parent'];
            $group_path[] = array('path_title' => $group_item['group_title'],
                'path_url' => System::urlFor(array('action' => 'group', 'id' => $group_item['group_id'])));
        }

        if ($group_path) {
            $group_path[] = array('path_title' => 'Творчество', 'path_url' => System::urlFor(array('action' => 'index')));
        }

        $this->view->assign('group_tree', $group_tree);
        $this->view->assign('work_list', $work_list);
        $this->view->assign('group_path', array_reverse($group_path));
        $this->content = $this->view->fetch('module/work/list');
    }
}
