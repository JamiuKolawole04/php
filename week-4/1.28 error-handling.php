<?php

declare(strict_types=1);

function divide(int $x, int $y) {
    if ($y === 0) {
        throw new Exception("Cannot divide by zero");
    } else {
        return $x /  $y;
    }
}


try {
 divide(5, 0);


} catch(Exception $e){
    echo "Error " . $e->getMessage();
}