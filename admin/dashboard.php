<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle article deletion
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM articles WHERE id = $id");
    header('Location: dashboard.php?msg=deleted');
    exit();
}

// Handle article status toggle
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $result = mysqli_query($conn, "SELECT status FROM articles WHERE id = $id");
    if($row = mysqli_fetch_assoc($result)) {
        $new_status = $row['status'] == 'published' ? 'draft' : 'published';
        mysqli_query($conn, "UPDATE articles SET status = '$new_status' WHERE id = $id");
    }
    header('Location: dashboard.php');
    exit();
}

// Get statistics
$total_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM articles"))['count'];
$published_articles = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM articles WHERE status = 'published'"))['count'];
$total_views = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(views) as total FROM articles"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - <?php echo SITE_TITLE; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .dashboard-header {
            background: #222;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #c00;
        }
        .stat-label {
            color: #666;
            margin-top: 10px;
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
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .status-published {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .status-draft {
            background: #ff9800;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .alert {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <div>
            <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
            <a href="logout.php" class="btn btn-secondary" style="margin-left: 20px;">Logout</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert">
                <?php 
                if($_GET['msg'] == 'deleted') echo 'Article deleted successfully!';
                if($_GET['msg'] == 'added') echo 'Article added successfully!';
                if($_GET['msg'] == 'updated') echo 'Article updated successfully!';
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div style="margin-bottom: 30px;">
            <a href="add_article.php" class="btn">+ Add New Article</a>
            <a href="../index.php" class="btn btn-secondary" target="_blank">View Website</a>
        </div>
        
        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_articles; ?></div>
                <div class="stat-label">Total Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $published_articles; ?></div>
                <div class="stat-label">Published Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_views; ?></div>
                <div class="stat-label">Total Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php echo mysqli_fetch_assoc(mysqli_query($conn, 
                        "SELECT COUNT(*) as count FROM articles WHERE DATE(published_at) = CURDATE()"
                    ))['count']; ?>
                </div>
                <div class="stat-label">Today's Articles</div>
            </div>
        </div>
        
        <!-- Articles Table -->
        <div class="table-container">
            <h2 style="padding: 20px; border-bottom: 1px solid #ddd;">Manage Articles</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Date</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT a.*, c.name as category_name 
                             FROM articles a 
                             LEFT JOIN categories c ON a.category_id = c.id 
                             ORDER BY a.published_at DESC 
                             LIMIT 50";
                    $result = mysqli_query($conn, $query);
                    
                    while($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                            <?php if($row['is_featured']): ?>
                                <span style="background: gold; padding: 2px 5px; border-radius: 3px; font-size: 0.8rem; margin-left: 5px;">FEATURED</span>
                            <?php endif; ?>
                            <?php if($row['is_breaking']): ?>
                                <span style="background: #c00; color: white; padding: 2px 5px; border-radius: 3px; font-size: 0.8rem; margin-left: 5px;">BREAKING</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['author']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['published_at'])); ?></td>
                        <td><?php echo $row['views']; ?></td>
                        <td>
                            <span class="status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="../article.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary" target="_blank">View</a>
                                <a href="edit_article.php?id=<?php echo $row['id']; ?>" class="btn">Edit</a>
                                <a href="?toggle=<?php echo $row['id']; ?>" class="btn btn-secondary">
                                    <?php echo $row['status'] == 'published' ? 'Draft' : 'Publish'; ?>
                                </a>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-secondary" 
                                   onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>