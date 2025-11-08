<?php
require_once 'includes/config.php';
require_once '/includes/functions.php';

if (!isAdmin()) {
    header('Location: ../403.php');
    exit;
}

$error = '';
$success = '';

// Add or update category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $category_id = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        try {
            if ($category_id) {
                // Update
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $description, $category_id]);
                $success = "Category updated successfully!";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, status) VALUES (?, ?, 'active')");
                $stmt->execute([$name, $description]);
                $success = "Category added successfully!";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Delete category
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare("UPDATE categories SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success = "Category deleted successfully!";
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Handmade Haven</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Manage Categories</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="categories-management">
                <div class="add-category-form">
                    <h2>Add New Category</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </form>
                </div>

                <div class="categories-list">
                    <h2>Existing Categories</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td class="actions">
                                        <a href="categories-edit.php?id=<?php echo $category['id']; ?>"
                                            class="btn btn-sm btn-outline">Edit</a>
                                        <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>