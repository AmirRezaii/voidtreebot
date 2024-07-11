<?php

namespace TBot;

class ArrayManager {
    public static function combineArrays(...$arrays) : array {
        $res = [];
        foreach ($arrays as $arr) {
            $res = array_unique(array_merge($res, $arr), SORT_REGULAR);
        }
        return $res;
    }
}
