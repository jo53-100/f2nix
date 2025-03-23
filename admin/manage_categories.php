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

// Get all faculties for the dropdown
$faculties_query = "SELECT * FROM faculties ORDER BY name";
$faculties_result = $conn->query($faculties_query);
$faculties = [];
while($faculty = $faculties_result->fetch_assoc()) {
    $faculties[] = $faculty;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = clean_input($_POST['name']);
            $slug = clean_input($_POST['slug']);
            $faculty_id = (int)$_POST['faculty_id'];

            if (empty($name) || empty($slug) || $faculty_id <= 0) {
                $error = 'Name, slug, and faculty are required.';
            } else {
                // Check if the slug exists in the same faculty
                $check_query = "SELECT COUNT(*) as count FROM categories WHERE slug = ? AND faculty_id = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("si", $slug, $faculty_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $count = $result->fetch_assoc()['count'];

                if ($count > 0) {
                    // Append faculty slug to make it unique
                    $faculty_query = "SELECT slug FROM faculties WHERE id = ?";
                    $faculty_stmt = $conn->prepare($faculty_query);
                    $faculty_stmt->bind_param("i", $faculty_id);
                    $faculty_stmt->execute();
                    $faculty_result = $faculty_stmt->get_result();
                    $faculty_slug = $faculty_result->fetch_assoc()['slug'];
                    
                    $slug = $slug . '-' . $faculty_slug;
                }

                $query = "INSERT INTO categories (name, slug, faculty_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssi", $name, $slug, $faculty_id);
                
                if ($stmt->execute()) {
                    $success = 'Category added successfully.';
                } else {
                    $error = 'Error adding category: ' . $conn->error;
                }
            }
        } 
        elseif ($_POST['action'] === 'edit') {
            $id = (int)$_POST['id'];
            $name = clean_input($_POST['name']);
            $slug = clean_input($_POST['slug']);
            $faculty_id = (int)$_POST['faculty_id'];

            if (empty($name) || empty($slug) || $faculty_id <= 0) {
                $error = 'Name, slug, and faculty are required.';
            } else {
                // Check if the slug exists in the same faculty (excluding current category)
                $check_query = "SELECT COUNT(*) as count FROM categories WHERE slug = ? AND faculty_id = ? AND id != ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param("sii", $slug, $faculty_id, $id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $count = $result->fetch_assoc()['count'];

                if ($count > 0) {
                    // Append faculty slug to make it unique
                    $faculty_query = "SELECT slug FROM faculties WHERE id = ?";
                    $faculty_stmt = $conn->prepare($faculty_query);
                    $faculty_stmt->bind_param("i", $faculty_id);
                    $faculty_stmt->execute();
                    $faculty_result = $faculty_stmt->get_result();
                    $faculty_slug = $faculty_result->fetch_assoc()['slug'];
                    
                    $slug = $slug . '-' . $faculty_slug;
                }

                $query = "UPDATE categories SET name = ?, slug = ?, faculty_id = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssii", $name, $slug, $faculty_id, $id);
                
                if ($stmt->execute()) {
                    $success = 'Category updated successfully.';
                } else {
                    $error = 'Error updating category: ' . $conn->error;
                }
            }
        }
        elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            // Check if category has any articles
            $check_query = "SELECT COUNT(*) as count FROM article_categories ac 
                           JOIN categories c ON ac.category_slug = c.slug 
                           WHERE c.id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("i", $id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $count = $result->fetch_assoc()['count'];

            if ($count > 0) {
                $error = 'Cannot delete category: It has associated articles.';
            } else {
                $query = "DELETE FROM categories WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success = 'Category deleted successfully.';
                } else {
                    $error = 'Error deleting category: ' . $conn->error;
                }
            }
        }
    }
}

// Get all categories with their faculty names
$query = "SELECT c.*, f.name as faculty_name 
          FROM categories c
          JOIN faculties f ON c.faculty_id = f.id
          ORDER BY f.name, c.name";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .edit-form {
            display: none;
        }
        .edit-form.active {
            display: block;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">Admin Panel</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Dashboard</a></li>
                        <li><a href="manage_faculties.php">Manage Faculties</a></li>
                        <li><a href="../index.php">View Site</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container admin-content">
        <h1>Manage Categories</h1>

        <?php if(!empty($error)): ?>
            <div class="error message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="success message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <section class="add-category">
            <h2>Add New Category</h2>
            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="slug">Slug:</label>
                    <input type="text" id="slug" name="slug" required>
                    <small>URL-friendly version of name (e.g., "computer-science")</small>
                </div>
                <div class="form-group">
                    <label for="faculty_id">Faculty:</label>
                    <select id="faculty_id" name="faculty_id" required>
                        <option value="">Select Faculty</option>
                        <?php foreach ($faculties as $faculty): ?>
                            <option value="<?= $faculty['id'] ?>"><?= htmlspecialchars($faculty['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Add Category</button>
                </div>
            </form>
        </section>

        <section class="category-list">
            <h2>Existing Categories</h2>
            <?php if ($result->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Faculty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= htmlspecialchars($category['slug']) ?></td>
                                <td><?= htmlspecialchars($category['faculty_name']) ?></td>
                                <td class="actions">
                                    <button class="btn small" onclick="showEditForm(<?= $category['id'] ?>, '<?= htmlspecialchars(addslashes($category['name'])) ?>', '<?= htmlspecialchars(addslashes($category['slug'])) ?>', <?= $category['faculty_id'] ?>)">Edit</button>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                        <button type="submit" class="btn small danger" 
                                                onclick="return confirm('Are you sure you want to delete this category?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="edit-form" id="edit-form-<?= $category['id'] ?>">
                                <td colspan="4">
                                    <form method="POST" action="" class="admin-form">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                        <div class="form-group">
                                            <label>Name:</label>
                                            <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Slug:</label>
                                            <input type="text" name="slug" value="<?= htmlspecialchars($category['slug']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Faculty:</label>
                                            <select name="faculty_id" required>
                                                <?php foreach ($faculties as $faculty): ?>
                                                    <option value="<?= $faculty['id'] ?>" <?= $category['faculty_id'] == $faculty['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($faculty['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn">Update</button>
                                            <button type="button" class="btn secondary" onclick="hideEditForm(<?= $category['id'] ?>)">Cancel</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No categories found.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function showEditForm(id, name, slug, facultyId) {
            document.querySelectorAll('.edit-form').forEach(form => form.classList.remove('active'));
            document.getElementById('edit-form-' + id).classList.add('active');
        }

        function hideEditForm(id) {
            document.getElementById('edit-form-' + id).classList.remove('active');
        }

        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            let slug = this.value.toLowerCase()
                .replace(/[^a-z0-9áéíóúñ]+/g, '-') // Allow Spanish characters
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Remove diacritics
                .replace(/(^-|-$)/g, '');
            document.getElementById('slug').value = slug;
        });
    </script>
</body>
</html> 