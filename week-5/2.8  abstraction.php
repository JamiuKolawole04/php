<?php

declare(strict_types=1);

class Transaction
{
    public int $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }

    public function process(): void
    {
        echo "Processing " . $this->amount . " transaction<br />";

        $this->generateReceipt();
        $this->sendEmail();
    }

    private function generateReceipt(): void
    {
        echo "Generate receipt<br />";
    }

    private function sendEmail(): void
    {
        echo "Send email receipt<br />";
    }
}

$transaction = new Transaction(10);
$transaction->process();
