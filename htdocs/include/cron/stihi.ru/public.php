<?php
    /**
     * Скрипт для публикации произведений на сайте "Стихи.ру"
     */
    
    include_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/config/config.php';
    
    $server = 'http://www.stihi.ru';
    $login_url = '/cgi-bin/login/intro.pl';
    $public_url = '/cgi-bin/login/page.pl';
    $put_url = '/login/page.html?put';
    $add_url = '/login/page.html?add';
    
    $user_login = 'poethrenoff';
    $user_password = '1q2w3e4r';
    $user_agent = 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.14 Safari/534.24';
    
//    $server_rubric = '8'; $local_rubric = '6';   // Приятные неожиданности (45)
//    $server_rubric = '9'; $local_rubric = '7';   // Исправленному не верить (21)
//    $server_rubric = '10'; $local_rubric = '9';  // Ветер меняется (28)
//    $server_rubric = '11'; $local_rubric = '10'; // Лампочка Кузьмича (31)
//    $server_rubric = '12'; $local_rubric = '11'; // Терпи, бумага (222)
//    $server_rubric = '13'; $local_rubric = '12'; // Под грохот арф (56)
//    $server_rubric = '14'; $local_rubric = '23'; // Песни подземной реки (8)
//    $server_rubric = '14'; $local_rubric = '24'; // Стихи о стихах (7)
//    $server_rubric = '14'; $local_rubric = '14'; // Ранние стихи (77)
//    $server_rubric = '15'; $local_rubric = '15'; // Первые опыты (3)
//    $server_rubric = '16'; $local_rubric = '16'; // Поэмы (3)
//    $server_rubric = '17'; $local_rubric = '18'; // Стихи, не вощедшие в циклы (11)
//    $server_rubric = '18';                       // Всякое, прочее и остальное (?)
//    $server_rubric = '20'; $local_rubric = '56'; // Это ничего (282)
//    $server_rubric = '21'; $local_rubric = '57'; // Нет никогда одиночества (142)
    $server_rubric = '22'; $local_rubric = '58'; // Чаепитие Узри (203)

    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POST, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, "login={$user_login}&password={$user_password}" );
    curl_setopt($ch, CURLOPT_URL, $server . $login_url);
    
    $result = curl_exec($ch);
    
    preg_match( '/(login=\w+;)/', $result, $login_match );
    preg_match( '/(pcode=\d+;)/', $result, $pcode_match );
    
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_COOKIE, $login_match[1] . ' ' . $pcode_match[1]);
    
    $work_list = db::select_all( '
        select * from work where work_group = :work_group and work_active = 1 order by work_order desc limit 20 offset 203',
            array('work_group' => $local_rubric) );
    
    foreach($work_list as $work) {
        curl_setopt($ch, CURLOPT_POST, 0 );
        curl_setopt($ch, CURLOPT_URL, $server . $add_url );
        
        $result = curl_exec($ch);
        
        preg_match( '/\<input type="hidden" name="code" value="(\d+)"\>/', $result, $code_match );
        $code = $code_match[1];
        
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_URL, $server . $public_url );
        
        print $work['work_title'] . "\t\t\t";
        
        $title = iconv('UTF-8', 'Windows-1251', $work['work_title']);
        $text = iconv('UTF-8', 'Windows-1251', $work['work_text']);
        $comment = iconv('UTF-8', 'Windows-1251', $work['work_comment']);
        
        $text  = preg_replace_callback ('/^ +| {2,}/m', create_function(
            '$matches', 'return str_repeat( \'  \', strlen($matches[0]) );'
        ), $text);
        
        $text = rtrim($text) . ($comment ? "\r\n\r\n" . trim($comment) : '');
        
        $text = urlencode($text); $title = urlencode($title);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, "title={$title}&text={$text}&code={$code}&block=save&text_topic=03&dogovor=on" );
        
        $result = curl_exec($ch);
        
        $success = preg_match( "/\<a href=\"\/login\/page\.html\?edit\&(link=\d+\/\d+\/\d+\/\d+)\"\>/isU", $result, $matches);
        
        if ($success) {
            curl_setopt($ch, CURLOPT_POST, 0 );
            curl_setopt($ch, CURLOPT_URL, $server . $put_url . "&{$matches[1]}&to={$server_rubric}" );
            curl_exec($ch);
        }
        
        print ($success ? '[OK]' : '[ERROR]') . "\n";
    }
