<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^\w\s]/', '', $title)));
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL; // Changed to handle NULL
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $status = $_POST['status'];

    // Handle image upload
    $featured_image = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'uploads/' . $file_name;
        }
    }

    // Validate if category exists if provided
    if ($category_id) {
        $check_cat = mysqli_query($conn, "SELECT id FROM categories WHERE id = $category_id");
        if (mysqli_num_rows($check_cat) == 0) {
            $error = "Selected category does not exist!";
        }
    }

    if (!isset($error)) {
        // Build query based on whether category_id is provided or not
        if ($category_id) {
            $query = "INSERT INTO articles (title, slug, excerpt, content, category_id, author, featured_image, is_featured, is_breaking, status) 
                      VALUES ('$title', '$slug', '$excerpt', '$content', $category_id, '$author', '$featured_image', $is_featured, $is_breaking, '$status')";
        } else {
            $query = "INSERT INTO articles (title, slug, excerpt, content, author, featured_image, is_featured, is_breaking, status) 
                      VALUES ('$title', '$slug', '$excerpt', '$content', '$author', '$featured_image', $is_featured, $is_breaking, '$status')";
        }

        if (mysqli_query($conn, $query)) {
            header('Location: dashboard.php?msg=added');
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}


// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^\w\s]/', '', $title)));
    $excerpt = mysqli_real_escape_string($conn, $_POST['excerpt']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL;
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    $status = $_POST['status'];

    // Handle image upload - FIXED VERSION
    $featured_image = '';

    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';

        // Create uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validate file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($_FILES['featured_image']['tmp_name']);

        if (in_array($file_type, $allowed_types)) {
            // Generate unique filename
            $file_ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $file_name = 'article_' . time() . '_' . uniqid() . '.' . strtolower($file_ext);
            $target_file = $upload_dir . $file_name;

            // Move uploaded file
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
                $featured_image = 'uploads/' . $file_name;
            } else {
                $error = "Failed to upload image. Please try again.";
            }
        } else {
            $error = "Only JPG, PNG, GIF, and WebP images are allowed.";
        }
    } elseif ($_FILES['featured_image']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File is too large (server limit).',
            UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit).',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory.',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.'
        ];
        $error = $upload_errors[$_FILES['featured_image']['error']] ?? 'Unknown upload error.';
    }

    // Validate category if provided
    if (!isset($error) && $category_id) {
        $check_cat = mysqli_query($conn, "SELECT id FROM categories WHERE id = $category_id");
        if (mysqli_num_rows($check_cat) == 0) {
            $error = "Selected category does not exist!";
        }
    }

    if (!isset($error)) {
        // Build query based on whether category_id is provided or not
        if ($category_id) {
            $query = "INSERT INTO articles (title, slug, excerpt, content, category_id, author, featured_image, is_featured, is_breaking, status) 
                      VALUES ('$title', '$slug', '$excerpt', '$content', $category_id, '$author', '$featured_image', $is_featured, $is_breaking, '$status')";
        } else {
            $query = "INSERT INTO articles (title, slug, excerpt, content, author, featured_image, is_featured, is_breaking, status) 
                      VALUES ('$title', '$slug', '$excerpt', '$content', '$author', '$featured_image', $is_featured, $is_breaking, '$status')";
        }

        if (mysqli_query($conn, $query)) {
            header('Location: dashboard.php?msg=added');
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Add New Article - Admin Dashboard</title>
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
    </style>
</head>

<body>
    <div class="header">
        <h1>Add New Article</h1>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Article Title <span class="required">*</span></label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Excerpt (Short Summary)</label>
                    <textarea name="excerpt" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Content <span class="required">*</span></label>
                    <textarea name="content" id="content" required></textarea>
                </div>

                <div class="form-group">
                    <label>Category (Optional)</label>
                    <select name="category_id">
                        <option value="">-- No Category --</option>
                        <?php
                        // Reset pointer to beginning
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <small style="color: #666;">Note: You can add categories from the dashboard later</small>
                </div>

                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" name="author" value="<?php echo $_SESSION['admin_name']; ?>">
                </div>

                <div class="form-group">
                    <label>Featured Image<span class="required">*</span></label>
                    <input type="file" multiple name="featured_image" accept="image/*" required>
                    
                </div>

                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="is_featured" value="1">
                        Mark as Featured
                    </label>
                    <label>
                        <input type="checkbox" name="is_breaking" value="1">
                        Mark as Breaking News
                    </label>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn">Publish Article</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>