<?php

declare(strict_types=1);


class Expense
{
    private int $id;
    private string $date;
    private string $description;
    private string $category;
    private float $amount;

    public function __construct(
        int $id,
        string $date,
        string $description,
        string $category,
        float $amount
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->description = $description;
        $this->category = $category;
        $this->amount = $amount;
    }

   
    public static function fromArray(array $row): self
    {
        return new self(
            (int) ($row['id'] ?? 0),
            (string) ($row['date'] ?? ''),
            (string) ($row['description'] ?? ''),
            (string) ($row['category'] ?? ''),
            (float) ($row['amount'] ?? 0)
        );
    }

    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'description' => $this->description,
            'category' => $this->category,
            'amount' => $this->amount,
        ];
    }

    public function id(): int
    {
        return $this->id;
    }

    public function date(): string
    {
        return $this->date;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function amount(): float
    {
        return $this->amount;
    }
}


class ExpenseTracker
{
    public const CATEGORIES = ['Food', 'Transport', 'Bills', 'Shopping', 'Health', 'Other'];

    private string $file;

    /** @var Expense[] */
    private array $expenses = [];

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->load();
    }

    
    private function load(): void
    {
        $this->expenses = [];

        if (!is_file($this->file)) {
            return;
        }

        $json = file_get_contents($this->file);

        if ($json === false || trim($json) === '') {
            return;
        }

        $rows = json_decode($json, true);

        if (!is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            if (is_array($row)) {
                $this->expenses[] = Expense::fromArray($row);
            }
        }
    }

   
    private function saveAll(array $expenses): bool
    {
        $rows = array_map(
            fn (Expense $expense): array => $expense->toArray(),
            $expenses
        );

        $json = json_encode($rows, JSON_PRETTY_PRINT);

        if ($json === false) {
            return false;
        }

        return file_put_contents($this->file, $json) !== false;
    }

   
    public function all(): array
    {
        return array_reverse($this->expenses);
    }

    public function find(int $id): ?Expense
    {
        foreach ($this->expenses as $expense) {
            if ($expense->id() === $id) {
                return $expense;
            }
        }

        return null;
    }

    public function add(string $date, string $description, string $category, float $amount): bool
    {
        $updated = $this->expenses;
        $updated[] = new Expense($this->nextId(), $date, $description, $category, $amount);

        if (!$this->saveAll($updated)) {
            return false;
        }

        $this->expenses = $updated;

        return true;
    }

    public function update(int $id, string $date, string $description, string $category, float $amount): bool
    {
        $updated = [];
        $found = false;

        foreach ($this->expenses as $expense) {
            if ($expense->id() === $id) {
                $updated[] = new Expense($id, $date, $description, $category, $amount);
                $found = true;
            } else {
                $updated[] = $expense;
            }
        }

        if (!$found || !$this->saveAll($updated)) {
            return false;
        }

        $this->expenses = $updated;

        return true;
    }

    public function delete(int $id): bool
    {
        $updated = [];
        $found = false;

        foreach ($this->expenses as $expense) {
            if ($expense->id() === $id) {
                $found = true;
            } else {
                $updated[] = $expense;
            }
        }

        if (!$found || !$this->saveAll($updated)) {
            return false;
        }

        $this->expenses = $updated;

        return true;
    }

    public function total(): float
    {
        $total = 0.0;

        foreach ($this->expenses as $expense) {
            $total += $expense->amount();
        }

        return $total;
    }

    public function totalsByCategory(): array
    {
        $totals = [];

        foreach ($this->expenses as $expense) {
            $category = $expense->category();
            $totals[$category] = ($totals[$category] ?? 0) + $expense->amount();
        }

        return $totals;
    }

    public function count(): int
    {
        return count($this->expenses);
    }

   
    public function validate(string $date, string $description, string $category, string $amount): array
    {
        $errors = [];

        if (trim($description) === '') {
            $errors[] = 'Description is required.';
        }

        $dateObject = DateTime::createFromFormat('Y-m-d', $date);

        if ($dateObject === false || $dateObject->format('Y-m-d') !== $date) {
            $errors[] = 'Date must be a valid date in YYYY-MM-DD format.';
        }

        if (!in_array($category, self::CATEGORIES, true)) {
            $errors[] = 'Please choose a category.';
        }

        if (!is_numeric($amount) || (float) $amount <= 0) {
            $errors[] = 'Amount must be a number greater than zero.';
        }

        return $errors;
    }

    private function nextId(): int
    {
        $max = 0;

        foreach ($this->expenses as $expense) {
            if ($expense->id() > $max) {
                $max = $expense->id();
            }
        }

        return $max + 1;
    }
}


function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$tracker = new ExpenseTracker(__DIR__ . '/transaction_files/sample_1.json');
$errors = [];
$notice = '';
$editing = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $id = (int) ($_POST['id'] ?? 0);
    $form = [
        'id' => $id,
        'date' => trim($_POST['date'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category' => $_POST['category'] ?? '',
        'amount' => trim($_POST['amount'] ?? ''),
    ];

    if ($action === 'delete') {
        if ($tracker->find($id) === null) {
            $errors[] = 'Expense not found.';
        } elseif (!$tracker->delete($id)) {
            $errors[] = 'Could not delete the expense. Check that the data file is writable.';
        } else {
            header('Location: expense-tracker.php?notice=delete');
            exit;
        }
    } else {
        $errors = $tracker->validate(
            $form['date'],
            $form['description'],
            $form['category'],
            $form['amount']
        );

        if ($errors === []) {
            $amount = round((float) $form['amount'], 2);

            if ($action === 'update') {
                if ($tracker->find($id) === null) {
                    $errors[] = 'Expense not found.';
                } elseif (!$tracker->update($id, $form['date'], $form['description'], $form['category'], $amount)) {
                    $errors[] = 'Could not save the expense. Check that the data file is writable.';
                } else {
                    header('Location: expense-tracker.php?notice=update');
                    exit;
                }
            } else {
                if (!$tracker->add($form['date'], $form['description'], $form['category'], $amount)) {
                    $errors[] = 'Could not save the expense. Check that the data file is writable.';
                } else {
                    header('Location: expense-tracker.php?notice=add');
                    exit;
                }
            }
        }

        $editing = $form;
    }
} elseif (isset($_GET['edit'])) {
    $expense = $tracker->find((int) $_GET['edit']);

    if ($expense === null) {
        $errors[] = 'Expense not found.';
    } else {
        $editing = $expense->toArray();
    }
}

$expenses = $tracker->all();
$categories = ExpenseTracker::CATEGORIES;
$total = $tracker->total();
$categoryTotals = $tracker->totalsByCategory();

$notices = [
    'add' => 'Expense added.',
    'update' => 'Expense updated.',
    'delete' => 'Expense deleted.',
];

if (isset($notices[$_GET['notice'] ?? ''])) {
    $notice = $notices[$_GET['notice']];
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
        <p class="count"><?= $tracker->count() ?> expense<?= $tracker->count() === 1 ? '' : 's' ?></p>

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
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?= e($expense->date()) ?></td>
                            <td><?= e($expense->description()) ?></td>
                            <td><span class="tag"><?= e($expense->category()) ?></span></td>
                            <td class="right">$<?= number_format($expense->amount(), 2) ?></td>
                            <td>
                                <div class="row-actions">
                                    <a href="expense-tracker.php?edit=<?= $expense->id() ?>">Edit</a>

                                    <form method="post" action="expense-tracker.php" onsubmit="return confirm('Delete this expense?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $expense->id() ?>">
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
