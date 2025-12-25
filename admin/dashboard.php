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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            line-height: 1.6;
        }
        
        /* Header Styles */
        .dashboard-header {
            background: #222;
            color: white;
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        /* Main Container */
        .dashboard-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }
        
        /* Stats Container - Mobile First */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #c00;
        }
        
        .stat-label {
            color: #666;
            margin-top: 8px;
            font-size: 0.9rem;
        }
        
        /* Button Styles */
        .btn {
            background: #c00;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            text-align: center;
            transition: background 0.3s;
            white-space: nowrap;
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
        
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px; /* Ensures table doesn't shrink too much */
        }
        
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 0.9rem;
        }
        
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        /* Status Badges */
        .status-published {
            background: #4CAF50;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        .status-draft {
            background: #ff9800;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        /* Alert Messages */
        .alert {
            background: #4CAF50;
            color: white;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Article Tags */
        .article-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 5px;
        }
        
        .tag-featured {
            background: gold;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7rem;
            color: #333;
        }
        
        .tag-breaking {
            background: #c00;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7rem;
        }
        
        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 10px 15px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .dashboard-container {
                padding: 10px;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 0.85rem;
                flex: 1;
                min-width: 120px;
            }
            
            .btn-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            th, td {
                padding: 8px 6px;
                font-size: 0.85rem;
            }
            
            .action-buttons {
                flex-direction: column;
                min-width: 120px;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin: 2px 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .header-user {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                padding: 10px;
            }
            
            .stat-number {
                font-size: 1.3rem;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 6px 4px;
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            
            .dashboard-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .header-top {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        /* Print Styles */
        @media print {
            .btn, .action-buttons {
                display: none;
            }
            
            .table-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-top">
            <h1>Admin Dashboard</h1>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
        </div>
        <div class="header-user" id="mobileMenu">
            <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
            <div>
                <a href="add_article.php" class="btn">+ Add New Article</a>
                <a href="../index.php" class="btn btn-secondary" target="_blank">View Website</a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
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
        
        <!-- Quick Actions for desktop --
        <div class="btn-group desktop-only">
            <a href="add_article.php" class="btn">+ Add New Article</a>
            <a href="../index.php" class="btn btn-secondary" target="_blank">View Website</a>
        </div>-->
        
        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_articles; ?></div>
                <div class="stat-label">Total Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $published_articles; ?></div>
                <div class="stat-label">Published</div>
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
            <h2 style="padding: 15px; border-bottom: 1px solid #ddd; font-size: 1.2rem;">Manage Articles</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
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
                            <div class="article-tags">
                                <?php if($row['is_featured']): ?>
                                    <span class="tag-featured">FEATURED</span>
                                <?php endif; ?>
                                <?php if($row['is_breaking']): ?>
                                    <span class="tag-breaking">BREAKING</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo $row['category_name']; ?></td>
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
    
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.style.display = menu.style.display === 'none' ? 'flex' : 'none';
        }
        
        // Auto-hide mobile menu on resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.getElementById('mobileMenu').style.display = 'flex';
            }
        });
        
        // Initialize mobile menu on load
        window.addEventListener('load', function() {
            if (window.innerWidth <= 768) {
                document.getElementById('mobileMenu').style.display = 'none';
            }
        });
    </script>
</body>
</html>
