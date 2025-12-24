<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = intval($_GET['id']);

// Get category info
$cat_query = "SELECT * FROM categories WHERE id = $id";
$cat_result = mysqli_query($conn, $cat_query);

if (mysqli_num_rows($cat_result) == 0) {
    header('Location: index.php');
    exit();
}

$category = mysqli_fetch_assoc($cat_result);

// Get articles in this category
$articles_query = "SELECT * FROM articles 
                  WHERE category_id = $id 
                  AND status = 'published' 
                  ORDER BY published_at DESC";
$articles_result = mysqli_query($conn, $articles_query);
$article_count = mysqli_num_rows($articles_result);

// Get popular articles in this category
$popular_query = "SELECT * FROM articles 
                  WHERE category_id = $id 
                  AND status = 'published' 
                  ORDER BY views DESC 
                  LIMIT 5";
$popular_result = mysqli_query($conn, $popular_query);

// Get other categories
$other_cats = mysqli_query($conn, "SELECT c.*, 
    (SELECT COUNT(*) FROM articles WHERE category_id = c.id AND status = 'published') as article_count
    FROM categories c 
    WHERE c.id != $id 
    ORDER BY c.name 
    LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> News - <?php echo SITE_TITLE; ?></title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            border-bottom: 3px double #000;
            padding: 20px;
            text-align: center;
            background: #fff;
        }

        .newspaper-title {
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .back-home {
            display: inline-block;
            margin-bottom: 20px;
            color: #c00;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        .category-header {
            background: linear-gradient(to right, #c00, #a00);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .category-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .category-description {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .article-count {
            background: white;
            color: #c00;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
            border: 2px solid #c00;
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            padding: 20px;
        }

        .articles-list {
            border-right: 1px solid #ddd;
            padding-right: 30px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #000;
        }

        .article-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
            display: flex;
            gap: 20px;
            transition: background 0.3s;
        }

        .article-item:hover {
            background: #f9f9f9;
        }

        .article-image {
            flex: 0 0 200px;
            height: 150px;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .article-item:hover .article-image img {
            transform: scale(1.05);
        }

        .article-content {
            flex: 1;
        }

        .article-date {
            color: #c00;
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .article-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .article-title a {
            color: #000;
            text-decoration: none;
        }

        .article-title a:hover {
            color: #c00;
            text-decoration: underline;
        }

        .article-excerpt {
            color: #666;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .article-meta {
            font-size: 0.9rem;
            color: #888;
            display: flex;
            gap: 15px;
        }

        .read-more {
            color: #c00;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.95rem;
        }

        .read-more:hover {
            text-decoration: underline;
        }

        .no-articles {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-style: italic;
        }

        .no-articles h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #999;
        }

        .sidebar {
            padding-left: 20px;
        }

        .sidebar-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .sidebar-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #000;
            border-left: 4px solid #c00;
            padding-left: 10px;
        }

        .popular-list {
            list-style: none;
        }

        .popular-item {
            padding: 12px 0;
            border-bottom: 1px dashed #ddd;
        }

        .popular-item:last-child {
            border-bottom: none;
        }

        .popular-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .popular-title a {
            color: #000;
            text-decoration: none;
        }

        .popular-title a:hover {
            color: #c00;
        }

        .popular-meta {
            font-size: 0.85rem;
            color: #666;
            display: flex;
            justify-content: space-between;
        }

        .categories-list {
            list-style: none;
        }

        .categories-list li {
            margin-bottom: 8px;
        }

        .categories-list a {
            color: #000;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f5f5f5;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .categories-list a:hover {
            background: #e0e0e0;
            color: #c00;
            transform: translateX(5px);
        }

        .cat-count {
            background: #c00;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .pagination a {
            padding: 8px 15px;
            border: 1px solid #ddd;
            color: #333;
            text-decoration: none;
            border-radius: 3px;
        }

        .pagination a:hover {
            background: #c00;
            color: white;
            border-color: #c00;
        }

        .pagination .current {
            background: #c00;
            color: white;
            border-color: #c00;
        }

        .breaking-tag {
            background: #c00;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 5px;
        }

        .featured-tag {
            background: gold;
            color: #000;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 5px;
        }

        .footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 50px;
            border-top: 3px double #fff;
        }

        .footer-links {
            margin: 20px 0;
        }

        .footer-links a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: #c00;
            text-decoration: underline;
        }

        .breadcrumb {
            padding: 15px 20px;
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: #c00;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
                padding: 15px;
            }

            .articles-list {
                border-right: none;
                padding-right: 0;
            }

            .category-title {
                font-size: 2.5rem;
            }

            .article-item {
                flex-direction: column;
                gap: 15px;
            }

            .article-image {
                flex: 0 0 auto;
                height: 200px;
            }

            .sidebar {
                padding-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="newspaper-container">
        

        <!-- Header -->
        <header class="header">
            <div class="newspaper-title"><?php echo SITE_TITLE; ?></div>
            <a href="index.php" class="back-home">← Back to Homepage</a>
        </header>

        <!-- Category Header -->
        <div class="category-header">
            <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
            <div class="category-description">
                Latest news, updates, and stories from <?php echo htmlspecialchars($category['name']); ?>.
                Stay informed with our comprehensive coverage.
            </div>
            <div class="article-count">
                <?php echo $article_count; ?> Article<?php echo $article_count != 1 ? 's' : ''; ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Articles List -->
            <div class="articles-list">
                <h2 class="section-title">Latest <?php echo htmlspecialchars($category['name']); ?> News</h2>

                <?php if ($article_count > 0): ?>
                    <?php while ($article = mysqli_fetch_assoc($articles_result)): ?>
                        <div class="article-item">
                            <div class="article-image">
                                <img src="<?php echo get_image_url($article['featured_image']); ?>"
                                    alt="<?php echo htmlspecialchars($article['title']); ?>">
                            </div>
                            <div class="article-content">
                                <div class="article-date">
                                    <?php echo date('F j, Y', strtotime($article['published_at'])); ?>
                                    <?php if ($article['is_breaking']): ?>
                                        <span class="breaking-tag">BREAKING</span>
                                    <?php endif; ?>
                                    <?php if ($article['is_featured']): ?>
                                        <span class="featured-tag">FEATURED</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="article-title">
                                    <a href="article.php?id=<?php echo $article['id']; ?>">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h3>
                                <div class="article-excerpt">
                                    <?php
                                    $excerpt = !empty($article['excerpt']) ? $article['excerpt'] : substr(strip_tags($article['content']), 0, 200);
                                    echo htmlspecialchars($excerpt) . '...';
                                    ?>
                                </div>
                                <div class="article-meta">
                                    <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                                    <span>📊 <?php echo number_format($article['views']); ?> views</span>
                                    <span>⏱️ <?php echo date('h:i A', strtotime($article['published_at'])); ?></span>
                                </div>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="read-more">
                                    Read Full Story →
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <!-- Pagination (for future use) -->
                    <div class="pagination">
                        <a href="#" class="current">1</a>
                        <a href="#">2</a>
                        <a href="#">3</a>
                        <a href="#">4</a>
                        <a href="#">5</a>
                        <a href="#">Next →</a>
                    </div>

                <?php else: ?>
                    <div class="no-articles">
                        <h2>No Articles Found</h2>
                        <p>There are currently no articles published in <?php echo htmlspecialchars($category['name']); ?>.</p>
                        <p style="margin-top: 20px;">
                            <a href="index.php" style="color: #c00; text-decoration: none; font-weight: bold;">
                                ← Browse other categories
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Popular in this category -->
                <?php if (mysqli_num_rows($popular_result) > 0): ?>
                    <div class="sidebar-section">
                        <h3 class="sidebar-title">Popular in <?php echo htmlspecialchars($category['name']); ?></h3>
                        <ul class="popular-list">
                            <?php
                            mysqli_data_seek($popular_result, 0);
                            while ($popular = mysqli_fetch_assoc($popular_result)):
                            ?>
                                <li class="popular-item">
                                    <div class="popular-title">
                                        <a href="article.php?id=<?php echo $popular['id']; ?>">
                                            <?php echo htmlspecialchars($popular['title']); ?>
                                        </a>
                                    </div>
                                    <div class="popular-meta">
                                        <span><?php echo date('M j', strtotime($popular['published_at'])); ?></span>
                                        <span>🔥 <?php echo number_format($popular['views']); ?> views</span>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Other Categories -->
                <?php if (mysqli_num_rows($other_cats) > 0): ?>
                    <div class="sidebar-section">
                        <h3 class="sidebar-title">Other Categories</h3>
                        <ul class="categories-list">
                            <?php while ($cat = mysqli_fetch_assoc($other_cats)): ?>
                                <li>
                                    <a href="category.php?id=<?php echo $cat['id']; ?>">
                                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                        <span class="cat-count"><?php echo $cat['article_count']; ?></span>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="newspaper-title" style="font-size: 2rem; margin-bottom: 10px;"><?php echo SITE_TITLE; ?></div>
            <p style="margin-bottom: 20px; opacity: 0.8;">
                "Truth, Integrity, and Quality Journalism"
            </p>

            

            <p style="margin-top: 20px; opacity: 0.7;">
                &copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?>. All rights reserved.<br>
                | Phone: +91 8489481039 | Email: recyclezone2004@gmail.com
            </p>
        </footer>
    </div>

    <script>
        // Add interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate article items on scroll
            const articleItems = document.querySelectorAll('.article-item');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });

            articleItems.forEach(item => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(item);
            });

            // Add click tracking for analytics (optional)
            const articleLinks = document.querySelectorAll('.article-title a, .read-more');
            articleLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // You can add analytics tracking here
                    console.log('Article clicked:', this.href);
                });
            });

            // Newsletter form submission
            const newsletterForm = document.querySelector('form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = this.querySelector('input[type="email"]').value;
                    if (email) {
                        alert('Thank you for subscribing to ' + '<?php echo htmlspecialchars($category['name']); ?>' + ' updates!');
                        this.reset();
                    }
                });
            }
        });
    </script>
</body>

</html>