<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet_id'])) {
    $db = new mysqli('localhost', 'uyapoezipn1un', 'ytllgftvo3kn', 'dbwugogkgkyu88');
    if ($db->connect_error) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $tweet_id = $db->real_escape_string($_POST['tweet_id']);

    $check_query = "SELECT id FROM likes WHERE user_id = ? AND tweet_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $tweet_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $delete_query = "DELETE FROM likes WHERE user_id = ? AND tweet_id = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bind_param("ii", $user_id, $tweet_id);
        $delete_stmt->execute();
    } else {
        $insert_query = "INSERT INTO likes (user_id, tweet_id) VALUES (?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bind_param("ii", $user_id, $tweet_id);
        $insert_stmt->execute();
    }

    $count_query = "SELECT COUNT(*) as like_count FROM likes WHERE tweet_id = ?";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bind_param("i", $tweet_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $like_count = $count_result->fetch_assoc()['like_count'];

    $db->close();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'likes' => $like_count]);
    exit();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}
