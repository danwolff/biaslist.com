<?php
// export_csv.php
try {
    $db = new PDO('sqlite:bias_db.sqlite');
    $query = "SELECT * FROM biases";
    $stmt = $db->query($query);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        $filename = "biases_export_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Output the column headings
        fputcsv($output, array_keys($rows[0]), '|');

        // Output the rows
        foreach ($rows as $row) {
            fputcsv($output, $row, '|');
        }

        fclose($output);
        exit;
    } else {
        echo "No data found in the table.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

