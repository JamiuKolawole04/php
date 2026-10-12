<?php
$programmingLanguages = ["PHP", "Python", "Javascript"];

print_r($programmingLanguages);

array_push($programmingLanguages, "Java", "C++");


echo "<br />";

print_r($programmingLanguages);

// keyed arrays
$programmingLanguages = [
    "PHP" => 8.1,
    "Python" => 3.11,
    "Javascript" => 2026,
];

echo "<br />";
print_r($programmingLanguages);


// multidimensional arrays
$programmingLanguages = [
    "PHP" => [
        "version" => 8.1,
        "release_date" => "2022-12-24",
    ],
    "Python" => [
        "version" => 3.1,
        "release_date" => "2022-12-24",
    ],
];

echo "<br />";
print_r($programmingLanguages);

echo "<br />";
echo $programmingLanguages["PHP"]["version"];

array_pop($programmingLanguages);
array_shift($programmingLanguages);

echo "<br />";

var_dump($programmingLanguages);

?>