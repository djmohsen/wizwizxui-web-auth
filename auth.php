<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
if ($username === '' || $password === '') {
    echo 'Missing credentials';
    exit;
}

// Configure your IBSng endpoint (use the exact endpoint you gave)
$IBSNG_ENDPOINT = 'http://secure.myvds.ir/IBSng/user/auth_V2.php';

// Do the GET as in your sample
$url = $IBSNG_ENDPOINT . '?username=' . urlencode($username) . '&password=' . urlencode($password);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
curl_setopt($ch, CURLOPT_FAILONERROR, false);
$resp = curl_exec($ch);
$err = curl_error($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($resp === false) {
    echo 'Auth request failed: ' . htmlspecialchars($err);
    exit;
}
$data = json_decode($resp, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'Invalid JSON from auth server';
    exit;
}
if (isset($data['Status']) && $data['Status'] === 'Wrong') {
    echo 'Invalid username or password';
    exit;
}
// store auth response in session
$_SESSION['ibsng_user'] = $data;
$_SESSION['ibsng_user']['username'] = $username;
session_regenerate_id(true);
header('Location: panel.php');
exit;
?>
