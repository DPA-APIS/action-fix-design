<?php

class ArrayUtils
{
     // 2) Funciones auxiliares
    public function removeKeysRecursive($array, $keyRemove) {
        foreach ($array as $key => $value) {
            if ($key === $keyRemove) {
                unset($array[$key]);
            } else if (is_array($value)) {
                $array[$key] = removeKeysRecursive($value, $keyRemove);
            } else if($value == "x-stoplight"){
                unset($array[$key]);
            }
        }
        return $array;
    }

    public function cleanEmptyArraysRec(array $array): array
    {
        $array = array_map(function ($item) {
            return is_array($item) ? cleanEmptyArraysRec($item) : $item;
        }, $array);

        $array = array_filter($array, function ($v) {
            return !(is_array($v) && empty($v));
        });

        return $array;
    }
}