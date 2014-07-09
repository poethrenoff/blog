<?php
namespace Adminko\Module;

use Adminko\Paginator;
use Adminko\Db\Db;
use Adminko\Model\Model;

class PortfolioModule extends Module
{
    // Вывод списка фотографий
    protected function actionIndex()
    {
        $rows_per_page = max(intval($this->getParam('rows_per_page_photo')), 1);
        $cols_per_page = max(intval($this->getParam('cols_per_page_photo')), 1);

        $items_per_page = $rows_per_page * $cols_per_page;
        
        $model_portfolio = Model::factory('portfolio');
        $portfolio_count = $model_portfolio->getCount(array('portfolio_active' => 1));
        
        if ($portfolio_count) {
            $pages = Paginator::create($portfolio_count, array('by_page' => $items_per_page));

            $portfolio_list = $model_portfolio->getList(array('portfolio_active' => 1),
                array('portfolio_order' => 'asc'), $pages['by_page'], $pages['offset']);

            $this->view->assign('portfolio_list', $portfolio_list);
            $this->view->assign('pages', Paginator::fetch($pages));
        }

        $this->content = $this->view->fetch('module/portfolio/list');
    }

}
