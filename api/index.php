<?php
// This is a simple API endpoint to verify PHP is working
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'PHP is working on Vercel!']);
?>