<?php
declare(strict_types=1);


// SCALAR

// boolean
 $completed = true;

 echo "{$completed} <br />";

// integer
 $age = 10;
 echo "{$age} <br />";


// float
 $price = 10.5;
 echo "{$price} <br />";

// string
 $greeting = "How far?";
 echo "{$greeting} <br />";


//  COMPOUND
$cities = ["Lagos", "Oyo", "Abuja", "Kano"];
print_r($cities);




function sum(int $x, int $y) {
    return $x + $y;
}

echo sum(5, 5);