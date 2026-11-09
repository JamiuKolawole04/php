<?php
 declare(strict_types=1);


class Transaction {
  public $amount;
  public $description;

  public function __construct($amount, $description) {
    $this->amount = $amount;
    $this->description = $description;
  }
}

$transaction = new Transaction(10, "Payment service description");


echo $transaction->amount . "<br />";
echo $transaction->description;