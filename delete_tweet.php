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

    // Check if the tweet belongs to the user
    $check_query = "SELECT id FROM tweets WHERE id = ? AND user_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bind_param("ii", $tweet_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Delete the tweet
        $delete_query = "DELETE FROM tweets WHERE id = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bind_param("i", $tweet_id);
        $delete_stmt->execute();

        // Delete associated likes
        $delete_likes_query = "DELETE FROM likes WHERE tweet_id = ?";
        $delete_likes_stmt = $db->prepare($delete_likes_query);
        $delete_likes_stmt->bind_param("i", $tweet_id);
        $delete_likes_stmt->execute();

        $db->close();

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    } else {
        $db->close();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Tweet not found or not owned by user']);
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}
