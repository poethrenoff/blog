<?php
namespace Adminko\Module;

use Adminko\Tree;
use Adminko\System;
use Adminko\Model\Model;
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
        try {
            $group_item = Model::factory('work_group')->get(System::id());
        } catch (\AlarmException $e) {
            Sysyem::notFound();
        }
        $this->displayGroup($group_item->getId());
    }

    // Вывод произведения
    protected function actionView()
    {
        try {
            $work_item = Model::factory('work')->getWorkItem(System::id());
        } catch (\AlarmException $e) {
            Sysyem::notFound();
        }

        $group_path = $this->getGroupPath($work_item->getWorkGroup());

        $this->view->assign('work_item', $work_item);
        $this->view->assign('group_path', $group_path);
        $this->content = $this->view->fetch('module/work/view');
    }

    // Вывод случайного произведения
    protected function actionRandom()
    {
        try {
            $work_item = Model::factory('work')->getWorkItem();
        } catch (\AlarmException $e) {
            Sysyem::notFound();
        }

        $group_path = $this->getGroupPath($work_item->getWorkGroup());

        $this->view->assign('work_item', $work_item);
        $this->view->assign('group_path', $group_path);
        $this->content = $this->view->fetch('module/work/view');
    }

    // Хлебные крошки
    protected function getGroupPath($group_parent = 0)
    {
        $group_path = array();
        while (true) {
            try {
                $group_item = Model::factory('work_group')->get($group_parent);
            } catch (\AlarmException $e) {
                break;
            }
            $group_path[] = $group_item;
            $group_parent = $group_item->getGroupParent();
        }
        if ($group_path) {
            $group_path[] = Model::factory('work_group');
        }

        return array_reverse($group_path);
    }
    
    // Вывод списка разделов
    protected function displayGroup($group_id = 0)
    {
        $group_list = Model::factory('work_group')->getList(array('group_active' => 1), array('group_order' => 'asc'));
        $group_tree = Model::factory('work_group')->getTree($group_list, $group_id);
        $work_list = Model::factory('work')->getWorkList($group_id);

        $group_path = $this->getGroupPath($group_id);

        $this->view->assign('group_tree', $group_tree);
        $this->view->assign('work_list', $work_list);
        $this->view->assign('group_path', $group_path);
        $this->content = $this->view->fetch('module/work/list');
    }
}