<?php
session_start();
if (!isset($_SESSION['ibsng_user'])) {
    header('Location: login.php');
    exit;
}
// include repo config to reuse addUser/addInboundAccount/getConnectionLink
require_once 'config.php';

$ibs = $_SESSION['ibsng_user'];

// Fetch available plans from server_plans table (reads DB only)
$stmt = $connection->prepare("SELECT id, server_id, inbound_id, name, price, volume, protocol, port, `type`, days FROM server_plans WHERE status = 1 ORDER BY id ASC");
$stmt->execute();
$plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>User Panel</title></head>
<body>
  <h2>User Panel</h2>
  <p>Logged in as: <?php echo htmlspecialchars($ibs['OwnerName'] ?? $ibs['UserID'] ?? $_SESSION['ibsng_user']['username']); ?></p>
  <h3>IBSng Info</h3>
  <pre><?php echo htmlspecialchars(json_encode($ibs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

  <h3>Create VPN account</h3>
  <form method="post" action="create_vpn.php">
    <label>International phone (E.164, e.g. +15551234567):<br>
      <input name="phone" required pattern="^\+[1-9]\d{1,14}$" placeholder="+15551234567">
    </label>
    <br><br>
    <label>Select plan:
      <select name="plan_id" required>
        <?php foreach ($plans as $p): ?>
          <option value="<?php echo $p['id']; ?>">
            <?php echo htmlspecialchars(($p['name'] ?? 'plan') . ' — ' . ($p['volume'] ?? 'N/A') . ' GB — ' . ($p['price'] ?? '0')); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <br><br>
    <button type="submit">Create VPN</button>
  </form>

  <p><a href="logout.php">Logout</a></p>
</body>
</html>
