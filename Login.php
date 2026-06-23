<?php

require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$db = getDB();
$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $db->prepare("
        SELECT *
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        if (password_verify($password, $user['password'])) {

            if ($user['status'] != 'active') {

                $error = "Account disabled";

            } else {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit;
            }

        } else {

            $error = "Wrong password";
        }

    } else {

        $error = "User not found";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Login</title>
</head>
<body>

<h2>Inventory Login</h2>

<?php if ($error): ?>
<p style="color:red;">
    <?= htmlspecialchars($error) ?>
</p>
<?php endif; ?>

<form method="POST">

    <input
        type="text"
        name="username"
        placeholder="Username"
        required
    >

    <br><br>

    <input
        type="password"
        name="password"
        placeholder="Password"
        required
    >

    <br><br>

    <button type="submit">
        Login
    </button>

</form>

</body>
</html>