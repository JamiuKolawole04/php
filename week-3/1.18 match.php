<?php

$paymentStatus = "Declined";

$paymentStatusDisplay =  match ($paymentStatus) {
    "Paid" => "paid",
    "Declined" => "Declined",
    default => "Unknown",
};

echo $paymentStatusDisplay;