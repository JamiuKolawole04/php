<?php
class Fruit {
  public string $name;
  public string $color;
  
  public function __construct(string $name, string $color) {
    $this->name = $name;
    $this->color = $color; 
  }
  
  public function intro() {
    echo "The fruit is $this->name and the color is $this->color.<br>"; 
  }
}

// Strawberry is inherited from Fruit
class Strawberry extends Fruit {
  public function message() {
    echo "Am I a fruit or a berry? "; 
  }
}

$strawberry = new Strawberry("Strawberry", "red");
$strawberry->intro();
$strawberry->message();