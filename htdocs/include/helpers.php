<?php
// Инициализация строковой переменной
function init_string($varname, $vardef = '')
{
    if (isset($_REQUEST[$varname])) {
        return (string) $_REQUEST[$varname];
    } else {
        return (string) $vardef;
    }
}

// Инициализация массива
function init_array($varname, $vardef = array())
{
    if (isset($_REQUEST[$varname]) && is_array($_REQUEST[$varname])) {
        return (array) $_REQUEST[$varname];
    } else {
        return (array) $vardef;
    }
}

// Инициализация переменной из сессии
function init_session($varname, $vardef = '')
{
    if (isset($_SESSION[$varname])) {
        return $_SESSION[$varname];
    } else {
        return $vardef;
    }
}

// Инициализация переменной из куков
function init_cookie($varname, $vardef = '')
{
    if (isset($_COOKIE[$varname])) {
        return $_COOKIE[$varname];
    } else {
        return $vardef;
    }
}

function array_reindex($array, $key1 = '', $key2 = '', $key3 = '', $key4 = '')
{
    $reverted_array = array();

    if (is_array($array)) {
        foreach ($array as $item) {
            if (!$key1) {
                $reverted_array[$item] = $item;
            } else if (!$key2) {
                $reverted_array[$item[$key1]] = $item;
            } else if (!$key3) {
                $reverted_array[$item[$key1]][$item[$key2]] = $item;
            } else if (!$key4) {
                $reverted_array[$item[$key1]][$item[$key2]][$item[$key3]] = $item;
            } else {
                $reverted_array[$item[$key1]][$item[$key2]][$item[$key3]][$item[$key4]] = $item;
            }
        }
    }

    return $reverted_array;
}

function array_group($array, $key1 = '', $key2 = '', $key3 = '', $key4 = '')
{
    $grouped_array = array();

    if (is_array($array)) {
        foreach ($array as $item) {
            if (!$key1) {
                $grouped_array[$item][] = $item;
            } else if (!$key2) {
                $grouped_array[$item[$key1]][] = $item;
            } else if (!$key3) {
                $grouped_array[$item[$key1]][$item[$key2]][] = $item;
            } else if (!$key4) {
                $grouped_array[$item[$key1]][$item[$key2]][$item[$key3]][] = $item;
            } else {
                $grouped_array[$item[$key1]][$item[$key2]][$item[$key3]][$item[$key4]][] = $item;
            }
        }
    }

    return $grouped_array;
}

function array_list($array, $key)
{
    $values_array = array();

    if (is_array($array)) {
        foreach ($array as $item) {
            $values_array[] = $item[$key];
        }
    }

    return $values_array;
}

function array_make_in($array, $key = '', $quote = false)
{
    $in = '0';
    $ids = array();

    if (is_array($array)) {
        foreach ($array as $record) {
            $ids[] = $quote ? ($key ? addslashes($record[$key]) : addslashes($record)) :
                    ($key ? intval($record[$key]) : intval($record));
        }

        if (count($ids)) {
            $in = $quote ? ("'" . join("', '", $ids) . "'") : join(", ", $ids);
        }
    }

    return $in;
}

function get_preference($preference_name, $default_value = '')
{
    if (defined($preference_name)) {
        return constant($preference_name);
    } else {
        return $default_value;
    }
}

function is_empty($var)
{
    if (is_array($var) || is_object($var)) {
        return empty($var);
    } else {
        return trim($var) === '';
    }
}

function decl_of_num($number, $titles, $view_number = true)
{
    $cases = array(2, 0, 1, 1, 1, 2);
    $value = abs($number);
    return ($view_number ? $number . ' ' : '') . $titles[($value % 100 > 4 && $value % 100 < 20) ? 2 : $cases[min($value % 10, 5)]];
}

function generate_key($max = 128)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $len = strlen($chars) - 1;
    $password = '';
    while ($max--) {
        $password .= $chars[rand(0, $len)];
    }
    return $password;
}

function get_probability($percent)
{
    return mt_rand(0, mt_getrandmax()) < $percent * mt_getrandmax() / 100;
}

function delete_directory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

function normalize_path($path)
{
    return preg_replace("/\/+/", "/", str_replace("\\", "/", trim($path)));
}

function strip_tags_attributes($string, $allowtags = null, $allowattributes = null)
{
    $string = strip_tags($string, $allowtags);

    if (!is_null($allowattributes)) {
        if (!is_array($allowattributes)) {
            $allowattributes = explode(',', $allowattributes);
        }
        if (is_array($allowattributes)) {
            $allowattributes = implode(')(?<!', $allowattributes);
        }
        if (strlen($allowattributes) > 0) {
            $allowattributes = '(?<!' . $allowattributes . ')';
        }
        $string = preg_replace_callback('/<[^>]*>/i', create_function(
                        '$matches', 'return preg_replace("/ [^ =]*' . $allowattributes .
                        '=(\"[^\"]*\"|\'[^\']*\')/i", "", $matches[0]);'), $string);
    }

    return $string;
}

function to_translit($string)
{
    $replace = array(
        "а" => "a", "А" => "a", "б" => "b", "Б" => "b", "в" => "v", "В" => "v",
        "г" => "g", "Г" => "g", "д" => "d", "Д" => "d", "е" => "e", "Е" => "e", "ж" => "zh", "Ж" => "zh",
        "з" => "z", "З" => "z", "и" => "i", "И" => "i", "й" => "y", "Й" => "y", "к" => "k", "К" => "k",
        "л" => "l", "Л" => "l", "м" => "m", "М" => "m", "н" => "n", "Н" => "n", "о" => "o", "О" => "o",
        "п" => "p", "П" => "p", "р" => "r", "Р" => "r", "с" => "s", "С" => "s", "т" => "t", "Т" => "t",
        "у" => "u", "У" => "u", "ф" => "f", "Ф" => "f", "х" => "h", "Х" => "h", "ц" => "c", "Ц" => "c",
        "ч" => "ch", "Ч" => "ch", "ш" => "sh", "Ш" => "sh", "щ" => "sch", "Щ" => "sch",
        "ъ" => "", "Ъ" => "", "ы" => "y", "Ы" => "y", "ь" => "", "Ь" => "", "э" => "e", "Э" => "e",
        "ю" => "yu", "Ю" => "yu", "я" => "ya", "Я" => "ya", "і" => "i", "І" => "i",
        "ї" => "yi", "Ї" => "yi", "є" => "e", "Є" => "e", "ё" => "e", "Ё" => "e"
    );
    return iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
}

function to_class_name($string)
{
    return preg_replace_callback(
        '/_([a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, ucfirst(strtolower($string))
    );
}

function to_field_name($string)
{
    return preg_replace_callback(
        '/[A-Z]/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, lcfirst($string)
    );
}
