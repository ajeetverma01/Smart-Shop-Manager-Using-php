<?php
$host = 'localhost';        
$user = 'root';
$password = ''; 
$database = 'inventory_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Check if this item is used in sales
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM sales WHERE item_id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        $message = "Cannot delete item. Sales data exists for this item.";
    } else {
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $message = "Item deleted successfully.";
        } else {
            $message = "Error deleting item: " . $stmt->error;
        }

        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM items");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Inventory Item</title>
    <link rel="stylesheet" href="deleteInventory.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>Delete Inventory Item</h1>
<div class="homepage-btn">
        <a href="../index.html" class="home-page-button">Go to Homepage</a> 
    </div>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <form method="POST" action="">
        <label for="id">Item ID:</label>
        <input type="number" name="id" placeholder="Enter Item ID" required>
        <button type="submit">Delete Item</button>
    </form>

    <h2>Current Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Cost Price</th>
                <th>Selling Price</th>
                <th>Quantity Available</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['cost_price'], 2); ?></td>
                        <td>$<?php echo number_format($item['selling_price'], 2); ?></td>
                        <td><?php echo $item['quantity_available']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No items found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    $conn->close();
    ?>
</body>
</html>
