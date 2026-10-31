<?php

declare(strict_types=1);

const DATA_FILE = __DIR__ . '/transaction_files/sample_1.json';

$categories = ['Food', 'Transport', 'Bills', 'Shopping', 'Health', 'Other'];


function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


function readExpenses(string $file): array
{
    if (!is_file($file)) {
        return [];
    }

    $json = file_get_contents($file);

    if ($json === false || trim($json) === '') {
        return [];
    }

    $expenses = json_decode($json, true);

    return is_array($expenses) ? $expenses : [];
}

function writeExpenses(string $file, array $expenses): bool
{
    $json = json_encode($expenses, JSON_PRETTY_PRINT);

    if ($json === false) {
        return false;
    }

    return file_put_contents($file, $json) !== false;
}


function nextId(array $expenses): int
{
    $max = 0;

    foreach ($expenses as $expense) {
        if ((int) $expense['id'] > $max) {
            $max = (int) $expense['id'];
        }
    }

    return $max + 1;
}


function validateExpense(array $input, array $categories): array
{
    $errors = [];

    if ($input['description'] === '') {
        $errors[] = 'Description is required.';
    }

    $date = DateTime::createFromFormat('Y-m-d', $input['date']);

    if ($date === false || $date->format('Y-m-d') !== $input['date']) {
        $errors[] = 'Date must be a valid date in YYYY-MM-DD format.';
    }

    if (!in_array($input['category'], $categories, true)) {
        $errors[] = 'Please choose a category.';
    }

    if (!is_numeric($input['amount']) || (float) $input['amount'] <= 0) {
        $errors[] = 'Amount must be a number greater than zero.';
    }

    return $errors;
}

$expenses = readExpenses(DATA_FILE);
$errors = [];
$notice = '';
$editing = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        $expenses = array_values(array_filter(
            $expenses,
            fn (array $expense): bool => (int) $expense['id'] !== $id
        ));

        if (writeExpenses(DATA_FILE, $expenses)) {
            header('Location: expense-tracker.php?notice=delete');
            exit;
        }

        $errors[] = 'Could not delete the expense. Check that the data file is writable.';
    } else {
        $form = [
            'id' => (int) ($_POST['id'] ?? 0),
            'date' => trim($_POST['date'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => $_POST['category'] ?? '',
            'amount' => trim($_POST['amount'] ?? ''),
        ];

        $errors = validateExpense($form, $categories);

        if ($errors === []) {
            $row = [
                'id' => $action === 'update' ? $form['id'] : nextId($expenses),
                'date' => $form['date'],
                'description' => $form['description'],
                'category' => $form['category'],
                'amount' => round((float) $form['amount'], 2),
            ];

            if ($action === 'update') {
                $found = false;

                foreach ($expenses as $index => $expense) {
                    if ((int) $expense['id'] === $row['id']) {
                        $expenses[$index] = $row;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $errors[] = 'Expense not found.';
                }
            } else {
                $expenses[] = $row;
            }

            if ($errors === []) {
                if (writeExpenses(DATA_FILE, $expenses)) {
                    header('Location: expense-tracker.php?notice=' . $action);
                    exit;
                }

                $errors[] = 'Could not save the expense. Check that the data file is writable.';
            }
        }

        $editing = $form;
    }
} elseif (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    foreach ($expenses as $expense) {
        if ((int) $expense['id'] === $id) {
            $editing = $expense;
            break;
        }
    }

    if ($editing === null) {
        $errors[] = 'Expense not found.';
    }
}

$notices = [
    'add' => 'Expense added.',
    'update' => 'Expense updated.',
    'delete' => 'Expense deleted.',
];

if (isset($notices[$_GET['notice'] ?? ''])) {
    $notice = $notices[$_GET['notice']];
}

$total = 0.0;
$categoryTotals = [];

foreach ($expenses as $expense) {
    $amount = (float) $expense['amount'];
    $total += $amount;
    $category = (string) $expense['category'];

    $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $amount;
}

$formValues = [
    'id' => $editing['id'] ?? 0,
    'date' => $editing['date'] ?? date('Y-m-d'),
    'description' => $editing['description'] ?? '',
    'category' => $editing['category'] ?? '',
    'amount' => $editing['amount'] ?? '',
];

$isEditing = (int) $formValues['id'] > 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <style>
        :root {
            --ink: #1f2933;
            --muted: #616e7c;
            --line: #d9e2ec;
            --page: #f5f7fa;
            --accent: #0f766e;
            --accent-dark: #115e59;
            --danger: #b42318;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 32px 16px;
            background: var(--page);
            color: var(--ink);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.5;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .subtitle {
            margin: 4px 0 24px;
            color: var(--muted);
        }

        .card {
            margin-bottom: 24px;
            padding: 20px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
        }

        h2 {
            margin: 0 0 16px;
            font-size: 1.15rem;
        }

        .summary {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 32px;
            align-items: baseline;
            margin-bottom: 24px;
            padding: 20px;
            background: var(--accent);
            border-radius: 10px;
            color: #fff;
        }

        .summary .total {
            margin: 0;
            font-size: 1.1rem;
        }

        .summary .total strong {
            display: block;
            font-size: 2rem;
            line-height: 1.2;
        }

        .summary .count {
            margin: 0;
            color: #ccfbf1;
        }

        .category-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
        }

        .category-list li {
            padding: 4px 10px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 999px;
            font-size: 0.85rem;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        input,
        select {
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 6px;
            font: inherit;
            font-weight: 400;
            background: #fff;
        }

        input:focus,
        select:focus {
            outline: 2px solid var(--accent);
            outline-offset: 1px;
        }

        .actions {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-top: 16px;
        }

        button {
            padding: 9px 18px;
            border: 0;
            border-radius: 6px;
            background: var(--accent);
            color: #fff;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: var(--accent-dark);
        }

        .cancel {
            color: var(--muted);
        }

        .notice,
        .errors {
            margin: 0 0 16px;
            padding: 12px 16px;
            border-radius: 8px;
        }

        .notice {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: var(--accent-dark);
        }

        .errors {
            background: #fef3f2;
            border: 1px solid #fecdca;
            color: var(--danger);
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: var(--muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .right {
            text-align: right;
        }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            background: #e6fffa;
            border-radius: 999px;
            color: var(--accent-dark);
            font-size: 0.8rem;
        }

        .row-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            white-space: nowrap;
        }

        .row-actions form {
            margin: 0;
        }

        .link-danger {
            padding: 0;
            background: none;
            color: var(--danger);
            font-weight: 400;
        }

        .link-danger:hover {
            background: none;
            text-decoration: underline;
        }

        .empty {
            margin: 0;
            color: var(--muted);
        }

        @media (max-width: 560px) {
            .summary {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
<main class="page">
    <header>
        <h1>Expense Tracker</h1>
        <p class="subtitle">Week 4 assignment: add, edit, delete, and total your expenses.</p>
    </header>

    <?php if ($notice !== ''): ?>
        <p class="notice"><?= e($notice) ?></p>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="summary">
        <p class="total">Total spent <strong>$<?= number_format($total, 2) ?></strong></p>
        <p class="count"><?= count($expenses) ?> expense<?= count($expenses) === 1 ? '' : 's' ?></p>

        <?php if ($categoryTotals !== []): ?>
            <ul class="category-list">
                <?php foreach ($categoryTotals as $category => $categoryTotal): ?>
                    <li><?= e($category) ?>: $<?= number_format($categoryTotal, 2) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2><?= $isEditing ? 'Edit expense' : 'Add an expense' ?></h2>

        <form method="post" action="expense-tracker.php">
            <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'add' ?>">

            <?php if ($isEditing): ?>
                <input type="hidden" name="id" value="<?= (int) $formValues['id'] ?>">
            <?php endif; ?>

            <div class="fields">
                <label>
                    Date
                    <input type="date" name="date" value="<?= e($formValues['date']) ?>" required>
                </label>

                <label>
                    Description
                    <input type="text" name="description" value="<?= e($formValues['description']) ?>" placeholder="e.g. Groceries" required>
                </label>

                <label>
                    Category
                    <select name="category" required>
                        <option value="">Choose...</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category) ?>" <?= $formValues['category'] === $category ? 'selected' : '' ?>>
                                <?= e($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Amount
                    <input type="number" name="amount" value="<?= e($formValues['amount']) ?>" min="0.01" step="0.01" placeholder="0.00" required>
                </label>
            </div>

            <div class="actions">
                <button type="submit"><?= $isEditing ? 'Save changes' : 'Add expense' ?></button>

                <?php if ($isEditing): ?>
                    <a class="cancel" href="expense-tracker.php">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Expenses</h2>

        <?php if ($expenses === []): ?>
            <p class="empty">No expenses yet. Add your first one above.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th class="right">Amount</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (array_reverse($expenses) as $expense): ?>
                        <tr>
                            <td><?= e($expense['date']) ?></td>
                            <td><?= e($expense['description']) ?></td>
                            <td><span class="tag"><?= e($expense['category']) ?></span></td>
                            <td class="right">$<?= number_format((float) $expense['amount'], 2) ?></td>
                            <td>
                                <div class="row-actions">
                                    <a href="expense-tracker.php?edit=<?= (int) $expense['id'] ?>">Edit</a>

                                    <form method="post" action="expense-tracker.php" onsubmit="return confirm('Delete this expense?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $expense['id'] ?>">
                                        <button type="submit" class="link-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
