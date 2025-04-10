<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new mysqli('localhost', 'uyapoezipn1un', 'ytllgftvo3kn', 'dbwugogkgkyu88');
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $username = $db->real_escape_string($_POST['username']);
    $email = $db->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Username or email already exists";
    } else {
        $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bind_param("sss", $username, $email, $password);

        if ($insert_stmt->execute()) {
            $_SESSION['user_id'] = $insert_stmt->insert_id;
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error creating account";
        }
    }

    $db->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Twitter Clone</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #15202B;
            color: #FFFFFF;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .signup-container {
            background-color: #192734;
            padding: 40px;
            border-radius: 15px;
            width: 300px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #38444D;
            border-radius: 5px;
            background-color: #253341;
            color: #FFFFFF;
        }
        button {
            background-color: #1DA1F2;
            color: #FFFFFF;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .error {
            color: #E0245E;
            margin-bottom: 15px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
        }
        .login-link a {
            color: #1DA1F2;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Sign Up</h1>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>
