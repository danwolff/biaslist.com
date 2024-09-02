<?php
// import_csv.php
function sanitize_input($data) {
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, 'r')) !== FALSE) {
        try {
            $db = new PDO('sqlite:bias_db.sqlite');
            $db->beginTransaction();
            $columns = fgetcsv($handle, 1000, '|');

            while (($data = fgetcsv($handle, 1000, '|')) !== FALSE) {
                $row = array_combine($columns, $data);

                // Sanitize input
                foreach ($row as $key => $value) {
                    $row[$key] = sanitize_input($value);
                }

                // Check for duplicates
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM biases WHERE id = :id AND bias_name = :bias_name AND also_known_as = :also_known_as AND bias_description = :bias_description AND reference = :reference AND date_added = :date_added AND date_updated = :date_updated");
                $checkStmt->execute([
                    ':id' => $row['id'],
                    ':bias_name' => $row['bias_name'],
                    ':also_known_as' => $row['also_known_as'],
                    ':bias_description' => $row['bias_description'],
                    ':reference' => $row['reference'],
                    ':date_added' => $row['date_added'],
                    ':date_updated' => $row['date_updated']
                ]);

                if ($checkStmt->fetchColumn() == 0) {
                    $stmt = $db->prepare("INSERT INTO biases (id, bias_name, also_known_as, bias_description, reference, date_added, date_updated) VALUES (:id, :bias_name, :also_known_as, :bias_description, :reference, :date_added, :date_updated)");
                    $stmt->execute([
                        ':id' => $row['id'],
                        ':bias_name' => $row['bias_name'],
                        ':also_known_as' => $row['also_known_as'],
                        ':bias_description' => $row['bias_description'],
                        ':reference' => $row['reference'],
                        ':date_added' => $row['date_added'],
                        ':date_updated' => $row['date_updated']
                    ]);
                }
            }

            $db->commit();
            fclose($handle);
            echo "Data imported successfully.";
        } catch (PDOException $e) {
            $db->rollBack();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error opening the file.";
    }
} else {
    echo "Invalid request.";
}
?>

