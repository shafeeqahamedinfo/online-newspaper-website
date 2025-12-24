<?php
require_once '../config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id = intval($_GET['id']);

// Get article data
$query = "SELECT * FROM articles WHERE id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header('Location: dashboard.php');
    exit();
}

$article = mysqli_fetch_assoc($result);

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^\w\s]/', '', $title)));
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL;
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $status = $_POST['status'];
    
    // Handle image upload
    $featured_image = $article['featured_image'];
    
    if(isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Delete old image if exists
        if($featured_image && file_exists('../' . $featured_image)) {
            unlink('../' . $featured_image);
        }
        
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'uploads/' . $file_name;
        }
    }
    
    // Handle image removal
    if(isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if($featured_image && file_exists('../' . $featured_image)) {
            unlink('../' . $featured_image);
        }
        $featured_image = '';
    }
    
    // Validate category if provided
    if($category_id) {
        $check_cat = mysqli_query($conn, "SELECT id FROM categories WHERE id = $category_id");
        if(mysqli_num_rows($check_cat) == 0) {
            $error = "Selected category does not exist!";
        }
    }
    
    if(!isset($error)) {
        // Build update query
        $query = "UPDATE articles SET 
                  title = '$title',
                  slug = '$slug',
                  excerpt = '$excerpt',
                  content = '$content',
                  category_id = " . ($category_id ? "$category_id" : "NULL") . ",
                  author = '$author',
                  featured_image = '$featured_image',
                  is_featured = $is_featured,
                  is_breaking = $is_breaking,
                  status = '$status' 
                  WHERE id = $id";
        
        if(mysqli_query($conn, $query)) {
            header('Location: dashboard.php?msg=updated');
            exit();
        } else {
            $error = "Error updating article: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Article - Admin Dashboard</title>
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
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        textarea {
            min-height: 150px;
        }
        #content {
            min-height: 400px;
        }
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn {
            background: #c00;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn:hover {
            background: #a00;
        }
        .btn-secondary {
            background: #666;
            text-decoration: none;
            display: inline-block;
            padding: 10px 20px;
        }
        .error {
            background: #ffdddd;
            color: #c00;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #ddffdd;
            color: #008800;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .required {
            color: #c00;
        }
        .image-preview {
            margin: 15px 0;
            max-width: 300px;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .remove-image {
            display: inline-block;
            margin-top: 10px;
            color: #c00;
            cursor: pointer;
            text-decoration: none;
        }
        .remove-image:hover {
            text-decoration: underline;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .stats-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c00;
        }
        .stats-box div {
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Edit Article</h1>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Article Statistics -->
        <div class="stats-box">
            <div><strong>Article ID:</strong> #<?php echo $article['id']; ?></div>
            <div><strong>Created:</strong> <?php echo date('F j, Y, h:i A', strtotime($article['published_at'])); ?></div>
            <div><strong>Total Views:</strong> <?php echo number_format($article['views']); ?></div>
            <div><strong>Current Status:</strong> 
                <span style="background: <?php echo $article['status'] == 'published' ? '#4CAF50' : '#ff9800'; ?>; 
                      color: white; padding: 2px 8px; border-radius: 3px;">
                    <?php echo ucfirst($article['status']); ?>
                </span>
            </div>
        </div>
        
        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Article Title <span class="required">*</span></label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Excerpt (Short Summary)</label>
                    <textarea name="excerpt" rows="3"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Content <span class="required">*</span></label>
                    <textarea name="content" id="content" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">-- No Category --</option>
                        <?php 
                        mysqli_data_seek($categories, 0);
                        while($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($article['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($article['author']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Featured Image</label>
                    
                    <?php if($article['featured_image']): ?>
                    <div class="image-preview">
                        <img src="../<?php echo $article['featured_image']; ?>" alt="Current Image">
                        <div style="margin-top: 10px;">
                            <label>
                                <input type="checkbox" name="remove_image" value="1">
                                Remove current image
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" name="featured_image" accept="image/*">
                    <small style="color: #666;">Leave empty to keep current image</small>
                </div>
                
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="is_featured" value="1" 
                            <?php echo $article['is_featured'] ? 'checked' : ''; ?>>
                        Mark as Featured
                    </label>
                    <label>
                        <input type="checkbox" name="is_breaking" value="1" 
                            <?php echo $article['is_breaking'] ? 'checked' : ''; ?>>
                        Mark as Breaking News
                    </label>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Update Article</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <a href="../article.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary" target="_blank">View Article</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Auto-resize textarea for content
        const contentTextarea = document.getElementById('content');
        contentTextarea.style.height = contentTextarea.scrollHeight + 'px';
        
        contentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Preview image before upload
        const imageInput = document.querySelector('input[name="featured_image"]');
        const removeImageCheckbox = document.querySelector('input[name="remove_image"]');
        
        imageInput.addEventListener('change', function(e) {
            if(removeImageCheckbox) {
                removeImageCheckbox.checked = false;
            }
            
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.image-preview') || createPreview();
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Image preview">
                        <div style="margin-top: 10px;">
                            <label>
                                <input type="checkbox" name="remove_image" value="1">
                                Remove this image
                            </label>
                        </div>
                    `;
                }
                reader.readAsDataURL(file);
            }
        });
        
        function createPreview() {
            const preview = document.createElement('div');
            preview.className = 'image-preview';
            imageInput.parentNode.insertBefore(preview, imageInput.nextSibling);
            return preview;
        }
    </script>
</body>
</html>