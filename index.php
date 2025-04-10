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

$tweets_query = "SELECT t.id, t.content, t.created_at, u.username, 
                 (SELECT COUNT(*) FROM likes WHERE tweet_id = t.id) as like_count,
                 (SELECT COUNT(*) FROM likes WHERE tweet_id = t.id AND user_id = ?) as user_liked
                 FROM tweets t
                 JOIN users u ON t.user_id = u.id
                 ORDER BY t.created_at DESC";
$stmt = $db->prepare($tweets_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #15202B;
            color: #FFFFFF;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
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
        .tweet-form {
            margin: 20px 0;
        }
        .tweet-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #38444D;
            border-radius: 5px;
            background-color: #192734;
            color: #FFFFFF;
            resize: none;
        }
        .tweet-form button {
            background-color: #1DA1F2;
            color: #FFFFFF;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 10px;
        }
        .tweet {
            border-bottom: 1px solid #38444D;
            padding: 15px 0;
        }
        .tweet-header {
            display: flex;
            justify-content: space-between;
        }
        .tweet-content {
            margin: 10px 0;
        }
        .tweet-actions {
            display: flex;
            gap: 15px;
        }
        .like-button {
            background: none;
            border: none;
            color: #8899A6;
            cursor: pointer;
        }
        .like-button.liked {
            color: #E0245E;
        }
        .logout {
            background-color: #192734;
            color: #FFFFFF;
            border: 1px solid #38444D;
            padding: 5px 10px;
            border-radius: 20px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Twitter Clone</h1>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <div class="tweet-form">
            <form action="post_tweet.php" method="post">
                <textarea name="content" rows="3" maxlength="280" placeholder="What's happening?"></textarea>
                <button type="submit">Tweet</button>
            </form>
        </div>
        <div class="tweets">
            <?php while ($tweet = $result->fetch_assoc()): ?>
                <div class="tweet">
                    <div class="tweet-header">
                        <strong><?= htmlspecialchars($tweet['username']) ?></strong>
                        <small><?= date('M d', strtotime($tweet['created_at'])) ?></small>
                    </div>
                    <div class="tweet-content">
                        <?= htmlspecialchars($tweet['content']) ?>
                    </div>
                    <div class="tweet-actions">
                        <button class="like-button <?= $tweet['user_liked'] ? 'liked' : '' ?>" data-tweet-id="<?= $tweet['id'] ?>">
                            ♥ <span class="like-count"><?= $tweet['like_count'] ?></span>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', function() {
                const tweetId = this.dataset.tweetId;
                fetch('like_tweet.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `tweet_id=${tweetId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('liked');
                        this.querySelector('.like-count').textContent = data.likes;
                    }
                });
            });
        });
    </script>
</body>
</html>
