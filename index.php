<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple PHP test first
if (isset($_GET['test'])) {
    echo "PHP is working!";
    exit;
}

// Database class
class Database {
    private $db;
    
    public function __construct() {
        $dbPath = '/tmp/finance.db';
        $this->db = new SQLite3($dbPath);
        $this->createTables();
    }
    
    private function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL,
            amount REAL NOT NULL,
            description TEXT,
            category TEXT,
            date DATE DEFAULT CURRENT_DATE
        )";
        $this->db->exec($sql);
    }
    
    public function addTransaction($type, $amount, $description, $category) {
        $stmt = $this->db->prepare("INSERT INTO transactions (type, amount, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $type, SQLITE3_TEXT);
        $stmt->bindValue(2, $amount, SQLITE3_FLOAT);
        $stmt->bindValue(3, $description, SQLITE3_TEXT);
        $stmt->bindValue(4, $category, SQLITE3_TEXT);
        return $stmt->execute();
    }
    
    public function getTransactions() {
        $result = $this->db->query("SELECT * FROM transactions ORDER BY date DESC");
        $transactions = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $transactions[] = $row;
        }
        return $transactions;
    }
    
    public function getBalance() {
        $income = $this->db->querySingle("SELECT SUM(amount) FROM transactions WHERE type = 'income'");
        $expense = $this->db->querySingle("SELECT SUM(amount) FROM transactions WHERE type = 'expense'");
        return ($income ?: 0) - ($expense ?: 0);
    }
    
    public function getIncome() {
        return $this->db->querySingle("SELECT SUM(amount) FROM transactions WHERE type = 'income'") ?: 0;
    }
    
    public function getExpenses() {
        return $this->db->querySingle("SELECT SUM(amount) FROM transactions WHERE type = 'expense'") ?: 0;
    }
}

// Initialize database and get data
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { text-align: center; margin-bottom: 30px; }
        header h1 { color: #2c3e50; font-size: 2.5em; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .card h3 { margin-bottom: 10px; color: #666; }
        .card p { font-size: 1.8em; font-weight: bold; }
        .positive { color: #27ae60; }
        .negative { color: #e74c3c; }
        .form-section { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .form-section h2 { margin-bottom: 20px; color: #2c3e50; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background-color: #3498db; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background-color: #2980b9; }
        .transactions-section { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .transactions-section h2 { margin-bottom: 20px; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; color: #555; }
        .type-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8em; font-weight: bold; }
        .type-badge.income { background-color: #d4edda; color: #27ae60; }
        .type-badge.expense { background-color: #f8d7da; color: #e74c3c; }
        .income { color: #27ae60; font-weight: bold; }
        .expense { color: #e74c3c; font-weight: bold; }
    </style>
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
            <form method="POST">
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
                        <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                        <td>
                            <span class="type-badge <?php echo htmlspecialchars($transaction['type']); ?>">
                                <?php echo ucfirst(htmlspecialchars($transaction['type'])); ?>
                            </span>
                        </td>
                        <td><?php echo ucfirst(htmlspecialchars($transaction['category'])); ?></td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                        <td class="<?php echo htmlspecialchars($transaction['type']); ?>">
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