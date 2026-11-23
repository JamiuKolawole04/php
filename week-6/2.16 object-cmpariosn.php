<?php

class User {
    public function __construct(
        public string $name,
        public int $age
    ) {}
}

$user1 = new User("Ada", 25);
$user2 = new User("Ada", 25); 
$user3 = $user1;              

var_dump($user1 == $user2); 
var_dump($user1 == $user3); 

var_dump($user1 === $user2); 
var_dump($user1 === $user3); 
