<?php
// Define the absolute path to the database file
$dbPath = __DIR__ . '/bias_db.sqlite';

function sanitize_input($data) {
    return trim(htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8'));
}

try {
    $db = new PDO('sqlite:' . $dbPath);

    // Create table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS biases (
        id TEXT NOT NULL,
        bias_name TEXT NOT NULL,
        also_known_as TEXT NOT NULL,
        bias_description TEXT NOT NULL,
        reference TEXT NOT NULL,
        date_added TEXT NOT NULL,
        date_updated TEXT NOT NULL
    )");

    echo "Database setup complete.  Please visit edit.php add data.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
