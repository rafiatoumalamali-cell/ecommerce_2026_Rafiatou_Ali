<?php
require "db.php";
$sql = "CREATE TABLE IF NOT EXISTS tasks (
id INT AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(255) NOT NULL,
description TEXT,
status ENUM('pending', 'in_progress', 'done') DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
echo "<h2>Setup complete!</h2>";
echo "<p><a href='index.php'>Go to Tasks App</a></p>";
} else {
echo "Error creating table: " . $conn->error;
}
$conn->close();