<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "inventory_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = 1; // demo user
$result = $conn->query("SELECT * FROM users WHERE id = $id");
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profile - Smart Shop Manager</title>
  <link rel="stylesheet" href="profile.css" />
  <script src="https://kit.fontawesome.com/c1df782baf.js" crossorigin="anonymous"></script>
</head>
<body>

<div id="mainContent">
  <header class="header">
    <div class="logo"><img src="../img2/logo.png" alt="Logo" /></div>
    <div class="homepage-btn"><a href="../index.html" class="home-page-button">Go to Homepage</a></div>
    <nav class="navbar">
      <a href="../index.html">Home</a>
      <a href="../Inventory/inventory.php">Inventory</a>
      <a href="../Sales/sales.php">Sales</a>
      <a href="../Staff/staff.php">Staff</a>
      <a href="../Customer/customers.php">Customers</a>
    </nav>
  </header>

  <main class="profile-container">
    <div class="profile-card">
      <div class="profile-picture">
        <img src="../images/fe1.png" alt="User Profile Picture" />
      </div>
      <div class="profile-details">
        <h1><?= $row['name'] ?></h1>
        <p class="role"><?= $row['role'] ?></p>
        <p class="email"><i class="fa-solid fa-envelope"></i> <?= $row['email'] ?></p>
        <p class="phone"><i class="fa-solid fa-phone"></i> <?= $row['phone'] ?></p>
        <p class="joined"><i class="fa-solid fa-calendar"></i> Joined on: <?= date('M Y', strtotime($row['joined'])) ?></p>
        <a href="#" class="edit-btn" id="editBtn">Edit Profile</a>
      </div>
    </div>
    <!-- <a href="add-user.php" class="new-user-btn">➕ Add New User</a> -->
  </main>

  <footer class="footer">
    <div class="footer-content">
      &copy; 2025 Smart Shop Manager. All rights reserved. <br>
      Developed by Ajeet Verma
    </div>
  </footer>
</div>

<!-- Edit Form Modal -->
<div class="edit-form" id="editForm" style="display:none;">
  <form method="POST" action="">
    <h2>Edit Profile</h2>
    <input type="text" name="name" value="<?= $row['name'] ?>" required />
    <input type="text" name="role" value="<?= $row['role'] ?>" required />
    <input type="email" name="email" value="<?= $row['email'] ?>" required />
    <input type="text" name="phone" value="<?= $row['phone'] ?>" required />
    <input type="date" name="joined" value="<?= $row['joined'] ?>" required />
    <input type="password" name="password" placeholder="New Password (optional)" />
    <button type="submit" name="update">Update</button>
    <button type="button" onclick="closeForm()">Cancel</button>
  </form>
</div>

<script>
  const editBtn = document.getElementById('editBtn');
  const mainContent = document.getElementById('mainContent');
  const form = document.getElementById('editForm');

  editBtn.addEventListener('click', (e) => {
    e.preventDefault();
    form.style.display = 'block';
    mainContent.classList.add('blurred');
  });

  function closeForm() {
    form.style.display = 'none';
    mainContent.classList.remove('blurred');
  }
</script>

</body>
</html>

<?php
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $joined = $_POST['joined'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = $password;
        $conn->query("UPDATE users SET name='$name', role='$role', email='$email', phone='$phone', joined='$joined', password='$hashed' WHERE id=$id");
    } else {
        $conn->query("UPDATE users SET name='$name', role='$role', email='$email', phone='$phone', joined='$joined' WHERE id=$id");
    }

    echo "<script>location.href='profile.php';</script>";
}
?>
