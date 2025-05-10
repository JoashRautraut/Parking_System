<?php
session_start();
require 'includes/db.php';

// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: registered_vehicles.php");
    exit();
}

// Handle Edit Student
$editStudent = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    $editStudent = $result->fetch_assoc();
    $stmt->close();
}

// Handle Update Student
if (isset($_POST['update_student'])) {
    $id = intval($_POST['student_id']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ? AND role = 'student'");
    $stmt->bind_param("sssi", $firstName, $lastName, $email, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: registered_vehicles.php");
    exit();
}

// Fetch Registered Vehicles
$searchQuery = "";
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $searchQuery = " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%')";
}

$students = $conn->query("SELECT * FROM users WHERE role = 'student' $searchQuery");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Vehicles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="registered_vehicles.php">Registered Vehicles</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</div>

<div class="main-content">
    <h2>Registered Vehicles</h2>
    <form method="GET" action="registered_vehicles.php">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit">Search</button>
    </form>

    <h3>Student List</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($student = $students->fetch_assoc()): ?>
            <tr>
                <td><?= $student['id'] ?></td>
                <td><?= htmlspecialchars($student['first_name']) ?></td>
                <td><?= htmlspecialchars($student['last_name']) ?></td>
                <td><?= htmlspecialchars($student['email']) ?></td>
                <td>
                    <a href="registered_vehicles.php?edit=<?= $student['id'] ?>">Edit</a> |
                    <a href="registered_vehicles.php?delete=<?= $student['id'] ?>" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php if ($editStudent): ?>
<div class="main-content">
    <h3>Edit Student</h3>
    <form method="POST" action="registered_vehicles.php">
        <input type="hidden" name="student_id" value="<?= $editStudent['id'] ?>">
        <input type="text" name="first_name" value="<?= htmlspecialchars($editStudent['first_name']) ?>" required>
        <input type="text" name="last_name" value="<?= htmlspecialchars($editStudent['last_name']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($editStudent['email']) ?>" required>
        <button type="submit" name="update_student">Update</button>
    </form>
</div>
<?php endif; ?>

</body>
</html>
