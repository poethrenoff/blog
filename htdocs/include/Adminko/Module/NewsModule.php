<?php
namespace Adminko\Module;

use Adminko\Date;
use Adminko\Captcha;
use Adminko\System;
use Adminko\Purifier;
use Adminko\Paginator;
use Adminko\View;
use Adminko\Sendmail;
use Adminko\Model\Model;

class NewsModule extends Module
{
    // Разрешен просмотр только опубликованных новостей
    protected $only_publish = false;

    // Инициализация модуля
    public function init($action = 'index', $params = array(), $is_main = false)
    {
        $this->only_publish = init_cookie('view') != 'all';

        parent::init($action, $params, $is_main);
    }

    // Вывод списка новостей
    protected function actionIndex()
    {
        $news_by_page = $this->getParam('news_by_page');

        $news_model = Model::factory('news');
        $news_count = $news_model->getCount($this->only_publish);

        if ($news_count) {
            $pages = Paginator::create($news_count, array('by_page' => $news_by_page));

            $news_list = $news_model->getList($this->only_publish, $pages['by_page'], $pages['offset']);

            $this->view->assign('news_list', $news_list);
            $this->view->assign('pages', Paginator::fetch($pages));
        }

        $this->content = $this->view->fetch('module/news/list');
    }

    // Вывод карточки новости
    protected function actionPost()
    {
        try {
            $news_item = Model::factory('news')->getNewsItem(System::id(), $this->only_publish);
        } catch (\AlarmException $e) {
            Sysyem::notFound();
        }

        if (init_string('action') == 'comment') {
            try {
                $this->addComment($news_item);
            } catch (\AlarmException $e) {
                $this->view->assign('error', $e->getMessage());
            }
        }
        if (init_string('mode') == 'reply') {
            $this->view->assign('show_form', intval(init_string('comment_parent')));
        }
        
        $this->view->assign($news_item);

        $comment_list = Model::factory('comment')->getList(array('comment_news' => System::id()), array('comment_date' => 'asc'));
        $comment_tree = Model::factory('comment')->getTree($comment_list);

        $this->view->assign('comment_author', init_string('comment_author') ?
                init_string('comment_author') : init_cookie('author') );
        $this->view->assign('comment_content', init_string('comment_content'));

        $this->view->assign('comment_tree', $comment_tree);

        $this->content = $this->view->fetch('module/news/post');
    }

    // Добавление комментария
    protected function addComment($news_item)
    {
        $comment_parent = init_string('comment_parent');
        $comment_author = init_string('comment_author');
        $comment_content = init_string('comment_content');

        $captcha_value = init_string('captcha_value');

        if (!$comment_author) {
            throw new \AlarmException('Ошибка! Не заполнено поле "ИМЯ"!');
        }
        if (!$comment_content) {
            throw new \AlarmException('Ошибка! Не заполнено поле "КОММЕНТАРИЙ"!');
        }
        if (!$captcha_value) {
            throw new \AlarmException('Ошибка! Не заполнено поле "ЧИСЛО"!');
        }
        if (!Captcha::check($captcha_value)) {
            throw new \AlarmException('Ошибка! Введеное число не соответствует коду на изображении!');
        }

        $comment_item = Model::factory('comment')
            ->setCommentNews($news_item->getId())
            ->setCommentParent((int) $comment_parent)
            ->setCommentContent(Purifier::clear($comment_content))
            ->setCommentAuthor($comment_author)
            ->setCommentDate(Date::now())
            ->setCommentInfo(
                filter_input(INPUT_SERVER, 'REMOTE_ADDR') . ", " .
                filter_input(INPUT_SERVER, 'HTTP_USER_AGENT')
            )
            ->save();

        $admin_email = get_preference('admin_email');
        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');

        $subject = 'Новый комментарий на "Не дождетесь!"';

        $mail_view = new View();
        $mail_view->assign($comment_item);

        $message = $mail_view->fetch('module/news/mail');

        Sendmail::send($admin_email, $from_email, $from_name, $subject, $message);

        setcookie('author', $comment_author, time() + 60 * 60 * 24 * 30, '/');

        System::redirectTo(System::selfUrl());
    }

    // Экспорт новостей
    protected function actionRss()
    {
        $news_by_page = $this->getParam('news_by_page_rss');

        $news_list = Model::factory('news')->getList($this->only_publish, $news_by_page, 0);

        $this->view->assign('news_list', $news_list);

        header('Content-type: text/xml; charset: UTF-8');

        $this->view->display('module/news/rss');

        exit;
    }

    // Пропуск под замок
    protected function actionAccess()
    {
        setcookie('view', 'all', time() + 356 * 60 * 60 * 24, '/');

        header('Location: ' . System::selfUrl());

        exit;
    }
}
