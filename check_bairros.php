<?php
require __DIR__ . '/sheep_core/config.php';
$pdo = new PDO('mysql:host=127.0.0.1;dbname=delivery;charset=utf8', 'root', '');
$stmt = $pdo->query('SELECT COUNT(*) FROM bairros_entrega');
echo 'Count: ' . $stmt->fetchColumn();
