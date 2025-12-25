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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Article - Admin Dashboard</title>
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f7fa;
            line-height: 1.6;
            color: #333;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }

        @media (min-width: 768px) {
            .header-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        /* Container */
        .container {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Form Container */
        .form-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 1rem;
        }

        @media (min-width: 768px) {
            .form-container {
                padding: 2rem;
            }
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.95rem;
        }

        .required {
            color: #e74c3c;
        }

        /* Form Controls */
        input[type="text"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        #content {
            min-height: 300px;
        }

        @media (min-width: 768px) {
            #content {
                min-height: 400px;
            }
        }

        /* Checkbox Group */
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: normal;
            margin: 0;
            padding: 0.5rem;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

        .checkbox-group label:hover {
            background: #e9ecef;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #3498db;
        }

        /* File Upload */
        .file-upload {
            position: relative;
            overflow: hidden;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: block;
            padding: 1rem;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #e9ecef;
            border-color: #3498db;
        }

        .file-preview {
            margin-top: 1rem;
            display: none;
        }

        .file-preview img {
            max-width: 200px;
            height: auto;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Buttons */
        .btn {
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #1c5f8a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            flex: 1;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (min-width: 768px) {
            .button-group {
                flex-direction: row;
            }

            .btn {
                flex: none;
                min-width: 150px;
            }
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #ffebee;
            border-left-color: #f44336;
            color: #c62828;
        }

        .alert-success {
            background: #e8f5e9;
            border-left-color: #4caf50;
            color: #2e7d32;
        }

        /* Helper Text */
        small {
            display: block;
            margin-top: 0.5rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-published {
            background: #d4edda;
            color: #155724;
        }

        .status-draft {
            background: #fff3cd;
            color: #856404;
        }

        /* Loading State */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 767px) {
            .mobile-menu-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <h1>➕ Add New Article</h1>
            <a href="dashboard.php" class="btn btn-secondary">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data" id="articleForm">
                <div class="form-group">
                    <label>Article Title <span class="required">*</span></label>
                    <input type="text" name="title" required placeholder="Enter article title...">
                </div>

                <div class="form-group">
                    <label>Excerpt (Short Summary)</label>
                    <textarea name="excerpt" rows="3" placeholder="Brief summary of the article..."></textarea>
                </div>

                <div class="form-group">
                    <label>Content <span class="required">*</span></label>
                    <textarea name="content" id="content" required placeholder="Write your article content here..."></textarea>
                </div>

                <div class="form-group">
                    <label>Category (Optional)</label>
                    <select name="category_id">
                        <option value="">-- No Category --</option>
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small>Note: You can add categories from the dashboard later</small>
                </div>

                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>" placeholder="Author name...">
                </div>

                <div class="form-group">
                    <label>Featured Image</label>
                    <div class="file-upload">
                        <input type="file" name="featured_image" id="featuredImage" accept="image/*" onchange="previewImage(this)">
                        <label for="featuredImage" class="file-upload-label">
                            📁 Click to upload or drag and drop
                            <br>
                            <small>Recommended size: 1200x630px, Max size: 5MB</small>
                        </label>
                    </div>
                    <div class="file-preview" id="imagePreview"></div>
                </div>

                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="is_featured" value="1">
                        ⭐ Mark as Featured
                    </label>
                    <label>
                        <input type="checkbox" name="is_breaking" value="1">
                        🔥 Mark as Breaking News
                    </label>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="published">📢 Published</option>
                        <option value="draft">📝 Draft</option>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        📤 Publish Article
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        ✖ Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.style.display = 'none';
            preview.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const file = input.files[0];

                // Check file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    input.value = '';
                    return;
                }

                reader.onload = function(e) {
                    preview.style.display = 'block';
                    preview.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <img src="${e.target.result}" alt="Preview">
                            <div>
                                <strong>${file.name}</strong><br>
                                <small>${(file.size / 1024).toFixed(2)} KB</small>
                            </div>
                        </div>
                    `;
                }
                reader.readAsDataURL(file);
            }
        }

        // Form submission handling
        document.getElementById('articleForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '⏳ Publishing...';
        });

        // Auto-save draft functionality
        let autoSaveTimeout;
        const form = document.getElementById('articleForm');
        
        form.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                // Implement auto-save logic here
                console.log('Auto-saving...');
            }, 3000);
        });

        // Mobile-friendly textarea resizing
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            textarea.addEventListener('focus', function() {
                if (window.innerWidth < 768) {
                    this.style.minHeight = '200px';
                }
            });
            
            textarea.addEventListener('blur', function() {
                if (window.innerWidth < 768 && this.value === '') {
                    this.style.minHeight = '120px';
                }
            });
        });
    </script>
</body>
</html>
