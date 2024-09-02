<?php
// edit.php
try {
    $db = new PDO('sqlite:bias_db.sqlite');
    $sortColumn = $_GET['sort'] ?? 'id';
    $validColumns = ['id', 'bias_name', 'also_known_as', 'bias_description', 'reference', 'date_added', 'date_updated'];
    if (!in_array($sortColumn, $validColumns)) {
        $sortColumn = 'bias_name';
    }
    $query = "SELECT * FROM biases ORDER BY $sortColumn";
    if ($sortColumn == 'bias_name') {
        $query .= ", bias_description";
    }
    $stmt = $db->query($query);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

function escapeHtml($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function convertNewlinesToBr($string) {
    return nl2br(escapeHtml($string), false);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bias List</title>
    <script src="edit.js" defer></script>
</head>
<body>
    <h2><a href=".">Back to Listing</a></h2>
    <h1>Edit Bias List</h1>
    <h4 style="color:red"><i><u>Do not add duplicate ID or Name values, since data loss can occur!</u></i></h3>
    <table>
        <thead>
            <tr>
                <th>Action</th>
                <?php foreach ($validColumns as $column): ?>
                    <th><a href="?sort=<?= $column ?>" style="color: blue; text-decoration: underline;"><?= escapeHtml($column) ?></a></th>
                <?php endforeach; ?>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $id => $row): ?>
                <tr class="<?= $id % 2 == 0 ? 'even' : 'odd' ?>" data-id="<?= escapeHtml($row['id']) ?>">
                    <td><button class="save-btn" disabled>Save</button></td>
                    <?php foreach ($row as $column => $value): ?>
                        <td contenteditable="true" data-column="<?= $column ?>" spellcheck="false"><?= convertNewlinesToBr($value) ?></td>
                    <?php endforeach; ?>
                    <td><button class="delete-btn">Delete</button></td>
                </tr>
            <?php endforeach; ?>
            <!-- Add empty row for new entry -->
            <tr class="new-row" data-id="">
                <td><button class="save-btn" disabled>Save</button></td>
                <?php foreach ($validColumns as $column): ?>
                    <td contenteditable="true" data-column="<?= $column ?>" spellcheck="false"></td>
                <?php endforeach; ?>
                <td></td>
            </tr>
        </tbody>
    </table>
    <button id="add-row-btn">Add another row</button>
    <div style="text-align: right; margin-top: 10px;">
        <form action="import_csv.php" method="post" enctype="multipart/form-data">
            <label for="csv_file">Import CSV:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv">
            <button type="submit">Import CSV</button>
        </form>
    </div>
</body>
</html>
