<?php
session_start();
if (isset($_SESSION['ibsng_user'])) {
    header('Location: panel.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>Login</title></head>
<body>
  <h2>Login</h2>
  <form method="post" action="auth.php">
    <label>Username: <input name="username" required></label><br><br>
    <label>Password: <input name="password" type="password" required></label><br><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>
