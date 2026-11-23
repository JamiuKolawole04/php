<?php

declare(strict_types=1);

echo '<pre>';
var_dump($_GET);
echo '</pre>';

echo '<pre>';
var_dump($_POST);
echo '</pre>';
?>

<form action="" method="post">
    <label for="amount">Amount</label>
    <input type="number" name="amount" id="amount" step="0.01">
    <button type="submit">Send POST</button>
</form>

<form action="" method="get">
    <label for="search">Search</label>
    <input type="text" name="search" id="search">
    <button type="submit">Send GET</button>
</form>
