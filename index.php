<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?> - Latest News</title>
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
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }

        .header {
            border-bottom: 3px double #000;
            padding: 20px;
            text-align: center;
            background: #fff;
        }

        .newspaper-title {
            font-size: 4rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 10px;
            font-family: 'Times New Roman', serif;
        }

        .newspaper-motto {
            font-size: 1.2rem;
            font-style: italic;
            margin-bottom: 10px;
            color: #666;
        }

        .date-line {
            font-size: 1.1rem;
            font-weight: bold;
            color: #000;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 10px 0;
        }

        .breaking-news {
            background: #c00;
            color: white;
            padding: 10px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0.8;
            }
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            padding: 20px;
        }

        .featured-article {
            grid-column: 1 / -1;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .headline {
            font-size: 2.5rem;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 10px;
        }

        .byline {
            font-style: italic;
            color: #666;
            margin-bottom: 15px;
        }

        .article-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .article-card {
            border: 1px solid #ddd;
            padding: 15px;
            background: #fff;
            transition: transform 0.3s;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .article-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }

        .article-excerpt {
            color: #666;
            margin-bottom: 10px;
        }

        .read-more {
            color: #c00;
            text-decoration: none;
            font-weight: bold;
        }

        .read-more:hover {
            text-decoration: underline;
        }

        .sidebar {
            border-left: 1px solid #ddd;
            padding-left: 20px;
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .category-list {
            list-style: none;
            margin-bottom: 20px;
        }

        .category-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
        }

        .footer {
            background: #222;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            border-top: 3px double #fff;
        }



        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .newspaper-title {
                font-size: 2.5rem;
            }

            .headline {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>


    <div class="newspaper-container">
        <!-- Header -->

        <header class="header">
            <div class="admin-panel" style="margin-left: 1200px;">
                    <a href="admin/login.php" style="color: blue; font-weight: bold; text-decoration: none;">LOGIN</a>
            </div>
            <div class="newspaper-title"><?php echo SITE_TITLE; ?></div>
            <div class="newspaper-motto">"Truth, Integrity, and Quality Journalism"</div>
            <div class="date-line">
                <?php
                echo date('l, F j, Y') . ' | ' .
                    'Vol. ' . date('Y') . ' No. ' . date('z') . ' | ' .
                    'Price: $1.00';
                ?>
            </div>
        </header>

        <!-- Breaking News Banner -->
        <?php
        $breaking_query = "SELECT * FROM articles WHERE is_breaking = 1 AND status = 'published' ORDER BY published_at DESC LIMIT 1";
        $breaking_result = mysqli_query($conn, $breaking_query);
        if ($breaking_row = mysqli_fetch_assoc($breaking_result)): ?>
            <div class="breaking-news">
                🚨 BREAKING: <?php echo htmlspecialchars($breaking_row['title']); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Featured Article -->

            <?php
            $featured_query = "SELECT a.*, c.name as category_name 
                             FROM articles a 
                             LEFT JOIN categories c ON a.category_id = c.id 
                             WHERE a.is_featured = 1 AND a.status = 'published' 
                             ORDER BY a.published_at DESC LIMIT 1";
            $featured_result = mysqli_query($conn, $featured_query);
            if ($featured_row = mysqli_fetch_assoc($featured_result)):
            ?>
                <div class="featured-article">
                    <?php if ($featured_row['featured_image']): ?>
                        <img src="<?php echo $featured_row['featured_image']; ?>" alt="<?php echo htmlspecialchars($featured_row['title']); ?>" class="featured-image">
                    <?php endif; ?>
                    <div class="article-category"><?php echo $featured_row['category_name']; ?></div>
                    <h1 class="headline"><?php echo htmlspecialchars($featured_row['title']); ?></h1>
                    <div class="byline">By <?php echo htmlspecialchars($featured_row['author']); ?> | <?php echo date('F j, Y', strtotime($featured_row['published_at'])); ?></div>
                    <div class="article-excerpt"><?php echo htmlspecialchars($featured_row['excerpt']); ?></div>
                    <a href="article.php?id=<?php echo $featured_row['id']; ?>" class="read-more">Read Full Story →</a>
                </div>
            <?php endif; ?>

            <!-- Left Column: Recent News -->
            <div class="left-column">
                <h2 class="sidebar-title">Latest News</h2>
                <div class="article-grid">
                    <?php
                    $news_query = "SELECT a.*, c.name as category_name 
                                  FROM articles a 
                                  LEFT JOIN categories c ON a.category_id = c.id 
                                  WHERE a.status = 'published' AND a.is_featured = 0 
                                  ORDER BY a.published_at DESC LIMIT 6";
                    $news_result = mysqli_query($conn, $news_query);
                    while ($news_row = mysqli_fetch_assoc($news_result)):
                    ?>
                        <div class="article-card">
                            <div class="article-category" style="color: #c00; font-weight: bold; margin-bottom: 5px;">
                                <?php echo $news_row['category_name']; ?>
                            </div>
                            <h3 class="article-title"><?php echo htmlspecialchars($news_row['title']); ?></h3>
                            <div class="article-excerpt"><?php echo htmlspecialchars(substr($news_row['excerpt'], 0, 150)); ?>...</div>
                            <div class="article-meta" style="font-size: 0.9rem; color: #666; margin: 10px 0;">
                                <?php echo date('h:i A', strtotime($news_row['published_at'])); ?> |
                                <?php echo $news_row['views']; ?> views
                            </div>
                            <a href="../newspaper/admin/article.php?id=<?php echo $news_row['id']; ?>" class="read-more">Continue Reading →</a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="sidebar">
               <!-- In index.php sidebar section -->
<h2 class="sidebar-title">Categories</h2>
<ul class="category-list">
    <?php
    $cat_query = "SELECT c.*, COUNT(a.id) as article_count 
                 FROM categories c 
                 LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' 
                 GROUP BY c.id 
                 ORDER BY c.name";
    $cat_result = mysqli_query($conn, $cat_query);
    while($cat_row = mysqli_fetch_assoc($cat_result)):
    ?>
    <li>
        <a href="category.php?id=<?php echo $cat_row['id']; ?>" style="color: #000; text-decoration: none;">
            <?php echo htmlspecialchars($cat_row['name']); ?>
            <span style="color: #c00; float: right;">(<?php echo $cat_row['article_count']; ?>)</span>
        </a>
    </li>
    <?php endwhile; ?>
</ul>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?>. All rights reserved.</p>
            <p>Phone:+91 8489481039 | Email: recyclezone2004@gmail.com</p>
        </footer>
    </div>
</body>

</html>