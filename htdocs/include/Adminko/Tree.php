<?php
namespace Adminko;

class Tree
{
    private static $primary_field = '';

    private static $parent_field = '';

    private static $records_by_parent = array();

    private static $records_as_tree = array();

    private static $except = array();

    public static function getTree(&$records, $primary_field, $parent_field, $begin = 0, $except = array())
    {
        self::$primary_field = $primary_field;
        self::$parent_field = $parent_field;
        self::$except = $except;

        self::$records_by_parent = array();
        foreach ($records as $record) {
            if (isset($record[self::$parent_field])) {
                self::$records_by_parent[$record[self::$parent_field]][] = $record;
            }
        }

        self::$records_as_tree = array();
        self::buildTree($begin);

        return self::$records_as_tree;
    }

    private static function buildTree($parent_field_id, $depth = 0)
    {
        if (isset(self::$records_by_parent[$parent_field_id])) {
            foreach (self::$records_by_parent[$parent_field_id] as $record) {
                if (isset($record[self::$primary_field]) &&
                    !in_array($record[self::$primary_field], self::$except)) {
                    $record['_depth'] = $depth;
                    $record['_has_children'] = isset(self::$records_by_parent[$record[self::$primary_field]]);
                    if ($record['_has_children']) {
                        $record['_children_count'] = count(self::$records_by_parent[$record[self::$primary_field]]);
                    }

                    self::$records_as_tree[] = $record;

                    self::buildTree($record[self::$primary_field], $depth + 1);
                }
            }
        }
    }
}
