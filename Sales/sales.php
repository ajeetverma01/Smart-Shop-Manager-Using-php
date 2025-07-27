<?php
$host = 'localhost'; 
$user = 'root';
$password = '';
$database = 'inventory_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id']) && isset($_POST['quantity_sold']) && isset($_POST['customer_name'])) {
    $item_id = intval($_POST['item_id']);
    $quantity_sold = intval($_POST['quantity_sold']);
    $customer_name = $conn->real_escape_string(trim($_POST['customer_name']));

    $result = $conn->query("SELECT quantity_available FROM items WHERE id = $item_id");
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $current_quantity = $item['quantity_available'];
    

        // Check if enough stock is available
        if ($current_quantity >= $quantity_sold) {
            // Update the inventory
            $new_quantity = $current_quantity - $quantity_sold;
            $conn->query("UPDATE items SET quantity_available = $new_quantity WHERE id = $item_id");

            // Record the sale
            $conn->query("INSERT INTO sales (item_id, quantity_sold, customer_name) VALUES ($item_id, $quantity_sold, '$customer_name')");

            $message = "Sale recorded successfully. New quantity: $new_quantity";
        } else {
            $message = "Not enough stock available.";
        }
    } else {
        $message = "Item not found.";
    }
}

if (isset($_POST['remove_sale_id'])) {
    $sale_id = intval($_POST['remove_sale_id']);
    $conn->query("DELETE FROM sales WHERE id = $sale_id");
    $conn->query("ALTER TABLE sales DROP COLUMN id;");
    $conn->query("ALTER TABLE sales ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;");
}

$availableItems = [];
$result = $conn->query("SELECT id, name, quantity_available FROM items WHERE quantity_available > 0");
while ($row = $result->fetch_assoc()) {
    $availableItems[] = $row;
}
$salesResult = $conn->query("SELECT s.id, s.item_id, s.quantity_sold, s.sale_date, i.name, s.customer_name FROM sales s JOIN items i ON s.item_id = i.id");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Sale</title>
    <style>
        body {
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 20px;
    background-color: #f4f4f4;
    color: #333;
}

h1, h2 {
    color: #003366;
    text-align: center;
}

/* form container card style */
form {
    margin: 20px auto;
    padding: 25px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    max-width: 600px;
}

label {
    display: block;
    margin: 12px 0 6px;
    font-weight: 600;
}

input[type="number"],
input[type="text"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    transition: border-color 0.3s;
}

input[type="number"]:focus,
input[type="text"]:focus {
    border-color: #0055a5;
    outline: none;
}

/* button styles matching the theme */
button {
    padding: 12px 20px;
    background-color: #0055a5;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.4s, transform 0.3s;
}

button:hover {
    background-color: #003366;
    transform: translateY(-2px);
}

/* sales table container as card */
#salesTableContainer {
    margin: 20px auto;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    padding: 25px;
    max-width: 1000px;
}

/* consistent table styling */
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

th {
    background-color: #004080;
    color: #fff;
    padding: 14px;
    text-align: left;
    font-weight: 600;
}

td {
    padding: 14px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    background-color: white;
    transition: background-color 0.3s ease;
}

/* alternate rows */
tbody tr:nth-child(even) td {
    background-color: #f0f8ff;
}

/* hover row highlight with colored border */
tbody tr:hover td {
    background-color: #e6f2ff;
    border-left: 4px solid #0055a5;
}
       /* Homepage Button Styles */
.homepage-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 100;
}

.home-page-button {
    display: inline-block;
    padding: 12px 24px;
    background-color: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
    transition: all 0.3s ease;
    border: 2px solid #3498db;
}

.home-page-button:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(52, 152, 219, 0.4);
}

.home-page-button:active {
    transform: translateY(0);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .homepage-btn {
        bottom: 20px;
        right: 20px;
    }
    
    .home-page-button {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .homepage-btn {
        bottom: 15px;
        right: 15px;
    }
    
    .home-page-button {
        padding: 8px 16px;
        font-size: 0.8rem;
    }
}
select, input {
    width: 100%;
    padding: 10px;
    margin-top: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

label {
    display: block;
    margin-top: 16px;
    font-weight: bold;
}

    </style>
</head>
<body>
    <h1>Record a Sale</h1>
    <form method="POST">
         <label for="item_id">Select Item</label>
    <select name="item_id" id="item_id" required>
        <option value="">-- Select an item --</option>
        <?php foreach ($availableItems as $item): ?>
            <option value="<?= $item['id'] ?>">
                <?= htmlspecialchars($item['name']) ?> (<?= $item['quantity_available'] ?> available)
            </option>
        <?php endforeach; ?>
    </select>
        
        <label for="quantity_sold">Quantity Sold:</label>
        <input type="number" name="quantity_sold" required>
        
        <label for="customer_name">Customer Name:</label>
        <input type="text" name="customer_name" required>
        
        <button type="submit">Submit Sale</button>
    </form>
    <div class="homepage-btn">
        <a href="../index.html" class="home-page-button">Go to Homepage</a> 
    </div>

    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <h2>Sales Records</h2>
    <button id="showSalesButton">Show Sales</button>
    <div id="salesTableContainer" style="display:none;">
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Item Name</th>
                    <th>Quantity Sold</th>
                    <th>Sale Date</th>
                    <th>Customer Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($salesResult->num_rows > 0): ?>
                    <?php while ($sale = $salesResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $sale['id']; ?></td>
                            <td><?php echo htmlspecialchars($sale['name']); ?></td>
                            <td><?php echo $sale['quantity_sold']; ?></td>
                            <td><?php echo $sale['sale_date']; ?></td>
                            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="remove_sale_id" value="<?php echo $sale['id']; ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to remove this sale?');">Remove Sale</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No sales records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('showSalesButton').addEventListener('click', function() {
            const salesTableContainer = document.getElementById('salesTableContainer');
            const isVisible = salesTableContainer.style.display === 'block';
            salesTableContainer.style.display = isVisible ? 'none' : 'block';
            this.textContent = isVisible ? 'Show Sales' : 'Hide Sales';
        });
    </script>
</body>
</html>
