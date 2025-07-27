<?php
$host = 'localhost';        
$user = 'root';
$password = ''; 
$database = 'inventory_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $quantity = $_POST["quantity_available"];

    $conn->begin_transaction();

    try {
        // Step 1: Check if item exists in stock
        $checkStockStmt = $conn->prepare("SELECT quantity_available, cost_price, selling_price FROM stock WHERE name = ?");
        $checkStockStmt->bind_param("s", $name);
        $checkStockStmt->execute();
        $checkStockStmt->store_result();

        if ($checkStockStmt->num_rows > 0) {
            $checkStockStmt->bind_result($available_quantity, $cost_price, $selling_price);
            $checkStockStmt->fetch();

            if ($available_quantity >= $quantity) {
                // Deduct quantity from stock
                $new_quantity = $available_quantity - $quantity;
                $updateStockStmt = $conn->prepare("UPDATE stock SET quantity_available = ? WHERE name = ?");
                $updateStockStmt->bind_param("is", $new_quantity, $name);
                $updateStockStmt->execute();

                // Check if item already exists in inventory
                $checkInventoryStmt = $conn->prepare("SELECT id, quantity_available FROM items WHERE name = ?");
                $checkInventoryStmt->bind_param("s", $name);
                $checkInventoryStmt->execute();
                $checkInventoryStmt->store_result();

                if ($checkInventoryStmt->num_rows > 0) {
                    // Item exists - update quantity
                    $checkInventoryStmt->bind_result($item_id, $current_quantity);
                    $checkInventoryStmt->fetch();
                    $new_inventory_quantity = $current_quantity + $quantity;
                    
                    $updateInventoryStmt = $conn->prepare("UPDATE items SET quantity_available = ? WHERE id = ?");
                    $updateInventoryStmt->bind_param("ii", $new_inventory_quantity, $item_id);
                    
                    if (!$updateInventoryStmt->execute()) {
                        throw new Exception("Failed to update inventory quantity.");
                    }
                } else {
                    // Item doesn't exist - insert new
                    $insertInventoryStmt = $conn->prepare("INSERT INTO items (name, cost_price, selling_price, quantity_available) VALUES (?, ?, ?, ?)");
                    $insertInventoryStmt->bind_param("sddi", $name, $cost_price, $selling_price, $quantity);

                    if (!$insertInventoryStmt->execute()) {
                        throw new Exception("Failed to add to inventory.");
                    }
                }

                $conn->commit();
                echo "<script>alert('Inventory updated successfully.'); window.location.href = 'inventory.php';</script>";
            } else {
                throw new Exception("Not enough stock. Available: $available_quantity");
            }
        } else {
            throw new Exception("Item not found in stock.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location.href = 'inventory.php';</script>";
    } finally {
        if (isset($checkStockStmt)) $checkStockStmt->close();
        if (isset($updateStockStmt)) $updateStockStmt->close();
        if (isset($checkInventoryStmt)) $checkInventoryStmt->close();
        if (isset($updateInventoryStmt)) $updateInventoryStmt->close();
        if (isset($insertInventoryStmt)) $insertInventoryStmt->close();
        $conn->close();
    }
}

// Fetch stock item names for datalist
$conn2 = new mysqli($host, $user, $password, $database);
$stockItems = [];
$result = $conn2->query("SELECT name FROM stock");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stockItems[] = $row['name'];
    }
}
$conn2->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Item to Inventory</title>
    <link rel="stylesheet" href="addInventory.css">
</head>
<body>
    <h1>Add Item to Inventory</h1>
     <div class="homepage-btn">
        <a href="../index.html" class="home-page-button">Go to Homepage</a> 
    </div>
    <form method="POST" action="">
        <label for="name">Item Name</label>
        <input list="stock-items" name="name" placeholder="Type or select item" required>
        <datalist id="stock-items">
            <?php foreach ($stockItems as $item): ?>
                <option value="<?= htmlspecialchars($item) ?>">
            <?php endforeach; ?>
        </datalist>

        <label for="quantity_available">Quantity to Add</label>
        <input type="number" name="quantity_available" placeholder="Enter Quantity" min="1" required>

        <button type="submit">Add to Inventory</button>
    </form>
</body>
</html>