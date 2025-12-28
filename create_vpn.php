<?php
session_start();
if (!isset($_SESSION['ibsng_user'])) {
    header('Location: login.php');
    exit;
}
require_once 'config.php';

// simple strict E.164 validation
function valid_e164($s) {
    return preg_match('/^\+[1-9]\d{1,14}$/', $s);
}

$phone = trim($_POST['phone'] ?? '');
$plan_id = intval($_POST['plan_id'] ?? 0);
if (!$phone || !valid_e164($phone) || $plan_id <= 0) {
    echo 'Invalid input (phone must be E.164, plan must be selected).';
    exit;
}

// fetch plan
$stmt = $connection->prepare("SELECT * FROM server_plans WHERE id = ?");
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$plan) {
    echo 'Plan not found';
    exit;
}

$server_id = intval($plan['server_id']);
$inbound_id = intval($plan['inbound_id']);
$volume = intval($plan['volume'] ?? 0);
$days = intval($plan['days'] ?? 0);
$protocol = $plan['protocol'] ?? ($plan['type'] ?? null);
$port = intval($plan['port'] ?? 0);
$remark = $phone; // use phone as remark/name

// use phone as client id (you requested phone used as user id)
$client_id = $phone;

// expiry in the microdate format used across the project (days * 86400000)
$expiry_microdate = $days > 0 ? $days * 86400000 : 0;

// call provisioning
if ($inbound_id == 0) {
    // addUser($server_id, $client_id, $protocol, $port, $expiry_microdate, $remark, $volume, $netType, $security, $rahgozar, $planId)
    $response = addUser($server_id, $client_id, $protocol, $port, $expiry_microdate, $remark, $volume, $plan['type'] ?? null, 'none', false, $plan_id);
} else {
    // addInboundAccount($server_id, $client_id, $inbound_id, $expiry_microdate, $remark, $volume, $limitip, $something, $planId)
    $response = addInboundAccount($server_id, $client_id, $inbound_id, $expiry_microdate, $remark, $volume, null, null, $plan_id);
}

if (!$response || !isset($response->success) || !$response->success) {
    echo 'Provisioning failed: ' . htmlspecialchars($response->msg ?? json_encode($response));
    exit;
}

// fetch connection links using same client id
$links = getConnectionLink($server_id, $client_id, $protocol, $remark, $port, $plan['type'] ?? null, $inbound_id, false, false, false, false);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>VPN Created</title></head>
<body>
  <h2>VPN Created</h2>
  <p>Phone / user id: <?php echo htmlspecialchars($phone); ?></p>
  <p>Plan: <?php echo htmlspecialchars($plan['name'] ?? $plan['id']); ?></p>
  <h3>Connection links / configs</h3>
  <?php
  if (is_array($links)) {
      foreach ($links as $ln) {
          echo '<div><a href="' . htmlspecialchars($ln) . '" target="_blank">' . htmlspecialchars($ln) . '</a></div>';
      }
  } elseif (is_object($links) || is_array($links)) {
      echo '<pre>' . htmlspecialchars(json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
  } else {
      echo '<pre>' . htmlspecialchars(json_encode($links)) . '</pre>';
  }
  ?>
  <p><a href="panel.php">Back</a></p>
</body>
</html>
