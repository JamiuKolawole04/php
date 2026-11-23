<?php

declare(STRICT_TYPES=1);


echo  "Late static binding";

class A {
    protected static string $name = "A";

    public function getName(): string {
        return static::$name;
    }
}