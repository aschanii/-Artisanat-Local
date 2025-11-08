<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Get blog posts
$stmt = $pdo->prepare("
    SELECT bp.*, u.first_name, u.last_name 
    FROM blog_posts bp 
    LEFT JOIN users u ON bp.author_id = u.id 
    WHERE bp.status = 'published' 
    ORDER BY bp.published_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Get total count for pagination
$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Handmade Haven</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Handmade Haven Blog</h1>
            <p>Stories, tips, and inspiration from the world of handmade crafts</p>
        </div>

        <div class="blog-container">
            <div class="blog-posts">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-post">
                        <div class="post-image">
                            <img src="<?php echo getBlogImage($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                        </div>
                        <div class="post-content">
                            <div class="post-meta">
                                <span class="post-date"><?php echo formatDate($post['published_at']); ?></span>
                                <span class="post-author">By <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></span>
                            </div>
                            <h2 class="post-title">
                                <a href="blog-post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
                            </h2>
                            <p class="post-excerpt"><?php echo truncateText($post['excerpt'], 150); ?></p>
                            <a href="blog-post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <aside class="blog-sidebar">
                <div class="sidebar-widget">
                    <h3>Categories</h3>
                    <ul class="categories-list">
                        <li><a href="?category=crafts">Crafts & DIY</a></li>
                        <li><a href="?category=artisan">Artisan Stories</a></li>
                        <li><a href="?category=business">Small Business</a></li>
                        <li><a href="?category=tutorials">Tutorials</a></li>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3>Popular Posts</h3>
                    <div class="popular-posts">
                        <?php
                        $stmt = $pdo->query("SELECT id, title, featured_image FROM blog_posts WHERE status = 'published' ORDER BY views DESC LIMIT 3");
                        $popular_posts = $stmt->fetchAll();
                        ?>
                        <?php foreach ($popular_posts as $popular): ?>
                            <div class="popular-post">
                                <img src="<?php echo getBlogImage($popular['featured_image']); ?>" alt="<?php echo htmlspecialchars($popular['title']); ?>">
                                <div>
                                    <h4><a href="blog-post.php?id=<?php echo $popular['id']; ?>"><?php echo truncateText($popular['title'], 50); ?></a></h4>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sidebar-widget">
                    <h3>Subscribe</h3>
                    <p>Get the latest posts delivered to your inbox</p>
                    <form class="subscribe-form">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </aside>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="prev">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="next">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>