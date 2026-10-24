<?php


$array = [1, 2, 3 , 4, 5, 6];

$val = array_filter($array, function($arr) {
    return $arr > 3;
});
