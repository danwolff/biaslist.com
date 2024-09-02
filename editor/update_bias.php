<?php
// update_bias.php
function sanitize_input($data) {
    return trim($data);
}

try {
    $db = new PDO('sqlite:bias_db.sqlite');
    $input = json_decode(file_get_contents('php://input'), true);
    $originalId = $input['id'];
    $data = $input['data'];

    $columns = ['id', 'bias_name', 'also_known_as', 'bias_description', 'reference', 'date_added', 'date_updated'];
    $set = [];
    $params = [];

    foreach ($columns as $column) {
        if (isset($data[$column])) {
            $data[$column] = sanitize_input($data[$column]);
            $set[] = "$column = :$column";
            $params[":$column"] = $data[$column];
        }
    }

    $params[':date_updated'] = date('Y-m-d');

    if (empty($originalId)) {
        // Inserting a new record
        $params[':date_added'] = date('Y-m-d');
        $sql = "INSERT INTO biases (" . implode(', ', array_keys($data)) . ", date_added, date_updated) VALUES (:" . implode(', :', array_keys($data)) . ", :date_added, :date_updated)";
    } else {
        // Updating an existing record
        $params[':original_id'] = $originalId;
        $sql = "UPDATE biases SET " . implode(', ', $set) . ", date_updated = :date_updated WHERE id = :original_id";

        // If the ID has changed, handle separately
        $newId = isset($data['id']) ? $data['id'] : $originalId;
        if ($newId !== $originalId) {
            $updateIdSql = "UPDATE biases SET id = :new_id WHERE id = :original_id";
            $updateIdStmt = $db->prepare($updateIdSql);
            $updateIdStmt->execute([':new_id' => $newId, ':original_id' => $originalId]);
            $params[':original_id'] = $newId;
        }
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Fetch the ID of the newly inserted record
    if (empty($originalId)) {
        $newId = $db->lastInsertId();
    }

    echo json_encode(['success' => true, 'id' => $newId ?? $originalId]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
