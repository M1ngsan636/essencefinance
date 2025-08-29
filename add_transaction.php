<?php
require_once 'db.php';

if ($_POST) {
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    $db = new Database();
    $db->addTransaction($type, $amount, $description, $category);
    
    header('Location: index.php');
    exit();
}
?>