inventory.php:
<?php
$host = 'localhost'; 
$user = 'root'; 
$password = ''; 
$database = 'inventory_db';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT * FROM items");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List</title>
    <link rel="stylesheet" href="inventory.css"> 
</head>
<body>
    <h1>Inventory List</h1>
    <div class="button-container">
        <a href="addinventory.php" class="button">Add Item</a>
        <a href="deleteinventory.php" class="button">Delete Item</a>
        <a href="updateinventory.php" class="button">Update Item</a>
    </div>
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
            <?php endif; ?> </tbody>/table>
    <?php
    $conn->close();
    ?>
</body>
</html>
addInventory.php:
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
    $name = $_POST['name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $quantity_available = $_POST['quantity_available'];
    $stmt = $conn->prepare("INSERT INTO items (name, cost_price, selling_price, quantity_available) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddi", $name, $cost_price, $selling_price, $quantity_available);
    if ($stmt->execute()) {
        $message = "Item added successfully.";
    } else {
        $message = "Error adding item: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item to Inventory</title>
    <link rel="stylesheet" href="addInventory.css">
</head>
<body>
    <h1>Add Item to Inventory</h1>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Item Name" required>
        <input type="number" name="cost_price" placeholder="Cost Price" step="0.01" required>
        <input type="number" name="selling_price" placeholder="Selling Price" step="0.01" required>
        <input type="number" name="quantity_available" placeholder="Quantity Available" required>
        <button type="submit">Add Item</button>
    </form><?php if (isset($message)) { echo "<p>$message</p>"; } ?>
</body>
</html>
deleteInventory.php:
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
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Item deleted successfully.";
        $stmt = $conn->prepare("ALTER TABLE items DROP COLUMN id;");
        $stmt->execute();
        $stmt = $conn->prepare("ALTER TABLE items ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;");
        $stmt->execute();
    } else {
        $message = "Error deleting item: " . $stmt->error;
    }
    $stmt->close();
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
                <tr><td colspan="5">No items found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $conn->close();
    ?>
</body>
</html>
updateInventory.php:
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
        $message = "Item updated successfully.";
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
                <tr><td colspan="5">No items found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php $conn->close();?>
</body>
</html>

And here is the stocks [page:
stock.php:
<?php
$host = 'localhost'; // Change if needed
$user = 'root'; // Your MySQL username
$password = ''; // Your MySQL password
$database = 'inventory_db';
// Create connection
$conn = new mysqli($host, $user, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch items from the database
$result = $conn->query("SELECT * FROM stock");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="Stock.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>Stock List</h1>
    <div class="button-container">
        <a href="addStock.php" class="button">Add Item</a>
        <a href="deleteStock.php" class="button">Delete Item</a>
        <a href="updateStock.php" class="button">Update Item</a></div>
    <table><thead><tr><th>ID</th><th>Name</th><th>Cost Price</th><th>Selling Price</th><th>Quantity Available</th></tr></thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($item = $result->fetch_assoc()): ?>
                    <tr><td><?php echo $item['id']; ?></td><td><?php echo htmlspecialchars($item['name']); ?></td><td>$<?php echo number_format($item['cost_price'], 2); ?></td><td>$<?php echo number_format($item['selling_price'], 2); ?></td><td><?php echo $item['quantity_available']; ?></td></tr>
                <?php endwhile; ?> <?php else: ?><tr><td colspan="5">No stocks found</td></tr><?php endif; ?>
        </tbody></table><?php $conn->close();?>
</body>
</html>
addStock:
<?php
$host = 'localhost'; // Change if needed
$user = 'root'; // Your MySQL username
$password = ''; // Your MySQL password
$database = 'inventory_db';
// Create connection
$conn = new mysqli($host, $user, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];$quantity_available = $_POST['quantity_available'];
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO stock (name, cost_price, selling_price, quantity_available) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddi", $name, $cost_price, $selling_price, $quantity_available);
    if ($stmt->execute()) {
        $message = "Item added successfully.";
    } else {
        $message = "Error adding item: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item to Inventory</title>
    <link rel="stylesheet" href="addStock.css">
</head>
<body>
    <h1>Add Item to Stock</h1>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Item Name" required>
        <input type="number" name="cost_price" placeholder="Cost Price" step="0.01" required>
        <input type="number" name="selling_price" placeholder="Selling Price" step="0.01" required>
        <input type="number" name="quantity_available" placeholder="Quantity Available" required>
        <button type="submit">Add Item</button>
    </form>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
</body>
</html>
deleteStock:
<?php
$host = 'localhost'; // Change if needed
$user = 'root'; // Your MySQL username
$password = ''; // Your MySQL password
$database = 'inventory_db';
// Create connection
$conn = new mysqli($host, $user, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    // Prepare and bind
    $stmt = $conn->prepare("DELETE FROM stock WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Item deleted successfully.";
        $stmt = $conn->prepare("ALTER TABLE stock DROP COLUMN id;");
        $stmt->execute();
        $stmt = $conn->prepare("ALTER TABLE stock ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;");
        $stmt->execute();
    } else {
        $message = "Error deleting item: " . $stmt->error;
    }
    $stmt->close();
}
// Fetch items from the database
$result = $conn->query("SELECT * FROM stock");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Stock Item</title>
    <link rel="stylesheet" href="deleteStock.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>Delete Stock Item</h1>
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
    <form method="POST" action="">
        <label for="id">Item ID:</label>
        <input type="number" name="id" placeholder="Enter Item ID" required>
        <button type="submit">Delete Item</button>
    </form>
    <h2>Current Inventory</h2>
    <table>
        <thead><tr>
                <th>ID</th><th>Name</th><th>Cost Price</th><th>Selling Price</th><th>Quantity Available</th></tr></thead>
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
updaetSock:
<?php
$host = 'localhost'; // Change if needed
$user = 'root'; // Your MySQL username
$password = ''; // Your MySQL password
$database = 'inventory_db';
// Create connection
$conn = new mysqli($host, $user, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $quantity_available = $_POST['quantity_available'];
    // Prepare and bind
    $stmt = $conn->prepare("UPDATE stock SET name = ?, cost_price = ?, selling_price = ?, quantity_available = ? WHERE id = ?");
    $stmt->bind_param("sddii", $name, $cost_price, $selling_price, $quantity_available, $id);
    if ($stmt->execute()) {
        $message = "Item updated successfully.";
    } else {
        $message = "Error updating item: " . $stmt->error;
    }
    $stmt->close();
}
// Fetch items from the database for selection
$result = $conn->query("SELECT * FROM stock");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory Item</title>
    <link rel="stylesheet" href="updateStock.css"> <!-- Link to CSS file -->
</head>
<body>
    <h1>Update Stock Item</h1>
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
    <h2>Current Stock</h2>
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
                    <td colspan="5">No stocks found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $conn->close();
    ?>
</body>
</html>
If i do changes in inventory, then it does not deducts from stocks or do not makes any chanhes in inventory