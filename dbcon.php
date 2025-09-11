<?php
// Read explicit app vars first, then fall back to Railway provided ones
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER');
$pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD');
$db   = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE');
$port = intval(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect($host, $user, $pass, $db, $port);
mysqli_set_charset($conn, 'utf8mb4');

