<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = intval($_GET['id']);

// Increment view count
mysqli_query($conn, "UPDATE articles SET views = views + 1 WHERE id = $id");

// Get article details
$query = "SELECT a.*, c.name as category_name 
          FROM articles a 
          LEFT JOIN categories c ON a.category_id = c.id 
          WHERE a.id = $id AND a.status = 'published'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

$article = mysqli_fetch_assoc($result);

// Get related articles
$related_query = "SELECT * FROM articles 
                  WHERE category_id = " . ($article['category_id'] ?: 'NULL') . "
                  AND id != $id 
                  AND status = 'published' 
                  ORDER BY published_at DESC 
                  LIMIT 3";
$related_result = mysqli_query($conn, $related_query);
?>


<!-- Featured Image -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - <?php echo SITE_TITLE; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .newspaper-container {
            max-width: auto;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .header {
            border-bottom: 3px double #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .newspaper-title {
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .article-meta {
            color: #666;
            font-size: 1rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .category-badge {
            background: #c00;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 15px;
        }

        .article-title {
            font-size: 2.8rem;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 20px;
            color: #000;
        }

        .byline {
            font-style: italic;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            margin: 30px 0;
            border: 1px solid #ddd;
        }

        .article-content {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .article-content p {
            margin-bottom: 20px;
        }

        .article-content h2 {
            font-size: 1.8rem;
            margin: 30px 0 15px 0;
            color: #000;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .article-content h3 {
            font-size: 1.5rem;
            margin: 25px 0 15px 0;
            color: #333;
        }

        .article-content blockquote {
            border-left: 4px solid #c00;
            padding-left: 20px;
            margin: 30px 0;
            font-style: italic;
            color: #666;
            font-size: 1.3rem;
        }

        .article-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-count {
            color: #666;
            font-size: 0.9rem;
        }

        .share-buttons {
            display: flex;
            gap: 10px;
        }

        .share-btn {
            background: #f0f0f0;
            color: #333;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .share-btn:hover {
            background: #e0e0e0;
        }

        .related-articles {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #000;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #000;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .related-card {
            border: 1px solid #ddd;
            padding: 15px;
            transition: transform 0.3s;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .related-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }

        .back-home {
            display: inline-block;
            margin-bottom: 30px;
            color: #c00;
            text-decoration: none;
            font-weight: bold;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        .breaking-tag {
            background: #c00;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .featured-tag {
            background: gold;
            color: #000;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .newspaper-container {
                padding: 20px;
            }

            .article-title {
                font-size: 2rem;
            }

            .article-content {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>


    <div class="newspaper-container">
        <!-- Article Header -->
        <a href="index.php" class="back-home">← Back to Homepage</a>
        <div class="header">
            <div class="newspaper-title"><?php echo SITE_TITLE; ?></div>
            <div class="article-meta">
                Published on <?php echo date('l, F j, Y', strtotime($article['published_at'])); ?>
                | <?php echo date('h:i A', strtotime($article['published_at'])); ?>
            </div>

            <?php if ($article['category_name']): ?>
                <div class="category-badge"><?php echo $article['category_name']; ?></div>
            <?php endif; ?>

            <h1 class="article-title">
                <?php echo htmlspecialchars($article['title']); ?>
                <?php if ($article['is_breaking']): ?>
                    <span class="breaking-tag">BREAKING</span>
                <?php endif; ?>
                <?php if ($article['is_featured']): ?>
                    <span class="featured-tag">FEATURED</span>
                <?php endif; ?>
            </h1>

            <div class="byline">
                By <?php echo htmlspecialchars($article['author']); ?> |
                Staff Writer
            </div>
        </div>

        <!-- Featured Image -->
        <?php if (!empty($article['featured_image'])): ?>
            <?php
            // Check if image exists
            $image_path = $article['featured_image'];

            // If image path doesn't start with http, assume it's local
            if (!filter_var($image_path, FILTER_VALIDATE_URL)) {
                // Check if file exists
                if (file_exists($image_path)) {
                    $image_src = $image_path;
                } elseif (file_exists('../' . $image_path)) {
                    $image_src = '../' . $image_path;
                } else {
                    $image_src = 'https://via.placeholder.com/800x400?text=Image+Not+Found';
                }
            } else {
                $image_src = $image_path;
            }
            ?>
            <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="featured-image">
        <?php else: ?>

        <?php endif; ?>

        <!-- Article Content -->
        <div class="article-content">
            <?php
            // Format the content with paragraphs
            $content = nl2br(htmlspecialchars($article['content']));
            echo $content;
            ?>
        </div>

        <!-- Article Footer -->
        <div class="article-footer">
            <div class="view-count">
                <?php echo number_format($article['views'] + 1); ?> views
            </div>
            <div class="share-buttons">
                <a href="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="share-btn">Share</a>
                <a onclick="javascript:window.print()" class="share-btn">Print</a>
            </div>
        </div>

        <!-- Related Articles -->
        <?php if (mysqli_num_rows($related_result) > 0): ?>
            <div class="related-articles">
                <h2 class="section-title">Related Articles</h2>
                <div class="related-grid">
                    <?php while ($related = mysqli_fetch_assoc($related_result)): ?>
                        <div class="related-card">
                            <h3 class="related-title">
                                <a href="article.php?id=<?php echo $related['id']; ?>" style="color: #000; text-decoration: none;">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h3>
                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">
                                <?php echo date('M j, Y', strtotime($related['published_at'])); ?>
                            </div>
                            <div style="font-size: 0.95rem; color: #555;">
                                <?php echo substr(htmlspecialchars($related['excerpt']), 0, 100); ?>...
                            </div>
                            <a href="article.php?id=<?php echo $related['id']; ?>" style="color: #c00; text-decoration: none; font-weight: bold; margin-top: 10px; display: inline-block;">
                                Read more →
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?>. All rights reserved.</p>
        <p>
            <a href="index.php" style="color: white; margin: 0 10px;">Home</a> |
            <a href="#" style="color: white; margin: 0 10px;">About Us</a> |
            <a href="#" style="color: white; margin: 0 10px;">Contact</a> |
            <a href="#" style="color: white; margin: 0 10px;">Privacy Policy</a>
        </p>
    </div>
</body>

</html>