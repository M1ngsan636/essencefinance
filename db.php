<?php
class Database {
    private $db;
    
    public function __construct() {
        // For Vercel, use /tmp directory (data won't persist)
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
?>