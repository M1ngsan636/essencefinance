<?php
require_once 'db.php';

$db = new Database();
$transactions = $db->getTransactions();
$balance = $db->getBalance();
$income = $db->getIncome();
$expenses = $db->getExpenses();

[phases.setup]
nixPkgs = ["php81", "php81Extensions.sqlite3"]

[phases.install]
cmds = ["mkdir -p data"]

[start]
cmd = "php -S 0.0.0.0:${PORT:-8000}"

// Vercel specific: Handle port
$port = $_ENV['PORT'] ?? 8000;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

try {
    $db = new Database();
    $transactions = $db->getTransactions();
    $balance = $db->getBalance();
    $income = $db->getIncome();
    $expenses = $db->getExpenses();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Personal Finance Tracker</h1>
        </header>
        
        <!-- Summary Cards -->
        <div class="summary">
            <div class="card balance">
                <h3>Balance</h3>
                <p class="<?php echo $balance >= 0 ? 'positive' : 'negative'; ?>">
                    $<?php echo number_format($balance, 2); ?>
                </p>
            </div>
            <div class="card income">
                <h3>Income</h3>
                <p class="positive">$<?php echo number_format($income, 2); ?></p>
            </div>
            <div class="card expense">
                <h3>Expenses</h3>
                <p class="negative">$<?php echo number_format($expenses, 2); ?></p>
            </div>
        </div>
        
        <!-- Add Transaction Form -->
        <div class="form-section">
            <h2>Add Transaction</h2>
            <form action="add_transaction.php" method="POST">
                <div class="form-group">
                    <label for="type">Type:</label>
                    <select name="type" id="type" required>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount ($):</label>
                    <input type="number" step="0.01" name="amount" id="amount" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" required>
                        <option value="salary">Salary</option>
                        <option value="freelance">Freelance</option>
                        <option value="investment">Investment</option>
                        <option value="food">Food</option>
                        <option value="rent">Rent</option>
                        <option value="utilities">Utilities</option>
                        <option value="transportation">Transportation</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <input type="text" name="description" id="description" required>
                </div>
                
                <button type="submit">Add Transaction</button>
            </form>
        </div>
        
        <!-- Transaction History -->
        <div class="transactions-section">
            <h2>Transaction History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo $transaction['date']; ?></td>
                        <td>
                            <span class="type-badge <?php echo $transaction['type']; ?>">
                                <?php echo ucfirst($transaction['type']); ?>
                            </span>
                        </td>
                        <td><?php echo ucfirst($transaction['category']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td class="<?php echo $transaction['type']; ?>">
                            $<?php echo number_format($transaction['amount'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>