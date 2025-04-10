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

    $email = $db->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT id, username, password FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }

    $db->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Twitter Clone</title>
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
        .login-container {
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
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
        .signup-link a {
            color: #1DA1F2;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign up</a>
        </div>
    </div>
</body>
</html>
