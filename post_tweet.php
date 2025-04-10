<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['content'])) {
    $db = new mysqli('localhost', 'uyapoezipn1un', 'ytllgftvo3kn', 'dbwugogkgkyu88');
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $content = $db->real_escape_string($_POST['content']);

    $query = "INSERT INTO tweets (user_id, content) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param("is", $user_id, $content);
    $stmt->execute();

    $db->close();
}

header("Location: index.php");
exit();
