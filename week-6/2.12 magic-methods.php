<?php

declare(STRICT_TYPES=1);

class MagicMethods {
    public function __construct() {
        echo "This is the construct method";
    }

    public function __destruct() {
        echo "Destruct method called";
    }

    public function __get(string $name) {
        echo "Getting value of $name";
    }
}