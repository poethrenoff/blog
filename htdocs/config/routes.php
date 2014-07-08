<?php
/**
 * Пользовательские правила маршрутизации
 */
$routes = array(
    '/blog/@id' => array(
        'controller' => 'blog',
        'action' => 'item',
   ),
);
