<?php
declare(strict_types=1);

class Transaction {
    public int $amount;

    public function __constructor(int $amount) {
        $this->amount = $amount;
    }

    public function process() {
        echo "Processing" . $this->amount . "transaction";

        $this->generateReceipt();
        $this->sendEmail();
    }

    private generateReceipt() {
        echo "Gnerate receipt";
    }

     private sendEmail() {
        echo "Gnerate receipt";
    }
}

$transaction = new Transaction(10);