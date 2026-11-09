<?php


class Transaction {
    const STATUS_PAID = "paid";
    const STATUS_PENDING = "pending";
    const STATUS_FAILED = "failed";
}

echo Transaction::STATUS_PAID . "<br />";
echo Transaction::STATUS_PENDING . "<br />";
echo Transaction::STATUS_FAILED . "<br />";