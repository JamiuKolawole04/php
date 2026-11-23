<?php

class InsufficientFundsException extends Exception {}

class BankAccount {

    private float $balance;

    public function __construct(float $balance) {
        if ($balance < 0) {
            throw new InvalidArgumentException("Initial balance cannot be negative");
        }
        $this->balance = $balance;
    }

    public function withdraw(float $amount): void {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Amount must be greater than zero");
        }
        if ($amount > $this->balance) {
            throw new InsufficientFundsException("Not enough funds");
        }
        $this->balance -= $amount;
    }

    public function getBalance(): float  {
        return $this->balance;
    }
}

try {
    $account = new BankAccount(100);
    $account->withdraw(150);
    echo "New balance: " . $account->getBalance();
} catch (InsufficientFundsException $e) {
    echo "Transaction failed: " . $e->getMessage();
} catch (InvalidArgumentException $e) {
    echo "Invalid input: " . $e->getMessage();
} finally {
    echo "Done.";
}