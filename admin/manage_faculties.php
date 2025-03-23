<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if(!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = clean_input($_POST['name']);
            $slug = clean_input($_POST['slug']);
            $description = clean_input($_POST['description']);

            if (empty($name) || empty($slug)) {
                $error = 'Name and slug are required.';
            } else {
                $query = "INSERT INTO faculties (name, slug, description) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sss", $name, $slug, $description);
                
                if ($stmt->execute()) {
                    $success = 'Faculty added successfully.';
                } else {
                    $error = 'Error adding faculty: ' . $conn->error;
                }
            }
        } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            // Check if faculty has any categories
            $check_query = "SELECT COUNT(*) as count FROM categories WHERE faculty_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];

            if ($count > 0) {
                $error = 'Cannot delete faculty: It has associated categories.';
            } else {
                $query = "DELETE FROM faculties WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success = 'Faculty deleted successfully.';
                } else {
                    $error = 'Error deleting faculty: ' . $conn->error;
                }
            }
        }
    }
}

// Get all faculties
$query = "SELECT * FROM faculties ORDER BY name";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculties - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Admin Panel</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="../index.php">View Site</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container admin-content">
        <h1>Manage Faculties</h1>

        <?php if(!empty($error)): ?>
            <div class="error message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="success message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <section class="add-faculty">
            <h2>Add New Faculty</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="slug">Slug:</label>
                    <input type="text" id="slug" name="slug" required>
                </div>
                <div>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div>
                    <button type="submit" class="btn">Add Faculty</button>
                </div>
            </form>
        </section>

        <section class="faculty-list">
            <h2>Existing Faculties</h2>
            <?php if ($result->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($faculty = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($faculty['name']) ?></td>
                                <td><?= htmlspecialchars($faculty['slug']) ?></td>
                                <td><?= htmlspecialchars($faculty['description']) ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $faculty['id'] ?>">
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this faculty?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No faculties found.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            document.getElementById('slug').value = slug;
        });
    </script>
</body>
</html> 