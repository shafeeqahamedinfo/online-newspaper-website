<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle category actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['add_category'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $slug = strtolower(str_replace(' ', '-', preg_replace('/[^\w\s]/', '', $name)));
        
        mysqli_query($conn, "INSERT INTO categories (name, slug) VALUES ('$name', '$slug')");
        $success = "Category added successfully!";
    }
    
    if(isset($_POST['update_category'])) {
        $id = intval($_POST['id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $slug = strtolower(str_replace(' ', '-', preg_replace('/[^\w\s]/', '', $name)));
        
        mysqli_query($conn, "UPDATE categories SET name = '$name', slug = '$slug' WHERE id = $id");
        $success = "Category updated successfully!";
    }
}

// Handle category deletion
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header('Location: categories.php?msg=deleted');
    exit();
}

// Get all categories
$categories = mysqli_query($conn, "SELECT c.*, 
    (SELECT COUNT(*) FROM articles WHERE category_id = c.id AND status = 'published') as article_count
    FROM categories c 
    ORDER BY c.name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories - Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #222;
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .btn {
            background: #c00;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #a00;
        }
        .btn-secondary {
            background: #666;
        }
        .btn-secondary:hover {
            background: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .alert {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background: #ffdddd;
            color: #c00;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-inline {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .form-inline .form-group {
            flex: 1;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Manage Categories</h1>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">
                Category deleted successfully!
            </div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Add Category Form -->
        <div class="card">
            <h2>Add New Category</h2>
            <form method="POST" action="">
                <div class="form-inline">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div>
                        <button type="submit" name="add_category" class="btn">Add Category</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Categories List -->
        <div class="card">
            <h2>All Categories</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Articles</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><?php echo htmlspecialchars($cat['name']); ?></td>
                        <td><?php echo $cat['slug']; ?></td>
                        <td>
                            <span style="background: #e0e0e0; padding: 3px 8px; border-radius: 15px;">
                                <?php echo $cat['article_count']; ?> articles
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($cat['created_at'])); ?></td>
                        <td>
                            <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-secondary" 
                               onclick="return confirm('Delete this category? Articles will NOT be deleted.')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>