<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_munaqosah';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Checking 'users' table...\n";
$result = $mysqli->query("SELECT id, username, email FROM users");

if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " users:\n";
        while($row = $result->fetch_assoc()) {
            echo "- " . $row['username'] . " (" . $row['email'] . ")\n";
        }
    } else {
        echo "Table 'users' is empty.\n";
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}

$mysqli->close();
