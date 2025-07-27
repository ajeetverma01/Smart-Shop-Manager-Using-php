<?php
$host = 'localhost'; 
$user = 'root'; 
$password = ''; 
$database = 'inventory_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $quantity_available = $_POST['quantity_available'];

    // Prepare and bind
    $stmt = $conn->prepare("UPDATE items SET name = ?, cost_price = ?, selling_price = ?, quantity_available = ? WHERE id = ?");
    $stmt->bind_param("sddii", $name, $cost_price, $selling_price, $quantity_available, $id);

   if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $message = "Item updated successfully.";
    } else {
        $message = "Item not found. No changes made.";
    }
} else {
    $message = "Error updating item: " . $stmt->error;
}


    $stmt->close();
}

// Fetch items from the database for selection
$result = $conn->query("SELECT * FROM items");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory Item</title>
    <link rel="stylesheet" href="updateInventory.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>Update Inventory Item</h1>
<div class="homepage-btn">
        <a href="../index.html" class="home-page-button">Go to Homepage</a> 
    </div>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <form method="POST" action="">
        <label for="id">Item ID:</label>
        <input type="number" name="id" placeholder="Enter Item ID" required>
        <label for="name">Item Name:</label>
        <input type="text" name="name" placeholder="Enter Item Name" required>
        <label for="cost_price">Cost Price:</label>
        <input type="number" name="cost_price" placeholder="Enter Cost Price" step="0.01" required>
        <label for="selling_price">Selling Price:</label>
        <input type="number" name="selling_price" placeholder="Enter Selling Price" step="0.01" required>
        <label for="quantity_available">Quantity Available:</label>
        <input type="number" name="quantity_available" placeholder="Enter Quantity Available" required>
        <button type="submit">Update Item</button>
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
