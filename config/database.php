<?php

$host = "localhost";
$username = "root";
$password = "12345";
$database = "event_ticket_db";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>