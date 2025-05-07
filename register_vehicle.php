<?php
session_start();
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
exit();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
   
}

require_once 'includes/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plate = trim($_POST['plate_number']);
    $owner = trim($_POST['owner_name']);
    $email = trim($_POST['owner_email']);
    $admin_id = $_SESSION['user_id'];

    if ($plate && $owner && $email) {
        $stmt = $conn->prepare("INSERT INTO vehicles (plate_number, owner_name, owner_email, registered_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $plate, $owner, $email, $admin_id);

        if ($stmt->execute()) {
            $message = "Vehicle registered successfully!";
        } else {
            $message = "Error: Vehicle may already be registered.";
        }
        $stmt->close();
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Vehicle</title>
</head>
<body>
    <h2>Register a Vehicle</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Plate Number:</label><br>
        <input type="text" name="plate_number" required><br><br>
        <label>Owner Name:</label><br>
        <input type="text" name="owner_name" required><br><br>
        <label>Owner Email:</label><br>
        <input type="email" name="owner_email" required><br><br>
        <button type="submit">Register Vehicle</button>
    </form>
    <br>
    <a href="admin.php">Back to Dashboard</a>
</body>
</html>
