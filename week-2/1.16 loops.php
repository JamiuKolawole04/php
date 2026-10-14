<?php

// while loop

$x = 0;


while ($x < 5) {
    echo "x is a number <br />";

    $x++;
}


$i = 0;
// do-while

do {
    echo "i is a number <br />";

    $i++;
} while($i < 5);

// for

for ($z = 0; $z <= 5; $z++) {
    echo "z is a number <br />";
}


//foreach
$programmingLanguages = ["PHP", "Java", "Python"];

foreach($programmingLanguages as $language) {
    echo $language . "<br />";
}