<?php

$host = "localhost";
$username = "root";
$password = "12345";
$database = "event_ticket_db";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SePay sandbox config (dien key that cua ban vao day)
if (!defined('SEPAY_MERCHANT_ID')) {
    define('SEPAY_MERCHANT_ID', 'SP-TEST-LT5B99A7');
}
if (!defined('SEPAY_SECRET_KEY')) {
    define('SEPAY_SECRET_KEY', 'spsk_test_gGE7uj0Qq2aZCMVaGm5kXqYc4b7aUV3V');
}
if (!defined('SEPAY_ENV')) {
    define('SEPAY_ENV', 'sandbox'); // sandbox | production
}
if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', 'http://localhost:8000');
}

?>