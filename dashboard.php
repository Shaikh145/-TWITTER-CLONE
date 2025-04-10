<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new mysqli('localhost', 'uyapoezipn1un', 'ytllgftvo3kn', 'dbwugogkgkyu88');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM tweets WHERE user_id = ?) AS tweet_count,
    (SELECT COUNT(*) FROM likes WHERE tweet_id IN (SELECT id FROM tweets WHERE user_id = ?)) AS likes_received,
    (SELECT COUNT(*) FROM likes WHERE user_id = ?) AS likes_given";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Fetch recent activity (last 5 tweets and likes)
$activity_query = "
    (SELECT 'tweet' AS type, t.id, t.content, t.created_at, u.username
     FROM tweets t
     JOIN users u ON t.user_id = u.id
     WHERE t.user_id = ?
     ORDER BY t.created_at DESC
     LIMIT 5)
    UNION ALL
    (SELECT 'like' AS type, t.id, t.content, l.created_at, u.username
     FROM likes l
     JOIN tweets t ON l.tweet_id = t.id
     JOIN users u ON t.user_id = u.id
     WHERE l.user_id = ?
     ORDER BY l.created_at DESC
     LIMIT 5)
    ORDER BY created_at DESC
    LIMIT 10";
$activity_stmt = $db->prepare($activity_query);
$activity_stmt->bind_param("ii", $user_id, $user_id);
$activity_stmt->execute();
$activity_result = $activity_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Twitter Clone</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #15202B;
            color: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #38444D;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            background-color: #192734;
            padding: 20px;
            border-radius: 15px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1DA1F2;
        }
        .stat-label {
            font-size: 14px;
            color: #8899A6;
        }
        .recent-activity {
            background-color: #192734;
            padding: 20px;
            border-radius: 15px;
        }
        .activity-item {
            border-bottom: 1px solid #38444D;
            padding: 10px 0;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-type {
            font-weight: bold;
            color: #1DA1F2;
        }
        .activity-content {
            margin-top: 5px;
        }
        .activity-meta {
            font-size: 12px;
            color: #8899A6;
        }
        .nav-links {
            margin-top: 20px;
            text-align: center;
        }
        .nav-links a {
            color: #1DA1F2;
            text-decoration: none;
            margin: 0 10px;
        }
        .delete-tweet {
            background-color: #E0245E;
            color: #FFFFFF;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($username) ?>!</p>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-value"><?= $stats['tweet_count'] ?></div>
                <div class="stat-label">Tweets</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $stats['likes_received'] ?></div>
                <div class="stat-label">Likes Received</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= $stats['likes_given'] ?></div>
                <div class="stat-label">Likes Given</div>
            </div>
        </div>
        
        <div class="recent-activity">
            <h2>Recent Activity</h2>
            <?php while ($activity = $activity_result->fetch_assoc()): ?>
                <div class="activity-item">
                    <span class="activity-type">
                        <?= $activity['type'] === 'tweet' ? 'Tweeted' : 'Liked' ?>
                    </span>
                    <div class="activity-content">
                        <?= htmlspecialchars($activity['content']) ?>
                    </div>
                    <div class="activity-meta">
                        <?= $activity['type'] === 'tweet' ? 'by you' : 'by ' . htmlspecialchars($activity['username']) ?> 
                        on <?= date('M d, Y H:i', strtotime($activity['created_at'])) ?>
                        <?php if ($activity['type'] === 'tweet'): ?>
                            <button class="delete-tweet" data-tweet-id="<?= $activity['id'] ?>">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-tweet').forEach(button => {
            button.addEventListener('click', function() {
                const tweetId = this.dataset.tweetId;
                if (confirm('Are you sure you want to delete this tweet?')) {
                    fetch('delete_tweet.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `tweet_id=${tweetId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.activity-item').remove();
                        } else {
                            alert('Failed to delete tweet');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
