<?php
class metadata
{
	public static $objects = array(
		/*
		 *	Таблица "Тексты"
		 */
		'text' => array(
			'title' => 'Тексты',
			'fields' => array(
				'text_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'text_tag' => array( 'title' => 'Метка', 'type' => 'string', 'show' => 1, 'sort' => 'asc', 'errors' => 'require|alpha', 'group' => array() ),
				'text_title' => array( 'title' => 'Заголовок', 'type' => 'string', 'show' => 1, 'main' => 1, 'errors' => 'require' ),
				'text_content' => array( 'title' => 'Текст', 'type' => 'text', 'editor' => 1, 'errors' => 'require' ),
			)
		),
		
		/*
		 *	Таблица "Новости"
		 */
		'news' => array(
			'title' => 'Новости',
			'class' => 'news',
			'fields' => array(
				'news_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'news_content' => array( 'title' => 'Текст', 'type' => 'text', 'editor' => 1, 'main' => 1, 'errors' => 'require' ),
				'news_date' => array( 'title' => 'Дата публикации', 'type' => 'datetime', 'show' => 1, 'sort' => 'desc', 'errors' => 'require' ),
				'news_publish' => array( 'title' => 'Опубликовать', 'type' => 'boolean', 'filter' => 1 ),
				'news_active' => array( 'title' => 'Видимость', 'type' => 'active' ),
			),
			'links' => array(
				'comment' => array( 'table' => 'comment', 'field' => 'comment_news', 'ondelete' => 'cascade' )
			),
			'relations' => array(
				'news_tag' => array( 'secondary_table' => 'tag', 'relation_table' => 'news_tag',
					'primary_field' => 'news_id', 'secondary_field' => 'tag_id', 'title' => 'Теги' ),
			),
		),
		
		/*
		 *	Таблица "Комментарии"
		 */
		'comment' => array(
			'title' => 'Комментарии',
			'no_add' => true,
			'fields' => array(
				'comment_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'comment_news' => array( 'title' => 'Новость', 'type' => 'table', 'table' => 'news', 'errors' => 'require', 'no_edit' => 1, 'no_filter' => 1 ),
				'comment_parent' => array( 'title' => 'Родительский комментарий', 'type' => 'table', 'table' => 'comment', 'no_edit' => 1 ),
				'comment_content' => array( 'title' => 'Текст', 'type' => 'text', 'main' => 1, 'editor' => 1, 'errors' => 'require' ),
				'comment_author' => array( 'title' => 'Автор', 'type' => 'string', 'errors' => 'require' ),
				'comment_date' => array( 'title' => 'Дата публикации', 'type' => 'datetime', 'show' => 1, 'sort' => 'desc', 'errors' => 'require' ),
				'comment_info' => array( 'title' => 'Доп. информация', 'type' => 'text' ),
			)
		),
		
		/*
		 *	Таблица "Теги"
		 */
		'tag' => array(
			'title' => 'Теги',
			'fields' => array(
				'tag_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'tag_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1 ),
			),
		),
		
		/*
		 *	Таблица "Теги новостей"
		 */
		'news_tag' => array(
			'title' => 'Теги новостей',
			'internal' => true,
			'fields' => array(
				'news_id' => array( 'title' => 'Новость', 'type' => 'table', 'table' => 'news', 'errors' => 'require' ),
				'tag_id' => array( 'title' => 'Тег', 'type' => 'table', 'table' => 'tag', 'errors' => 'require' ),
			),
		),
		
		/*
		 *	Таблица "Портфолио"
		 */
		'portfolio' => array(
			'title' => 'Портфолио',
			'fields' => array(
				'portfolio_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'portfolio_title' => array( 'title' => 'Название', 'type' => 'string', 'show' => 1, 'main' => 1, 'errors' => 'require' ),
				'portfolio_picture' => array( 'title' => 'Изображение', 'type' => 'image', 'upload_dir' => '/upload/portfolio/' ),
				'portfolio_url' => array( 'title' => 'Ссылка на сайт', 'type' => 'string', 'show' => 1 ),
				'portfolio_order' => array( 'title' => 'Порядок', 'type' => 'order' ),
				'portfolio_active' => array( 'title' => 'Активность', 'type' => 'active' ),
			),
		),
		
		/*
		 *	Таблица "Произведения"
		 */
		'work' => array(
			'class' => 'work',
			'title' => 'Произведения',
			'fields' => array(
				'work_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'work_group' => array( 'title' => 'Раздел', 'type' => 'table', 'table' => 'work_group', 'errors' => 'require' ),
				'work_title' => array( 'title' => 'Заголовок', 'type' => 'string', 'main' => 1 ),
				'work_text' => array( 'title' => 'Текст', 'type' => 'text', 'errors' => 'require', 'filter' => 1 ),
				'work_comment' => array( 'title' => 'Комментарий', 'type' => 'string' ),
				'work_order' => array( 'title' => 'Порядок', 'type' => 'order', 'group' => array( 'work_group' ) ),
				'work_active' => array( 'title' => 'Видимость', 'type' => 'active' ),
			),
		),
		
		/*
		 *	Таблица "Разделы"
		 */
		'work_group' => array(
			'title' => 'Разделы',
			'fields' => array(
				'group_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'group_parent' => array( 'title' => 'Родительский комментарий', 'type' => 'parent' ),
				'group_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'group_comment' => array( 'title' => 'Комментарий', 'type' => 'string' ),
				'group_order' => array( 'title' => 'Порядок', 'type' => 'order', 'group' => array( 'group_parent' ) ),
				'group_active' => array( 'title' => 'Видимость', 'type' => 'active' ),
			),
			'links' => array(
				'work' => array( 'table' => 'work', 'field' => 'work_group' )
			),
		),
		
		////////////////////////////////////////////////////////////////////////////////////////
		
		/*
		 *	Таблица "Настройки"
		 */
		'preference' => array(
			'title' => 'Настройки',
			'class' => 'builder',
			'fields' => array(
				'preference_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'preference_title' => array( 'title' => 'Название', 'type' => 'string', 'show' => 1, 'main' => 1, 'errors' => 'require' ),
				'preference_name' => array( 'title' => 'Имя', 'type' => 'string', 'show' => 1, 'errors' => 'require|alpha', 'group' => array() ),
				'preference_value' => array( 'title' => 'Значение', 'type' => 'string', 'show' => 1 ),
			)
		),
		
		/*
		 *	Таблица "Разделы"
		 */
		'page' => array(
			'title' => 'Разделы',
			'class' => 'page',
			'fields' => array(
				'page_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'page_parent' => array( 'title' => 'Родительский раздел', 'type' => 'parent' ),
				'page_layout' => array( 'title' => 'Шаблон', 'type' => 'table', 'table' => 'layout', 'errors' => 'require' ),
				'page_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'page_name' => array( 'title' => 'Каталог', 'type' => 'string', 'show' => 1, 'errors' => 'alpha', 'group' => array( 'page_parent' ) ),
				'page_folder' => array( 'title' => 'Папка', 'type' => 'boolean' ),
				'meta_title' => array( 'title' => 'Заголовок', 'type' => 'text' ),
				'meta_keywords' => array( 'title' => 'Ключевые слова', 'type' => 'text' ),
				'meta_description' => array( 'title' => 'Описание', 'type' => 'text' ),
				'page_order' => array( 'title' => 'Порядок', 'type' => 'order', 'group' => array( 'page_parent' ) ),
				'page_active' => array( 'title' => 'Видимость', 'type' => 'active' ),
			),
			'links' => array(
				'block' => array( 'table' => 'block', 'field' => 'block_page', 'ondelete' => 'cascade' ),
			),
		),
		
		/*
		 *	Таблица "Блоки"
		 */
		'block' => array(
			'title' => 'Блоки',
			'class' => 'block',
			'fields' => array(
				'block_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'block_page' => array( 'title' => 'Раздел', 'type' => 'table', 'table' => 'page', 'errors' => 'require' ),
				'block_module' => array( 'title' => 'Модуль', 'type' => 'table', 'table' => 'module', 'errors' => 'require' ),
				'block_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'block_area' => array( 'title' => 'Область шаблона', 'type' => 'table', 'table' => 'layout_area', 'errors' => 'require' ),
			),
			'links' => array(
				'block_param' => array( 'table' => 'block_param', 'field' => 'block', 'ondelete' => 'cascade' ),
			),
		),
		
		/*
		 *	Таблица "Шаблоны"
		 */
		'layout' => array(
			'title' => 'Шаблоны',
			'class' => 'layout',
			'fields' => array(
				'layout_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'layout_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'layout_name' => array( 'title' => 'Системное имя', 'type' => 'string', 'show' => 1, 'errors' => 'require|alpha' ),
			),
			'links' => array(
				'page' => array( 'table' => 'page', 'field' => 'page_layout', 'hidden' => 1 ),
				'area' => array( 'table' => 'layout_area', 'field' => 'area_layout', 'title' => 'Области' ),
			),
		),
		
		/*
		 *	Таблица "Области шаблона"
		 */
		'layout_area' => array(
			'title' => 'Области шаблона',
			'class' => 'builder',
			'fields' => array(
				'area_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'area_layout' => array( 'title' => 'Шаблон', 'type' => 'table', 'table' => 'layout', 'errors' => 'require' ),
				'area_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'area_name' => array( 'title' => 'Системное имя', 'type' => 'string', 'show' => 1, 'errors' => 'require|alpha' ),
				'area_main' => array( 'title' => 'Главная область', 'type' => 'default', 'show' => 1, 'group' => array( 'area_layout' ) ),
				'area_order' => array( 'title' => 'Порядок', 'type' => 'order', 'group' => array( 'area_layout' ) ),
			),
			'links' => array(
				'bloсk' => array( 'table' => 'block', 'field' => 'block_area' ),
			),
		),
		
		/*
		 *	Таблица "Модули"
		 */
		'module' => array(
			'title' => 'Модули',
			'class' => 'module',
			'fields' => array(
				'module_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'module_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'module_name' => array( 'title' => 'Системное имя', 'type' => 'string', 'show' => 1, 'errors' => 'require|alpha' ),
			),
			'links' => array(
				'block' => array( 'table' => 'block', 'field' => 'block_module' ),
				'module_param' => array( 'table' => 'module_param', 'field' => 'param_module', 'title' => 'Параметры', 'ondelete' => 'cascade' ),
			),
		),
		
		/*
		 *	Таблица "Параметры модулей"
		 */
		'module_param' => array(
			'title' => 'Параметры модулей',
			'class' => 'param',
			'fields' => array(
				'param_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'param_module' => array( 'title' => 'Модуль', 'type' => 'table', 'table' => 'module', 'errors' => 'require' ),
				'param_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'param_type' => array( 'title' => 'Тип параметра', 'type' => 'select', 'filter' => 1, 'values' => array(
						array( 'value' => 'string', 'title' => 'Строка' ),
						array( 'value' => 'int', 'title' => 'Число' ),
						array( 'value' => 'text', 'title' => 'Текст' ),
						array( 'value' => 'select', 'title' => 'Список' ),
						array( 'value' => 'table', 'title' => 'Таблица' ),
						array( 'value' => 'boolean', 'title' => 'Флаг' ) ), 'show' => 1, 'errors' => 'require' ),
				'param_name' => array( 'title' => 'Системное имя', 'type' => 'string', 'show' => 1, 'group' => array( 'param_module' ), 'errors' => 'require|alpha' ),
				'param_table' => array( 'title' => 'Имя таблицы', 'type' => 'string', 'show' => 1 ),
				'param_default' => array( 'title' => 'Значение по умолчанию', 'type' => 'string' ),
				'param_require' => array( 'title' => 'Обязательное', 'type' => 'boolean' ),
				'param_order' => array( 'title' => 'Порядок', 'type' => 'order', 'group' => array( 'param_module' ) ),
			),
			'links' => array(
				'param_value' => array( 'table' => 'param_value', 'field' => 'value_param', 'show' => array( 'param_type' => array( 'select' ) ), 'title' => 'Значения', 'ondelete' => 'cascade' ),
				'block_param' => array( 'table' => 'block_param', 'field' => 'param', 'ondelete' => 'cascade' ),
			),
		),
		
		/*
		 *	Таблица "Значения параметров модулей"
		 */
		'param_value' => array(
			'title' => 'Значения параметров модулей',
			'class' => 'value',
			'fields' => array(
				'value_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'value_param' => array( 'title' => 'Параметр', 'type' => 'table', 'table' => 'module_param', 'errors' => 'require' ),
				'value_title' => array( 'title' => 'Название', 'type' => 'string', 'main' => 1, 'errors' => 'require' ),
				'value_content' => array( 'title' => 'Значение', 'type' => 'string', 'show' => 1, 'group' => array( 'value_param' ), 'errors' => 'require' ),
				'value_default' => array( 'title' => 'По умолчанию', 'type' => 'default', 'show' => 1, 'group' => array( 'value_param' ) ),
			),
		),
		
		/*
		 *	Таблица "Параметры блоков"
		 */
		'block_param' => array(
			'title' => 'Параметры блоков',
			'internal' => true,
			'fields' => array(
				'block' => array( 'title' => 'Блок', 'type' => 'table', 'table' => 'block' ),
				'param' => array( 'title' => 'Параметр', 'type' => 'table', 'table' => 'module_param' ),
				'value' => array( 'title' => 'Значение', 'type' => 'text' ),
			),
		),
		
		/*
		 *	Таблицы управления правами доступа
		 */
		
		'admin' => array(
			'title' => 'Администраторы',
			'fields' => array(
				'admin_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'admin_title' => array( 'title' => 'Имя', 'type' => 'string', 'show' => 1, 'main' => 1, 'errors' => 'require' ),
				'admin_login' => array( 'title' => 'Логин', 'type' => 'string', 'show' => 1, 'errors' => 'require|alpha', 'group' => array() ),
				'admin_password' => array( 'title' => 'Пароль', 'type' => 'password' ),
				'admin_email' => array( 'title' => 'Email', 'type' => 'string', 'errors' => 'email' ),
				'admin_active' => array( 'title' => 'Активный', 'type' => 'active' ),
			),
			'relations' => array(
				'admin_role' => array( 'secondary_table' => 'role', 'relation_table' => 'admin_role',
					'primary_field' => 'admin_id', 'secondary_field' => 'role_id' ),
			),
		),
		
		'admin_role' => array(
			'title' => 'Роли администраторов',
			'internal' => true,
			'fields' => array(
				'admin_id' => array( 'title' => 'Администратор', 'type' => 'table', 'table' => 'admin', 'errors' => 'require' ),
				'role_id' => array( 'title' => 'Роль', 'type' => 'table', 'table' => 'role', 'errors' => 'require' ),
			),
		),
		
		'role' => array(
			'title' => 'Роли',
			'fields' => array(
				'role_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'role_title' => array( 'title' => 'Название', 'type' => 'string', 'show' => 1, 'main' => 1, 'errors' => 'require' ),
				'role_default' => array( 'title' => 'Главный администратор', 'type' => 'default', 'show' => 1 ),
			),
			'relations' => array(
				'role_object' => array( 'secondary_table' => 'object', 'relation_table' => 'role_object',
					'primary_field' => 'role_id', 'secondary_field' => 'object_id' ),
			),
		),
		
		'role_object' => array(
			'title' => 'Права на системные разделы',
			'internal' => true,
			'fields' => array(
				'role_id' => array( 'title' => 'Роль', 'type' => 'table', 'table' => 'role', 'errors' => 'require' ),
				'object_id' => array( 'title' => 'Системный раздел', 'type' => 'table', 'table' => 'object', 'errors' => 'require' ),
			),
		),
		
		'object' => array(
			'title' => 'Системные разделы',
			'fields' => array(
				'object_id' => array( 'title' => 'Идентификатор', 'type' => 'pk' ),
				'object_parent' => array( 'title' => 'Родительский раздел', 'type' => 'parent' ),
				'object_title' => array( 'title' => 'Название', 'type' => 'string', 'show' => 1, 'main' => 1, 'errors' => 'require' ),
				'object_name' => array( 'title' => 'Объект', 'type' => 'string', 'show' => 1 ),
				'object_order' => array( 'title' => 'Порядок', 'type' => 'order', 'group' => array( 'object_parent' ) ),
				'object_active' => array( 'title' => 'Видимость', 'type' => 'active' ),
			)
		),
		
		/*
		 *	Утилита "Файл-менеджер"
		 */
		'fm' => array(
			'title' => 'Файл-менеджер',
			'class' => 'fm'
		),
	);
}

// db::create();
