<?php


// variable function
$sum = function(int $x,int $y) {
    return $x + $y;
};

echo $sum(2, 3);

$array = [1,2,3,4];

$result = array_map(fn($x) => $x * 2, $array);
print_r($result);