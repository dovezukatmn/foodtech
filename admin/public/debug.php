<?php
// debug.php — Дигностика сервера
// Откройте https://vezuroll.ru/debug.php

echo "<h1>Server Debug</h1>";
echo "<pre>";

echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "\n--- REQUEST ---\n";
echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Scheme (HTTPS): " . ($_SERVER['HTTPS'] ?? 'off') . "\n";
echo "Port: " . $_SERVER['SERVER_PORT'] . "\n";

echo "\n--- HEADERS ---\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}

echo "\n--- FULL SERVER VARS ---\n";
print_r($_SERVER);

echo "</pre>";
