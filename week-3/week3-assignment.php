<?php

declare(strict_types=1);


function calculateTax(float $amount, float $rate = 0.15): float
{
    if ($amount < 0) {
        echo 'Error: Amount cannot be negative.' . "<br />";
        return 0.0;
    }

    if ($rate < 0 || $rate > 1) {
        echo 'Error: Tax rate must be between 0 and 1.' . "<br />";
        return 0.0;
    }

    return round($amount * $rate, 2);
}



function formatCurrency(float $amount, string $currency = 'USD'): string
{
    $currency = strtoupper(trim($currency));

    if (!preg_match('/^[A-Z]{3}$/', $currency)) {
        echo 'Error: Currency code must be exactly 3 letters.' . "<br />";
        return '';
    }

    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
    ];

    $number = number_format(abs($amount), 2, '.', ',');
    $isNegative = $amount < 0 && round(abs($amount), 2) > 0;

    if (isset($symbols[$currency])) {
        $formatted = $symbols[$currency] . $number;
    } else {
        $formatted = $currency . ' ' . $number;
    }

    if ($isNegative) {
        return '-' . $formatted;
    }

    return $formatted;
}


function generateSlug(string $title): string
{
    $title = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $title);
    $slug = trim($slug, '-');

    if ($slug === '') {
        return 'untitled';
    }

    return $slug;
}


function calculateAge(string $birthDate): int
{
    $date = DateTime::createFromFormat('!Y-m-d', $birthDate);

    if ($date === false || $date->format('Y-m-d') !== $birthDate) {
        echo 'Error: Birth date must be a real date in YYYY-MM-DD format.' . "<br />";
        return 0;
    }

    $today = new DateTime('today');

    if ($date > $today) {
        echo 'Error: Birth date cannot be in the future.' . "<br />";
        return 0;
    }

    $difference = $today->diff($date);

    return $difference->y;
}


function validateEmail(string $email): bool
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
}


function paginate(int $totalItems, int $perPage, int $currentPage = 1): array
{
    if ($totalItems < 0) {
        echo 'Error: Total items cannot be negative.' . "<br />";
        return [];
    }

    if ($perPage < 1) {
        echo 'Error: Items per page must be at least 1.' . "<br />";
        return [];
    }

    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'totalItems' => $totalItems,
        'perPage' => $perPage,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'hasNext' => $currentPage < $totalPages,
        'hasPrevious' => $currentPage > 1,
    ];
}

function generateReference(string $prefix = 'REF', int $length = 8): string
{
    if ($length < 4 || $length > 16) {
        echo 'Error: Reference length must be between 4 and 16.' . "<br />";
        return '';
    }

    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $prefix));

    if ($prefix === '') {
        $prefix = 'REF';
    }

    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $prefix . '-' . $code;
}


echo '<h2>Week 3 PHP Utility Library</h2>';

echo '<h3>calculateTax()</h3>';
echo 'calculateTax(100, 0.15) = ' . calculateTax(100, 0.15) . '<br />';
echo 'calculateTax(19.99, 0.15) = ' . calculateTax(19.99, 0.15) . '<br />';

echo '<h3>formatCurrency()</h3>';
echo 'formatCurrency(1234.5) = ' . formatCurrency(1234.5) . '<br />';
echo 'formatCurrency(-1234.5) = ' . formatCurrency(-1234.5) . '<br />';

echo '<h3>generateSlug()</h3>';
echo 'generateSlug("Hello, World!") = ' . generateSlug('Hello, World!') . '<br />';
echo 'generateSlug("!!!") = ' . generateSlug('!!!') . '<br />';

echo '<h3>calculateAge()</h3>';
echo 'calculateAge("2000-05-15") = ' . calculateAge('2000-05-15') . '<br />';

echo '<h3>validateEmail()</h3>';
echo 'validateEmail("user@example.com") = ' . (validateEmail('user@example.com') ? 'true' : 'false') . '<br />';

echo '<h3>paginate()</h3>';
$pagination = paginate(95, 10, 3);
echo 'paginate(95, 10, 3): totalPages = ' . $pagination['totalPages']
    . ', offset = ' . $pagination['offset']
    . ', hasNext = ' . ($pagination['hasNext'] ? 'true' : 'false')
    . ', hasPrevious = ' . ($pagination['hasPrevious'] ? 'true' : 'false')
    . '<br />';

echo '<h3>generateReference()</h3>';
echo 'generateReference() = ' . generateReference() . '<br />';
echo 'generateReference("INV", 6) = ' . generateReference('INV', 6) . '<br />';
