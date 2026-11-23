<?php

declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

class Customer
{
    private array $accounts = [];

    public function __construct(
        private string $name,
        private string $email
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function addAccount(Account $account): void
    {
        $this->accounts[] = $account;
    }

    public function accounts(): array
    {
        return $this->accounts;
    }
}

abstract class Account
{
    public function __construct(
        private string $number,
        private Customer $owner,
        protected float $balance = 0.0
    ) {
    }

    public function number(): string
    {
        return $this->number;
    }

    public function owner(): Customer
    {
        return $this->owner;
    }

    public function balance(): float
    {
        return $this->balance;
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception('Deposit amount must be greater than zero.');
        }

        $this->balance += $amount;
    }

    abstract public function withdraw(float $amount): void;
}

class SavingsAccount extends Account
{
    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception('Withdrawal amount must be greater than zero.');
        }

        if ($amount > $this->balance) {
            throw new Exception('Insufficient funds in savings account ' . $this->number() . '.');
        }

        $this->balance -= $amount;
    }
}

class CurrentAccount extends Account
{
    public function __construct(
        string $number,
        Customer $owner,
        float $balance = 0.0,
        private float $overdraftLimit = 500.0
    ) {
        parent::__construct($number, $owner, $balance);
    }

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception('Withdrawal amount must be greater than zero.');
        }

        if ($this->balance - $amount < -$this->overdraftLimit) {
            throw new Exception('Overdraft limit exceeded for account ' . $this->number() . '.');
        }

        $this->balance -= $amount;
    }
}

abstract class Transaction
{
    protected float $amount;
    protected string $date;

    public function __construct(float $amount)
    {
        if ($amount <= 0) {
            throw new Exception('Transaction amount must be greater than zero.');
        }

        $this->amount = $amount;
        $this->date = date('Y-m-d H:i:s');
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function date(): string
    {
        return $this->date;
    }

    abstract public function describe(): string;
}

class Deposit extends Transaction
{
    public function __construct(
        float $amount,
        private Account $account,
        private float $balanceAfter
    ) {
        parent::__construct($amount);
    }

    public function describe(): string
    {
        return 'Deposit of $' . number_format($this->amount, 2)
            . ' to ' . $this->account->number()
            . '. New balance: $' . number_format($this->balanceAfter, 2);
    }
}

class Withdrawal extends Transaction
{
    public function __construct(
        float $amount,
        private Account $account,
        private float $balanceAfter
    ) {
        parent::__construct($amount);
    }

    public function describe(): string
    {
        return 'Withdrawal of $' . number_format($this->amount, 2)
            . ' from ' . $this->account->number()
            . '. New balance: $' . number_format($this->balanceAfter, 2);
    }
}

class Transfer extends Transaction
{
    public function __construct(
        float $amount,
        private Account $from,
        private Account $to,
        private float $fromBalanceAfter,
        private float $toBalanceAfter
    ) {
        parent::__construct($amount);
    }

    public function describe(): string
    {
        return 'Transfer of $' . number_format($this->amount, 2)
            . ' from ' . $this->from->number()
            . ' to ' . $this->to->number()
            . '. From balance: $' . number_format($this->fromBalanceAfter, 2)
            . '. To balance: $' . number_format($this->toBalanceAfter, 2);
    }
}

class Bank
{
    private array $accounts = [];
    private array $transactions = [];
    private int $nextAccountNumber = 1001;

    public function openAccount(Customer $customer, string $type, float $initialDeposit = 0.0): Account
    {
        $number = 'ACC' . $this->nextAccountNumber++;

        $account = match ($type) {
            'savings' => new SavingsAccount($number, $customer),
            'current' => new CurrentAccount($number, $customer),
            default => throw new Exception('Unknown account type: ' . $type),
        };

        $customer->addAccount($account);
        $this->accounts[$number] = $account;

        echo 'Opened ' . $type . ' account ' . $number . ' for ' . $customer->name() . PHP_EOL;

        if ($initialDeposit > 0) {
            $this->deposit($account, $initialDeposit);
        }

        return $account;
    }

    public function findAccount(string $number): Account
    {
        if (!isset($this->accounts[$number])) {
            throw new Exception('Account not found: ' . $number);
        }

        return $this->accounts[$number];
    }

    public function accounts(): array
    {
        return $this->accounts;
    }

    public function deposit(Account $account, float $amount): Deposit
    {
        $account->deposit($amount);

        $transaction = new Deposit($amount, $account, $account->balance());
        $this->transactions[] = $transaction;

        echo $transaction->describe() . PHP_EOL;

        return $transaction;
    }

    public function withdraw(Account $account, float $amount): Withdrawal
    {
        $account->withdraw($amount);

        $transaction = new Withdrawal($amount, $account, $account->balance());
        $this->transactions[] = $transaction;

        echo $transaction->describe() . PHP_EOL;

        return $transaction;
    }

    public function transfer(Account $from, Account $to, float $amount): Transfer
    {
        $from->withdraw($amount);
        $to->deposit($amount);

        $transaction = new Transfer($amount, $from, $to, $from->balance(), $to->balance());
        $this->transactions[] = $transaction;

        echo $transaction->describe() . PHP_EOL;

        return $transaction;
    }

    public function transactions(): array
    {
        return $this->transactions;
    }
}

$bank = new Bank();

$ada = new Customer('Ada Lovelace', 'ada@example.com');
$grace = new Customer('Grace Hopper', 'grace@example.com');

$savings = $bank->openAccount($ada, 'savings', 500);
$current = $bank->openAccount($ada, 'current', 200);
$graceAccount = $bank->openAccount($grace, 'current', 100);

$bank->deposit($savings, 250);
$bank->withdraw($savings, 100);
$bank->transfer($savings, $current, 200);
$bank->withdraw($current, 600);

try {
    $bank->withdraw($savings, 10000);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

try {
    $bank->withdraw($current, 400);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . 'Account summary' . PHP_EOL;

foreach ($bank->accounts() as $account) {
    echo $account->number() . ' | ' . $account->owner()->name()
        . ' | $' . number_format($account->balance(), 2) . PHP_EOL;
}

echo PHP_EOL . 'Transaction history' . PHP_EOL;

foreach ($bank->transactions() as $transaction) {
    echo $transaction->date() . ' | ' . $transaction->describe() . PHP_EOL;
}
