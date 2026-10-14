<?php

$paymentStatus = "Declined";

switch ($paymentStatus) {
    case "Paid":
        echo "Paid";
        break;

    case "Declined":
        echo "Payment declined";
        break;

     case "Pending":
        echo "Payment pending";
        break;
        
    default :
        echo "Unknown payment status";
}